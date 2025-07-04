<?php
// Check if user is logged in
include_once("../../includes/auth-check.php");
include_once("../../includes/permission-check.php");

header('Content-Type: application/json');

// Check if user has permission to perform this action
if (!hasPermission(PERM_MANAGE_NOTICES)) {
    echo json_encode([
        'success' => false,
        'message' => 'You do not have permission to perform this action.'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $notice_date = trim($_POST['notice_date'] ?? '');

    // Validate inputs
    if (empty($title) || empty($content) || empty($notice_date)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }

    // Additional validation
    if (strlen($title) > 255) {
        echo json_encode(['success' => false, 'message' => 'Title is too long (max 255 characters)']);
        exit();
    }

    if (!strtotime($notice_date)) {
        echo json_encode(['success' => false, 'message' => 'Invalid date format']);
        exit();
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Insert notice
        $stmt = $pdo->prepare("INSERT INTO notices (title, content, notice_date) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $notice_date]);

        // Get FCM tokens
        $stmt = $pdo->prepare("SELECT fcm_token FROM parent_mobile_sessions");
        $stmt->execute();
        $fcm_tokens = $stmt->fetchAll();

        // Send notifications
        $notification_title = $title . " (" . $notice_date . ")";
        $notification_sent = true;
        try {
            sendFirebaseNotification($fcm_tokens, $notification_title, $content);
        } catch (Exception $e) {
            $notification_sent = false;
            // Log this error if you have a logging system
            // error_log("Failed to send notification: " . $e->getMessage());
        }

        $pdo->commit();

        $response = [
            'success' => true,
            'message' => 'Notice added successfully'
        ];
        if (!$notification_sent) {
            $response['notification_sent'] = false;
            $response['message'] .= ' (but notification failed to send)';
        }

        echo json_encode($response);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>