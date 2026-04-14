<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "asset_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// USER NAME
$name = ($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '');
$name = trim($name);
if (empty($name)) $name = "User";

// FORM
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $serial = mysqli_real_escape_string($conn, $_POST['serial_number']);
    $csrv = mysqli_real_escape_string($conn, $_POST['csrv_number']);
    $rec_date = mysqli_real_escape_string($conn, $_POST['received_date']);
    $user_name = mysqli_real_escape_string($conn, $_POST['assigned_user']);
    $dept = mysqli_real_escape_string($conn, $_POST['assigned_dept']);

    $bill = "";
    if (!empty($_FILES['bill_attachment']['name'])) {
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        $bill = time() . "_" . $_FILES['bill_attachment']['name'];
        move_uploaded_file($_FILES['bill_attachment']['tmp_name'], "uploads/" . $bill);
    }

    $model = mysqli_real_escape_string($conn, $_POST['model'] ?? '');
    $mac = mysqli_real_escape_string($conn, $_POST['mac_address'] ?? '');
    $storage = mysqli_real_escape_string($conn, $_POST['storage'] ?? '');
    $ram = mysqli_real_escape_string($conn, $_POST['ram'] ?? '');
    $processor = mysqli_real_escape_string($conn, $_POST['processor'] ?? '');
    $manufacturer = mysqli_real_escape_string($conn, $_POST['manufacturer'] ?? '');
    $cartridge = mysqli_real_escape_string($conn, $_POST['cartridge_model'] ?? '');
    $asset_type = mysqli_real_escape_string($conn, $_POST['asset_type'] ?? '');

    $sql = "INSERT INTO assets (category, serial_number, csrv_number, mac_address, storage, ram, processor, model, manufacturer, cartridge_model, asset_type, received_date, bill_attachment, assigned_user, assigned_dept) 
            VALUES ('$category', '$serial', '$csrv', '$mac', '$storage', '$ram', '$processor', '$model', '$manufacturer', '$cartridge', '$asset_type', '$rec_date', '$bill', '$user_name', '$dept')";

    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Asset</title>

<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', Arial, sans-serif;
}

body {
    background:#eef2f7;
}

/* NAVBAR */
.navbar {
    background:#1e293b;
    color:#fff;
    padding:18px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.nav-left {
    font-size:20px;
    font-weight:600;
    color:#f97316;
}

.nav-links a {
    color:#e2e8f0;
    text-decoration:none;
    margin:0 15px;
    font-size:16px;
}

.nav-links a:hover {
    color:#fff;
}

.user-box {
    font-size:15px;
}

.logout-btn {
    background:#ef4444;
    color:#fff;
    padding:6px 12px;
    border-radius:5px;
    text-decoration:none;
    margin-left:10px;
}

/* CONTENT */
.container {
    width:70%;
    margin:30px auto;
}

/* FORM CARD */
.card {
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 2px 8px rgba(0,0,0,0.08);
}

h2 {
    margin-bottom:15px;
}

/* INPUTS */
label {
    font-weight:600;
    margin-top:10px;
    display:block;
}

input, select {
    width:100%;
    padding:10px;
    margin-top:6px;
    margin-bottom:12px;
    border:1px solid #ccc;
    border-radius:5px;
}

/* HIDDEN */
.hidden-section {
    display:none;
    background:#f8fafc;
    padding:10px;
    border-left:4px solid #2563eb;
    margin-bottom:10px;
}

/* BUTTON */
.btn {
    width:100%;
    padding:12px;
    background:#2563eb;
    color:#fff;
    border:none;
    border-radius:6px;
    font-size:15px;
    cursor:pointer;
}

.btn:hover {
    background:#1d4ed8;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="nav-left">Kernex Microsystems</div>

    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="#">Add Asset</a>
        <a href="view_assets.php">View Assets</a>
    </div>

    <div class="user-box">
        <?php echo htmlspecialchars($name); ?>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<!-- FORM -->
<div class="container">
<div class="card">

<h2>Add New Asset</h2>

<form method="POST" enctype="multipart/form-data">

<label>Category</label>
<select name="category" id="assetSelector" onchange="updateForm()" required>
    <option value="">-- Select --</option>
    <option value="CPU">CPU</option>
    <option value="Monitor">Monitor</option>
    <option value="Printer">Printer</option>
    <option value="Other">Other</option>
</select>

<div id="cpu_fields" class="hidden-section">
    <input type="text" name="mac_address" placeholder="MAC Address">
    <input type="text" name="storage" placeholder="Storage">
    <input type="text" name="ram" placeholder="RAM">
    <input type="text" name="processor" placeholder="Processor">
    <input type="text" name="model" placeholder="Model">
</div>

<div id="monitor_fields" class="hidden-section">
    <input type="text" name="model" placeholder="Model">
    <input type="text" name="manufacturer" placeholder="Manufacturer">
</div>

<div id="printer_fields" class="hidden-section">
    <input type="text" name="model" placeholder="Model">
    <input type="text" name="cartridge_model" placeholder="Cartridge">
</div>

<div id="other_fields" class="hidden-section">
    <input type="text" name="asset_type" placeholder="Asset Type">
</div>

<label>Serial Number</label>
<input type="text" name="serial_number" required>

<label>CSRV Number</label>
<input type="text" name="csrv_number" required>

<label>Received Date</label>
<input type="date" name="received_date" required>

<label>User</label>
<input type="text" name="assigned_user" required>

<label>Department</label>
<input type="text" name="assigned_dept" required>

<label>Bill</label>
<input type="file" name="bill_attachment">

<button type="submit" class="btn">Save Asset</button>

</form>
</div>
</div>

<script>
function updateForm() {
    let val = document.getElementById("assetSelector").value;

    document.getElementById("cpu_fields").style.display = "none";
    document.getElementById("monitor_fields").style.display = "none";
    document.getElementById("printer_fields").style.display = "none";
    document.getElementById("other_fields").style.display = "none";

    if(val === "CPU") document.getElementById("cpu_fields").style.display = "block";
    if(val === "Monitor") document.getElementById("monitor_fields").style.display = "block";
    if(val === "Printer") document.getElementById("printer_fields").style.display = "block";
    if(val === "Other") document.getElementById("other_fields").style.display = "block";
}
</script>

</body>
</html>