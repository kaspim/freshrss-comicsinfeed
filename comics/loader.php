<?php

$files = array_diff(scandir(__DIR__), [basename(__FILE__), '.', '..']);
$files = array_filter($files, function ($file) {
    return pathinfo($file, PATHINFO_EXTENSION) === 'php';
});

foreach ($files as $file) {
    require_once __DIR__ . '/' . $file;
}
