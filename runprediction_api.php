<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

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
    $json_file = 'combined_matches.json';
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

    $homeTeam = $_GET['home_team'] ?? '';
    $awayTeam = $_GET['away_team'] ?? '';

    if (empty($homeTeam) || empty($awayTeam)) {
        throw new Exception("Both home_team and away_team must be provided");
    }

    $prediction = runPrediction($homeTeam, $awayTeam, $matches);
    echo json_encode($prediction, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}