<?php
include_once("../../includes/header-open.php");
echo "<title>Student Application - " . $school_name . "</title>";
include_once("../../includes/header-close.php");

if ($websiteConfig['admission_open'] !== 'yes') {
    echo '<script>location.replace("/");</script>';
    die();
}

// Fetch all classes
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name")->fetchAll(PDO::FETCH_ASSOC);

// Blood group options
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
?>

<style>
    /* Cropper modal styles */
    .modal-xl {
        max-width: 50%;
    }

    .img-container {
        overflow: hidden;
        margin: 0 auto;
    }

    .cropper-container {
        width: 100% !important;
        height: 100% !important;
    }

    .cropper-modal {
        background-color: rgba(255, 255, 255, 0.8);
    }

    .cropper-view-box {
        outline: 2px solid #007bff;
        outline-color: rgba(0, 123, 255, 0.75);
    }

    .logo-container {
        align-items: center;
        text-align: center;
        padding: 0.8rem;
    }

    .logo-container .navbar-brand {
        display: block;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
    }

    .navbar-brand img {
        height: 100px;
        width: 100px;
        object-fit: cover;
    }

    .navbar-brand-text {
        color: var(--primary-color);
        text-transform: uppercase;
        font-size: 2rem;
        white-space: normal;
        /* Allows word wrapping */
        word-wrap: break-word;
        /* Breaks long words if needed */
        text-align: center;
        /* Optional: center-align text */
        max-width: 100%;
        /* Prevent overflow */
        font-family: "Oswald", sans-serif;
        font-optical-sizing: auto;
        font-weight: 700;
        font-style: normal;
        margin: 1rem 0;
    }
</style>

<div class="container mt-4 mb-5">

    <!-- School Name & Logo Section -->
    <div class="logo-container container">
        <a class="navbar-brand" href="/">
            <img src="../../uploads/school/logo-square.png" alt="School Logo" onerror="this.style.display='none'"
                class="rounded">
            <div class="navbar-brand-text"><?= $schoolInfo['name']; ?></div>
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 py-2"><i class="fa-solid fa-graduation-cap"></i> Admission Enquiry</h4>
        </div>

        <div class="card-body">
            <form id="studentAdmissionForm" method="post">
                <div class="row g-3">
                    <!-- Personal Information Section -->
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 text-primary">
                            <i class="fas fa-user-circle me-2"></i>Personal Information
                        </h5>
                    </div>

                    <div class="col-md-4">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="John Doe">
                    </div>

                    <div class="col-md-4">
                        <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="" selected disabled>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                    </div>

                    <div class="col-md-4">
                        <label for="blood_group" class="form-label">Blood Group</label>
                        <select class="form-select" id="blood_group" name="blood_group">
                            <option value="" selected disabled>Select Blood Group</option>
                            <?php foreach ($blood_groups as $group): ?>
                                <option value="<?= $group ?>"><?= $group ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="phone_number" class="form-label">Phone Number <span class="text-danger"> *</span>
                            <i title="This phone number will be used as primary phone number. OTPs will be sent to this number. Enter phone number without country code." class="fa-solid fa-circle-question"></i>
                        </label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" required placeholder="8348313317">
                    </div>

                    <div class="col-md-4">
                        <label for="alternate_phone_number" class="form-label">Alternate Phone Number <span class="text-danger">*</span>
                            <i title="This phone number will be used as alternate phone number if primary number is not reachable. Enter phone number without country code." class="fa-solid fa-circle-question"></i>
                        </label>
                        <input type="tel" class="form-control" id="alternate_phone_number" name="alternate_phone_number" placeholder="9083063784">
                    </div>

                    <div class="col-md-6">
                        <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                        <select class="form-select" id="class_id" name="class_id" required>
                            <option value="" selected disabled>Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>"><?= safe_htmlspecialchars($class['class_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Id <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="">
                    </div>

                    <div class="col-md-12">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="2" required placeholder="Full address"></textarea>
                    </div>

                    <!-- Family Information Section -->
                    <div class="col-md-12 mt-4">
                        <h5 class="border-bottom pb-2 text-primary">
                            <i class="fas fa-users me-2"></i>Family Information
                        </h5>
                    </div>

                    <div class="col-md-6">
                        <label for="father_name" class="form-label">Father's Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="father_name" name="father_name" required placeholder="Father's name">
                    </div>

                    <div class="col-md-6">
                        <label for="mother_name" class="form-label">Mother's Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mother_name" name="mother_name" required placeholder="Mother's name">
                    </div>

                    <div class="col-md-6">
                        <label for="father_occupation" class="form-label">Father's Occupation</label>
                        <input type="text" class="form-control" id="father_occupation" name="father_occupation" placeholder="Occupation">
                    </div>

                    <div class="col-md-6">
                        <label for="mother_occupation" class="form-label">Mother's Occupation</label>
                        <input type="text" class="form-control" id="mother_occupation" name="mother_occupation" placeholder="Occupation">
                    </div>

                    <!-- hCaptcha Section -->
                    <div class="col-md-12 mt-4">
                        <h5 class="border-bottom pb-2 text-primary">
                            <i class="fa-solid fa-shield-halved"></i> Captcha Validation
                        </h5>
                    </div>
                    <div class="hcaptcha-container col-md-12">
                        <div class="h-captcha" data-sitekey="<?= $websiteConfig['captcha_site_key'] ?>"></div>
                    </div>
                </div>

                <div class="mt-5 mb-3 text-center">
                    <button id="submitBtn" type="submit" class="btn btn-primary px-4 py-2">
                        <i class="fas fa-plus-circle me-2"></i> Apply Admission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let hcaptchaWidgetId;

    // Initialize hCaptcha when DOM is ready
    function initHcaptcha() {
        const container = document.querySelector('.h-captcha');

        if (typeof hcaptcha !== 'undefined' && container && !hcaptchaWidgetId) {
            try {
                hcaptchaWidgetId = hcaptcha.render(container, {
                    sitekey: '<?= $websiteConfig['captcha_site_key'] ?>',
                    theme: 'light',
                    size: 'normal'
                });
                console.log('hCaptcha initialized successfully');
            } catch (error) {
                console.error('hCaptcha initialization error:', error);
                // Retry after a short delay
                setTimeout(initHcaptcha, 500);
            }
        } else if (typeof hcaptcha === 'undefined') {
            // If hCaptcha hasn't loaded yet, wait and try again
            setTimeout(initHcaptcha, 200);
        }
    }

    // Helper function to reset hCaptcha
    function resetHcaptcha() {
        if (typeof hcaptcha !== 'undefined' && hcaptchaWidgetId !== undefined) {
            try {
                hcaptcha.reset(hcaptchaWidgetId);
            } catch (error) {
                console.error('Error resetting hCaptcha:', error);
            }
        }
    }

    // Optional: Define the callback function in case the script was loaded with callback
    // This prevents the "Callback 'onHcaptchaLoad' is not defined" error
    window.onHcaptchaLoad = function() {
        console.log('hCaptcha loaded via callback');
        // The initialization is already handled in document ready, so this is just a fallback
    };

    $(document).ready(function() {

        // Start initialization
        initHcaptcha();

        // AJAX form submission
        $('#studentAdmissionForm').submit(function(e) {
            e.preventDefault();

            // Check if hCaptcha is completed
            let hcaptchaResponse = '';
            if (typeof hcaptcha !== 'undefined' && hcaptchaWidgetId !== undefined) {
                try {
                    hcaptchaResponse = hcaptcha.getResponse(hcaptchaWidgetId);
                } catch (error) {
                    console.error('Error getting hCaptcha response:', error);
                }
            }

            if (!hcaptchaResponse) {
                toastr.error('Please complete the captcha verification');
                return;
            }

            let btn = $('#submitBtn');
            let submitBtnHtml = $('#submitBtn').html();

            $.ajax({
                url: '../action/process-student-enquiry.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                beforeSend: function() {
                    btn.prop('disabled', true);
                    btn.html("Sending...");
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        // Show sweetalert2 message here
                        Swal.fire({
                            icon: 'success',
                            title: 'Application Submitted',
                            text: response.message,
                            confirmButtonColor: '#3085d6',
                        });
                        $('#studentAdmissionForm')[0].reset();

                    } else {
                        toastr.error(response.message);
                        // Show sweetalert2 error message here
                        Swal.fire({
                            icon: 'error',
                            title: 'Application Submission Failed',
                            text: response.message
                        });
                    }

                    btn.prop('disabled', false);
                    btn.html(submitBtnHtml);
                },
                error: function(xhr, status, error) {
                    toastr.error('An error occurred. Please try again.');
                    Swal.fire({
                        icon: 'error',
                        title: 'Application Submission Failed',
                        text: 'An error occurred. Please try again.'
                    });
                    btn.prop('disabled', false);
                    btn.html(submitBtnHtml);
                    console.error(xhr.responseText);
                },
                complete: function() {
                    // Reset hCaptcha after form submission
                    resetHcaptcha();
                }
            });
        });
    });
</script>

<?php include_once("../../includes/body-close.php"); ?>