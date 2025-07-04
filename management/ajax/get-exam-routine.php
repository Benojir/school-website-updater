<?php
require_once("../../includes/db.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$exam_id = $_GET['exam_id'] ?? '';
$class_id = $_GET['class_id'] ?? '';

if (empty($exam_id) || empty($class_id)) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT * FROM exam_routines 
        WHERE exam_id = ? AND class_id = ?
    ");
    $stmt->execute([$exam_id, $class_id]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$subjects) {
        echo json_encode(['success' => false, 'message' => 'No exam routine found for the given exam and class']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $subjects
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}