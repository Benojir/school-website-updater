<?php
include_once("../../includes/auth-check.php");
include_once("../../includes/permission-check.php");
include_once("../../includes/header-open.php");
echo "<title>Bulk Edit Students - " . $school_name . "</title>";
include_once("../../includes/header-close.php");
include_once("../../includes/dashboard-navbar.php");

// Check admin permission
if (!hasPermission(PERM_MANAGE_STUDENTS)) {
    include_once("../../includes/permission-denied.php");
    exit();
}

// Get student IDs from query string
$student_ids = $_GET['student_ids'] ?? '';
if (!$student_ids) {
    die("Student IDs are required.");
}

// Convert comma-separated string to array
$student_ids_array = explode(',', $student_ids);
$student_ids_array = array_map('trim', $student_ids_array);
$student_ids_array = array_filter($student_ids_array);

if (empty($student_ids_array)) {
    die("No valid student IDs provided.");
}

// Fetch students data
$placeholders = implode(',', array_fill(0, count($student_ids_array), '?'));
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id IN ($placeholders)");
$stmt->execute($student_ids_array);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($students)) {
    die("No students found with the provided IDs.");
}

// Check if all students are from the same class
$class_ids = array_unique(array_column($students, 'class_id'));
if (count($class_ids) > 1) {
    die("All selected students must be from the same class.");
}

$current_class_id = $class_ids[0];

// Fetch sections for the current class
$sections = $pdo->query("SELECT id, section_name FROM sections WHERE class_id = $current_class_id ORDER BY section_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Blood group options
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

?>

<style>
    .editable-field {
        border: 1px solid #ddd;
        padding: 5px;
        min-width: 100px;
    }

    .editable-field:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .student-row.changed {
        background-color: #fffde7;
    }

    .update-btn {
        display: none;
    }

    .student-row.changed .update-btn {
        display: inline-block;
    }

    .field-checkboxes {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .field-checkboxes label {
        margin-right: 15px;
        font-weight: normal;
    }

    .photo-preview {
        width: 50px;
        height: 60px;
        object-fit: cover;
        cursor: pointer;
    }

    /* Cropper modal styles */
    .modal-xl {
        max-width: 80%;
    }

    .img-container {
        overflow: hidden;
        margin: 0 auto;
        height: 70vh;
    }

    .cropper-view-box {
        outline: 2px solid #007bff;
        outline-color: rgba(0, 123, 255, 0.75);
    }

    #imageToCrop {
        max-width: 100%;
    }

    /* Enhanced Cropper Modal Styles */
    .modal-xl {
        max-width: 90%;
    }

    .img-container {
        position: relative;
        overflow: hidden;
        margin: 0 auto;
        height: 70vh;
        min-height: 400px;
        max-height: 600px;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #imageToCrop {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        display: block;
    }

    .cropper-container{direction:ltr;font-size:0;line-height:0;position:relative;touch-action:none;user-select:none;width:100% !important;height:100% !important;}.cropper-wrap-box,.cropper-canvas,.cropper-drag-box,.cropper-crop-box,.cropper-modal{position:absolute;top:0;right:0;bottom:0;left:0;}.cropper-wrap-box{overflow:hidden;}.cropper-drag-box{opacity:0;background-color:#fff;}.cropper-modal{opacity:.5;background-color:#000;}.cropper-view-box{display:block;overflow:hidden;width:100%;height:100%;outline:2px solid #007bff;outline-color:rgba(0,123,255,.75);}.cropper-dashed{position:absolute;display:block;opacity:.5;border:0 dashed #eee;}.cropper-dashed.dashed-h{top:33.33333%;left:0;width:100%;height:33.33333%;border-top-width:1px;border-bottom-width:1px;}.cropper-dashed.dashed-v{top:0;left:33.33333%;width:33.33333%;height:100%;border-right-width:1px;border-left-width:1px;}.cropper-center{position:absolute;top:50%;left:50%;display:block;width:0;height:0;opacity:.75;}.cropper-center:before,.cropper-center:after{position:absolute;display:block;content:' ';background-color:#eee;}.cropper-center:before{top:0;left:-3px;width:7px;height:1px;}.cropper-center:after{top:-3px;left:0;width:1px;height:7px;}.cropper-face,.cropper-line,.cropper-point{position:absolute;display:block;width:100%;height:100%;opacity:.1;}.cropper-face{top:0;left:0;background-color:#fff;}.cropper-line{background-color:#007bff;}.cropper-line.line-e{top:0;right:-3px;width:5px;cursor:e-resize;}.cropper-line.line-n{top:-3px;left:0;height:5px;cursor:n-resize;}.cropper-line.line-w{top:0;left:-3px;width:5px;cursor:w-resize;}.cropper-line.line-s{bottom:-3px;left:0;height:5px;cursor:s-resize;}.cropper-point{width:5px;height:5px;opacity:.75;background-color:#007bff;}.cropper-point.point-e{top:50%;right:-3px;margin-top:-3px;cursor:e-resize;}.cropper-point.point-n{top:-3px;left:50%;margin-left:-3px;cursor:n-resize;}.cropper-point.point-w{top:50%;left:-3px;margin-top:-3px;cursor:w-resize;}.cropper-point.point-s{bottom:-3px;left:50%;margin-left:-3px;cursor:s-resize;}.cropper-point.point-ne{top:-3px;right:-3px;cursor:ne-resize;}.cropper-point.point-nw{top:-3px;left:-3px;cursor:nw-resize;}.cropper-point.point-sw{bottom:-3px;left:-3px;cursor:sw-resize;}.cropper-point.point-se{right:-3px;bottom:-3px;width:5px;height:5px;cursor:se-resize;opacity:1;}.cropper-point.point-se:before{position:absolute;right:-50%;bottom:-50%;display:block;width:200%;height:200%;content:' ';opacity:0;background-color:#007bff;}
    
    @media (max-width: 768px) {
        .modal-xl { max-width: 95%; }
        .img-container { height: 60vh; min-height: 300px; }
    }
</style>

<div class="container-fluid mt-4 mb-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-users-edit me-2"></i> Bulk Edit Students (Class: <?= safe_htmlspecialchars($students[0]['class_id']) ?>)</h4>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> You are editing <?= count($students) ?> students. Only changed fields will be updated.
            </div>

            <div class="field-checkboxes mb-4">
                <h5><i class="fas fa-check-square me-2"></i> Fields to Display:</h5>
                <div class="form-check form-check-inline">
                    <input class="form-check-input field-toggle" type="checkbox" id="toggle-image" value="student_image" checked>
                    <label class="form-check-label" for="toggle-image">Photo</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input field-toggle" type="checkbox" id="toggle-section" value="section_id" checked>
                    <label class="form-check-label" for="toggle-section">Section</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input field-toggle" type="checkbox" id="toggle-roll" value="roll_no" checked>
                    <label class="form-check-label" for="toggle-roll">Roll No</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input field-toggle" type="checkbox" id="toggle-phone" value="phone_number" checked>
                    <label class="form-check-label" for="toggle-phone">Phone</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input field-toggle" type="checkbox" id="toggle-alt-phone" value="alternate_phone_number">
                    <label class="form-check-label" for="toggle-alt-phone">Alt. Phone</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input field-toggle" type="checkbox" id="toggle-blood" value="blood_group">
                    <label class="form-check-label" for="toggle-blood">Blood Group</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input field-toggle" type="checkbox" id="toggle-mother" value="mother_name">
                    <label class="form-check-label" for="toggle-mother">Mother's Name</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input field-toggle" type="checkbox" id="toggle-address" value="address">
                    <label class="form-check-label" for="toggle-address">Address</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input field-toggle" type="checkbox" id="toggle-father-occupation" value="father_occupation">
                    <label class="form-check-label" for="toggle-father-occupation">Father's Occupation</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input field-toggle" type="checkbox" id="toggle-mother-occupation" value="mother_occupation">
                    <label class="form-check-label" for="toggle-mother-occupation">Mother's Occupation</label>
                </div>
                 <div class="form-check form-check-inline">
                    <input class="form-check-input field-toggle" type="checkbox" id="toggle-car-route" value="car_route">
                    <label class="form-check-label" for="toggle-car-route">Car Route</label>
                </div>
                 <div class="form-check form-check-inline">
                    <input class="form-check-input field-toggle" type="checkbox" id="toggle-car-fee" value="car_fee">
                    <label class="form-check-label" for="toggle-car-fee">Car Fee</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input field-toggle" type="checkbox" id="toggle-hostel-fee" value="hostel_fee">
                    <label class="form-check-label" for="toggle-hostel-fee">Hostel Fee</label>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="studentsTable">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th class="field-column" data-field="student_image">Photo</th>
                            <th class="field-column" data-field="section_id">Section</th>
                            <th class="field-column" data-field="roll_no">Roll No</th>
                            <th class="field-column" data-field="phone_number">Phone</th>
                            <th class="field-column" data-field="alternate_phone_number">Alt. Phone</th>
                            <th class="field-column" data-field="blood_group">Blood Group</th>
                            <th class="field-column" data-field="mother_name">Mother's Name</th>
                            <th class="field-column" data-field="address">Address</th>
                            <th class="field-column" data-field="father_occupation">Father's Occupation</th>
                            <th class="field-column" data-field="mother_occupation">Mother's Occupation</th>
                            <th class="field-column" data-field="car_route">Car Route</th>
                            <th class="field-column" data-field="car_fee">Car Fee</th>
                            <th class="field-column" data-field="hostel_fee">Hostel Fee</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr class="student-row" data-student-id="<?= safe_htmlspecialchars($student['student_id']) ?>">
                                <td><?= safe_htmlspecialchars($student['student_id']) ?></td>
                                <td><?= safe_htmlspecialchars($student['name']) ?></td>

                                <td class="field-column" data-field="student_image">
                                    <?php if (!empty($student['student_image'])): ?>
                                        <img src="../../uploads/students/<?= safe_htmlspecialchars($student['student_image']) ?>" class="photo-preview">
                                    <?php else: ?>
                                        <img src="../../uploads/students/default_student_dp.jpg" class="photo-preview">
                                    <?php endif; ?>
                                    <input type="file" class="form-control mt-1 image-upload" style="display: none;" accept="image/*">
                                    <input type="hidden" class="current-image" value="<?= safe_htmlspecialchars($student['student_image']) ?>">
                                    <input type="hidden" class="cropped-image-data">
                                </td>

                                <td class="field-column" data-field="section_id">
                                    <select class="form-control form-control-sm editable-field section-select">
                                        <option value="">Select Section</option>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?= $section['id'] ?>" <?= $student['section_id'] == $section['id'] ? 'selected' : '' ?>>
                                                <?= safe_htmlspecialchars($section['section_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <td class="field-column" data-field="roll_no">
                                    <input type="number" class="form-control form-control-sm editable-field roll-no-input" value="<?= safe_htmlspecialchars($student['roll_no']) ?>">
                                </td>

                                <td class="field-column" data-field="phone_number">
                                    <input type="tel" class="form-control form-control-sm editable-field phone-input" value="<?= safe_htmlspecialchars($student['phone_number']) ?>">
                                </td>

                                <td class="field-column" data-field="alternate_phone_number">
                                    <input type="tel" class="form-control form-control-sm editable-field alt-phone-input" value="<?= safe_htmlspecialchars($student['alternate_phone_number']) ?>">
                                </td>

                                <td class="field-column" data-field="blood_group">
                                    <select class="form-control form-control-sm editable-field blood-group-select">
                                        <option value="">Select</option>
                                        <?php foreach ($blood_groups as $group): ?>
                                            <option value="<?= $group ?>" <?= $student['blood_group'] === $group ? 'selected' : '' ?>>
                                                <?= $group ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <td class="field-column" data-field="mother_name">
                                    <input type="text" class="form-control form-control-sm editable-field mother-name-input" value="<?= safe_htmlspecialchars($student['mother_name']) ?>">
                                </td>

                                <td class="field-column" data-field="address">
                                    <textarea class="form-control form-control-sm editable-field address-input" rows="3"><?= safe_htmlspecialchars($student['address']) ?></textarea>
                                </td>
                                
                                <td class="field-column" data-field="father_occupation">
                                    <input type="text" class="form-control form-control-sm editable-field father-occupation-input" value="<?= safe_htmlspecialchars($student['father_occupation']) ?>">
                                </td>

                                <td class="field-column" data-field="mother_occupation">
                                    <input type="text" class="form-control form-control-sm editable-field mother-occupation-input" value="<?= safe_htmlspecialchars($student['mother_occupation']) ?>">
                                </td>

                                <td class="field-column" data-field="car_route">
                                    <input type="text" class="form-control form-control-sm editable-field car-route-input" value="<?= safe_htmlspecialchars($student['car_route']) ?>">
                                </td>

                                <td class="field-column" data-field="car_fee">
                                    <input type="number" class="form-control form-control-sm editable-field car-fee-input" value="<?= safe_htmlspecialchars($student['car_fee']) ?>">
                                </td>

                                <td class="field-column" data-field="hostel_fee">
                                    <input type="number" class="form-control form-control-sm editable-field hostel-fee-input" value="<?= safe_htmlspecialchars($student['hostel_fee']) ?>">
                                </td>

                                <td>
                                    <button class="btn btn-sm btn-primary update-btn" data-student-id="<?= safe_htmlspecialchars($student['student_id']) ?>">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-4">
                <button id="updateAllBtn" class="btn btn-primary px-4 py-2">
                    <i class="fas fa-save me-2"></i> Update All Changes
                </button>
                <a href="../view/students-list.php" class="btn btn-secondary px-4 py-2 ms-2">
                    <i class="fas fa-times me-2"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropModalLabel">Crop Student Photo (5:6 ratio required)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="img-container">
                    <img id="imageToCrop" src="#" alt="Image to crop">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="cropImageBtn">Crop & Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize cropper variables
        let cropper;
        let currentImageRow;
        let imageDataUrl;
        const cropModal = new bootstrap.Modal(document.getElementById('cropModal'));

        // Store all roll numbers for validation
        let rollNumbers = {};

        function initializeRollNumbers() {
            $('.student-row').each(function() {
                const studentId = $(this).data('student-id');
                const rollNo = parseInt($(this).find('.roll-no-input').val());
                if (rollNo > 0) {
                    rollNumbers[studentId] = rollNo;
                }
            });
        }

        function validateRollNumber(studentId, newRollNo) {
            if (newRollNo <= 0) {
                return { valid: false, message: 'Roll number must be a positive number' };
            }
            for (let [otherId, otherRoll] of Object.entries(rollNumbers)) {
                if (otherId !== studentId && otherRoll === newRollNo) {
                    return { valid: false, message: `Roll number ${newRollNo} is already assigned` };
                }
            }
            return { valid: true, message: '' };
        }

        initializeRollNumbers();

        $('.field-toggle').change(function() {
            const field = $(this).val();
            const isChecked = $(this).is(':checked');
            $(`.field-column[data-field="${field}"]`).toggle(isChecked);
        }).trigger('change');

        $('.editable-field, .image-upload').on('input change', function() {
            $(this).closest('.student-row').addClass('changed');
        });

        $('.roll-no-input').on('input blur', function() {
            const $input = $(this);
            const studentId = $input.closest('.student-row').data('student-id').toString();
            const newRollNo = parseInt($input.val());
            const $feedback = $input.siblings('.roll-validation-feedback');
            $feedback.remove();
            $input.removeClass('is-invalid is-valid');

            if ($input.val() && !isNaN(newRollNo)) {
                const validation = validateRollNumber(studentId, newRollNo);
                if (!validation.valid) {
                    $input.addClass('is-invalid');
                    $input.after(`<div class="invalid-feedback d-block roll-validation-feedback">${validation.message}</div>`);
                } else {
                    $input.addClass('is-valid');
                    rollNumbers[studentId] = newRollNo;
                }
            } else if ($input.val() === '') {
                delete rollNumbers[studentId];
            }
        });

        $('.photo-preview').click(function() {
            currentImageRow = $(this).closest('td');
            currentImageRow.find('.image-upload').click();
        });

        $('.image-upload').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imageToCrop').attr('src', e.target.result);
                    cropModal.show();
                }
                reader.readAsDataURL(file);
            }
        });

        $('#cropModal').on('shown.bs.modal', function() {
            if (cropper) cropper.destroy();
            const image = document.getElementById('imageToCrop');
            cropper = new Cropper(image, {
                aspectRatio: 5 / 6, viewMode: 1, autoCropArea: 0.8, responsive: true,
                restore: false, guides: true, center: true, highlight: false,
                cropBoxMovable: true, cropBoxResizable: true, toggleDragModeOnDblclick: false,
            });
        });

        $('#cropImageBtn').click(function() {
            if (!cropper) return;
            const canvas = cropper.getCroppedCanvas({ width: 500, height: 600, fillColor: '#fff' });
            if (!canvas) return;

            canvas.toBlob(function(blob) {
                const previewUrl = URL.createObjectURL(blob);
                currentImageRow.find('.photo-preview').attr('src', previewUrl);
                const reader = new FileReader();
                reader.onload = () => {
                    currentImageRow.find('.cropped-image-data').val(reader.result);
                    currentImageRow.closest('.student-row').addClass('changed');
                };
                reader.readAsDataURL(blob);
                cropModal.hide();
            }, 'image/jpeg', 0.9);
        });

        $('#cropModal').on('hidden.bs.modal', function() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            $('#imageToCrop').attr('src', '#');
            if (currentImageRow) currentImageRow.find('.image-upload').val('');
        });
        
        $(document).on('click', '.update-btn', function() {
            const studentId = $(this).data('student-id');
            const row = $(`tr[data-student-id="${studentId}"]`);
            if (row.find('.is-invalid').length > 0) {
                toastr.error('Please fix validation errors before updating.');
                return;
            }
            updateStudent(studentId);
        });

        $('#updateAllBtn').click(function() {
            if ($('.is-invalid').length > 0) {
                toastr.error('Please fix all validation errors before updating.');
                return;
            }
            const changedRows = $('.student-row.changed');
            if (changedRows.length === 0) {
                toastr.info('No changes detected.');
                return;
            }
            let updateCount = 0, errorCount = 0;
            const progressToast = toastr.info(`Updating ${changedRows.length} students...`, 'Progress', { timeOut: 0 });
            changedRows.each((index, row) => {
                const studentId = $(row).data('student-id');
                setTimeout(() => {
                    updateStudent(studentId, success => {
                        success ? updateCount++ : errorCount++;
                        if (updateCount + errorCount === changedRows.length) {
                            toastr.clear(progressToast);
                            if (errorCount === 0) {
                                toastr.success(`All ${updateCount} students updated successfully!`);
                            } else {
                                toastr.warning(`${updateCount} updated, ${errorCount} failed.`);
                            }
                        }
                    });
                }, index * 200);
            });
        });

        function updateStudent(studentId, callback) {
            const row = $(`tr[data-student-id="${studentId}"]`);
            const formData = new FormData();
            formData.append('student_id', studentId);
            let hasChanges = false;

            const fields = [
                { selector: '.section-select', name: 'section_id' },
                { selector: '.roll-no-input', name: 'roll_no' },
                { selector: '.phone-input', name: 'phone_number' },
                { selector: '.alt-phone-input', name: 'alternate_phone_number' },
                { selector: '.blood-group-select', name: 'blood_group' },
                { selector: '.mother-name-input', name: 'mother_name' },
                { selector: '.address-input', name: 'address' },
                { selector: '.father-occupation-input', name: 'father_occupation' },
                { selector: '.mother-occupation-input', name: 'mother_occupation' },
                { selector: '.car-route-input', name: 'car_route' },
                { selector: '.car-fee-input', name: 'car_fee' },
                { selector: '.hostel-fee-input', name: 'hostel_fee' }
            ];

            fields.forEach(field => {
                const element = row.find(field.selector);
                const originalValue = element.data('original-value');
                if (element.val() !== originalValue) {
                    formData.append(field.name, element.val());
                    hasChanges = true;
                }
            });

            const croppedImageData = row.find('.cropped-image-data').val();
            if (croppedImageData) {
                formData.append('cropped_image_data', croppedImageData);
                hasChanges = true;
            }
            formData.append('current_image', row.find('.current-image').val());

            if (!hasChanges) {
                toastr.info(`No changes for student ID ${studentId}.`);
                if (callback) callback(true);
                return;
            }

            const btn = row.find('.update-btn');
            const originalBtnText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

            $.ajax({
                url: '../action/process-bulk-edit-student.php',
                type: 'POST', data: formData, processData: false, contentType: false,
                success: function(response) {
                    try {
                        response = typeof response === 'string' ? JSON.parse(response) : response;
                        if (response.success) {
                            toastr.success(`Student ${studentId} updated.`);
                            row.removeClass('changed');
                            fields.forEach(field => {
                                const element = row.find(field.selector);
                                element.data('original-value', element.val());
                                element.removeClass('is-invalid is-valid');
                            });
                            row.find('.roll-validation-feedback').remove();
                            if (response.new_image) {
                                row.find('.current-image').val(response.new_image);
                                row.find('.cropped-image-data').val('');
                            }
                            if (callback) callback(true);
                        } else {
                            toastr.error(response.message || 'Update failed.');
                            if (callback) callback(false);
                        }
                    } catch (e) {
                        toastr.error('Invalid server response.');
                        if (callback) callback(false);
                    }
                },
                error: function(xhr) {
                    toastr.error('An error occurred. Check console.');
                    console.error(xhr.responseText);
                    if (callback) callback(false);
                },
                complete: function() {
                    btn.html(originalBtnText).prop('disabled', false);
                }
            });
        }

        // Store original values for comparison
        $('.student-row').each(function() {
            const row = $(this);
            row.find('.editable-field').each(function() {
                $(this).data('original-value', $(this).val());
            });
        });
    });
</script>

<?php include_once("../../includes/body-close.php"); ?>