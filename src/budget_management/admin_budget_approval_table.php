<?php
// Include the database connection file
include 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Budget Approvals</title>
    <!--Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Selectize -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<!-- Alert Box -->
<div id="alertBox" class="alert alert-success alert-dismissible fade show d-none" role="alert">
    <span id="alertMessage"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<div class="container mt-4 p-4">
    <h2>Budget Approvals</h2>

    <!-- Approval Table -->
    <table class="table mt-4" id="approvalsTable">
        <thead>
            <tr>
                <th>Organization</th> <!-- New column for organization -->
                <th>Title</th>
                <th>Category</th>
                <th>Attachment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch budget approvals with organization names from the database
            $query = "
                SELECT b.*, o.organization_name 
                FROM budget_approvals b 
                JOIN organizations o ON b.organization_id = o.organization_id";
            $result = mysqli_query($conn, $query);

            while ($row = mysqli_fetch_assoc($result)) {
                $organization = $row['organization_name'];  // Organization name
                $title = $row['title'];
                $category = $row['category'];
                $attachment = $row['attachment'];
                $status = $row['status'];
                $id = $row['approval_id']; // Assuming there's an ID field in your budget_approvals table
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($organization); ?></td> <!-- Organization name -->
                    <td><?php echo htmlspecialchars($title); ?></td>
                    <td><?php echo htmlspecialchars($category); ?></td>
                    <td>
                        <a href="uploads/<?php echo htmlspecialchars($attachment); ?>" class='link-offset-2 link-underline link-underline-opacity-0' target="_blank">
                            <?php echo htmlspecialchars($attachment); ?>
                        </a>
                    </td>
                    <td>
                        <?php 
                        if ($row['status'] == 'Pending') {
                            echo " <span class='badge rounded-pill pending'> ";
                        } else if ($row['status'] == 'Approved') {
                            echo " <span class='badge rounded-pill approved'> ";
                        } else if ($row['status'] == 'Disapproved') {
                            echo " <span class='badge rounded-pill disapproved'> ";
                        }
                        echo ucfirst($row['status']); 
                        ?>
                    </span>
                    </td>
                    <td>
                        <form action="admin_budget_approval.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i> Approve</button>
                        </form>
                        <form action="admin_budget_approval.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="action" value="disapprove">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-xmark"></i> Disapprove</button>
                        </form>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('#approvalsTable').DataTable({
            "paging": true,
            "searching": true,
            "info": true,
            "lengthChange": true,
            "pageLength": 10,
            "ordering": true,
            "order": [],
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        var alertMessageText = document.getElementById('alertMessage').innerText;
        if (alertMessageText !== '') {
            document.getElementById('alertBox').classList.remove('d-none');
        }
    });

</script>
</body>
</html>
