<?php
// Database connection
require 'connection.php';

// Check if 'event_id' is passed in the URL
if (isset($_GET['event_id']) && !empty($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']); // Get and sanitize the event_id from the URL

    // Prepare SQL query to fetch event details
    $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if event exists
    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
    } else {
        echo "No event found.";
        exit;
    }

    // Fetch the items for Financial Plan
    $itemStmt = $conn->prepare("SELECT * FROM event_items WHERE event_id = ?");
    if ($itemStmt === false) {
        die('Prepare for items failed: ' . $conn->error);
    }
    $itemStmt->bind_param("i", $event_id);
    $itemStmt->execute();
    $itemsResult = $itemStmt->get_result();
    $items = [];
    if ($itemsResult->num_rows > 0) {
        while ($row = $itemsResult->fetch_assoc()) {
            $items[] = $row;
        }
    }
    $stmt->close();
    $itemStmt->close();

    // Fetch items for Financial Summary only if the event is accomplished
    if ($event['accomplishment_status'] === 1) {
        $summaryStmt = $conn->prepare("SELECT * FROM event_items WHERE event_id = ?");
        if ($summaryStmt === false) {
            die('Prepare for summary items failed: ' . $conn->error);
        }
        $summaryStmt->bind_param("i", $event_id);
        $summaryStmt->execute();
        $summaryResult = $summaryStmt->get_result();
        $summaryItems = [];
        if ($summaryResult->num_rows > 0) {
            while ($row = $summaryResult->fetch_assoc()) {
                $summaryItems[] = $row;
            }
        }
        $summaryStmt->close();
    }
} else {
    echo "No event ID provided.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Event Financial Details</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <!--Custom CSS for Sidebar-->
    <link rel="stylesheet" href="../html/sidebar.css" />
    <!--Custom CSS for Budget Overview-->
    <link rel="stylesheet" href="../budget_management/budget.css" />
    <!--Boxicon-->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <!--Font Awesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- Lordicon (for animated icons) -->
    <script src="https://cdn.lordicon.com/lordicon.js"></script>
    <!-- Selectize -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css"
        integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
</head>

<body>
    <!-- Overall Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        <aside class="left-sidebar" id="sidebar">
            <div class="top-bar">
                <div id="toggleSidebar" class="burger-icon">
                    <i class="bx bx-menu"></i>
                </div>
            </div>
            <!-- Sidebar scroll -->
            <div>
                <!-- Brand Logo -->
                <div class="brand-logo logo-container">
                    <a href="../html/officer_dashboard.html" class="logo-img">
                        <img src="angat sikat.png" alt="Angat Sikat Logo" class="sidebar-logo">
                    </a>
                    <span class="logo-text">ANGATSIKAT</span>
                </div>
                <!-- Sidebar navigation -->
                <nav class="sidebar-nav">
                    <ul id="sidebarnav">
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="../dashboard/officer_dashboard.php" aria-expanded="false"
                                data-tooltip="Dashboard">
                                <i class="bx bxs-dashboard"></i>
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" aria-expanded="false" data-tooltip="Manage Events">
                                <i class="bx bx-calendar"></i>
                                <span class="hide-menu">Manage Events</span>
                            </a>
                            <div class="submenu">
                                <a href="../activity_management/calendar.php">› Calendar</a>
                                <a href="../activity_management/activities.php">› Activities</a>
                            </div>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" aria-expanded="false" data-tooltip="Budget">
                                <i class="bx bx-wallet"></i>
                                <span class="hide-menu">Budget</span>
                            </a>
                            <div class="submenu">
                                <a href="../budget_management/budget_overview.php">› Overall</a>
                                <a href="#purchases">› Purchases</a>
                                <a href="#moe">› MOE</a>
                                <a href="../budget_management/budget_approval_table.php">› Approval</a>
                            </div>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="#transactions" aria-expanded="false"
                                data-tooltip="Transactions">
                                <i class="bx bx-dollar-circle"></i>
                                <span class="hide-menu">Transactions</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" aria-expanded="false" data-tooltip="Income & Expenses">
                                <i class="bx bx-chart"></i>
                                <span class="hide-menu">Income & Expenses</span>
                            </a>
                            <div class="submenu">
                                <a href="#income">› Income</a>
                                <a href="../income_and_expenses/expenses.php">› Expenses</a>
                            </div>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="reports.php" aria-expanded="false" data-tooltip="Reports">
                                <i class="bx bx-file"></i>
                                <span class="hide-menu">Reports</span>
                            </a>
                        </li>
                        <li class="sidebar-item profile-container">
                            <a class="sidebar-link" href="../user/profile.html" aria-expanded="false"
                                data-tooltip="Profile">
                                <div class="profile-pic-border">
                                    <img src="byte.png" alt="Profile Picture" class="profile-pic" />
                                </div>
                                <span class="profile-name">BYTE</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <!-- Sidebar End -->

        <!-- JavaScript to toggle submenu visibility -->
        <script>
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.addEventListener('click', function () {
                    // Toggle the submenu for the clicked item
                    this.classList.toggle('show-submenu');

                    // Close other submenus
                    document.querySelectorAll('.sidebar-item').forEach(otherItem => {
                        if (otherItem !== this) {
                            otherItem.classList.remove('show-submenu');
                        }
                    });
                });
            });
        </script>


        <!-- JavaScript to toggle the sidebar -->
        <script>
            document.getElementById('toggleSidebar').addEventListener('click', function () { document.getElementById('sidebar').classList.toggle('collapsed'); });
        </script>

        <!--  2nd Body wrapper -->
        <div class="body-wrapper">
            <!-- Header Start -->
            <header class="app-header">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <ul class="navbar-nav">
                        <li class="nav-item d-block d-xl-none">
                            <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                                <i class="ti ti-menu-2"></i>
                            </a>
                        </li>
                    </ul>

                    <!-- Custom Search and Profile Section -->
                    <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
                        <div class="container-fluid d-flex justify-content-end align-items-center"
                            style="padding: 0 1rem; background-color: transparent;">
                            <!-- Search Bar -->
                            <div class="d-none d-sm-flex position-relative" style=" width: 250px; margin-right: 10px;">
                                <input class="form-control py-1 ps-4 pe-3 border border-dark rounded-pill" type="search"
                                    placeholder="Search" id="searchInput" style="width: 100%; padding: 0.25rem 1rem;">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="position-absolute top-50 start-0 translate-middle-y ms-2 text-secondary"
                                    width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    id="searchIcon" style="margin-left: 8px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-2-2m0 0a7 7 0 1110 0l-2 2m-2-2a7 7 0 110-14 7 7 0 010 14z" />
                                </svg>
                            </div>

                            <!-- Notification Icon -->
                            <button id="notificationBtn"
                                style="background-color: transparent; border: none; padding: 0;">
                                <lord-icon src="https://cdn.lordicon.com/lznlxwtc.json" trigger="hover"
                                    colors="primary:#004024" style="width:30px; height:30px;">
                                </lord-icon>
                            </button>

                            <!-- Profile Dropdown -->
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown"
                                    aria-expanded="false" style="text-decoration: none;">
                                    <img class="border border-dark rounded-circle" src="byte.png" alt="Profile"
                                        style="width: 40px; height: 40px; margin-left: 10px;">
                                    <div class="d-flex flex-column align-items-start ms-2">
                                        <span style="font-weight: bold; color: #004024; text-decoration: none;">BYTE
                                            ORG</span>
                                        <span
                                            style="font-size: 0.85em; color: #6c757d; text-decoration: none;">byte.org@gmail.com</span>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="../user/profile.html"><i class="bx bx-user"></i>
                                            My
                                            Profile</a></li>
                                    <li><a class="dropdown-item" href="../user/login.html"><i class="bx bx-log-out"></i>
                                            Logout</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </header>
            <!-- Header End -->
            <div class="container mt-5 p-4">
                <h2>Event Details</h2>
                <!-- Tabs for Financial Plan and Financial Summary -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="financial-plan-tab" data-bs-toggle="tab" href="#financial-plan"
                            role="tab" aria-controls="financial-plan" aria-selected="true">Financial Plan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="financial-summary-tab" data-bs-toggle="tab" href="#financial-summary"
                            role="tab" aria-controls="financial-summary" aria-selected="false">Financial Summary</a>
                    </li>
                </ul>


                <div class="tab-content mt-4 mx-3">
                    <!-- Financial Plan Tab -->
                    <div class="tab-pane fade show active" id="financial-plan" role="tabpanel"
                        aria-labelledby="financial-plan-tab">

                        <!-- Event Information -->
                        <h4>Title:
                            <?php echo $event['title']; ?>
                        </h4>
                        <p>Venue:
                            <?php echo $event['event_venue']; ?>
                        </p>
                        <p>Start Date:
                            <?php echo $event['event_start_date']; ?>
                        </p>
                        <p>End Date:
                            <?php echo $event['event_end_date']; ?>
                        </p>

                        <h4>Items<button class="btn btn-primary ms-3" data-bs-toggle="modal"
                                data-bs-target="#addItemModal"><i class="fa-solid fa-plus"></i> Add Item</button></h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Amount</th>
                                    <th>Total Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                    if (!empty($items)) {
                        foreach ($items as $item) {
                            $total_amount = $item['quantity'] * $item['amount'];
                            echo "<tr>
                                    <td>{$item['description']}</td>
                                    <td>{$item['quantity']}</td>
                                    <td>{$item['unit']}</td>
                                    <td>{$item['amount']}</td>
                                    <td>{$total_amount}</td>
                                    <td>
                                        <button class='btn edit-btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#editItemModal' data-id='{$item['item_id']}' data-description='{$item['description']}' data-quantity='{$item['quantity']}' data-unit='{$item['unit']}' data-amount='{$item['amount']}'><i class='fa-solid fa-pen'></i> Edit</button>
                                        <button class='btn delete-btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteItemModal' data-id='{$item['item_id']}'><i class='fa-solid fa-trash'></i> Delete</button>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No items found</td></tr>";
                    }
                    ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Financial Summary Tab -->
                    <div class="tab-pane fade" id="financial-summary" role="tabpanel"
                        aria-labelledby="financial-summary-tab">
                        <?php if ($event['accomplishment_status'] === 1): ?>
                        <!-- Event Information -->
                        <h4>Title:
                            <?php echo $event['title']; ?>
                        </h4>
                        <p>Venue:
                            <?php echo $event['event_venue']; ?>
                        </p>
                        <p>Start Date:
                            <?php echo $event['event_start_date']; ?>
                        </p>
                        <p>End Date:
                            <?php echo $event['event_end_date']; ?>
                        </p>

                        <h4>Items<button class="btn btn-primary ms-3" data-bs-toggle="modal"
                                data-bs-target="#summaryAddItemModal"><i class="fa-solid fa-plus"></i> Add Item</button>
                        </h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Amount</th>
                                    <th>Total Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                        if (!empty($summaryItems)) {
                            foreach ($summaryItems as $item) {
                                $total_amount = $item['quantity'] * $item['amount'];
                                echo "<tr>
                                        <td>{$item['description']}</td>
                                        <td>{$item['quantity']}</td>
                                        <td>{$item['unit']}</td>
                                        <td>{$item['amount']}</td>
                                        <td>{$total_amount}</td>
                                        <td>
                                        <button class='btn summary-edit-btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#summaryEditItemModal' data-id='{$item['item_id']}' data-description='{$item['description']}' data-quantity='{$item['quantity']}' data-unit='{$item['unit']}' data-amount='{$item['amount']}'><i class='fa-solid fa-pen'></i> Edit</button>
                                        <button class='btn summary-delete-btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#summaryDeleteItemModal' data-id='{$item['item_id']}'><i class='fa-solid fa-trash'></i> Delete</button>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No summary items found</td></tr>";
                        }
                        ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p>This event has not been accomplished yet. The financial summary will be available once the
                            event is marked as accomplished.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-1" onclick="history.back()"> Cancel </button>
                    <button type="button" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save </button>
                </div>


                <!-- Plan Add Item Modal -->
                <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="addItemForm">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addItemModalLabel">Add Item</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">

                                    <!-- Modal content for adding item -->
                                    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                                    <!-- Event ID -->
                                    <div class="form-group row mb-2">
                                        <div class="col">
                                            <label for="description">Description</label>
                                            <input type="text" class="form-control" id="description" name="description"
                                                required>
                                        </div>
                                        <div class="col">
                                            <label for="quantity">Quantity</label>
                                            <input type="number" class="form-control" id="quantity" name="quantity"
                                                required>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <div class="col">
                                            <label for="unit">Unit</label>
                                            <input type="text" class="form-control" id="unit" name="unit" required>
                                        </div>
                                        <div class="col">
                                            <label for="amount">Amount</label>
                                            <input type="number" step="0.01" class="form-control" id="amount"
                                                name="amount" required>
                                        </div>
                                    </div>

                                    <!-- Success Message Alert -->
                                    <div id="successMessage" class="alert alert-success d-none mt-3" role="alert">
                                        Item added successfully!
                                    </div>
                                    <!-- Error Message Alert -->
                                    <div id="errorMessage" class="alert alert-danger d-none mt-3" role="alert">
                                        <ul id="errorList"></ul> <!-- List for showing validation errors -->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Add Item</button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Item Modal -->
                <div class="modal fade" id="editItemModal" tabindex="-1" role="dialog"
                    aria-labelledby="editItemModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editItemModalLabel">Edit Item</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form id="editItemForm">
                                <div class="modal-body">
                                    <input type="hidden" id="edit_item_id" name="item_id">
                                    <input type="hidden" id="edit_event_id" name="event_id"
                                        value="<?php echo $event_id; ?>"> <!-- Add event_id -->
                                    <div class="form-group row mb-2">
                                        <div class="col">
                                            <label for="edit_description" class="form-label">Description</label>
                                            <input type="text" class="form-control" id="edit_description"
                                                name="description" required>
                                        </div>
                                        <div class="col">
                                            <label for="edit_quantity" class="form-label">Quantity</label>
                                            <input type="number" class="form-control" id="edit_quantity" name="quantity"
                                                required>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <div class="col">
                                            <label for="edit_unit" class="form-label">Unit</label>
                                            <input type="text" class="form-control" id="edit_unit" name="unit" required>
                                        </div>
                                        <div class="col">
                                            <label for="edit_amount" class="form-label">Amount</label>
                                            <input type="number" step="0.01" class="form-control" id="edit_amount"
                                                name="amount" required>
                                        </div>
                                    </div>

                                    <!-- Success Message Alert -->
                                    <div id="successMessage" class="alert alert-success d-none mt-3" role="alert">
                                        Item updated successfully!
                                    </div>
                                    <!-- Error Message Alert -->
                                    <div id="errorMessage" class="alert alert-danger d-none mt-3" role="alert">
                                        <ul id="errorList"></ul> <!-- List for showing validation errors -->
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


                <!-- Delete Item Modal -->
                <div class="modal fade" id="deleteItemModal" tabindex="-1" aria-labelledby="deleteItemModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteItemModalLabel">Delete Item</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete this item?</p>
                                <form id="deleteItemForm">
                                    <input type="hidden" name="item_id" id="delete_item_id">
                                    <input type="hidden" id="delete_event_id" name="event_id"
                                        value="<?php echo $event_id; ?>">

                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Add Item Modal for Summary Tab -->
                <div class="modal fade" id="summaryAddItemModal" tabindex="-1"
                    aria-labelledby="summaryAddItemModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form id="summaryAddItemForm">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="summaryAddItemModalLabel">Add Item</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                                    <div class="form-group row mb-2">
                                        <div class="col">
                                            <label for="summary_description">Description</label>
                                            <input type="text" class="form-control" id="summary_description"
                                                name="description" required>
                                        </div>
                                        <div class="col">
                                            <label for="summary_quantity">Quantity</label>
                                            <input type="number" class="form-control" id="summary_quantity"
                                                name="quantity" required>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <div class="col">
                                            <label for="summary_unit">Unit</label>
                                            <input type="text" class="form-control" id="summary_unit" name="unit"
                                                required>
                                        </div>
                                        <div class="col">
                                            <label for="summary_amount">Amount</label>
                                            <input type="number" step="0.01" class="form-control" id="summary_amount"
                                                name="amount" required>
                                        </div>
                                    </div>
                                    <div id="summarySuccessMessage" class="alert alert-success d-none mt-3"
                                        role="alert">Item added successfully!</div>
                                    <div id="summaryErrorMessage" class="alert alert-danger d-none mt-3" role="alert">
                                        <ul id="summaryErrorList"></ul>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Add Item</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Item Modal for Summary Tab -->
                <div class="modal fade" id="summaryEditItemModal" tabindex="-1"
                    aria-labelledby="summaryEditItemModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form id="summaryEditItemForm">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="summaryEditItemModalLabel">Edit Item</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="summary_edit_item_id" name="item_id">
                                    <input type="hidden" id="summary_edit_event_id" name="event_id"
                                        value="<?php echo $event_id; ?>">
                                    <div class="form-group row mb-2">
                                        <div class="col">
                                            <label for="summary_edit_description">Description</label>
                                            <input type="text" class="form-control" id="summary_edit_description"
                                                name="description" required>
                                        </div>
                                        <div class="col">
                                            <label for="summary_edit_quantity">Quantity</label>
                                            <input type="number" class="form-control" id="summary_edit_quantity"
                                                name="quantity" required>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <div class="col">
                                            <label for="summary_edit_unit">Unit</label>
                                            <input type="text" class="form-control" id="summary_edit_unit" name="unit"
                                                required>
                                        </div>
                                        <div class="col">
                                            <label for="summary_edit_amount">Amount</label>
                                            <input type="number" step="0.01" class="form-control"
                                                id="summary_edit_amount" name="amount" required>
                                        </div>
                                    </div>
                                    <div id="summaryEditSuccessMessage" class="alert alert-success d-none mt-3"
                                        role="alert">Item updated successfully!</div>
                                    <div id="summaryEditErrorMessage" class="alert alert-danger d-none mt-3"
                                        role="alert">
                                        <ul id="summaryEditErrorList"></ul>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Item Modal for Summary Tab -->
                <div class="modal fade" id="summaryDeleteItemModal" tabindex="-1"
                    aria-labelledby="summaryDeleteItemModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="summaryDeleteItemModalLabel">Delete Item</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete this item?</p>
                                <form id="summaryDeleteItemForm">
                                    <input type="hidden" name="item_id" id="summary_delete_item_id">
                                    <input type="hidden" name="event_id" id="summary_delete_event_id"
                                        value="<?php echo $event_id; ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- End of 2nd Body Wrapper -->
    </div>
    <!-- End of Overall Body Wrapper -->

    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
    <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
    <script src="../assets/js/dashboard.js"></script>
    <!-- solar icons -->
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

    <script src="js/event_details.js"></script>
</body>

</html>