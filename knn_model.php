<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

/**
 * Calculate Euclidean Distance between two instances
 */
function euclideanDistance(array $a, array $b): float {
    return sqrt(pow($a['home_score'] - $b['home_score'], 2) + pow($a['away_score'] - $b['away_score'], 2));
}

/**
 * Perform KNN Prediction
 */
function knnPredict(array $trainingData, array $testInstance, int $k): string {
    $distances = array_map(fn($instance) => euclideanDistance($instance, $testInstance), $trainingData);
    asort($distances); // Sort distances in ascending order
    $neighbors = array_slice($distances, 0, $k, true); // Get top k neighbors

    $outcomes = array_map(fn($index) => $trainingData[$index]['outcome'], array_keys($neighbors));
    $outcomeCounts = array_count_values($outcomes); // Count occurrences of each outcome
    arsort($outcomeCounts); // Sort outcomes by frequency

    return array_key_first($outcomeCounts); // Return the most common outcome
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
    if (empty($matches)) {
        throw new Exception("No match data found in JSON.");
    }

    // Prepare training data
    $trainingData = array_filter(array_map(function ($match) {
        if (isset($match['score']['home'], $match['score']['away'])) {
            return [
                'home_score' => $match['score']['home'],
                'away_score' => $match['score']['away'],
                'outcome' => $match['score']['home'] > $match['score']['away'] ? 'home_win' : 
                             ($match['score']['home'] < $match['score']['away'] ? 'away_win' : 'draw')
            ];
        }
        return null;
    }, $matches));

    if (empty($trainingData)) {
        throw new Exception("No valid training data available.");
    }

    // Get test instance from query parameters (default values if not provided)
    $home_score = isset($_GET['home_score']) ? (int)$_GET['home_score'] : 0;
    $away_score = isset($_GET['away_score']) ? (int)$_GET['away_score'] : 0;
    $testInstance = ['home_score' => $home_score, 'away_score' => $away_score];

    // Get value of k from query parameters (default: 5)
    $k = isset($_GET['k']) ? max(1, (int)$_GET['k']) : 5;

    // Predict outcome using KNN
    $prediction = knnPredict($trainingData, $testInstance, $k);

    // Output JSON response
    echo json_encode([
        'test_instance' => $testInstance,
        'k' => $k,
        'prediction' => $prediction
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
