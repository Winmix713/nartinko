<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

/**
 * Logistic Sigmoid Function
 */
function sigmoid(float $z): float {
    return 1 / (1 + exp(-$z));
}

/**
 * Predict probability using logistic regression weights
 */
function predict(array $features, array $weights): float {
    $z = $weights[0]; // Intercept (bias term)
    for ($i = 0; $i < count($features); $i++) {
        $z += $features[$i] * $weights[$i + 1];
    }
    return sigmoid($z);
}

/**
 * Train logistic regression model
 */
function train(array $trainingData, float $learningRate, int $iterations): array {
    $numFeatures = count($trainingData[0]['features']);
    $weights = array_fill(0, $numFeatures + 1, 0); // Initialize weights to 0

    for ($i = 0; $i < $iterations; $i++) {
        foreach ($trainingData as $instance) {
            $prediction = predict($instance['features'], $weights);
            $error = $instance['label'] - $prediction;
            
            // Update weights
            $weights[0] += $learningRate * $error; // Update bias term
            for ($j = 0; $j < $numFeatures; $j++) {
                $weights[$j + 1] += $learningRate * $error * $instance['features'][$j];
            }
        }
    }

    return $weights;
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
    
    if (empty($matches)) {
        throw new Exception("No match data found in JSON.");
    }

    // Filter matches by home_team and away_team if provided
    $home_team = $_GET['home_team'] ?? null;
    $away_team = $_GET['away_team'] ?? null;

    if ($home_team || $away_team) {
        $matches = array_filter($matches, function ($match) use ($home_team, $away_team) {
            return (!$home_team || strcasecmp($match['home_team'], $home_team) === 0) &&
                   (!$away_team || strcasecmp($match['away_team'], $away_team) === 0);
        });
    }

    // Prepare training data
    $trainingData = array_filter(array_map(function ($match) {
        if (isset($match['score']['home'], $match['score']['away'])) {
            return [
                'features' => [$match['score']['home'], $match['score']['away']],
                'label' => $match['score']['home'] > $match['score']['away'] ? 1 : 0
            ];
        }
        return null;
    }, $matches));

    if (empty($trainingData)) {
        throw new Exception("No valid training data available.");
    }

    // Train logistic regression model
    $learningRate = 0.01;
    $iterations = 1000;
    $weights = train($trainingData, $learningRate, $iterations);

    // Dynamic test instance from GET parameters
    $home_score = isset($_GET['home_score']) ? (int)$_GET['home_score'] : 0;
    $away_score = isset($_GET['away_score']) ? (int)$_GET['away_score'] : 0;
    $testInstance = [$home_score, $away_score];

    // Predict outcome for the test instance
    $predictionProbability = predict($testInstance, $weights);
    $binaryPrediction = $predictionProbability >= 0.5 ? 1 : 0;

    // Output response
    echo json_encode([
        'weights' => $weights,
        'test_instance' => $testInstance,
        'prediction_probability' => $predictionProbability,
        'predicted_outcome' => $binaryPrediction == 1 ? 'home_win' : 'away_win_or_draw'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
