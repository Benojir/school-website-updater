<?php
include_once("../../includes/auth-check.php");
include_once("../../includes/permission-check.php");
include_once("../../includes/header-open.php");
echo "<title>Admin Profile - " . $school_name . "</title>";
include_once("../../includes/header-close.php");
include_once("../../includes/dashboard-navbar.php");

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

// Get admin details from database
$admin_id = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

// Get admin role name if role_id exists
$role_name = "Super Admin";
if (!empty($admin['role_id'])) {
    $stmt = $pdo->prepare("SELECT name FROM admin_roles WHERE id = ?");
    $stmt->execute([$admin['role_id']]);
    $role = $stmt->fetch();
    $role_name = $role['name'] ?? "Custom Role";
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-cog me-2"></i>Admin Profile</h4>
                </div>
                <div class="card-body">
                    <!-- Admin Information Section -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2"><i class="fas fa-info-circle me-2"></i>Account Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Full Name:</strong> <?= safe_htmlspecialchars($admin['full_name']) ?></p>
                                <p><strong>Username:</strong> <span id="currentUsernameShowSpan"><?= safe_htmlspecialchars($admin['username']) ?></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Admin Type:</strong> <?= safe_htmlspecialchars($role_name) ?></p>
                                <p><strong>Account Status:</strong>
                                    <span class="badge bg-<?= $admin['status'] == 'active' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($admin['status']) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Update Username Form -->
                    <div class="mb-4 border-top pt-3">
                        <h5 class="border-bottom pb-2"><i class="fas fa-user-edit me-2"></i>Update Username</h5>
                        <form id="updateUsernameForm" class="mt-3">
                            <div class="mb-3">
                                <label for="adminName" class="form-label">Admin Name</label>
                                <input type="text" class="form-control" name="adminName" id="adminName"
                                    value="<?= safe_htmlspecialchars($admin['full_name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="currentUsername" class="form-label">Current Username</label>
                                <input type="text" class="form-control" id="currentUsername"
                                    value="<?= safe_htmlspecialchars($admin['username']) ?>" readonly disabled>
                            </div>
                            <div class="mb-3">
                                <label for="newUsername" class="form-label">New Username</label>
                                <input type="text" class="form-control" id="newUsername" name="newUsername"
                                    minlength="3" maxlength="25" required>
                                <div class="form-text">Username must be 3-25 characters long</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirmPasswordUsername" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPasswordUsername"
                                    name="confirmPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Username
                            </button>
                            <div id="usernameMessage" class="mt-2 small"></div>
                        </form>
                    </div>

                    <!-- Update Password Form -->
                    <div class="border-top pt-3">
                        <h5 class="border-bottom pb-2"><i class="fas fa-key me-2"></i>Change Password</h5>
                        <form id="updatePasswordForm" class="mt-3">
                            <div class="mb-3">
                                <label for="currentPassword" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="currentPassword"
                                    name="currentPassword" required>
                            </div>
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="newPassword"
                                    name="newPassword" minlength="6" required>
                                <div class="form-text">Password must be at least 6 characters long</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirmPassword"
                                    name="confirmPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Change Password
                            </button>
                            <div id="passwordMessage" class="mt-2 small"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Update Username Form Submission
        $('#updateUsernameForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = form.find('button[type="submit"]');
            const originalBtnText = btn.html();

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
            $('#usernameMessage').removeClass('text-success text-danger').html('');

            $.ajax({
                url: '../action/update-admin-username.php',
                type: 'POST',
                data: {
                    newUsername: $('#newUsername').val(),
                    confirmPassword: $('#confirmPasswordUsername').val(),
                    adminName: $('#adminName').val() || 'Super Admin' // Use provided name or default
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#usernameMessage').addClass('text-success').html(response.message);
                        $('#currentUsername').val($('#newUsername').val());
                        $('#currentUsernameShowSpan').text($('#newUsername').val());
                        $('#newUsername').val("")
                        $('#confirmPasswordUsername').val("")
                        toastr.success(response.message);
                    } else {
                        $('#usernameMessage').addClass('text-danger').html(response.message);
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    $('#usernameMessage').addClass('text-danger').html('An error occurred. Please try again.');
                    toastr.error('An error occured. Please try again.');
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalBtnText);
                }
            });
        });

        // Update Password Form Submission
        $('#updatePasswordForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = form.find('button[type="submit"]');
            const originalBtnText = btn.html();

            // Validate password match
            if ($('#newPassword').val() !== $('#confirmPassword').val()) {
                $('#passwordMessage').addClass('text-danger').html('Passwords do not match');
                toastr.error('Passwords do not match');
                return;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
            $('#passwordMessage').removeClass('text-success text-danger').html('');

            $.ajax({
                url: '../action/update-admin-password.php',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#passwordMessage').addClass('text-success').html(response.message);
                        form.trigger('reset');
                        toastr.success(response.message);
                    } else {
                        $('#passwordMessage').addClass('text-danger').html(response.message);
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    $('#passwordMessage').addClass('text-danger').html('An error occurred. Please try again.');
                    toastr.error('An error occured. Please try again.');
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalBtnText);
                }
            });
        });

        // Remove all spaces from new username field
        $('#newUsername').on('input', function() {
            $(this).val($(this).val().replace(/\s+/g, ''));
        });

    });
</script>

<?php include_once("../../includes/body-close.php"); ?>