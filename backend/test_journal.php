#!/usr/bin/env php
<?php

// Test script to call journal API and see error message

$today = date('Y-m-d');

$data = [
    'entry_date' => $today,
    'sleep' => 7,
    'stress' => 5,
    'energy' => 8,
    'sugar' => 'low',
    'caffeine' => 2,
    'hydration' => 2.5,
    'meals' => [
        ['type' => 'breakfast', 'label' => 'Omelette et pain'],
    ],
    'calories' => 500,
    'activity_type' => 'Marche',
    'activity_duration' => 30,
    'intensity' => 'medium',
    'tobacco' => false,
    'alcohol' => false,
    'tobacco_types' => ['cigarette' => false, 'vape' => false],
    'cigarettes_per_day' => null,
    'vape_frequency' => null,
    'vape_liquid_ml' => null,
    'alcohol_drinks' => 0,
];

echo "Test data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

// Test with curl
$ch = curl_init('http://localhost:8000/api/journal');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpcode\n";
echo "Response:\n$response\n";
