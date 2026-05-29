<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
}
