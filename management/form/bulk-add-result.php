<?php
include_once("../../includes/auth-check.php");
include_once("../../includes/permission-check.php");
include_once("../../includes/header-open.php");
echo "<title>Add Results - " . $school_name . "</title>";
include_once("../../includes/header-close.php");
include_once("../../includes/dashboard-navbar.php");

// chech admin permission
if (!hasPermission(PERM_MANAGE_RESULTS)) {
    include_once("../../includes/permission-denied.php");
}

// Get student IDs from URL
$student_ids = $_GET['student_ids'] ?? '';
if (empty($student_ids)) {
    die("No students selected");
}

$student_ids = explode(',', $student_ids);

// Fetch students data
$placeholders = implode(',', array_fill(0, count($student_ids), '?'));
$stmt = $pdo->prepare("
    SELECT s.*, c.class_name, sec.section_name 
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN sections sec ON s.section_id = sec.id
    WHERE s.student_id IN ($placeholders) AND s.status = 'Active'
");
$stmt->execute($student_ids);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($students)) {
    die("<script>toastr.error('No students found');</script>");
}

// Verify all students are from the same class
$class_ids = array_unique(array_column($students, 'class_id'));
if (count($class_ids) > 1) {
    die("Selected students must be from the same class");
}

$class_id = $class_ids[0];
$class_name = $students[0]['class_name'];

// Fetch active exams
$stmt = $pdo->prepare("
    SELECT * FROM exams 
    WHERE status = 'active'
    ORDER BY exam_date DESC
");
$stmt->execute();
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subjects for the class
$stmt = $pdo->prepare("
    SELECT * FROM subjects 
    WHERE class_id = ?
    ORDER BY subject_name
");
$stmt->execute([$class_id]);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .is-invalid {
        border: 1px solid #dc3545;
    }

    .is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    table,
    th,
    td {
        text-align: center;
        /* Horizontal centering */
        vertical-align: middle;
        /* Vertical centering */
    }

    /* Chrome, Safari, Edge, Opera */
    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Firefox */
    input[type=number] {
        appearance: textfield;
    }
</style>

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3><i class="fas fa-plus-circle"></i> Bulk Add Results</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> You are adding results for <?= count($students) ?> students from <?= safe_htmlspecialchars($class_name) ?>
            </div>
            <div class="alert alert-dismissible fade show" role="alert" id="responseAlert" style="display: none;"></div>

            <form id="bulkResultForm">
                <input type="hidden" name="class_id" value="<?= safe_htmlspecialchars($class_id) ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="exam_id" class="form-label">Exam</label>
                        <select class="form-select" id="exam_id" name="exam_id" required>
                            <option value="">-- Select Exam --</option>
                            <?php foreach ($exams as $exam): ?>
                                <option value="<?= $exam['id'] ?>">
                                    <?= safe_htmlspecialchars($exam['exam_name']) ?> (<?= safe_htmlspecialchars($exam['exam_date']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="subject_filter" class="form-label">Subjects to Include</label>
                        <select class="form-select" id="subject_filter" multiple>
                            <option value="all" selected>All Subjects</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject['id'] ?>"><?= safe_htmlspecialchars($subject['subject_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Roll No</th>
                                <?php foreach ($subjects as $subject): ?>
                                    <th class="subject-col" data-subject-id="<?= $subject['id'] ?>">
                                        <?= safe_htmlspecialchars($subject['subject_name']) ?>
                                        <br>
                                        <span style="color:rgb(136, 189, 250);" id="id-<?= safe_htmlspecialchars($subject['id']) ?>"></span>
                                        <input type="hidden" name="subjects[<?= $subject['id'] ?>][subject_id]" value="<?= $subject['id'] ?>">
                                        <input type="hidden" name="subjects[<?= $subject['id'] ?>][subject_name]" value="<?= safe_htmlspecialchars($subject['subject_name']) ?>">
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <?= safe_htmlspecialchars($student['name']) ?>
                                        <input type="hidden" name="students[<?= $student['student_id'] ?>][student_id]" value="<?= $student['student_id'] ?>">
                                        <input type="hidden" name="students[<?= $student['student_id'] ?>][name]" value="<?= safe_htmlspecialchars($student['name']) ?>">
                                    </td>
                                    <td><?= safe_htmlspecialchars($student['class_name']) ?></td>
                                    <td><?= safe_htmlspecialchars($student['section_name']) ?></td>
                                    <td><?= safe_htmlspecialchars($student['roll_no']) ?></td>

                                    <?php foreach ($subjects as $subject): ?>
                                        <td class="subject-col" data-subject-id="<?= $subject['id'] ?>">
                                            <div class="row g-2">
                                                <div class="col-12" style="text-align: left;">
                                                    <!-- <label class="small">Total</label> -->
                                                    <input type="hidden" class="form-control total-marks"
                                                        name="students[<?= $student['student_id'] ?>][subjects][<?= $subject['id'] ?>][total_marks]"
                                                        min="0" step="0.01" required readonly tabindex="-1">
                                                </div>
                                                <div class="col-12" style="text-align: left;">
                                                    <label class="small">Written</label>
                                                    <input type="number" class="form-control theory-marks"
                                                        name="students[<?= $student['student_id'] ?>][subjects][<?= $subject['id'] ?>][theory_marks]"
                                                        min="0" step="0.01" />
                                                </div>
                                                <div class="col-12" style="text-align: left;">
                                                    <label class="small">Oral</label>
                                                    <input type="number" class="form-control practical-marks"
                                                        name="students[<?= $student['student_id'] ?>][subjects][<?= $subject['id'] ?>][practical_marks]"
                                                        min="0" step="0.01" />
                                                </div>
                                                <div class="col-12" style="text-align: left;">
                                                    <label class="small">Obtained</label>
                                                    <input type="number" class="form-control obtained-marks"
                                                        name="students[<?= $student['student_id'] ?>][subjects][<?= $subject['id'] ?>][obtained_marks]"
                                                        min="0" step="0.01" readonly tabindex="-1">
                                                </div>
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save All Results
                    </button>
                    <a href="../view/students-list.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Hide the mark entry table initally
        $('.table-responsive').hide();

        // Validate wrong input
        $('body').on('input', 'input.theory-marks, input.practical-marks', function() {
            let original = $(this).val();

            // Remove all spaces
            let noSpaces = original.replace(/\s/g, '');

            // Allow only digits
            let isValid = /^\d*$/.test(noSpaces);

            // Apply red border if invalid
            $(this).toggleClass('is-invalid', !isValid);

            // Update the value without spaces
            $(this).val(noSpaces);
        });



        // Subject filter functionality
        $('#subject_filter').select({
            placeholder: "Select subjects to show",
            allowClear: true
        });

        $('#subject_filter').on('change', function() {
            const selected = $(this).val();

            if (selected && selected.includes('all')) {
                // Show all subjects
                $('.subject-col').show();
            } else if (selected && selected.length > 0) {
                // Show only selected subjects
                $('.subject-col').hide();
                selected.forEach(subjectId => {
                    $(`.subject-col[data-subject-id="${subjectId}"]`).show();
                });
            } else {
                // If nothing selected, show all
                $('.subject-col').show();
            }
        });

        // Auto-calculate obtained marks when theory/practical marks change
        // Modify the exam change handler to also load existing results
        $('#exam_id').change(function() {
            const examId = $(this).val();
            if (!examId) {
                $('.table-responsive').hide();
                return;
            } else {
                $('.table-responsive').show();
            }

            // Show loading
            const submitBtn = $('#bulkResultForm').find('[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading exam data...'
            );

            // Fetch exam routine data AND existing results
            $.when(
                $.ajax({
                    url: '../ajax/get-exam-routine.php',
                    type: 'GET',
                    data: {
                        exam_id: examId,
                        class_id: <?= $class_id ?>
                    },
                    dataType: 'json'
                }),
                $.ajax({
                    url: '../ajax/get-existing-results.php',
                    type: 'GET',
                    data: {
                        exam_id: examId,
                        class_id: <?= $class_id ?>
                    },
                    dataType: 'json'
                })
            ).then(function(routineResponse, resultsResponse) {
                // Handle routine data
                if (routineResponse[0].success) {
                    window.subjectMarksData = {};
                    routineResponse[0].data.forEach(subject => {
                        let theoryMarks = parseFloat(subject.theory_marks) || 0;
                        let practicalMarks = parseFloat(subject.practical_marks) || 0;
                        let totalMarks = theoryMarks + practicalMarks;

                        $(`#id-${subject.subject_id}`).text(`(${totalMarks})`);

                        // Store the full subject data for validation
                        window.subjectMarksData[subject.subject_id] = {
                            theory_marks: subject.theory_marks,
                            practical_marks: subject.practical_marks
                        };

                        $(`input[name*="[${subject.subject_id}][total_marks]"]`).val(totalMarks);
                        $(`input[name*="[${subject.subject_id}][theory_marks]"]`).attr('max', subject.theory_marks);
                        $(`input[name*="[${subject.subject_id}][practical_marks]"]`).attr('max', subject.practical_marks);
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: routineResponse[0].message || "Failed to load exam routine",
                        allowOutsideClick: false
                    });
                }

                // Handle existing results
                if (resultsResponse[0].success && resultsResponse[0].data.length > 0) {
                    resultsResponse[0].data.forEach(result => {
                        const studentId = result.student_id;
                        result.subjects.forEach(subject => {

                            if (subject.theory_marks > 0) {
                                $(`input[name*="[${studentId}][subjects][${subject.subject_id}][theory_marks]"]`).val(subject.theory_marks);
                            }
                            if (subject.practical_marks > 0) {
                                $(`input[name*="[${studentId}][subjects][${subject.subject_id}][practical_marks]"]`).val(subject.practical_marks);
                            }
                            if (subject.obtained_marks > 0) {
                                $(`input[name*="[${studentId}][subjects][${subject.subject_id}][obtained_marks]"]`).val(subject.obtained_marks);
                            }
                        });
                    });
                }
            }).fail(function(xhr) {
                toastr.error('Error loading data: ' + xhr.statusText);
            }).always(function() {
                submitBtn.prop('disabled', false).html(originalText);
            });
        });

        // Real-time validation for theory/practical marks
        $(document).on('input', '.theory-marks, .practical-marks', function() {
            // Debugging - check if data exists
            if (!window.subjectMarksData) {
                console.log('subjectMarksData not initialized');
                return;
            }

            const subjectId = $(this).closest('[data-subject-id]').data('subject-id');
            const subjectData = window.subjectMarksData[subjectId];

            if (!subjectData) {
                console.log('No data for subject ID:', subjectId);
                return;
            }

            const fieldType = $(this).hasClass('theory-marks') ? 'theory' : 'practical';
            const maxValue = fieldType === 'theory' ? subjectData.theory_marks : subjectData.practical_marks;
            const currentValue = parseFloat($(this).val()) || 0;

            // Validate against max value
            if (currentValue > maxValue) {
                // $(this).val(maxValue);
                $(this).css('background-color', '#FF7777');
                $('.theory-marks, .practical-marks').not(this).attr('readonly', true);

                toastr.warning(`${fieldType.charAt(0).toUpperCase() + fieldType.slice(1)} marks cannot exceed ${maxValue}`);
                console.log(`Max ${fieldType} marks: ${maxValue}, Current value: ${currentValue}`);

                Swal.fire({
                    icon: "error",
                    title: `${fieldType.charAt(0).toUpperCase() + fieldType.slice(1)} marks cannot exceed ${maxValue}`,
                    allowOutsideClick: false
                });
            } else {
                $(this).css('background-color', '');
                $('.theory-marks, .practical-marks').attr('readonly', false);
            }

            // Calculate obtained marks
            const row = $(this).closest('.row');
            const theory = parseFloat(row.find('.theory-marks').val()) || 0;
            const practical = parseFloat(row.find('.practical-marks').val()) || 0;
            row.find('.obtained-marks').val((theory + practical).toFixed(2));

            console.log(`Subject ID: ${subjectId}, Theory: ${theory}, Practical: ${practical}`);
        });

        // Form submission
        $('#bulkResultForm').submit(function(e) {
            e.preventDefault();

            let isValid = true;

            // Validate all obtained marks
            $('.obtained-marks').each(function() {
                console.log($(this).val());

                const totalMarksInput = $(this).closest('.row').find('.total-marks');
                const totalMarks = parseFloat(totalMarksInput.val()) || 0;
                const obtainedMarks = parseFloat($(this).val()) || 0;

                if (obtainedMarks > totalMarks) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                    toastr.error('Obtained marks cannot exceed total marks');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Check only visible theory/practical marks (not hidden ones)
            $('.subject-col:visible .theory-marks, .subject-col:visible .practical-marks').each(function() {
                const value = parseFloat($(this).val());
                if (isNaN(value) || value < 0) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                    toastr.error('Please fill all required fields with valid values');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) return;

            // Show loading state
            const submitBtn = $(this).find('[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...'
            );

            // Submit via AJAX
            $.ajax({
                url: '../action/save-bulk-add-result.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#responseAlert').addClass('alert-success').html(response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                        $('responseAlert').removeClass('alert-danger').removeClass('alert-info');
                    } else {
                        toastr.error(response.message);
                        $('#responseAlert').addClass('alert-danger').html(response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                        $('#responseAlert').removeClass('alert-success').removeClass('alert-info');
                    }
                    $('#responseAlert').show();
                },
                error: function(xhr) {
                    toastr.error('Error: ' + xhr.statusText);
                    $('#responseAlert').addClass('alert-danger').html('Error: ' + xhr.statusText + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                    $('#responseAlert').removeClass('alert-success').removeClass('alert-info');
                    $('#responseAlert').show();
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
</script>

<?php include_once("../../includes/body-close.php"); ?>