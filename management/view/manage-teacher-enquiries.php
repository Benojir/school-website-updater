<?php
include_once("../../includes/auth-check.php");
include_once("../../includes/permission-check.php");
require_once("../../includes/header-open.php");
echo "<title>Teacher Applications Management - " . $school_name . "</title>";
require_once("../../includes/header-close.php");
require_once("../../includes/dashboard-navbar.php");

// Check permission
if (!hasPermission(PERM_MANAGE_TEACHERS)) {
    die("You don't have permission to access this page.");
}

// Initialize variables
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => ['default' => 1, 'min_range' => 1]
]) ?: 1;

$search = safe_htmlspecialchars(trim(filter_input(INPUT_GET, 'search', FILTER_DEFAULT) ?? ''));
$specialization_filter = safe_htmlspecialchars(trim(filter_input(INPUT_GET, 'specialization', FILTER_DEFAULT) ?? ''));

$per_page = 25;
$offset = ($page - 1) * $per_page;

try {
    // Build query with search and filter conditions
    $where_conditions = [];
    $params = [];

    if (!empty($search)) {
        $where_conditions[] = "(ta.name LIKE ? OR ta.email LIKE ? OR ta.phone_number LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
    }

    if (!empty($specialization_filter)) {
        $where_conditions[] = "ta.specialization LIKE ?";
        $params[] = "%$specialization_filter%";
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Get total count
    $count_sql = "SELECT COUNT(*) FROM teacher_applications ta $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_applications = $count_stmt->fetchColumn();
    $total_pages = ceil($total_applications / $per_page);

    // Get applications
    $sql = "SELECT ta.* 
            FROM teacher_applications ta 
            $where_clause 
            ORDER BY ta.application_date DESC 
            LIMIT ? OFFSET ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($params, [$per_page, $offset]));
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all specializations for filter dropdown
    $specializations_stmt = $pdo->query("SELECT DISTINCT specialization FROM teacher_applications WHERE specialization IS NOT NULL AND specialization != '' ORDER BY specialization");
    $specializations = $specializations_stmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (PDOException $e) {
    error_log("Database error in manage teacher applications: " . $e->getMessage());
    $applications = [];
    $specializations = [];
    $total_applications = 0;
    $total_pages = 0;
}
?>

<!-- Similar styling section as manage-admission-enquiries.php -->
<style>
    .table-responsive {
        overflow-x: auto;
        overflow: visible;
    }

    .table-responsive .dropdown-menu {
        z-index: 9999 !important;
        position: absolute !important;
    }

    .dropdown-menu li {
        cursor: pointer;
        user-select: none;
    }

    .dropdown-menu li:hover {
        background-color: var(--bs-primary);
        color: #FFFFFF !important;
    }

    .action-dropdown .dropdown-menu {
        min-width: 150px;
    }

    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }

    .search-filters {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .btn-action {
        padding: 2px 8px;
        font-size: 12px;
        margin: 1px;
    }

    .enquiry-status {
        font-size: 11px;
        padding: 2px 6px;
    }

    @media (max-width: 768px) {
        .btn-sm {
            font-size: 10px;
            padding: 4px 8px;
        }

        .table th,
        .table td {
            font-size: 12px;
            padding: 8px 4px;
        }
    }
</style>

<div class="container-fluid mt-4">
    <!-- Stats Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="stats-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1"><i class="fas fa-chalkboard-teacher me-2"></i>Teacher Applications Management</h4>
                        <p class="mb-0">Total Pending Applications: <?= number_format($total_applications) ?></p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="list-teachers.php" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Teachers
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Search and Filter Section -->
            <div class="search-filters">
                <form method="GET" class="row g-3" id="filterForm">
                    <div class="col-md-5">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                            value="<?= safe_htmlspecialchars($search) ?>"
                            placeholder="Search by name, email, or phone">
                    </div>
                    <div class="col-md-4">
                        <label for="specialization" class="form-label">Filter by Specialization</label>
                        <select class="form-select" id="specialization" name="specialization">
                            <option value="">All Specializations</option>
                            <?php foreach ($specializations as $spec): ?>
                                <option value="<?= safe_htmlspecialchars($spec) ?>" <?= $specialization_filter == $spec ? 'selected' : '' ?>>
                                    <?= safe_htmlspecialchars($spec) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                        <a href="?" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Results Info -->
            <?php if (!empty($search) || !empty($specialization_filter)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Showing <?= number_format(count($applications)) ?> of <?= number_format($total_applications) ?> results
                    <?php if (!empty($search)): ?>
                        for "<strong><?= safe_htmlspecialchars($search) ?></strong>"
                    <?php endif; ?>
                    <?php if (!empty($specialization_filter)): ?>
                        with specialization: <strong><?= safe_htmlspecialchars($specialization_filter) ?></strong>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Applicant Details</th>
                            <th>Contact Info</th>
                            <th>Qualifications</th>
                            <th>Specialization</th>
                            <th>Application Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-search fa-3x mb-3"></i>
                                        <h5>No applications found</h5>
                                        <p>Try adjusting your search criteria or filters.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $index => $application): ?>
                                <tr id="application-row-<?= $application['id'] ?>">
                                    <td><?= $offset + $index + 1 ?></td>
                                    <td>
                                        <div class="fw-bold"><?= safe_htmlspecialchars($application['name']) ?></div>
                                        <small class="text-muted">
                                            <?= $application['has_experience'] == 'yes' ? 'Experienced' : 'Fresher' ?>
                                            <?php if ($application['has_experience'] == 'yes' && !empty($application['previous_school'])): ?>
                                                | Previously at <?= safe_htmlspecialchars($application['previous_school']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div><i class="fas fa-phone fa-sm me-1"></i><?= safe_htmlspecialchars($application['phone_number']) ?></div>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope fa-sm me-1"></i><?= safe_htmlspecialchars($application['email']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="small"><?= safe_htmlspecialchars($application['qualification']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= safe_htmlspecialchars($application['specialization']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div><?= date('d M Y', strtotime($application['application_date'])) ?></div>
                                        <small class="text-muted"><?= date('h:i A', strtotime($application['application_date'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-success btn-sm"
                                                onclick="handleApplication(<?= $application['id'] ?>, 'approve')"
                                                title="Approve Application">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm"
                                                onclick="handleApplication(<?= $application['id'] ?>, 'reject')"
                                                title="Reject Application">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button type="button" class="btn btn-info btn-sm"
                                                onclick="viewApplicationDetails(<?= $application['id'] ?>)"
                                                title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="handleApplication(<?= $application['id'] ?>, 'delete')"
                                                title="Delete Application">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination - Same as manage-admission-enquiries.php -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Enquiries pagination" class="mt-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <p class="text-muted mb-0">
                                Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $total_enquiries) ?>
                                of <?= number_format($total_enquiries) ?> entries
                            </p>
                        </div>
                        <div class="col-md-6">
                            <ul class="pagination justify-content-end mb-0">
                                <?php
                                $query_params = $_GET;
                                unset($query_params['page']);
                                $base_url = '?' . http_build_query($query_params);
                                $base_url = $base_url === '?' ? '?' : $base_url . '&';
                                ?>

                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $base_url ?>page=1" aria-label="First">
                                            <span aria-hidden="true">&laquo;&laquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $base_url ?>page=<?= $page - 1 ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $page + 2);
                                for ($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= $base_url ?>page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $base_url ?>page=<?= $page + 1 ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $base_url ?>page=<?= $total_pages ?>" aria-label="Last">
                                            <span aria-hidden="true">&raquo;&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="applicationDetailsModal" tabindex="-1" aria-labelledby="applicationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applicationDetailsModalLabel">
                    <i class="fas fa-chalkboard-teacher me-2"></i>Application Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="applicationDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Enhanced application handling function
    function handleApplication(application_id, action) {
        const actionTexts = {
            'approve': {
                title: 'Approve Application',
                text: 'approve this application and add teacher to system',
                color: '#28a745'
            },
            'reject': {
                title: 'Reject Application',
                text: 'reject this application and send notification',
                color: '#ffc107'
            },
            'delete': {
                title: 'Delete Application',
                text: 'permanently delete this application',
                color: '#dc3545'
            }
        };

        const actionInfo = actionTexts[action];

        Swal.fire({
            title: actionInfo.title,
            text: `Are you sure you want to ${actionInfo.text}?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: actionInfo.color,
            cancelButtonColor: "#6c757d",
            confirmButtonText: `Yes, ${action}!`,
            cancelButtonText: "Cancel",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                performApplicationAction(application_id, action);
            }
        });
    }

    function performApplicationAction(application_id, action) {
        $.ajax({
            url: "../action/process-teacher-application.php",
            type: "POST",
            data: {
                application_id: application_id,
                action: action
            },
            beforeSend: function() {
                Swal.fire({
                    title: "Processing...",
                    text: `Please wait while the application is being processed.`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                if (response.success) {
                    const successMessages = {
                        'approve': 'Application approved successfully! Teacher added to system.',
                        'reject': 'Application rejected and notification sent!',
                        'delete': 'Application deleted successfully!'
                    };

                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: successMessages[action] || response.message,
                        confirmButtonColor: "#0d6efd",
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        // Remove row with animation
                        $(`#application-row-${application_id}`).fadeOut(500, function() {
                            $(this).remove();

                            // If no more rows, show empty state or reload page
                            if ($('#application-row-' + application_id).siblings('tr').length === 0) {
                                location.reload();
                            }
                        });
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Operation Failed",
                        text: response.message || "Something went wrong while processing the request.",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#dc3545"
                    });
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = "Something went wrong while processing the request.";

                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res.message) {
                        errorMessage = res.message;
                    }
                } catch (e) {
                    if (xhr.status === 403) {
                        errorMessage = "You don't have permission to perform this action.";
                    } else if (xhr.status === 404) {
                        errorMessage = "The requested application was not found.";
                    }
                }

                Swal.fire({
                    icon: "error",
                    title: "Request Failed",
                    text: errorMessage,
                    confirmButtonColor: "#dc3545"
                });

                console.error("Error details:", xhr.responseText);
            }
        });
    }

    function viewApplicationDetails(application_id) {
        // Show loading in modal
        $('#applicationDetailsContent').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading application details...</p>
            </div>
        `);

        $('#applicationDetailsModal').modal('show');

        // Load application details via AJAX
        $.ajax({
            url: "../ajax/get-teacher-application-details.php",
            type: "GET",
            data: {
                application_id: application_id
            },
            success: function(response) {
                if (response.success) {
                    $('#applicationDetailsContent').html(response.html);
                } else {
                    $('#applicationDetailsContent').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${response.message || 'Failed to load application details.'}
                        </div>
                    `);

                    console.log(response);
                }
            },
            error: function() {
                $('#applicationDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Failed to load application details. Please try again.
                    </div>
                `);
            }
        });
    }

    // Auto-submit form on filter change
    $('#specialization').on('change', function() {
        $('#filterForm').submit();
    });

    // Search with debouncing
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            $('#filterForm').submit();
        }, 500);
    });
</script>

<?php include_once("../../includes/body-close.php"); ?>