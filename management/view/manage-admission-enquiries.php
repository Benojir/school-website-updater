<?php
include_once("../../includes/auth-check.php");
include_once("../../includes/permission-check.php");
require_once("../../includes/header-open.php");
echo "<title>Admission Enquiries Management - " . $school_name . "</title>";
require_once("../../includes/header-close.php");
require_once("../../includes/dashboard-navbar.php");

// Check permission
if (!hasPermission(PERM_MANAGE_STUDENTS)) {
    die("You don't have permission to access this page.");
}

// Initialize variables
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => ['default' => 1, 'min_range' => 1]
]) ?: 1;

$search = safe_htmlspecialchars(trim(filter_input(INPUT_GET, 'search', FILTER_DEFAULT) ?? ''));
$class_filter = filter_input(INPUT_GET, 'class', FILTER_VALIDATE_INT) ?: 0;

$per_page = 25;
$offset = ($page - 1) * $per_page;

try {
    // Build query with search and filter conditions
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(se.name LIKE ? OR se.father_name LIKE ? OR se.phone_number LIKE ? OR se.email LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    if ($class_filter > 0) {
        $where_conditions[] = "se.class_id = ?";
        $params[] = $class_filter;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM admission_enquiries se $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_enquiries = $count_stmt->fetchColumn();
    $total_pages = ceil($total_enquiries / $per_page);
    
    // Get enquiries with class information
    $sql = "SELECT se.*, c.class_name 
            FROM admission_enquiries se 
            LEFT JOIN classes c ON se.class_id = c.id 
            $where_clause 
            ORDER BY se.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($params, [$per_page, $offset]));
    $enquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all classes for filter dropdown
    $classes_stmt = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name");
    $classes = $classes_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error in manage enquiries: " . $e->getMessage());
    $enquiries = [];
    $classes = [];
    $total_enquiries = 0;
    $total_pages = 0;
}
?>

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
        
        .table th, .table td {
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
                        <h4 class="mb-1"><i class="fas fa-graduation-cap me-2"></i>Admission Enquiries Management</h4>
                        <p class="mb-0">Total Pending Applications: <?= number_format($total_enquiries) ?></p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="students-list.php" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Students
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
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= safe_htmlspecialchars($search) ?>" 
                               placeholder="Search by name, father name, phone, or email">
                    </div>
                    <div class="col-md-3">
                        <label for="class" class="form-label">Filter by Class</label>
                        <select class="form-select" id="class" name="class">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>" <?= $class_filter == $class['id'] ? 'selected' : '' ?>>
                                    <?= safe_htmlspecialchars($class['class_name']) ?>
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
                    <!-- <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-success" onclick="exportEnquiries()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div> -->
                </form>
            </div>

            <!-- Results Info -->
            <?php if (!empty($search) || $class_filter > 0): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Showing <?= number_format(count($enquiries)) ?> of <?= number_format($total_enquiries) ?> results
                    <?php if (!empty($search)): ?>
                        for "<strong><?= safe_htmlspecialchars($search) ?></strong>"
                    <?php endif; ?>
                    <?php if ($class_filter > 0): ?>
                        in class: <strong><?= safe_htmlspecialchars($classes[array_search($class_filter, array_column($classes, 'id'))]['class_name'] ?? 'Unknown') ?></strong>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Student Details</th>
                            <th>Contact Info</th>
                            <th>Parent Details</th>
                            <th>Class</th>
                            <th>Application Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($enquiries)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-search fa-3x mb-3"></i>
                                        <h5>No enquiries found</h5>
                                        <p>Try adjusting your search criteria or filters.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($enquiries as $index => $enquiry): ?>
                                <tr id="enquiry-row-<?= $enquiry['id'] ?>">
                                    <td><?= $offset + $index + 1 ?></td>
                                    <td>
                                        <div class="fw-bold"><?= safe_htmlspecialchars($enquiry['name']) ?></div>
                                        <small class="text-muted">
                                            <?= safe_htmlspecialchars($enquiry['gender']) ?>
                                            <?php if (!empty($enquiry['date_of_birth'])): ?>
                                                | DOB: <?= date('d/m/Y', strtotime($enquiry['date_of_birth'])) ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div><i class="fas fa-phone fa-sm me-1"></i><?= safe_htmlspecialchars($enquiry['phone_number']) ?></div>
                                        <?php if (!empty($enquiry['email'])): ?>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope fa-sm me-1"></i><?= safe_htmlspecialchars($enquiry['email']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <strong>Father:</strong> <?= safe_htmlspecialchars($enquiry['father_name']) ?>
                                            <?php if (!empty($enquiry['mother_name'])): ?>
                                                <br><strong>Mother:</strong> <?= safe_htmlspecialchars($enquiry['mother_name']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= safe_htmlspecialchars($enquiry['class_name'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div><?= date('d M Y', strtotime($enquiry['created_at'])) ?></div>
                                        <small class="text-muted"><?= date('h:i A', strtotime($enquiry['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-success btn-sm" 
                                                    onclick="handleEnquiry(<?= $enquiry['id'] ?>, 'approve')"
                                                    title="Approve Application">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm" 
                                                    onclick="handleEnquiry(<?= $enquiry['id'] ?>, 'reject')"
                                                    title="Reject Application">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button type="button" class="btn btn-info btn-sm" 
                                                    onclick="viewEnquiryDetails(<?= $enquiry['id'] ?>)"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="handleEnquiry(<?= $enquiry['id'] ?>, 'delete')"
                                                    title="Delete Enquiry">
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

            <!-- Pagination -->
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
<div class="modal fade" id="enquiryDetailsModal" tabindex="-1" aria-labelledby="enquiryDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enquiryDetailsModalLabel">
                    <i class="fas fa-user-graduate me-2"></i>Enquiry Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="enquiryDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Enhanced enquiry handling function
    function handleEnquiry(enquiry_id, action) {
        const actionTexts = {
            'approve': { title: 'Approve Application', text: 'approve this enquiry and add student to system', color: '#28a745' },
            'reject': { title: 'Reject Application', text: 'reject this enquiry and send notification', color: '#ffc107' },
            'delete': { title: 'Delete Enquiry', text: 'permanently delete this enquiry', color: '#dc3545' }
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
                performEnquiryAction(enquiry_id, action);
            }
        });
    }

    function performEnquiryAction(enquiry_id, action) {
        $.ajax({
            url: "../action/process-admission-enquiry.php",
            type: "POST",
            data: {
                enquiry_id: enquiry_id,
                action: action
            },
            beforeSend: function() {
                Swal.fire({
                    title: "Processing...",
                    text: `Please wait while the enquiry is being processed.`,
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
                        'approve': 'Application approved successfully!',
                        'reject': 'Application rejected and notification sent!',
                        'delete': 'Enquiry deleted successfully!'
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
                        $(`#enquiry-row-${enquiry_id}`).fadeOut(500, function() {
                            $(this).remove();
                            
                            // If no more rows, show empty state or reload page
                            if ($('#enquiry-row-' + enquiry_id).siblings('tr').length === 0) {
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
                        errorMessage = "The requested enquiry was not found.";
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

    function viewEnquiryDetails(enquiry_id) {
        // Show loading in modal
        $('#enquiryDetailsContent').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading enquiry details...</p>
            </div>
        `);
        
        $('#enquiryDetailsModal').modal('show');
        
        // Load enquiry details via AJAX
        $.ajax({
            url: "../ajax/get-enquiry-details.php",
            type: "GET",
            data: { enquiry_id: enquiry_id },
            success: function(response) {
                if (response.success) {
                    $('#enquiryDetailsContent').html(response.html);
                } else {
                    $('#enquiryDetailsContent').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${response.message || 'Failed to load enquiry details.'}
                        </div>
                    `);
                    
                    console.log(response);
                }
            },
            error: function() {
                $('#enquiryDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Failed to load enquiry details. Please try again.
                    </div>
                `);
            }
        });
    }

    // function exportEnquiries() {
    //     const currentUrl = new URL(window.location);
    //     currentUrl.pathname = currentUrl.pathname.replace('manage-admission-enquiries.php', '../action/export-enquiries.php');
    //     window.open(currentUrl.toString(), '_blank');
    // }

    // Auto-submit form on filter change
    $('#class').on('change', function() {
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