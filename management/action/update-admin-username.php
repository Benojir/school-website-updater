<?php
require_once("../../includes/config.php");
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $admin_id = $_SESSION['user']['id'];
        $newUsername = trim($_POST['newUsername'] ?? '');
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $adminName = trim($_POST['adminName'] ?? 'Super Admin'); // Optional field for admin name 'Super Admin' is default
        
        // Validate inputs
        if (empty($newUsername) || empty($confirmPassword)) {
            throw new Exception('All fields are required');
        }
        
        if (strlen($newUsername) < 3 || strlen($newUsername) > 25) {
            throw new Exception('Username must be 3-25 characters long');
        }
        
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
        
        if (!password_verify($confirmPassword, $admin['password'])) {
            throw new Exception('Current password is incorrect');
        }
        
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$newUsername, $admin_id]);
        if ($stmt->fetch()) {
            throw new Exception('Username already exists');
        }
        
        // Update username
        $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ? WHERE id = ?");
        $stmt->execute([$newUsername, $adminName, $admin_id]);

        // Update session if needed
        $_SESSION['user']['username'] = $newUsername;
        $_SESSION['user']['full_name'] = $adminName;

        echo json_encode([
            'success' => true,
            'message' => 'Username updated successfully!'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}