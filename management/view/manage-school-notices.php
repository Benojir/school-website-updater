<?php
include_once("../../includes/auth-check.php");
include_once("../../includes/permission-check.php");
include_once("../../includes/header-open.php");
echo "<title>Manage School Notices - " . $school_name . "</title>";
include_once("../../includes/header-close.php");
include_once("../../includes/dashboard-navbar.php");

// chech admin permission
if (!hasPermission(PERM_MANAGE_NOTICES)) {
    include_once("../../includes/permission-denied.php");
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-bullhorn me-2"></i> School Notices</h4>
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addNoticeModal">
                            <i class="fas fa-plus me-1"></i> Add Notice
                        </button>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="15%">Date</th>
                                    <th width="25%">Title</th>
                                    <th>Content</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="noticesTableBody">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Notice Modal -->
<div class="modal fade" id="addNoticeModal" tabindex="-1" aria-labelledby="addNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addNoticeModalLabel">Add New Notice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addNoticeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="noticeTitle" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="noticeTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="noticeContent" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea id="noticeContent" class="form-control" name="content" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="noticeDate" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="noticeDate" name="notice_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Notice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Notice Modal -->
<div class="modal fade" id="editNoticeModal" tabindex="-1" aria-labelledby="editNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editNoticeModalLabel">Edit Notice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editNoticeForm">
                <input type="hidden" id="editNoticeId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editNoticeTitle" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editNoticeTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="editNoticeContent" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea id="editNoticeContent" class="form-control" name="content" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editNoticeDate" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="editNoticeDate" name="notice_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Notice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteNoticeModal" tabindex="-1" aria-labelledby="deleteNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteNoticeModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this notice? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Load notices on page load function
    function loadNotices() {
        $.ajax({
            url: '../ajax/get-school-notices.php',
            type: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $('#noticesTableBody').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading notices...</td></tr>');
            },
            success: function(response) {
                if (response.success) {
                    $('#noticesTableBody').html(response.html);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(e) {
                console.error("Error loading notices:", e);
                toastr.error("Failed to load notices. Please try again later.");
            }
        });
    }

    // Edit notice button click function
    function editNotice(noticeId) {
        $.ajax({
            url: '../ajax/get-school-notice.php',
            type: 'GET',
            data: { id: noticeId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editNoticeId').val(response.data.id);
                    $('#editNoticeTitle').val(response.data.title);
                    $('#editNoticeContent').val(response.data.content);
                    $('#editNoticeDate').val(response.data.notice_date);
                    $('#editNoticeModal').modal('show');
                } else {
                    toastr.error(response.message);
                }
            }
        });
    }

    // Delete notice button click function
    function deleteNotice(noticeId) {
        $('#deleteNoticeModal').data('id', noticeId).modal('show');
    }

    $(document).ready(function() {

        // Set today's date as default
        $('#noticeDate').val(new Date().toISOString().substr(0, 10));
        // Load notices when the page is ready
        loadNotices();

        // Handle add notice form submission
        $('#addNoticeForm').submit(function(e) {
            e.preventDefault();

            let submitButton = $(this).find('button[type="submit"]');
            submitButton.prop('disabled', true);
            submitButton.html('<i class="fas fa-spinner fa-spin"></i> Adding...');

            $.ajax({
                url: '../action/add-school-notice.php',
                type: 'POST',
                data: {
                    title: $('#noticeTitle').val(),
                    content: $('#noticeContent').val(),
                    notice_date: $('#noticeDate').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#addNoticeModal').modal('hide');
                        loadNotices(); // Reload notices after adding

                        if (response.notification_sent) {
                            toastr.info("Notification sent to parents' devices.");
                        } else {
                            toastr.warning("Notice added but notification could not be sent.");
                        }
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(e) {
                    console.error(e.responseText);
                    toastr.error("Failed to add notice. Please try again later.");
                },
                complete: function() {
                    submitButton.prop('disabled', false);
                    submitButton.html('Save Notice');
                }
            });
        });


        // Handle edit notice form submission
        $('#editNoticeForm').submit(function(e) {
            e.preventDefault();

            let submitButton = $(this).find('button[type="submit"]');
            submitButton.prop('disabled', true);
            submitButton.html('<i class="fas fa-spinner fa-spin"></i> Updating...');

            $.ajax({
                url: '../action/update-school-notice.php',
                type: 'POST',
                data: {
                    id: $('#editNoticeId').val(),
                    title: $('#editNoticeTitle').val(),
                    content: $('#editNoticeContent').val(),
                    notice_date: $('#editNoticeDate').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#editNoticeModal').modal('hide');
                        loadNotices(); // Reload notices after editing
                    } else {
                        toastr.error(response.message);
                    }
                },
                complete: function() {
                    submitButton.prop('disabled', false);
                    submitButton.html('Update Notice');
                }
            });
        });


        // Handle confirm delete
        $('#confirmDelete').click(function() {
            var noticeId = $('#deleteNoticeModal').data('id');
            if (!noticeId) {
                toastr.error("No notice selected for deletion.");
                return;
            }

            $.ajax({
                url: '../action/delete-school-notice.php',
                type: 'POST',
                data: {
                    id: noticeId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#deleteNoticeModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });
    });
</script>

<?php include_once("../../includes/body-close.php"); ?>