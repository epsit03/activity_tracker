<?php
include('includes/config.php');

session_start(); // Start session if not already started

// Redirect to index if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$response = array('success' => false, 'message' => '');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['membershipType'], $_POST['membershipAmount'], $_POST['edit_id'])) {
        $membershipType = $_POST['membershipType'];
        $membershipAmount = $_POST['membershipAmount'];
        $id = $_POST['edit_id'];

        // Use prepared statements to prevent SQL injection
        $updateQuery = $conn->prepare("UPDATE membership_types SET type = ?, amount = ? WHERE id = ?");
        $updateQuery->bind_param("sdi", $membershipType, $membershipAmount, $id);

        if ($updateQuery->execute()) {
            $response['success'] = true;
            $response['message'] = 'Membership type updated successfully!';
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $updateQuery->close(); // Close prepared statement
    }
}

$edit_id = isset($_GET['id']) ? $_GET['id'] : null;

// Ensure $conn is a valid connection before using it
if ($conn) {
    $editQuery = "SELECT * FROM membership_types WHERE id = $edit_id";
    $result = $conn->query($editQuery);
    $editData = $result->fetch_assoc();
    $result->close(); // Close result set
} else {
    // Handle database connection error
    $response['message'] = 'Database connection error.';
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
                    <div class="row">
                        <div class="col-md-12">

                            <?php if ($response['success']): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <h5><i class="icon fas fa-check"></i> Success</h5>
                                <?php echo $response['message']; ?>
                            </div>
                            <?php elseif (!empty($response['message'])): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <h5><i class="icon fas fa-ban"></i> Error</h5>
                                <?php echo $response['message']; ?>
                            </div>
                            <?php endif; ?>

                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-keyboard"></i> Edit Membership Type</h3>
                                </div>

                                <form method="post" action="">
                                    <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <label for="membershipType">Membership Type</label>
                                                <input type="text" class="form-control" id="membershipType" name="membershipType" placeholder="Enter membership type" value="<?php echo isset($editData['type']) ? $editData['type'] : ''; ?>" required>
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="membershipAmount">Amount</label>
                                                <input type="number" class="form-control" id="membershipAmount" name="membershipAmount" placeholder="Enter membership type amount" value="<?php echo isset($editData['amount']) ? $editData['amount'] : ''; ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <!-- /.content-wrapper -->

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
</body>
</html>
