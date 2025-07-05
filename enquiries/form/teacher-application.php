<?php
include_once("../../includes/header-open.php");
echo "<title>Teacher Application Form - " . $school_name . "</title>";
include_once("../../includes/header-close.php");

if ($websiteConfig['teacher_application'] !== 'yes') {
    echo '<script>location.replace("/");</script>';
    die();
}
?>

<style>
    /* FIXED Cropper modal styles */
    .modal-xl {
        max-width: 80%;
    }

    .img-container {
        overflow: hidden;
        margin: 0 auto;
        max-height: 500px;
        min-height: 400px;
        position: relative;
        width: 100%;
        background-color: #f8f9fa;
    }

    /* Modal body improvements */
    .modal-body {
        padding: 1.5rem;
    }

    .modal-dialog {
        margin: 1.75rem auto;
    }

    /* Remove loading styles that might interfere */
    .img-container.loading {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
    }

    .img-container.loading::after {
        content: "Loading image...";
        color: #6c757d;
        font-size: 1rem;
    }

    .img-container.loading img {
        visibility: hidden;
    }

    /* Other existing styles remain the same */
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
        word-wrap: break-word;
        text-align: center;
        max-width: 100%;
        font-family: "Oswald", sans-serif;
        font-optical-sizing: auto;
        font-weight: 700;
        font-style: normal;
        margin: 1rem 0;
    }

    .photo-upload-section {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: border-color 0.3s ease;
    }

    .photo-upload-section:hover {
        border-color: #007bff;
    }

    .photo-preview {
        max-width: 200px;
        max-height: 240px;
        object-fit: cover;
        border-radius: 8px;
        margin: 10px 0;
    }

    #cropRequiredMessage {
        font-size: 0.9rem;
    }

    #experience_details {
        display: none;
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
            <h4 class="mb-0 py-2"><i class="fa-solid fa-graduation-cap"></i> Teacher Application Form</h4>
        </div>

        <div class="card-body">
            <form id="teacherApplicationForm" method="post" enctype="multipart/form-data">
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
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="col-md-4">
                        <label for="phone_number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" required placeholder="8348313317">
                    </div>

                    <div class="col-md-6">
                        <label for="qualification" class="form-label">Qualification <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="qualification" name="qualification" required placeholder="B.Ed, M.A, etc.">
                    </div>

                    <div class="col-md-6">
                        <label for="specialization" class="form-label">Specialization (Comma Separated) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="specialization" name="specialization" required placeholder="Geography, English">
                    </div>

                    <div class="col-md-12">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="2" required placeholder="Full address"></textarea>
                    </div>

                    <!-- Photo Upload Section -->
                    <div class="col-md-12 mt-4">
                        <h5 class="border-bottom pb-2 text-primary">
                            <i class="fas fa-camera me-2"></i>Photo Upload
                        </h5>
                    </div>

                    <div class="col-md-12">
                        <div class="photo-upload-section">
                            <label for="photo" class="form-label">Upload Photo <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*" required>
                            <small class="text-muted">Please upload a clear photo. You must crop the image to 5:6 ratio before proceeding.</small>
                            <div id="photoPreview" style="display: none;">
                                <img id="previewImage" class="photo-preview" alt="Photo Preview">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-danger" id="removePhoto">
                                        <i class="fas fa-trash me-1"></i>Remove Photo
                                    </button>
                                </div>
                            </div>
                            <div id="cropRequiredMessage" style="display: none;" class="alert alert-warning mt-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Please crop your photo before submitting the form.
                            </div>
                        </div>
                        <input type="hidden" id="cropped_image_data" name="cropped_image_data">
                    </div>

                    <!-- Experience Section -->
                    <div class="col-md-12 mt-4">
                        <h5 class="border-bottom pb-2 text-primary">
                            <i class="fas fa-briefcase me-2"></i>Experience
                        </h5>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Do you have any previous teaching experience? <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="has_experience" id="experience_yes" value="yes" required>
                            <label class="form-check-label" for="experience_yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="has_experience" id="experience_no" value="no" required>
                            <label class="form-check-label" for="experience_no">No</label>
                        </div>
                    </div>

                    <div class="col-md-12" id="experience_details">
                        <label for="previous_school" class="form-label">Previous School/Institution <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="previous_school" name="previous_school" rows="3" placeholder="Please mention the school/institution names and duration of work"></textarea>
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
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="fas fa-paper-plane me-2"></i> Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Cropper Modal -->
<div class="modal fade" id="cropperModal" tabindex="-1" aria-labelledby="cropperModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropperModalLabel">
                    <i class="fas fa-crop me-2"></i>Crop Your Photo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Please crop your photo to the required 5:6 ratio. You can drag and resize the crop area.
                </div>
                <div class="img-container">
                    <img id="cropperImage" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmCrop">
                    <i class="fas fa-check me-1"></i>Apply Crop & Continue
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Replace the cropper-related JavaScript in teacher-application.php with this fixed version

    let hcaptchaWidgetId;
    let cropper;
    let originalImageFile;

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
                setTimeout(initHcaptcha, 500);
            }
        } else if (typeof hcaptcha === 'undefined') {
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

    window.onHcaptchaLoad = function() {
        console.log('hCaptcha loaded via callback');
    };


    // Separate function to initialize cropper
    function initializeCropper() {
        if (cropper) {
            cropper.destroy();
        }

        // Small delay to ensure modal is fully rendered
        setTimeout(() => {
            cropper = new Cropper($('#cropperImage')[0], {
                aspectRatio: 5 / 6,
                viewMode: 1,
                autoCropArea: 0.8,
                responsive: true,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                ready: function() {
                    // Force a resize after cropper is ready
                    this.cropper.resize();

                    // Set optimal crop box size
                    const containerData = this.cropper.getContainerData();
                    const imageData = this.cropper.getImageData();

                    // Calculate optimal crop box size
                    const maxWidth = containerData.width * 0.8;
                    const maxHeight = containerData.height * 0.8;
                    const aspectRatio = 5 / 6;

                    let cropWidth, cropHeight;
                    if (maxWidth / aspectRatio <= maxHeight) {
                        cropWidth = maxWidth;
                        cropHeight = maxWidth / aspectRatio;
                    } else {
                        cropHeight = maxHeight;
                        cropWidth = maxHeight * aspectRatio;
                    }

                    this.cropper.setCropBoxData({
                        width: cropWidth,
                        height: cropHeight,
                        left: (containerData.width - cropWidth) / 2,
                        top: (containerData.height - cropHeight) / 2
                    });
                }
            });
        }, 100); // Small delay to ensure DOM is ready
    }

    $(document).ready(function() {
        initHcaptcha();

        // Experience radio button change handler
        $('input[name="has_experience"]').change(function() {
            if ($(this).val() === 'yes') {
                $('#experience_details').show();
                $('#previous_school').attr('required', true);
            } else {
                $('#experience_details').hide();
                $('#previous_school').attr('required', false).val('');
            }
        });

        // Handle window resize to recalculate cropper dimensions
        $(window).resize(function() {
            if (cropper && $('#cropperModal').hasClass('show')) {
                cropper.resize();
            }
        });

        // Photo upload handler - FIXED VERSION (same as add-student.php)
        $('#photo').change(function(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                originalImageFile = file;
                const reader = new FileReader();

                reader.onload = function(event) {
                    // Set the image source first
                    $('#cropperImage').attr('src', event.target.result);

                    // Show the modal
                    $('#cropperModal').modal('show');
                };
                reader.readAsDataURL(file);
            } else {
                $('#photoPreview').hide();
                $('#cropRequiredMessage').hide();
                $('#cropped_image_data').val('');
                originalImageFile = null;
            }
        });

        // Handle crop button click - FIXED VERSION (same as add-student.php)
        $('#confirmCrop').click(function() {
            if (cropper) {
                // Get cropped canvas
                const canvas = cropper.getCroppedCanvas({
                    width: 500,
                    height: 600,
                    minWidth: 500,
                    minHeight: 600,
                    maxWidth: 500,
                    maxHeight: 600,
                    fillColor: '#fff',
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });

                if (canvas) {
                    // Convert canvas to blob and update preview
                    canvas.toBlob(function(blob) {
                        // Create a new File from the blob
                        const file = new File([blob], originalImageFile.name, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });

                        // Create a new DataTransfer to replace the file input
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        $('#photo')[0].files = dataTransfer.files;

                        // Update preview
                        const img = $('#previewImage')[0];
                        img.src = URL.createObjectURL(blob);
                        $('#photoPreview').show();

                        // Also store the base64 version for AJAX submission
                        const croppedImageData = canvas.toDataURL('image/jpeg');
                        $('#cropped_image_data').val(croppedImageData);

                        // Hide crop required message
                        $('#cropRequiredMessage').hide();

                        // Close the modal
                        $('#cropperModal').modal('hide');

                        // Destroy cropper
                        cropper.destroy();
                        cropper = null;

                        toastr.success('Photo cropped successfully!');
                    }, 'image/jpeg', 0.9);
                }
            }
        });

        // Clean up when modal is closed - FIXED VERSION
        $('#cropperModal').on('shown.bs.modal', function() {
            const imageElement = $('#cropperImage')[0];

            // Wait for image to load if not already loaded
            if (imageElement.complete) {
                initializeCropper();
            } else {
                imageElement.onload = function() {
                    initializeCropper();
                };
            }
        });

        // Clean up when modal is closed - ENHANCED VERSION
        $('#cropperModal').on('hidden.bs.modal', function() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }

            // If user closed without cropping, reset file input
            if (!$('#cropped_image_data').val()) {
                $('#photo').val('');
                $('#photoPreview').hide();
                $('#cropRequiredMessage').hide();
                originalImageFile = null;
            }
        });

        // Remove photo button handler
        $('#removePhoto').click(function() {
            $('#photo').val('');
            $('#photoPreview').hide();
            $('#cropRequiredMessage').hide();
            $('#cropped_image_data').val('');
            originalImageFile = null;

            // Destroy cropper if it exists
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }

            toastr.info('Photo removed successfully');
        });

        // AJAX form submission
        $('#teacherApplicationForm').submit(function(e) {
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

            // Check if photo is uploaded and cropped
            if (!$('#cropped_image_data').val()) {
                toastr.error('Please upload and crop your photo before submitting');
                return;
            }

            const formData = new FormData(this);

            $.ajax({
                url: '../action/process-teacher-application.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...');
                },
                success: function(response) {
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Submit Application');

                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            console.error("Failed to parse response:", response);
                            toastr.error("Invalid server response");
                            return;
                        }
                    }

                    if (response.success) {
                        toastr.success(response.message || 'Application submitted successfully!');
                        setTimeout(() => {
                            $('#teacherApplicationForm')[0].reset();
                            $('#photoPreview').hide();
                            $('#cropRequiredMessage').hide();
                            $('#experience_details').hide();
                            $('#cropped_image_data').val('');
                            originalImageFile = null;
                        }, 1500);
                    } else {
                        toastr.error(response.message || 'Operation failed');
                    }
                },
                error: function(xhr, status, error) {
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Submit Application');

                    console.error("AJAX Error:", status, error, xhr.responseText);
                    try {
                        const response = JSON.parse(xhr.responseText);
                        toastr.error(response.message || 'An error occurred');
                    } catch (e) {
                        toastr.error('An error occurred. Please try again.');
                    }
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