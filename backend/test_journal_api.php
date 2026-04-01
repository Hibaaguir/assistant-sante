#!/usr/bin/env php
<?php

// Test the journal API with sample data

$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0Ojgwb'; // Replace with actual token

$data = [
    'entry_date' => date('Y-m-d'),
    'sleep' => 7,
    'stress' => 5,
    'energy' => 8,
    'sugar' => 'low',
    'caffeine' => 2,
    'hydration' => 2.5,
    'meals' => [
        ['type' => 'breakfast', 'label' => 'Omelette et pain'],
        ['type' => 'lunch', 'label' => 'Poulet et riz'],
    ],
    'calories' => 1500,
    'activity_type' => 'Marche',
    'activity_duration' => 30,
    'intensity' => 'medium',
    'tobacco' => true,
    'alcohol' => false,
    'tobacco_types' => ['cigarette' => true, 'vape' => false],
    'cigarettes_per_day' => 5,
    'vape_frequency' => null,
    'vape_liquid_ml' => null,
    'alcohol_drinks' => 0,
];

echo "Sending test data to API...\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
