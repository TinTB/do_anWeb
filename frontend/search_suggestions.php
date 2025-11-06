<?php
require_once 'config/database.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'suggestions' => []]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$search_query = "SELECT id, name, category, image FROM products 
                 WHERE (name LIKE ? OR category LIKE ?) 
                 AND stock > 0
                 ORDER BY 
                   CASE 
                     WHEN name LIKE ? THEN 1
                     WHEN category LIKE ? THEN 2
                     ELSE 3
                   END,
                   name ASC
                 LIMIT 8";
                 
$stmt = $db->prepare($search_query);
$search_term = "%$query%";
$exact_term = "$query%";
$stmt->execute([$search_term, $search_term, $exact_term, $exact_term]);

$suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'suggestions' => $suggestions]);
?>