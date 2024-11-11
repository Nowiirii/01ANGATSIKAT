<?php
include '../connection.php';
include '../session_check.php';

// Check if the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$sql = "SELECT * FROM organizations";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include '../head.php'; ?>
        <title>Organizations Management</title>
    </head>
<body>

<div class="container mt-5 p-5">
    <h2 class="mb-4">Organizations
        <button class="btn btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#addOrganizationModal">
            <i class="fa-solid fa-plus"></i> Add Organization
        </button>
    </h2>
    <table id="organizationsTable" class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Logo</th>
                <th>Members</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $organization_logo = $row['organization_logo']; // Assuming the logo is stored as the file name
                    $logo_path = !empty($organization_logo) ? 'uploads/' . $organization_logo : 'uploads/default_logo.png';
                    echo "<tr>
                            <td>{$row['organization_name']}</td>
                            <td><img src='$logo_path' alt='Logo' style='width: 50px; height: 50px;'></td>
                            <td>{$row['organization_members']}</td>
                            <td>{$row['organization_status']}</td>
                            <td>
                                <button class='btn btn-primary btn-sm edit-btn mb-3' 
                                        data-bs-toggle='modal' 
                                        data-bs-target='#editOrganizationModal' 
                                        data-id='{$row['organization_id']}'>
                                    <i class='fa-solid fa-pen'></i> Edit
                                </button>
                                <button class='btn btn-danger btn-sm archive-btn mb-3' 
                                        data-id='{$row['organization_id']}'>
                                    <i class='fa-solid fa-box-archive'></i> Archive
                                </button>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center'>No organizations found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Add Organization Modal -->
<div class="modal fade" id="addOrganizationModal" tabindex="-1" role="dialog" aria-labelledby="addOrganizationLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="addOrganizationForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="addOrganizationLabel">Add New Organization</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Form fields -->
          <div class="form-group mb-3">
            <label for="organization_name">Organization Name</label>
            <input type="text" class="form-control" id="organization_name" name="organization_name" required>
          </div>
          <div class="form-group mb-3">
            <label for="organization_logo">Logo</label>
            <input type="file" class="form-control" id="organization_logo" name="organization_logo" required>
          </div>
          <div class="form-group mb-3">
            <label for="organization_members">Members</label>
            <input type="number" class="form-control" id="organization_members" name="organization_members" min="1" required>
          </div>
          <div class="form-group mb-3">
            <label for="organization_status">Status</label>
            <select class="form-control" id="organization_status" name="organization_status">
              <option value="Active">Probationary</option>
              <option value="Active">Level I</option>
              <option value="Inactive">Level II</option>
            </select>
          </div>
          
          <!-- Success Message Alert -->
          <div id="successMessage" class="alert alert-success d-none mt-3" role="alert">
            Organization added successfully!
          </div>  
          <!-- Error Message Alert -->
          <div id="errorMessage" class="alert alert-danger d-none mt-3" role="alert">
            <ul id="errorList"></ul> <!-- List for showing validation errors -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Add Organization</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Organization Modal -->
<div class="modal fade" id="editOrganizationModal" tabindex="-1" role="dialog" aria-labelledby="editOrganizationLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="editOrganizationForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="editOrganizationLabel">Edit Organization</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Hidden field for organization ID -->
          <input type="hidden" id="editOrganizationId" name="organization_id">

          <!-- Other form fields -->
          <div class="form-group mb-3">
            <label for="editOrganizationName">Organization Name</label>
            <input type="text" class="form-control" id="editOrganizationName" name="organization_name" required>
          </div>
          <div class="form-group mb-3">
            <label for="editOrganizationLogo">Logo</label>
            <input type="file" class="form-control" id="editOrganizationLogo" name="organization_logo" accept="image/*">
          </div>
          <div class="form-group mb-3">
            <label for="editOrganizationMembers">Members</label>
            <input type="number" class="form-control" id="editOrganizationMembers" name="organization_members" min="1" required>
          </div>
          <div class="form-group mb-3">
            <label for="editOrganizationStatus">Status</label>
            <select class="form-control" id="editOrganizationStatus" name="organization_status">
              <option value="Active">Probationary</option>
              <option value="Active">Level I</option>
              <option value="Inactive">Level II</option>
            </select>
          </div>

          <!-- Success Message Alert -->
          <div id="successMessage" class="alert alert-success d-none mt-3" role="alert">
            Organization updated successfully!
          </div>  
          <!-- Error Message Alert -->
          <div id="errorMessage" class="alert alert-danger d-none mt-3" role="alert">
            <ul id="errorList"></ul> <!-- List for showing validation errors -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Backend Scripts -->
<script src="js/organizations.js"></script>
</body>
</html>

<?php
$conn->close();
?>
