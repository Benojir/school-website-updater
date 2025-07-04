<?php
include_once("../../includes/auth-check.php");
include_once("../../includes/permission-check.php");
include_once("../../includes/header-open.php");
echo "<title>Add Exam Routines - " . $school_name . "</title>";
include_once("../../includes/header-close.php");
include_once("../../includes/dashboard-navbar.php");

// chech admin permission
if (!hasPermission(PERM_MANAGE_EXAMS)) {
    include_once("../../includes/permission-denied.php");
}

// Fetch required data
$exams = $pdo->query("SELECT * FROM exams WHERE status='active'")->fetchAll();
$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
?>

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h2 class="h5 mb-0"><i class="fas fa-calendar-alt me-2"></i> Add Exam Routine</h2>
        </div>

        <div class="card-body">
            <form id="examRoutineForm" method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Exam</label>
                        <select name="exam_id" class="form-select" required>
                            <option value="" selected disabled>Select Exam</option>
                            <?php foreach ($exams as $exam): ?>
                                <option value="<?= $exam['id'] ?>"><?= $exam['exam_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Class</label>
                        <select name="class_id" id="classSelect" class="form-select" required>
                            <option value="" selected disabled>Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>"><?= $class['class_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Subject Schedule</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table" id="subjectScheduleTable">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th width="120">Written Marks</th>
                                                <th width="120">Oral Marks</th>
                                                <th>Exam Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Room Number</th>
                                                <th width="40"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="noSubjectsRow">
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    No subjects added yet. Select a class to add subjects.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-primary" id="addSubjectBtn" disabled>
                                        <i class="fas fa-plus me-1"></i> Add Subject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="button" class="btn btn-secondary me-2" onclick="window.location.href='../view/list-exam-routines.php'">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" id="submitBtn" class="btn btn-primary" disabled>
                        <i class="fas fa-save me-1"></i> Save Routine
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Subject Selection Modal -->
<div class="modal fade" id="subjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Subject to Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <select class="form-select" id="modalSubjectSelect">
                        <option value="" selected disabled>Select Subject</option>
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Written Marks</label>
                        <input type="number" class="form-control" id="modalTheoryMarks" min="0" value="80">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Oral Marks</label>
                        <input type="number" class="form-control" id="modalPracticalMarks" min="0" value="20">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Exam Date</label>
                    <input type="date" class="form-control" id="modalExamDate" min="<?= date('Y-m-d') ?>">
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Start Time</label>
                        <input type="time" class="form-control" id="modalStartTime">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Time</label>
                        <input type="time" class="form-control" id="modalEndTime">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Room Number</label>
                    <input type="text" class="form-control" id="modalRoomNumber">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddSubject">Add Subject</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const subjectModal = new bootstrap.Modal('#subjectModal');
        let classSubjects = [];
        let addedSubjects = [];

        // Load subjects when class changes
        $('#classSelect').change(function() {
            const classId = $(this).val();
            const subjectSelect = $('#modalSubjectSelect');

            if (classId) {
                $('#addSubjectBtn').prop('disabled', false);

                // Load subjects for this class
                $.get(`../ajax/get-subjects.php?class_id=${classId}`, function(data) {
                    classSubjects = data;
                    subjectSelect.html('<option value="" selected disabled>Select Subject</option>');

                    // Filter out already added subjects
                    const availableSubjects = classSubjects.filter(subject =>
                        !addedSubjects.some(added => added.subject_id == subject.id)
                    );

                    if (availableSubjects.length === 0) {
                        subjectSelect.html('<option value="" selected disabled>No subjects available</option>');
                    } else {
                        $.each(availableSubjects, function(index, subject) {
                            subjectSelect.append(`<option value="${subject.id}">${subject.subject_name}</option>`);
                        });
                    }
                }).fail(function() {
                    toastr.error('Failed to load subjects. Please try again.');
                });
            } else {
                $('#addSubjectBtn').prop('disabled', true);
            }
        });

        // Open subject modal
        $('#addSubjectBtn').click(function() {
            // Reset modal fields
            $('#modalTotalMarks').val(100);
            // $('#modalExamDate').val('');
            $('#modalStartTime').val('10:00');
            $('#modalEndTime').val('12:00');
            // $('#modalRoomNumber').val('');

            subjectModal.show();
        });

        // Add subject to schedule
        $('#confirmAddSubject').click(function() {
            const subjectId = $('#modalSubjectSelect').val();
            const subjectName = $('#modalSubjectSelect option:selected').text();
            const theoryMarks = $('#modalTheoryMarks').val();
            const practicalMarks = $('#modalPracticalMarks').val();
            const examDate = $('#modalExamDate').val();
            const startTime = $('#modalStartTime').val();
            const endTime = $('#modalEndTime').val();
            const roomNumber = $('#modalRoomNumber').val();

            // Basic validation
            if (!subjectId || !examDate || !startTime || !endTime || !roomNumber) {
                toastr.error('Please fill all required fields');
                return;
            }

            if (startTime >= endTime) {
                toastr.error('End time must be after start time');
                return;
            }

            if(addedSubjects.some(subject => subject.subject_id == subjectId)) {
                toastr.error('This subject is already added to the schedule');
                return;
            }

            // Validate exam date and time conflicts like diffrent subjects cannot be in same time and show message
            const conflictingSubject = addedSubjects.find(subject => {
                return subject.exam_date === examDate && (
                    (subject.start_time < endTime && subject.end_time > startTime) ||
                    (subject.start_time < startTime && subject.end_time > startTime)
                );
            });
            
            if (conflictingSubject) {
                toastr.error(`Time conflict with ${conflictingSubject.subject_name}`);
                return;
            }
            

            // Add to table
            const subjectData = {
                subject_id: subjectId,
                subject_name: subjectName,
                theory_marks: theoryMarks,
                practical_marks: practicalMarks,
                exam_date: examDate,
                start_time: startTime,
                end_time: endTime,
                room_number: roomNumber
            };

            addedSubjects.push(subjectData);
            updateSubjectTable();

            // Close modal
            subjectModal.hide();
        });

        // Remove subject from schedule
        $(document).on('click', '.remove-subject', function() {
            const index = $(this).data('index');
            addedSubjects.splice(index, 1);
            updateSubjectTable();
        });

        // Update subject table display
        function updateSubjectTable() {
            const tableBody = $('#subjectScheduleTable tbody');

            if (addedSubjects.length === 0) {
                tableBody.html('<tr id="noSubjectsRow"><td colspan="7" class="text-center text-muted py-4">No subjects added yet</td></tr>');
                $('#submitBtn').prop('disabled', true);
                return;
            }

            tableBody.empty();
            $('#submitBtn').prop('disabled', false);

            addedSubjects.forEach((subject, index) => {
                const row = `
                <tr>
                    <td>
                        ${subject.subject_name}
                        <input type="hidden" name="subjects[${index}][subject_id]" value="${subject.subject_id}">
                        <input type="hidden" name="subjects[${index}][subject_name]" value="${subject.subject_name}">
                    </td>
                    <td>
                        <input type="number" class="form-control" name="subjects[${index}][theory_marks]" 
                            value="${subject.theory_marks}" min="0" required>
                    </td>
                    <td>
                        <input type="number" class="form-control" name="subjects[${index}][practical_marks]" 
                            value="${subject.practical_marks}" min="0" required>
                    </td>
                    <td>
                        <input type="date" class="form-control" name="subjects[${index}][exam_date]" 
                               value="${subject.exam_date}" required>
                    </td>
                    <td>
                        <input type="time" class="form-control" name="subjects[${index}][start_time]" 
                               value="${subject.start_time}" required>
                    </td>
                    <td>
                        <input type="time" class="form-control" name="subjects[${index}][end_time]" 
                               value="${subject.end_time}" required>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="subjects[${index}][room_number]" 
                               value="${subject.room_number}" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-subject" data-index="${index}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
                tableBody.append(row);
            });
        }

        // AJAX form submission
        $('#examRoutineForm').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            const submitBtn = $('#submitBtn');

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...');

            $.ajax({
                url: '../action/save-exam-routine.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => {
                            window.location.href = '../view/list-exam-routines.php';
                        }, 1500);
                    } else {
                        toastr.error(response.message);
                        submitBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Routine');
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('An error occurred. Please try again.');
                    submitBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Routine');
                    console.error(error);
                }
            });
        });
    });
</script>

<?php include_once("../../includes/body-close.php"); ?>