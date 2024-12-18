<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

class Node {
    public ?string $feature;
    public ?float $value;
    public ?Node $left;
    public ?Node $right;
    public ?int $prediction;

    public function __construct(?string $feature = null, ?float $value = null) {
        $this->feature = $feature;
        $this->value = $value;
        $this->left = null;
        $this->right = null;
        $this->prediction = null;
    }
}

/**
 * Build a decision tree based on the provided training data.
 */
function buildTree(array $data, int $depth = 0, int $maxDepth = 3): Node {
    if ($depth >= $maxDepth || count($data) <= 1) {
        return createLeafNode($data);
    }

    $bestSplit = findBestSplit($data);
    if ($bestSplit === null) {
        return createLeafNode($data);
    }

    $node = new Node($bestSplit['feature'], $bestSplit['value']);
    $leftData = array_filter($data, fn($instance) => $instance['features'][$bestSplit['feature']] <= $bestSplit['value']);
    $rightData = array_filter($data, fn($instance) => $instance['features'][$bestSplit['feature']] > $bestSplit['value']);

    $node->left = buildTree($leftData, $depth + 1, $maxDepth);
    $node->right = buildTree($rightData, $depth + 1, $maxDepth);

    return $node;
}

function createLeafNode(array $data): Node {
    $node = new Node();
    $node->prediction = mostCommonOutcome($data);
    return $node;
}

function findBestSplit(array $data): ?array {
    if (empty($data) || !isset($data[0]['features'])) {
        return null;
    }

    $bestGini = 1;
    $bestSplit = null;
    $features = array_keys($data[0]['features']);

    foreach ($features as $feature) {
        $values = array_unique(array_column(array_column($data, 'features'), $feature));
        foreach ($values as $value) {
            $gini = calculateGini($data, $feature, $value);
            if ($gini < $bestGini) {
                $bestGini = $gini;
                $bestSplit = ['feature' => $feature, 'value' => $value];
            }
        }
    }

    return $bestSplit;
}

function calculateGini(array $data, string $feature, $value): float {
    $left = array_filter($data, fn($instance) => $instance['features'][$feature] <= $value);
    $right = array_filter($data, fn($instance) => $instance['features'][$feature] > $value);

    if (count($left) === 0 || count($right) === 0) {
        return 1;
    }

    $giniLeft = calculateGiniImpurity($left);
    $giniRight = calculateGiniImpurity($right);

    return (count($left) / count($data)) * $giniLeft + (count($right) / count($data)) * $giniRight;
}

function calculateGiniImpurity(array $data): float {
    $labelCounts = array_count_values(array_column($data, 'label'));
    $impurity = 1;
    $totalCount = count($data);

    foreach ($labelCounts as $count) {
        $probability = $count / $totalCount;
        $impurity -= $probability ** 2;
    }

    return $impurity;
}

function mostCommonOutcome(array $data): ?int {
    if (empty($data)) {
        return null;
    }

    $outcomes = array_count_values(array_column($data, 'label'));
    return array_search(max($outcomes), $outcomes);
}

function predict(Node $tree, array $instance): ?int {
    if ($tree->prediction !== null) {
        return $tree->prediction;
    }

    if (!isset($instance['features'][$tree->feature])) {
        return null;
    }

    if ($instance['features'][$tree->feature] <= $tree->value) {
        return predict($tree->left, $instance);
    } else {
        return predict($tree->right, $instance);
    }
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

    // Filter matches by home_team and away_team if specified
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
                'features' => [
                    'home_score' => $match['score']['home'],
                    'away_score' => $match['score']['away']
                ],
                'label' => $match['score']['home'] > $match['score']['away'] ? 1 : 0
            ];
        }
        return null;
    }, $matches));

    if (empty($trainingData)) {
        throw new Exception("No valid training data available.");
    }

    $tree = buildTree($trainingData);

    // Accept dynamic test instance via GET
    $home_score = isset($_GET['home_score']) ? (int)$_GET['home_score'] : 0;
    $away_score = isset($_GET['away_score']) ? (int)$_GET['away_score'] : 0;

    $testInstance = ['features' => ['home_score' => $home_score, 'away_score' => $away_score]];
    $prediction = predict($tree, $testInstance);

    echo json_encode([
        'test_instance' => $testInstance,
        'prediction' => $prediction,
        'note' => $prediction === 1 ? 'Home win' : 'Away win or draw'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
