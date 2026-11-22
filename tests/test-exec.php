<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$file = __DIR__ . '/info.php';
$iterations = 200; // Change to 200 for warm testing
$times = [];

if (!is_file($file)) {
    die("File not found: $file");
}

// Cache statistics before execution
$statsBefore = gc_cache_stats();

ob_start();

try {
    for ($i = 1; $i <= $iterations; $i++) {
        $start = hrtime(true);

        include $file;

        $ms = (hrtime(true) - $start) / 1e6;

        ob_clean();

        $times[] = $ms;
    }
} finally {
    ob_end_clean();
}

// Cache statistics after execution
$statsAfter = gc_cache_stats();

echo "<pre>";

// Cold execution
if (count($times) > 0) {
    printf("Cold execution : %.3f ms\n", $times[0]);
}

// Warm execution (skip first 10 iterations)
$warm = array_slice($times, 10);
sort($warm);

$count = count($warm);

if ($count > 0) {
    $avg = array_sum($warm) / $count;
    $median = $warm[(int) floor($count * 0.5)];
    $p95 = $warm[(int) floor(($count - 1) * 0.95)];
    $min = min($warm);
    $max = max($warm);

    printf("Warm average   : %.3f ms\n", $avg);
    printf("Warm median    : %.3f ms\n", $median);
    printf("Warm P95       : %.3f ms\n", $p95);
    printf("Warm min       : %.3f ms\n", $min);
    printf("Warm max       : %.3f ms\n", $max);
} else {
    echo "Warm statistics: Not enough iterations\n";
}

echo "\nCache statistics before:\n";
print_r($statsBefore);

echo "\nCache statistics after:\n";
print_r($statsAfter);

echo "</pre>";
