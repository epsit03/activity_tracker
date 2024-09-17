<?php
include('includes/config.php');

$selectQuery = "SELECT * FROM members ORDER BY created_at DESC";
// $selectQuery = "SELECT id, fullname, contact_number, email, address, membership_type, status, amount, duration, expiry_date FROM members ORDER BY created_at DESC";

$result = $conn->query($selectQuery);

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $membershipType = $_POST['membershipType'];
    $membershipAmount = $_POST['membershipAmount'];

    $insertQuery = "INSERT INTO membership_types (type, amount) VALUES ('$membershipType', $membershipAmount)";
    
    if ($conn->query($insertQuery) === TRUE) {
        $successMessage = 'Membership type added successfully!';
        // header("Location: dashboard.php");
        // exit();
    } else {
        echo "Error: " . $insertQuery . "<br>" . $conn->error;
    }
}
?>

<?php include('includes/header.php');?>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
  <?php include('includes/nav.php');?>

 <?php include('includes/sidebar.php');?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    
  <?php include('includes/pagetitle.php');?>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
        
        <div class="col-12">

        <div class="card">
    <div class="card-header">
        <h3 class="card-title">Members DataTable</h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
    <table id="example1" class="table table-bordered table-striped">
        <thead>
   <tr>
            <th>#</th>
            <th>Fullname</th>
            <th>Contact</th>
            <th>Email</th>
            <th>Address</th>
            <!--<th>Type</th>-->
            <th>Amount</th>
            <th>Duration</th>
            <th>Start Date</th>  <!-- Ensure this column is in the HTML -->
            <th>Status</th>
            <th>Actions</th>
        </tr>
</thead>

       <tbody>
    <?php
    $counter = 1;
    while ($row = $result->fetch_assoc()) {
      $duration = (int) $row['duration']; // Duration in months

// Parse the membership start date
$membershipStartDate = new DateTime($row['membership_start_date']);
$expiryDate = clone $membershipStartDate;
$expiryDate->modify("+{$duration} months");

// Get current date
$currentDate = new DateTime();

// Calculate the difference between current date and expiry date
$interval = $currentDate->diff($expiryDate);
$monthsRemaining = $interval->y * 12 + $interval->m;
$daysRemaining = $interval->days;

// Determine the status
if ($currentDate > $expiryDate) {
    // Membership has expired
    $monthsExpired = $monthsRemaining;
    $status = ($monthsExpired > 0) ? "Expired since {$monthsExpired} months" : "Expired";
} else {
    // Membership is still valid
    $status = ($monthsRemaining > 0) ? "Remaining {$monthsRemaining} months" : "Expires today";
}

        // $expiryDate = strtotime($row['expiry_date']);
        // $currentDate = time();
        // $daysDifference = floor(($expiryDate - $currentDate) / (60 * 60 * 24));

        // $membershipStatus = ($daysDifference < 0) ? 'Expired' : 'Active';

        // $membershipTypeId = $row['membership_type'];
        // $membershipTypeQuery = "SELECT type FROM membership_types WHERE id = $membershipTypeId";
        // $membershipTypeResult = $conn->query($membershipTypeQuery);
        // $membershipTypeRow = $membershipTypeResult->fetch_assoc();
        // $membershipTypeName = ($membershipTypeRow) ? $membershipTypeRow['type'] : 'Unknown';

        echo "<tr>";
        echo "<td>{$row['membership_number']}</td>";
        echo "<td>{$row['fullname']}</td>";
        echo "<td>{$row['contact_number']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['address']}</td>";
        // echo "<td>{$membershipTypeName}</td>";
        // echo "<td>{$row['status']}</td>";
        echo "<td>{$row['amt']}</td>"; // Changed from 'amount' to match the database column
        echo "<td>{$row['duration']}</td>";
        echo "<td>{$row['membership_start_date']}</td>";
        echo "<td>{$status}</td>";
//         $startDate = date('d-m-Y', strtotime($row['membership_start_date']));
// echo "<td>{$created_at}</td>";

        // echo "<td>{$row['membership_start_date']}</td>";  
        echo "<td>";

        // if (!empty($row['expiry_date'])) {
        //     echo "<a href='memberProfile.php?id={$row['id']}' class='btn btn-info'><i class='fas fa-id-card'></i></a>";
        // }

        // echo "
        //     <a href='edit_member.php?id={$row['id']}' class='btn btn-primary'><i class='fas fa-edit'></i></a>
        //     <button class='btn btn-danger' onclick='deleteMember({$row['id']})'><i class='fas fa-trash'></i></button>
        // </td>";
        // echo "</tr>";
        if (!empty($row['expiry_date'])) {
            echo "<a href='memberProfile.php?id={$row['id']}' class='btn btn-info'><i class='fas fa-id-card'></i></a>";
        }

        echo "
            <a href='edit_member.php?id={$row['id']}' class='btn btn-primary'><i class='fas fa-edit'></i></a>
            <button class='btn btn-danger' onclick='deleteMember({$row['id']})'><i class='fas fa-trash'></i></button>
        </td>";
        echo "</tr>";
        // $counter++;
    }
    ?>
</tbody>

    </table>
</div>

    <!-- /.card-body -->
</div>
            <!-- /.card -->
          </div>
          <!-- /.col -->

        </div>
        <!-- /.row -->

        
      </div><!--/. container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <!-- Visit codeastro.com for more projects -->
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
  <strong> &copy; <?php echo date('Y');?> deshyoga.org</a> -</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
        <b>Developed By</b> <a href="https://deshyoga.org/">Deshyoga Charitable Trust</a>
        </div>
  </footer>
</div>
<!-- ./wrapper -->

<?php include('includes/footer.php');?>
<script>
  $(function () {
    $("#example1").DataTable({
      "responsive": true,
      "autoWidth": false,
    });
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
  });
</script>

<script>
    function deleteMember(id) {
        if (confirm("Are you sure you want to delete this member?")) {
            window.location.href = 'delete_members.php?id=' + id;
        }
    }
</script>

</body>
</html>
