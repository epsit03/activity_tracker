<?php
include('includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$response = array('success' => false, 'message' => '');

$membershipTypesQuery = "SELECT id, type, amt FROM membership_types";
$membershipTypesResult = $conn->query($membershipTypesQuery);

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

function generateUniqueFileName($filename)
{
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $basename = pathinfo($filename, PATHINFO_FILENAME);
    $uniqueName = $basename . '_' . time() . '.' . $ext;
    return $uniqueName;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $contactNumber = $_POST['contactNumber'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $country = $_POST['country'];
    $postcode = $_POST['postcode'];
    $occupation = $_POST['occupation'];
     $membership_start_date = $_POST['membership_start_date'];
    $membershipType = $_POST['membershipType'];
    $amt = $_POST['amt'];
    $duration = $membershipType == 'Lifetime' ? 1200 : $_POST['duration']; // Default 100 years for Lifetime

    $photoUpdate = "";
    $uploadedPhoto = $_FILES['photo'];

    if (!empty($uploadedPhoto['name'])) {
        $uniquePhotoName = generateUniqueFileName($uploadedPhoto['name']);
        move_uploaded_file($uploadedPhoto['tmp_name'], 'uploads/member_photos/' . $uniquePhotoName);
        $photoUpdate = ", photo='$uniquePhotoName'";
    }

    $updateQuery = "UPDATE members SET fullname='$fullname', dob='$dob', gender='$gender', 
                    contact_number='$contactNumber', email='$email', address='$address', country='$country', 
                    postcode='$postcode', occupation='$occupation', membership_start_date = '$membership_start_date', membership_type='$membershipType', 
                    amt='$amt', duration='$duration' $photoUpdate
                    WHERE id = $memberId";

    if ($conn->query($updateQuery) === TRUE) {
        $response['success'] = true;
        $response['message'] = 'Member updated successfully!';
        
        header("Location: manage_members.php");
        exit();
    } else {
        $response['message'] = 'Error: ' . $conn->error;
    }
}
?>

<?php include('includes/header.php');?>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
    <?php include('includes/nav.php'); ?>

    <?php include('includes/sidebar.php'); ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <?php include('includes/pagetitle.php'); ?>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Info boxes -->
                <div class="row">
                    <!-- left column -->
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

                        <!-- general form elements -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-keyboard"></i> Edit Member Details</h3>
                            </div>
                            <!-- /.card-header -->
                            <!-- form start -->
                            <form method="post" action="" enctype="multipart/form-data">
                            <input type="hidden" name="member_id" value="<?php echo $memberId; ?>">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label for="fullname">Full Name</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname"
                                                placeholder="Enter full name" required value="<?php echo $memberDetails['fullname']; ?>">
                                        </div>
                                        <div class="col-sm-3">
                                            <label for="dob">Date of Birth</label>
                                            <input type="date" class="form-control" id="dob" name="dob" value="<?php echo $memberDetails['dob']; ?>" required>
                                        </div>
                                        <div class="col-sm-3">
                                            <label for="gender">Gender</label>
                                            <select class="form-control" id="gender" name="gender" required>
                                                <option value="Male" <?php echo ($memberDetails['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                                <option value="Female" <?php echo ($memberDetails['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                                <option value="Other" <?php echo ($memberDetails['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-sm-6">
                                            <label for="contactNumber">Contact Number</label>
                                            <input type="tel" class="form-control" id="contactNumber"
                                                name="contactNumber" placeholder="Enter contact number" value="<?php echo $memberDetails['contact_number']; ?>" required>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                placeholder="Enter email" value="<?php echo $memberDetails['email']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-sm-6">
                                            <label for="address">Address</label>
                                            <input type="text" class="form-control" id="address" name="address"
                                                placeholder="Enter address" value="<?php echo $memberDetails['address']; ?>" required>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="country">Country</label>
                                            <input type="text" class="form-control" id="country" name="country"
                                                placeholder="Enter country" value="<?php echo $memberDetails['country']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-sm-6">
                                            <label for="postcode">Postcode</label>
                                            <input type="text" class="form-control" id="postcode" name="postcode"
                                                placeholder="Enter postcode" value="<?php echo $memberDetails['postcode']; ?>" required>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="occupation">Occupation</label>
                                            <input type="text" class="form-control" id="occupation" name="occupation"
                                                placeholder="Enter occupation" value="<?php echo $memberDetails['occupation']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-sm-6">
                                            <label for="membership_start_date">Membership Start Date</label>
                                            <input type="date" class="form-control" id="membership_start_date" name="membership_start_date"
                                                   value="<?php echo $memberDetails['membership_start_date']; ?>" required>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="membershipType">Time of Membership</label>
                                            <select class="form-control" id="membershipType" name="membershipType" required onchange="toggleMembershipFields()">
                                                <option value="Lifetime" <?php echo ($memberDetails['membership_type'] == 'Lifetime') ? 'selected' : ''; ?>>Lifetime</option>
                                                <option value="Not Lifetime" <?php echo ($memberDetails['membership_type'] == 'Not Lifetime') ? 'selected' : ''; ?>>Not Lifetime</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mt-3" id="amountField">
                                        <div class="col-sm-6">
                                            <label for="amt">Amount</label>
                                            <input type="text" class="form-control" id="amt" name="amt"
                                                placeholder="Enter amount" value="<?php echo $memberDetails['amt']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mt-3" id="durationField">
                                        <div class="col-sm-6">
                                            <label for="duration">Duration (in months)</label>
                                            <input type="number" class="form-control" id="duration" name="duration"
                                                placeholder="Enter duration" value="<?php echo $memberDetails['duration']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-sm-6">
                                            <label for="photo">Member Photo</label>
                                            <input type="file" class="form-control" id="photo" name="photo">
                                            <small class="text-muted">Leave it blank if you don't want to change the photo.</small>
                                        </div>
                                    </div>

                                </div>
                                <!-- /.card-body -->

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                        <!-- /.card -->

                    </div>
                    <!--/.col (left) -->

                </div>
                <!-- /.row -->

            </div><!--/. container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <footer class="main-footer">
        <strong> &copy; <?php echo date('Y');?> deshyoga.org</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Developed By</b> <a href="https://deshyoga.org/">Deshyoga Charitable Trust</a>
        </div>
    </footer>
</div>
<!-- ./wrapper -->

<?php include('includes/footer.php'); ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const membershipType = document.getElementById('membershipType').value;
        toggleMembershipFields(membershipType);

        document.getElementById('membershipType').addEventListener('change', function () {
            toggleMembershipFields(this.value);
        });
    });

    function toggleMembershipFields(membershipType) {
        const amountField = document.getElementById('amountField');
        const durationField = document.getElementById('durationField');

        if (membershipType === 'Lifetime') {
            amountField.style.display = 'block';
            durationField.style.display = 'none';
            document.getElementById('duration').value = 1200; // Default duration of 100 years for Lifetime
        } else {
            amountField.style.display = 'block';
            durationField.style.display = 'block';
        }
    }
</script>
</body>
</html>
