<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

try {

    // Prepare and execute query
    $stmt = $pdo->prepare("SELECT * FROM notices ORDER BY notice_date DESC, created_at DESC LIMIT 30");
    $stmt->execute();

    $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $table_data_html = '';

    if (empty($notices)) {
        echo json_encode([
            'success' => true,
            'html' => '<tr><td colspan="4" class="text-center">No notices found.</td></tr>'
        ]);
        exit();
    }
    
    foreach ($notices as &$notice) {
        $notice_id = $notice['id'];
        $formatted_date = date('d M Y', strtotime($notice['notice_date']));
        $notice_title = safe_htmlspecialchars($notice['title']);
        $notice_content = substr(strip_tags($notice['content']), 0, 100) . '...'; // Limit to 100 characters
        $table_data_html .= '<tr>
                                <td>' . $formatted_date . '</td>
                                <td>' . $notice_title . '</td>
                                <td>' . $notice_content . '</td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-notice" onclick="editNotice(' . $notice_id . ')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-notice" onclick="deleteNotice(' . $notice_id . ')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>';
    }

    // Return response
    echo json_encode([
        'success' => true,
        'html' => $table_data_html
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
