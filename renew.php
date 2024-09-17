<?php
include('includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$response = array('success' => false, 'message' => '');

if (isset($_GET['id'])) {
    $memberId = $_GET['id'];

    $fetchMemberQuery = "SELECT * FROM members WHERE id = $memberId";
    $fetchMemberResult = $conn->query($fetchMemberQuery);

    if ($fetchMemberResult->num_rows > 0) {
        $memberDetails = $fetchMemberResult->fetch_assoc();
    } else {
        header("Location: members_list.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $membershipType = $_POST['membershipType'];
    $membership_start_date = $_POST['membership_start_date'];
    
    // Set duration based on membership type
    if ($membershipType === 'Lifetime') {
        $membershipDuration = 1200; // Set to 100 years in months (100 years = 1200 months)
        $amount = $_POST['amt']; // Ask for amount only
    } else {
        $membershipDuration = $_POST['duration']; // User enters duration in months
        $amount = $_POST['amt']; // Ask for amount
    }

    // Calculate expiry date based on new start date and duration
    $expiryDate = date('Y-m-d', strtotime("+$membershipDuration months", strtotime($membership_start_date)));

    // Update the membership details
    $updateMemberQuery = "UPDATE members SET created_at = '$membership_start_date', duration = $membershipDuration, expiry_date = '$expiryDate' WHERE id = $memberId";
    $updateMemberResult = $conn->query($updateMemberQuery);

    // Insert renewal record
    $renewDate = date('Y-m-d');
    $insertRenewQuery = "INSERT INTO renew (member_id, amt, renew_date) VALUES ($memberId, $amt, '$renewDate')";
    $insertRenewResult = $conn->query($insertRenewQuery);

    if ($updateMemberResult && $insertRenewResult) {
        $response['success'] = true;
        $response['message'] = 'Membership updated and renewed successfully.';
    } else {
        $response['message'] = 'Error updating membership or renewing: ' . $conn->error;
    }
}
?>

<?php include('includes/header.php');?>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
    <?php include('includes/nav.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-wrapper">
        <?php include('includes/pagetitle.php'); ?>

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
                                <h3 class="card-title"><i class="fas fa-keyboard"></i> Modify Membership Form</h3>
                            </div>

                            <form method="post" action="">
                                <input type="hidden" name="member_id" value="<?php echo $memberId; ?>">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label for="fullname">Full Name</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname"
                                                value="<?php echo $memberDetails['fullname']; ?>" disabled>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="mm">Membership Number</label>
                                            <input type="text" class="form-control" id="mm" name="mm" value="<?php echo $memberDetails['membership_number']; ?>" disabled>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-sm-6">
                                            <label for="membership_start_date">New Start Date</label>
                                            <input type="date" class="form-control" id="membership_start_date" name="membership_start_date" required>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="membershipType">Membership Type</label>
                                            <select class="form-control" id="membershipType" name="membershipType" required>
                                                <option value="Lifetime">Lifetime</option>
                                                <option value="Not Lifetime">Not Lifetime</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-sm-6" id="durationContainer">
                                            <label for="duration">Membership Duration (in months)</label>
                                            <input type="number" class="form-control" id="duration" name="duration" min="1" placeholder="Enter duration">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="amt">Amount</label>
                                            <input type="number" class="form-control" id="amt" name="amt" placeholder="Enter amount" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong> &copy; <?php echo date('Y');?> deshyoga.org</strong> - All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Developed By</b> <a href="https://deshyoga.org/">Deshyoga Charitable Trust</a>
        </div>
    </footer>
</div>

<?php include('includes/footer.php'); ?>

<script>
    $(document).ready(function () {
        function toggleDurationField() {
            var membershipType = $('#membershipType').val();

            if (membershipType === 'Lifetime') {
                $('#durationContainer').hide(); // Hide duration input if Lifetime is selected
                $('#duration').val(''); // Clear the duration field if Lifetime is selected
            } else {
                $('#durationContainer').show(); // Show duration input if Not Lifetime is selected
            }
        }

        $('#membershipType').change(toggleDurationField);

        toggleDurationField(); // Call initially to set the correct state
    });
</script>

</body>
</html>
