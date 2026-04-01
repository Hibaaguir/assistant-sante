<?php
require 'vendor/autoload.php';

$config = require 'config/database.php';
$mysql = $config['connections']['mysql'];

try {
    $pdo = new PDO(
        "mysql:host={$mysql['host']};port={$mysql['port']};dbname={$mysql['database']}",
        $mysql['username'],
        $mysql['password']
    );
    
    // Check journal_quotidien entries
    $stmt = $pdo->query("SELECT * FROM journal_quotidien ORDER BY id DESC LIMIT 5");
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'journal_entries' => $entries,
        'count' => count($entries),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
