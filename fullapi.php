<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$json_file = 'combined_matches.json';

function calculateBothTeamsScoredPercentage($matches) {
    $bothTeamsScoredCount = array_reduce($matches, function($count, $match) {
        return $count + (($match['score']['home'] > 0 && $match['score']['away'] > 0) ? 1 : 0);
    }, 0);
    return count($matches) > 0 ? round(($bothTeamsScoredCount / count($matches)) * 100, 2) : 0;
}

function calculateAverageGoals($matches) {
    $totalGoals = array_reduce($matches, function($sum, $match) {
        return $sum + $match['score']['home'] + $match['score']['away'];
    }, 0);
    $homeGoals = array_reduce($matches, function($sum, $match) {
        return $sum + $match['score']['home'];
    }, 0);
    $awayGoals = array_reduce($matches, function($sum, $match) {
        return $sum + $match['score']['away'];
    }, 0);
    $totalMatches = count($matches);
    return [
        'average_total_goals' => $totalMatches > 0 ? round($totalGoals / $totalMatches, 2) : 0,
        'average_home_goals' => $totalMatches > 0 ? round($homeGoals / $totalMatches, 2) : 0,
        'average_away_goals' => $totalMatches > 0 ? round($awayGoals / $totalMatches, 2) : 0
    ];
}

function calculateFormIndex($matches, $team, $recentGames = 5) {
    $teamMatches = array_filter($matches, function ($match) use ($team) {
        return strcasecmp($match['home_team'], $team) === 0 || strcasecmp($match['away_team'], $team) === 0;
    });
    $teamMatches = array_slice(array_values($teamMatches), 0, $recentGames);

    $points = array_reduce($teamMatches, function($sum, $match) use ($team) {
        if (strcasecmp($match['home_team'], $team) === 0) {
            if ($match['score']['home'] > $match['score']['away']) return $sum + 3;
            if ($match['score']['home'] === $match['score']['away']) return $sum + 1;
        } else {
            if ($match['score']['away'] > $match['score']['home']) return $sum + 3;
            if ($match['score']['away'] === $match['score']['home']) return $sum + 1;
        }
        return $sum;
    }, 0);

    return $recentGames > 0 ? round($points / ($recentGames * 3) * 100, 2) : 0;
}

function calculateHeadToHeadStats($matches) {
    $homeWins = 0;
    $awayWins = 0;
    $draws = 0;

    foreach ($matches as $match) {
        if ($match['score']['home'] > $match['score']['away']) {
            $homeWins++;
        } elseif ($match['score']['home'] < $match['score']['away']) {
            $awayWins++;
        } else {
            $draws++;
        }
    }

    $totalMatches = count($matches);
    return [
        'home_wins' => $homeWins,
        'away_wins' => $awayWins,
        'draws' => $draws,
        'home_win_percentage' => $totalMatches > 0 ? round(($homeWins / $totalMatches) * 100, 2) : 0,
        'away_win_percentage' => $totalMatches > 0 ? round(($awayWins / $totalMatches) * 100, 2) : 0,
        'draw_percentage' => $totalMatches > 0 ? round(($draws / $totalMatches) * 100, 2) : 0
    ];
}

function filterMatches(array $matches, array $params): array {
    return array_filter($matches, function($match) use ($params) {
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'team':
                    if (!matchesTeam($match, $value)) return false;
                    break;
                case 'home_team':
                    if (!matchesHomeTeam($match, $value)) return false;
                    break;
                case 'away_team':
                    if (!matchesAwayTeam($match, $value)) return false;
                    break;
                case 'date':
                    if (!matchesDate($match, $value)) return false;
                    break;
                case (strpos($key, 'score_') !== false):
                    if (!matchesScore($match, $key, $value)) return false;
                    break;
                case 'both_teams_scored':
                    if (!matchesBothTeamsScored($match, $value)) return false;
                    break;
                default:
                    if (!matchesDefault($match, $key, $value)) return false;
            }
        }
        return true;
    });
}

function matchesTeam(array $match, string $value): bool {
    return strcasecmp($match['home_team'], $value) === 0 || strcasecmp($match['away_team'], $value) === 0;
}

function matchesHomeTeam(array $match, string $value): bool {
    return strcasecmp($match['home_team'], $value) === 0;
}

function matchesAwayTeam(array $match, string $value): bool {
    return strcasecmp($match['away_team'], $value) === 0;
}

function matchesDate(array $match, string $value): bool {
    $match_date = new DateTime($match['date']);
    $param_date = new DateTime($value);
    return $match_date >= $param_date;
}

function matchesScore(array $match, string $key, string $value): bool {
    $score_type = str_replace('score_', '', $key);
    return isset($match['score'][$score_type]) && $match['score'][$score_type] == $value;
}

function matchesBothTeamsScored(array $match, string $value): bool {
    $both_scored = ($match['score']['home'] > 0 && $match['score']['away'] > 0);
    return $both_scored == filter_var($value, FILTER_VALIDATE_BOOLEAN);
}

function matchesDefault(array $match, string $key, string $value): bool {
    return isset($match[$key]) && strcasecmp($match[$key], $value) === 0;
}

function getAvailableTeams($matches) {
    $teams = [];
    foreach ($matches as $match) {
        if (isset($match['home_team']) && !empty($match['home_team'])) {
            $teams[] = $match['home_team'];
        }
        if (isset($match['away_team']) && !empty($match['away_team'])) {
            $teams[] = $match['away_team'];
        }
    }
    return array_unique(array_filter($teams));
}

function calculateExpectedGoals($team, $matches) {
    $totalGoals = 0;
    $matchCount = 0;
    foreach ($matches as $match) {
        if ($match['home_team'] == $team) {
            $totalGoals += $match['score']['home'];
        } elseif ($match['away_team'] == $team) {
            $totalGoals += $match['score']['away'];
        }
        $matchCount++;
    }
    return $matchCount > 0 ? $totalGoals / $matchCount : 0;
}

function calculateBothTeamsToScoreProb($matches) {
    $bothScoredCount = 0;
    foreach ($matches as $match) {
        if ($match['score']['home'] > 0 && $match['score']['away'] > 0) {
            $bothScoredCount++;
        }
    }
    return count($matches) > 0 ? ($bothScoredCount / count($matches)) * 100 : 0;
}

function predictWinner($homeTeam, $awayTeam, $matches) {
    $homeWins = 0;
    $awayWins = 0;
    $draws = 0;
    foreach ($matches as $match) {
        if ($match['home_team'] == $homeTeam && $match['away_team'] == $awayTeam) {
            if ($match['score']['home'] > $match['score']['away']) {
                $homeWins++;
            } elseif ($match['score']['home'] < $match['score']['away']) {
                $awayWins++;
            } else {
                $draws++;
            }
        }
    }
    $totalMatches = $homeWins + $awayWins + $draws;
    if ($totalMatches == 0) return ['winner' => 'unknown', 'confidence' => 0];
    
    if ($homeWins > $awayWins) {
        return ['winner' => 'home', 'confidence' => $homeWins / $totalMatches];
    } elseif ($awayWins > $homeWins) {
        return ['winner' => 'away', 'confidence' => $awayWins / $totalMatches];
    } else {
        return ['winner' => 'draw', 'confidence' => $draws / $totalMatches];
    }
}

function runPrediction($homeTeam, $awayTeam, $matches) {
    $homeExpectedGoals = calculateExpectedGoals($homeTeam, $matches);
    $awayExpectedGoals = calculateExpectedGoals($awayTeam, $matches);
    $bothTeamsToScoreProb = calculateBothTeamsToScoreProb($matches);
    $winnerPrediction = predictWinner($homeTeam, $awayTeam, $matches);

    return [
        'homeExpectedGoals' => $homeExpectedGoals,
        'awayExpectedGoals' => $awayExpectedGoals,
        'bothTeamsToScoreProb' => $bothTeamsToScoreProb,
        'predictedWinner' => $winnerPrediction['winner'],
        'confidence' => $winnerPrediction['confidence'],
        'modelPredictions' => [
            'randomForest' => $winnerPrediction['winner'] . '_win',
            'poisson' => [
                'homeGoals' => round($homeExpectedGoals),
                'awayGoals' => round($awayExpectedGoals)
            ],
            'elo' => [
                'homeWinProb' => $winnerPrediction['winner'] == 'home' ? $winnerPrediction['confidence'] : (1 - $winnerPrediction['confidence']) / 2,
                'drawProb' => $winnerPrediction['winner'] == 'draw' ? $winnerPrediction['confidence'] : (1 - $winnerPrediction['confidence']) / 3,
                'awayWinProb' => $winnerPrediction['winner'] == 'away' ? $winnerPrediction['confidence'] : (1 - $winnerPrediction['confidence']) / 2
            ]
        ]
    ];
}

try {
    if (!file_exists($json_file)) {
        throw new Exception("JSON file not found");
    }

    $json_data = file_get_contents($json_file);
    if ($json_data === false) {
        throw new Exception("Failed to read JSON file");
    }

    $data = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }

    $matches = $data['matches'] ?? [];

    $params = array_filter($_GET, fn($value) => $value !== '', ARRAY_FILTER_USE_BOTH);
    $params = array_map(fn($value) => filter_var($value, FILTER_SANITIZE_STRING), $params);

    $filtered_matches = filterMatches($matches, $params);

    usort($filtered_matches, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $page_size = isset($_GET['page_size']) ? max(1, (int)$_GET['page_size']) : 10;
    $total_matches = count($filtered_matches);
    $offset = ($page - 1) * $page_size;

    $paginated_matches = array_slice($filtered_matches, $offset, $page_size);

    $homeTeam = $params['home_team'] ?? '';
    $awayTeam = $params['away_team'] ?? '';
    $teamAnalysis = null;
    if (!empty($homeTeam) && !empty($awayTeam)) {
        $teamAnalysisMatches = array_filter($filtered_matches, function ($match) use ($homeTeam, $awayTeam) {
            return (strcasecmp($match['home_team'], $homeTeam) === 0 && strcasecmp($match['away_team'], $awayTeam) === 0) ||
                (strcasecmp($match['home_team'], $awayTeam) === 0 && strcasecmp($match['away_team'], $homeTeam) === 0);
        });

        $bothTeamsScoredPercentage = calculateBothTeamsScoredPercentage($teamAnalysisMatches);
        $averageGoals = calculateAverageGoals($teamAnalysisMatches);
        $homeFormIndex = calculateFormIndex($filtered_matches, $homeTeam);
        $awayFormIndex = calculateFormIndex($filtered_matches, $awayTeam);
        $headToHeadStats = calculateHeadToHeadStats($teamAnalysisMatches);

        $teamAnalysis = [
            'home_team' => $homeTeam,
            'away_team' => $awayTeam,
            'matches_count' => count($teamAnalysisMatches),
            'both_teams_scored_percentage' => $bothTeamsScoredPercentage,
            'average_goals' => $averageGoals,
            'home_form_index' => $homeFormIndex,
            'away_form_index' => $awayFormIndex,
            'head_to_head_stats' => $headToHeadStats
        ];
    }
     $prediction = null;
     if(!empty($homeTeam) && !empty($awayTeam)){
        $prediction = runPrediction($homeTeam, $awayTeam, $filtered_matches);
     }

    echo json_encode([
        'total_matches' => $total_matches,
        'page' => $page,
        'page_size' => $page_size,
        'matches' => array_values($paginated_matches),
        'team_analysis' => $teamAnalysis,
         'prediction' => $prediction
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}