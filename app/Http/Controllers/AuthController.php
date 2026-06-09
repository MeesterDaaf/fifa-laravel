<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Bot-accounts kunnen niet inloggen.
        $candidate = \App\Models\User::where('email', $request->email)->first();
        if ($candidate?->is_bot) {
            return back()->withErrors(['email' => 'Dit account kan niet inloggen.'])->withInput();
        }

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect('/');
        }

        return back()->withErrors(['email' => 'Ongeldig e-mailadres of wachtwoord.'])->withInput();
    }

    public function registerForm(Request $request)
    {
        $code = $request->query('code', '');
        return view('auth.register', compact('code'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users',
            'password'     => 'required|min:6|confirmed',
            'invite_code'  => 'required',
        ]);

        if ($request->invite_code !== Setting::inviteCode()) {
            return back()->withErrors(['invite_code' => 'Ongeldige uitnodigingscode.'])->withInput();
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);
        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function forgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Bot-accounts hebben geen wachtwoord en kunnen geen reset aanvragen.
        $candidate = User::where('email', $request->email)->first();
        if ($candidate?->is_bot) {
            return back()->withErrors(['email' => 'Dit account kan geen wachtwoord resetten.'])->withInput();
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors(['email' => 'Je hebt net al een resetlink aangevraagd. Wacht even en probeer het opnieuw.'])->withInput();
        }

        // Geen onderscheid tussen wel/niet bestaand e-mailadres (voorkomt account-enumeratie).
        return back()->with('status', 'Als dit e-mailadres bij ons bekend is, ontvang je zo een link om je wachtwoord te resetten. ✉️');
    }

    public function resetPasswordForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect('/login')->with('status', 'Je wachtwoord is opnieuw ingesteld. Log nu in met je nieuwe wachtwoord. ✅');
        }

        $messages = [
            Password::INVALID_TOKEN => 'Deze resetlink is ongeldig of verlopen. Vraag een nieuwe aan.',
            Password::INVALID_USER  => 'We konden geen account met dit e-mailadres vinden.',
        ];

        return back()->withErrors(['email' => $messages[$status] ?? 'Er ging iets mis bij het resetten. Probeer het opnieuw.'])->withInput();
    }
}
