<?php
// Check if user is logged in
include_once("../../includes/auth-check.php");
include_once("../../includes/permission-check.php");
require_once("../../includes/db.php");
require_once("../../includes/functions.php");

header('Content-Type: application/json');

// Check if user is superadmin only
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['user']['role'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

// Validate all required fields
$requiredFields = [
    'super_admin_email', 
    'country_code', 
    'imgbb_api_key', 
    'captcha_site_key', 
    'captcha_secret_key', 
    'otp_gateway', 
    'razorpay_key_id', 
    'razorpay_key_secret', 
    'razorpay_charge_percentage', 
    'gst_on_razorpay_charge', 
    'smtp_server', 
    'smtp_username', 
    'smtp_password'
];

foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "All fields are required"]);
        exit();
    }
}

if (strlen($_POST['country_code']) > 5) {
    echo json_encode(['success' => false, 'message' => "All fields are required"]);
    exit;
}

if (!is_numeric($_POST['razorpay_charge_percentage']) || !is_numeric($_POST['gst_on_razorpay_charge'])) {
    echo json_encode(['success' => false, 'message' => "Razorpay charge or gst must be a numerical value"]);
    exit();
}

// Sanitize input
$data = [
    'super_admin_email' => sanitize_input($_POST['super_admin_email']),
    'country_code' => sanitize_input($_POST['country_code']),
    'imgbb_api_key' => sanitize_input($_POST['imgbb_api_key']),
    'sms_api_key' => sanitize_input($_POST['sms_api_key']),
    'whatsapp_access_token' => sanitize_input($_POST['whatsapp_access_token']),
    'whatsapp_phone_number_id' => sanitize_input($_POST['whatsapp_phone_number_id']),
    'captcha_site_key' => sanitize_input($_POST['captcha_site_key']),
    'captcha_secret_key' => sanitize_input($_POST['captcha_secret_key']),
    'otp_messages_gateway' => sanitize_input($_POST['otp_gateway']),
    'razorpay_key_id' => sanitize_input($_POST['razorpay_key_id']),
    'razorpay_key_secret' => sanitize_input($_POST['razorpay_key_secret']),
    'razorpay_webhook_secret' => sanitize_input($_POST['razorpay_webhook_secret']),
    'razorpay_charge_percentage' => sanitize_input($_POST['razorpay_charge_percentage']),
    'gst_on_razorpay_charge' => sanitize_input($_POST['gst_on_razorpay_charge']),
    'smtp_server' => sanitize_input($_POST['smtp_server']),
    'smtp_username' => sanitize_input($_POST['smtp_username']),
    'smtp_password' => sanitize_input($_POST['smtp_password']),
    'timezone' => sanitize_input($_POST['timezone']),
    'admission_open' => isset($_POST['admission_open_checkbox']) ? 'yes' : 'no',
    'teacher_application' => isset($_POST['teacher_application']) ? 'yes' : 'no',
    'total_student_show' => isset($_POST['total_students_checkbox']) ? 'yes' : 'no',
    'admin_login_option_show' => isset($_POST['admin_login_option_show']) ? 'yes' : 'no',
    'allow_online_payment' => isset($_POST['allow_online_payment']) ? 'yes' : 'no'
];


try {
    // Check if school info already exists
    $stmt = $pdo->query("SELECT id FROM website_config LIMIT 1");
    $existingInfo = $stmt->fetch();

    if ($existingInfo) {
        // Update existing record
        $sql = "UPDATE website_config SET 
            super_admin_email = :super_admin_email,
            country_code = :country_code,
            imgbb_api_key = :imgbb_api_key,
            sms_api_key = :sms_api_key,
            whatsapp_access_token = :whatsapp_access_token,
            whatsapp_phone_number_id = :whatsapp_phone_number_id,
            captcha_site_key = :captcha_site_key,
            captcha_secret_key = :captcha_secret_key,
            otp_messages_gateway = :otp_messages_gateway,
            razorpay_key_id = :razorpay_key_id,
            razorpay_key_secret = :razorpay_key_secret,
            razorpay_webhook_secret = :razorpay_webhook_secret,
            razorpay_charge_percentage = :razorpay_charge_percentage,
            gst_on_razorpay_charge = :gst_on_razorpay_charge,
            smtp_server = :smtp_server,
            smtp_username = :smtp_username,
            smtp_password = :smtp_password,
            timezone = :timezone,
            admission_open = :admission_open,
            teacher_application = :teacher_application,
            total_student_show = :total_student_show,
            admin_login_option_show = :admin_login_option_show,
            allow_online_payment = :allow_online_payment
            WHERE id = :id";
        
        $data['id'] = $existingInfo['id'];
    } else {
        // Insert new record
        $sql = "INSERT INTO website_config 
            (super_admin_email, 
            country_code, 
            imgbb_api_key, 
            sms_api_key, 
            whatsapp_access_token, 
            whatsapp_phone_number_id, 
            captcha_site_key, 
            captcha_secret_key, 
            otp_messages_gateway, 
            razorpay_key_id, 
            razorpay_key_secret, 
            razorpay_webhook_secret, 
            razorpay_charge_percentage, 
            gst_on_razorpay_charge, 
            smtp_server, 
            smtp_username, 
            smtp_password, 
            timezone, 
            admission_open, 
            teacher_application, 
            total_student_show,
            admin_login_option_show,
            allow_online_payment)
            VALUES 
            (:super_admin_email, 
            :country_code, 
            :imgbb_api_key, 
            :sms_api_key, 
            :whatsapp_access_token, 
            :whatsapp_phone_number_id, 
            :captcha_site_key, 
            :captcha_secret_key, 
            :otp_messages_gateway, 
            :razorpay_key_id, 
            :razorpay_key_secret, 
            :razorpay_webhook_secret, 
            :razorpay_charge_percentage, 
            :gst_on_razorpay_charge, 
            :smtp_server, 
            :smtp_username, 
            :smtp_password, 
            :timezone, 
            :admission_open, 
            :teacher_application, 
            :total_student_show,
            :admin_login_option_show,
            :allow_online_payment)";
    }

    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute($data);

    // Update users table to update the superadmin email id
    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = 2");
    $success2 = $stmt->execute([$data['super_admin_email']]);

    if ($success && $success2) {
        echo json_encode([
            'success' => true, 
            'message' => 'Website settings saved successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save website settings']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>