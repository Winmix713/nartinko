<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

/**
 * Calculate Poisson probability for `k` goals given a mean `lambda`.
 */
function poissonProbability(float $lambda, int $k): float {
    return (pow($lambda, $k) * exp(-$lambda)) / factorial($k);
}

/**
 * Calculate factorial of a number.
 */
function factorial(int $n): int {
    if ($n === 0) return 1;
    $result = 1;
    for ($i = 1; $i <= $n; $i++) {
        $result *= $i;
    }
    return $result;
}

try {
    // Load JSON data
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
    $num_matches = count($matches);
    
    if ($num_matches === 0) {
        throw new Exception("No matches data found in JSON.");
    }

    // Filter matches by home_team and away_team if specified
    $home_team = $_GET['home_team'] ?? null;
    $away_team = $_GET['away_team'] ?? null;

    if ($home_team || $away_team) {
        $matches = array_filter($matches, function ($match) use ($home_team, $away_team) {
            return (!$home_team || strcasecmp($match['home_team'], $home_team) === 0) &&
                   (!$away_team || strcasecmp($match['away_team'], $away_team) === 0);
        });
        $num_matches = count($matches);

        if ($num_matches === 0) {
            throw new Exception("No matches found for the specified teams.");
        }
    }

    // Calculate average goals
    $total_home_goals = array_sum(array_column(array_column($matches, 'score'), 'home'));
    $total_away_goals = array_sum(array_column(array_column($matches, 'score'), 'away'));

    $avg_home_goals = $total_home_goals / $num_matches;
    $avg_away_goals = $total_away_goals / $num_matches;

    // Set max goals for probability calculation (default: 5)
    $max_goals = isset($_GET['max_goals']) ? max(1, (int)$_GET['max_goals']) : 5;
    $probabilities = [];

    // Calculate Poisson probabilities for each scoreline
    for ($home = 0; $home <= $max_goals; $home++) {
        for ($away = 0; $away <= $max_goals; $away++) {
            $prob_home = poissonProbability($avg_home_goals, $home);
            $prob_away = poissonProbability($avg_away_goals, $away);
            $probabilities["$home-$away"] = $prob_home * $prob_away;
        }
    }

    // Output JSON response
    echo json_encode([
        'average_home_goals' => $avg_home_goals,
        'average_away_goals' => $avg_away_goals,
        'team_filter' => [
            'home_team' => $home_team,
            'away_team' => $away_team
        ],
        'max_goals' => $max_goals,
        'probabilities' => $probabilities
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
