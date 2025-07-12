<?php
include_once("../../includes/auth-check.php");
include_once("../../includes/permission-check.php");
include_once("../../includes/header-open.php");
echo "<title>Website Configuration - " . $school_name . "</title>";
include_once("../../includes/header-close.php");
include_once("../../includes/dashboard-navbar.php");

// Check if user is superadmin only
if ($_SESSION['user']['role'] !== 'superadmin') {
    include_once("../../includes/permission-denied.php");
}

$new_update = checkForUpdates();

$admissionOpen = $websiteConfig['admission_open'] ?? 'no';
$teacherApplication = $websiteConfig['teacher_application'] ?? 'no';
$totalStudents = $websiteConfig['total_student_show'] ?? 'no';
$adminLogin = $websiteConfig['admin_login_option_show'] ?? 'no';
$allowOnlinePayment = $websiteConfig['allow_online_payment'] ?? 'no';

$otp_messages_gateway = $websiteConfig['otp_messages_gateway'] ?? 'sms';
?>

<div class="container py-5">
    <?php if ($new_update['is_update_available']): ?>
        <div class="alert alert-warning mb-5" role="alert">
            <strong>New Update Available!</strong> <a href="../../updates/update.php" class="btn btn-link">Update Now</a>
            <br>
            <?= $new_update['description'] ?>
        </div>
    <?php endif; ?>
    <div class="row justify-content-center">
        <div>
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0"><i class="fa-solid fa-gear"></i> Website Configuration</h4>
                </div>
                <div class="card-body p-4">
                    <form id="websiteConfigForm" class="needs-validation" novalidate enctype="multipart/form-data">
                        <div class="row g-3">
                            <!-- General Configuration -->
                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3 text-primary">General Information</h5>
                            </div>

                            <div class="col-md-6">
                                <label for="super_admin_email" title="This email id will be used for resetting username and password of super admin." class="form-label">Super Admin Email: <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="super_admin_email" name="super_admin_email"
                                    value="<?= safe_htmlspecialchars($websiteConfig['super_admin_email'] ?? '') ?>" required>
                                <div class="invalid-feedback">Please enter super admin email id</div>
                            </div>

                            <div class="col-md-6">
                                <label for="country_code" title="A country code identifies a nation in phone numbers. (Example: India's code 91)" class="form-label">Country Code (eg: 91): <span class="text-danger">*</span></label>
                                <input type="text" maxlength="5" class="form-control" id="country_code" name="country_code"
                                    value="<?= safe_htmlspecialchars($websiteConfig['country_code'] ?? '') ?>" required>
                                <div class="invalid-feedback">Please enter valid country code.</div>
                            </div>

                            <!-- API Keys -->
                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3 text-primary">API Keys</h5>
                            </div>

                            <div class="col-md-6">
                                <label for="imgbb_api_key" title="Please get a free Imgbb API key from Imgbb website." class="form-label">Imgbb API Key: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="imgbb_api_key" name="imgbb_api_key"
                                    value="<?= safe_htmlspecialchars($websiteConfig['imgbb_api_key'] ?? '') ?>" required>
                                <div class="invalid-feedback">Please enter Imgbb API Key</div>
                            </div>

                            <div class="col-md-6">
                                <label for="sms_api_key" class="form-label">SMS API Key:</label>
                                <input type="text" class="form-control" id="sms_api_key" name="sms_api_key"
                                    value="<?= safe_htmlspecialchars($websiteConfig['sms_api_key'] ?? '') ?>">
                                <div class="invalid-feedback">Please enter SMS API Key</div>
                            </div>

                            <div class="col-md-6">
                                <label for="whatsapp_access_token" class="form-label">WhatsApp API Access Token:</label>
                                <input type="text" class="form-control" id="whatsapp_access_token" name="whatsapp_access_token"
                                    value="<?= safe_htmlspecialchars($websiteConfig['whatsapp_access_token'] ?? '') ?>">
                                <div class="invalid-feedback">Please enter WhatsApp API Access Token</div>
                            </div>

                            <div class="col-md-6">
                                <label for="whatsapp_phone_number_id" class="form-label">WhatsApp Phone Number Id:</label>
                                <input type="text" class="form-control" id="whatsapp_phone_number_id" name="whatsapp_phone_number_id"
                                    value="<?= safe_htmlspecialchars($websiteConfig['whatsapp_phone_number_id'] ?? '') ?>">
                                <div class="invalid-feedback">Please enter WhatsApp Phone Number Id</div>
                            </div>

                            <!-- HCaptcha Credentials -->
                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3 text-primary">HCaptcha Credentials</h5>
                            </div>

                            <div class="col-md-6">
                                <label for="captcha_site_key" class="form-label">Captcha Site Key: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="captcha_site_key" name="captcha_site_key"
                                    value="<?= safe_htmlspecialchars($websiteConfig['captcha_site_key'] ?? '') ?>" required>
                                <div class="invalid-feedback">Please enter captcha site key</div>
                            </div>

                            <div class="col-md-6">
                                <label for="captcha_secret_key" class="form-label">Captcha Secret Key: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="captcha_secret_key" name="captcha_secret_key"
                                    value="<?= safe_htmlspecialchars($websiteConfig['captcha_secret_key'] ?? '') ?>" required>
                                <div class="invalid-feedback">Please enter captcha secret key</div>
                            </div>

                            <!-- OTP Gateway -->
                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3 text-primary">OTP Gateway Configarution</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="otp_gateway" title="Which way are you want to send otp messages?" class="form-label">OTP Messages Gateway: <span class="text-danger">*</span></label>
                                <div class="d-flex justify-content-left gap-3 mt-2">
                                    <label>
                                        <input type="radio" class="form-check-input" <?= ($otp_messages_gateway == 'sms') ? 'checked' : '' ?> name="otp_gateway" value="sms" required> Via SMS
                                    </label>
                                    <label>
                                        <input type="radio" class="form-check-input" <?= ($otp_messages_gateway == 'whatsapp') ? 'checked' : '' ?> name="otp_gateway" value="whatsapp"> Via WhatsApp Messages
                                    </label>
                                </div>
                                <div class="invalid-feedback">Please choose OTP Messages Gateway</div>
                            </div>

                            <!-- Razorpay Config -->
                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3 text-primary">Razorpay Configuration</h5>
                            </div>

                            <div class="col-md-6">
                                <label for="razorpay_key_id" class="form-label">Key ID: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="razorpay_key_id" name="razorpay_key_id"
                                    value="<?= safe_htmlspecialchars($websiteConfig['razorpay_key_id'] ?? '') ?>" required>
                                <div class="invalid-feedback">Please enter Razorpay Key ID</div>
                            </div>

                            <div class="col-md-6">
                                <label for="razorpay_key_secret" class="form-label">Key Secret: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="razorpay_key_secret" name="razorpay_key_secret"
                                    value="<?= safe_htmlspecialchars($websiteConfig['razorpay_key_secret'] ?? '') ?>" required>
                                <div class="invalid-feedback">Please enter Razorpay Key Secret</div>
                            </div>

                            <div class="col-md-6">
                                <label for="razorpay_webhook_secret" class="form-label">Webhook Secret: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="razorpay_webhook_secret" name="razorpay_webhook_secret"
                                    value="<?= safe_htmlspecialchars($websiteConfig['razorpay_webhook_secret'] ?? '') ?>">
                                <div class="invalid-feedback">Please enter Razorpay Webhook Secret</div>
                            </div>

                            <div class="col-md-6">
                                <label for="razorpay_charge_percentage" title="Razorpay charge in percentage" class="form-label">Razorpay Charge: <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" placeholder="eg: 2" id="razorpay_charge_percentage" name="razorpay_charge_percentage"
                                    value="<?= safe_htmlspecialchars($websiteConfig['razorpay_charge_percentage'] ?? '') ?>" required>
                                <div class="invalid-feedback">Please enter Razorpay charge in percentage</div>
                            </div>

                            <div class="col-md-6">
                                <label for="gst_on_razorpay_charge" title="GST on Razorpay charge amount" class="form-label">GST on Razorpay Charge: <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" placeholder="eg: 18" id="gst_on_razorpay_charge" name="gst_on_razorpay_charge"
                                    value="<?= safe_htmlspecialchars($websiteConfig['gst_on_razorpay_charge'] ?? '') ?>" required>
                                <div class="invalid-feedback">Please enter GST percent on Razorpay Charge</div>
                            </div>

                            <!-- PHPMailer Config -->
                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3 text-primary">PHPMailer Configuration</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="smtp_server" title="An SMTP server address is the location used to send outgoing emails. (eg: smtp.gmail.com)" class="form-label">SMTP Server Address: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="smtp_server" name="smtp_server"
                                    value="<?= safe_htmlspecialchars($websiteConfig['smtp_server'] ?? '') ?>" required>
                                <div class="invalid-feedback">Please enter SMTP Server Address</div>
                            </div>

                            <div class="col-md-12">
                                <label for="smtp_username" title="It is your email id like example@gmail.com or example@outlook.com" class="form-label">SMTP Username: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="smtp_username" name="smtp_username"
                                    value="<?= safe_htmlspecialchars($websiteConfig['smtp_username'] ?? '') ?>" required>
                                <div class="invalid-feedback">Please enter SMTP Username</div>
                            </div>

                            <div class="col-md-12">
                                <label for="smtp_password" title="An SMTP password is the secret key used with the SMTP username to authenticate and send emails through the SMTP server." class="form-label">SMTP Password: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="smtp_password" name="smtp_password"
                                    value="<?= safe_htmlspecialchars($websiteConfig['smtp_password'] ?? '') ?>" required>
                                <div class="invalid-feedback">Please enter SMTP Password</div>
                            </div>

                            <!-- Additional Configuration -->
                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3 text-primary">Additional Configuration</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="timezone" class="form-label">Select Timezone:</label>
                                <select id="timezone" name="timezone" class="form-control">
                                    <?php
                                    $timezones = DateTimeZone::listIdentifiers();
                                    $grouped = [];

                                    // Determine the selected timezone
                                    $selectedTimezone = !empty($websiteConfig['timezone']) ? $websiteConfig['timezone'] : 'Asia/Kolkata';

                                    foreach ($timezones as $tz) {
                                        $parts = explode('/', $tz, 2);
                                        if (count($parts) == 2) {
                                            $grouped[$parts[0]][] = $parts[1];
                                        }
                                    }

                                    foreach ($grouped as $continent => $cities) {
                                        echo "<optgroup label=\"$continent\">";
                                        foreach ($cities as $city) {
                                            $tz = "$continent/$city";
                                            $selected = ($tz == $selectedTimezone) ? 'selected' : '';
                                            echo "<option value=\"$tz\" $selected>$city ($tz)</option>";
                                        }
                                        echo "</optgroup>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" name="admission_open_checkbox" type="checkbox" value="yes" id="admission_open_checkbox"
                                        <?= ($admissionOpen === 'yes') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="admission_open_checkbox">
                                        Students can apply for admission.
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="teacher_application" type="checkbox" value="yes" id="teacher_application"
                                        <?= ($teacherApplication === 'yes') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="teacher_application">
                                        Teachers can apply for job.
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="total_students_checkbox" type="checkbox" value="yes" id="total_students_checkbox"
                                        <?= ($totalStudents === 'yes') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="total_students_checkbox">
                                        Show total students count on website's front page.
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="admin_login_option_show" type="checkbox" value="yes" id="admin_login_option_show"
                                        <?= ($adminLogin === 'yes') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="admin_login_option_show">
                                        Show admin login option on website's front page.
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="allow_online_payment" type="checkbox" value="yes" id="allow_online_payment"
                                        <?= ($allowOnlinePayment === 'yes') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="allow_online_payment">
                                        Allow online payments.
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="submit" class="btn btn-primary px-4 py-2" id="submitBtn">
                                <span id="submitText"><i class="fa-solid fa-floppy-disk"></i> Save Settings</span>
                                <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Form validation
        (function() {
            'use strict';
            var form = document.getElementById('websiteConfigForm');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        })();

        // AJAX form submission with file upload
        $('#websiteConfigForm').submit(function(e) {
            e.preventDefault();

            // Show loading state
            $('#submitBtn').prop('disabled', true);
            $('#submitText').text('Saving...');
            $('#spinner').removeClass('d-none');

            // Create FormData object for file upload
            var formData = new FormData(this);

            $.ajax({
                url: '../action/save-website-config.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        // Reset form validation
                        $('#websiteConfigForm').removeClass('was-validated');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('An error occurred: ' + error);
                    console.error(xhr.responseText);
                },
                complete: function() {
                    // Reset button state
                    $('#submitBtn').prop('disabled', false);
                    $('#submitText').html('<i class="fa-solid fa-floppy-disk"></i> Save Settings');
                    $('#spinner').addClass('d-none');
                }
            });
        });
    });
</script>

<?php include_once("../../includes/body-close.php"); ?>