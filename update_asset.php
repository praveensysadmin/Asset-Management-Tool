<?php
session_start();

// 1. Security Check
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    exit("Unauthorized");
}

// 2. Database Connection
$conn = mysqli_connect("localhost", "root", "", "asset_db");
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

// 3. Process the Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the data from the hidden form in the table
    $id = $_POST['asset_id'];
    $user = mysqli_real_escape_string($conn, $_POST['new_user']);
    $dept = mysqli_real_escape_string($conn, $_POST['new_dept']);

    // Run the Update Query
    $sql = "UPDATE assets SET assigned_user='$user', assigned_dept='$dept' WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        // Redirect back to the view page with a success flag
        header("location: view_assets.php?msg=success");
        exit;
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>
