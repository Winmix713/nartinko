<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

/**
 * Filter matches based on given parameters.
 *
 * @param array $matches Array of match data
 * @param array $params Filter parameters
 * @return array Filtered matches
 */
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

    $params = array_filter($_GET, fn($value) => $value !== '', ARRAY_FILTER_USE_BOTH);
    $params = array_map(fn($value) => filter_var($value, FILTER_SANITIZE_STRING), $params);

    $filtered_matches = filterMatches($matches, $params);

    // Sort matches by date in descending order
    usort($filtered_matches, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));

    // Pagination (optional)
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $page_size = isset($_GET['page_size']) ? max(1, (int)$_GET['page_size']) : 10;
    $total_matches = count($filtered_matches);
    $offset = ($page - 1) * $page_size;

    $paginated_matches = array_slice($filtered_matches, $offset, $page_size);

    // Return the response
    echo json_encode([
        'total_matches' => $total_matches,
        'page' => $page,
        'page_size' => $page_size,
        'matches' => array_values($paginated_matches)
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
