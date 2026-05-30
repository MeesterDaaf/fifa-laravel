<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use Illuminate\Database\Eloquent\Collection;

class GroepenController extends Controller
{
    public function index()
    {
        $fixtures = Fixture::where('stage', 'GROUP_STAGE')
            ->whereNotNull('match_group')
            ->orderBy('scheduled_at')
            ->get();

        $groups = $fixtures->groupBy('match_group')->sortKeys();

        $overview = [];
        $thirds = [];
        $allComplete = $groups->isNotEmpty();

        foreach ($groups as $key => $matches) {
            $standing = $this->standingFor($matches);
            $complete = $matches->isNotEmpty() && $matches->every(fn ($m) => $m->isFinished());
            if (! $complete) {
                $allComplete = false;
            }

            $overview[$key] = ['matches' => $matches, 'standing' => $standing, 'complete' => $complete];

            if ($complete && isset($standing[2])) {
                $thirds[] = ['group' => $key] + $standing[2];
            }
        }

        // Kwalificatie: top 2 van elke afgeronde groep gaat direct door.
        $qualified = [];
        foreach ($overview as $data) {
            if ($data['complete']) {
                foreach (array_slice($data['standing'], 0, 2) as $row) {
                    $qualified[$row['code']] = 'direct';
                }
            }
        }

        // De 8 beste nummers 3 gaan ook door — pas te bepalen als álle groepen klaar zijn.
        if ($allComplete && count($thirds) >= 8) {
            usort($thirds, fn ($x, $y) => [$y['points'], $y['gf'] - $y['ga'], $y['gf']]
                <=> [$x['points'], $x['gf'] - $x['ga'], $x['gf']]);
            foreach (array_slice($thirds, 0, 8) as $t) {
                $qualified[$t['code']] = 'third';
            }
        }

        return view('groepen.index', compact('overview', 'qualified'));
    }

    /** Berekent de stand van een groep met de officiële FIFA-volgorde. */
    private function standingFor(Collection $matches): array
    {
        $table = [];

        $ensure = function (array &$table, ?string $code, ?string $name) {
            if ($code && ! isset($table[$code])) {
                $table[$code] = [
                    'code' => $code, 'name' => $name,
                    'played' => 0, 'won' => 0, 'draw' => 0, 'lost' => 0,
                    'gf' => 0, 'ga' => 0, 'points' => 0,
                ];
            }
        };

        foreach ($matches as $m) {
            $ensure($table, $m->home_team_code, $m->home_team);
            $ensure($table, $m->away_team_code, $m->away_team);

            if (! $m->isFinished() || $m->home_score === null || $m->away_score === null) {
                continue;
            }

            $h = &$table[$m->home_team_code];
            $a = &$table[$m->away_team_code];

            $h['played']++; $a['played']++;
            $h['gf'] += $m->home_score; $h['ga'] += $m->away_score;
            $a['gf'] += $m->away_score; $a['ga'] += $m->home_score;

            if ($m->home_score > $m->away_score) {
                $h['won']++; $a['lost']++; $h['points'] += 3;
            } elseif ($m->home_score < $m->away_score) {
                $a['won']++; $h['lost']++; $a['points'] += 3;
            } else {
                $h['draw']++; $a['draw']++; $h['points']++; $a['points']++;
            }
            unset($h, $a);
        }

        $rows = array_values($table);

        // 1) Algemeen: punten → doelsaldo → doelpunten.
        usort($rows, fn ($x, $y) => [$y['points'], $y['gf'] - $y['ga'], $y['gf']]
            <=> [$x['points'], $x['gf'] - $x['ga'], $x['gf']]);

        // 2) Gelijke teams onderling scheiden via head-to-head.
        return $this->breakTies($rows, $matches);
    }

    /** Herordent clusters van gelijk-geëindigde teams op onderling resultaat. */
    private function breakTies(array $rows, Collection $matches): array
    {
        $result = [];
        $i = 0;
        $n = count($rows);

        while ($i < $n) {
            $j = $i;
            while ($j + 1 < $n && $this->sameOverall($rows[$j], $rows[$j + 1])) {
                $j++;
            }

            $cluster = array_slice($rows, $i, $j - $i + 1);
            if (count($cluster) > 1) {
                $cluster = $this->orderByHeadToHead($cluster, $matches);
            }
            array_push($result, ...$cluster);
            $i = $j + 1;
        }

        return $result;
    }

    private function sameOverall(array $x, array $y): bool
    {
        return $x['points'] === $y['points']
            && ($x['gf'] - $x['ga']) === ($y['gf'] - $y['ga'])
            && $x['gf'] === $y['gf'];
    }

    private function orderByHeadToHead(array $cluster, Collection $matches): array
    {
        $codes = array_column($cluster, 'code');
        $h2h = [];
        foreach ($codes as $c) {
            $h2h[$c] = ['points' => 0, 'gf' => 0, 'ga' => 0];
        }

        foreach ($matches as $m) {
            if (! $m->isFinished() || $m->home_score === null) {
                continue;
            }
            if (! in_array($m->home_team_code, $codes, true) || ! in_array($m->away_team_code, $codes, true)) {
                continue;
            }

            $h2h[$m->home_team_code]['gf'] += $m->home_score;
            $h2h[$m->home_team_code]['ga'] += $m->away_score;
            $h2h[$m->away_team_code]['gf'] += $m->away_score;
            $h2h[$m->away_team_code]['ga'] += $m->home_score;

            if ($m->home_score > $m->away_score) {
                $h2h[$m->home_team_code]['points'] += 3;
            } elseif ($m->home_score < $m->away_score) {
                $h2h[$m->away_team_code]['points'] += 3;
            } else {
                $h2h[$m->home_team_code]['points']++;
                $h2h[$m->away_team_code]['points']++;
            }
        }

        usort($cluster, function ($x, $y) use ($h2h) {
            $hx = $h2h[$x['code']];
            $hy = $h2h[$y['code']];
            return ([$hy['points'], $hy['gf'] - $hy['ga'], $hy['gf']]
                <=> [$hx['points'], $hx['gf'] - $hx['ga'], $hx['gf']])
                ?: strcmp($x['name'] ?? '', $y['name'] ?? '');
        });

        return $cluster;
    }
}
