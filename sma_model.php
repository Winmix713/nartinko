<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

/**
 * Calculate Simple Moving Average (SMA)
 */
function calculateSMA(array $data, int $period): array {
    $sma = [];

    if ($period <= 0) {
        throw new InvalidArgumentException("Period must be greater than 0.");
    }

    if (count($data) < $period) {
        return $sma; // Not enough data points for the period
    }

    for ($i = $period - 1; $i < count($data); $i++) {
        $sum = array_sum(array_slice($data, $i - $period + 1, $period));
        $sma[] = $sum / $period;
    }

    return $sma;
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

    // Filter matches by home_team if provided
    $home_team = $_GET['home_team'] ?? null;
    if ($home_team) {
        $matches = array_filter($matches, function ($match) use ($home_team) {
            return strcasecmp($match['home_team'], $home_team) === 0;
        });
    }

    // Extract home scores
    $home_scores = array_column(array_column($matches, 'score'), 'home');

    // Get period from query parameters (default: 5)
    $period = isset($_GET['period']) ? max(1, (int)$_GET['period']) : 5;

    // Calculate SMA
    $sma_home = calculateSMA($home_scores, $period);

    // Output JSON response
    echo json_encode([
        'sma_home' => $sma_home,
        'raw_data' => $home_scores,
        'period' => $period,
        'team_filter' => $home_team
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
