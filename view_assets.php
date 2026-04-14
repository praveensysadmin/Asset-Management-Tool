<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

$conn = mysqli_connect("localhost", "root", "", "asset_db");

// USER NAME
$name = ($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '');
$name = trim($name);
if (empty($name)) $name = "User";

// SEARCH
$search_serial = $_GET['serial'] ?? '';
$search_csrv   = $_GET['csrv'] ?? '';

$sql = "SELECT * FROM assets WHERE 1=1";

if (!empty($search_serial)) {
    $serial = mysqli_real_escape_string($conn, $search_serial);
    $sql .= " AND serial_number LIKE '%$serial%'";
}

if (!empty($search_csrv)) {
    $csrv = mysqli_real_escape_string($conn, $search_csrv);
    $sql .= " AND csrv_number LIKE '%$csrv%'";
}

$sql .= " ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Asset Inventory</title>

<style>
* {
    box-sizing: border-box;
    font-family: 'Segoe UI', Roboto, sans-serif;
}

body {
    margin: 0;
    padding: 0;
    background-color: #eef1f5;
    background-image: radial-gradient(circle at 1px 1px, #dcdfe3 1px, transparent 0);
    background-size: 20px 20px;
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

/* CARD */
.card {
    background: #ffffff;
    padding: 25px;
    border-radius: 12px;
    max-width: 1200px;
    margin: 30px auto;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
}

h2 { margin-bottom: 5px; }

/* SEARCH */
.search-box {
    display: flex;
    gap: 10px;
    margin: 20px 0;
}

.search-box input {
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ddd;
}

.search-box button {
    padding: 10px 18px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 10px;
    overflow: hidden;
}

th {
    background: #1f2937;
    color: #fff;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

tbody tr:hover {
    background: #f9fbfd;
}

td input {
    padding: 6px;
    border-radius: 5px;
    border: 1px solid #ddd;
    width: 100%;
}

/* BUTTONS */
.btn-update {
    background: #22c55e;
    color: white;
    border: none;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
}

.btn-view {
    background: #0ea5e9;
    color: white;
    border: none;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(4px);
}

.modal-content {
    background: #fff;
    margin: 5% auto;
    padding: 25px;
    width: 60%;
    border-radius: 12px;
}

.details-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 10px;
}

.label {
    font-weight: 600;
}

.close {
    float: right;
    font-size: 22px;
    cursor: pointer;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="nav-left">Kernex Microsystems</div>

    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="add_asset.php">Add Asset</a>
        <a href="#">View Assets</a>
    </div>

    <div class="user-box">
        <?php echo htmlspecialchars($name); ?>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="card">
    <h2>Asset Inventory</h2>

    <!-- SEARCH -->
    <form method="GET" class="search-box">
        <input type="text" name="serial" placeholder="Serial Number" value="<?php echo htmlspecialchars($search_serial); ?>">
        <input type="text" name="csrv" placeholder="CSRV Number" value="<?php echo htmlspecialchars($search_csrv); ?>">
        <button type="submit">Search</button>
    </form>

    <!-- TABLE -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Category</th>
                <th>Serial</th>
                <th>CSRV</th>
                <th>User</th>
                <th>Dept</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <form action="update_asset.php" method="POST">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['category']; ?></td>
                    <td><?php echo $row['serial_number']; ?></td>
                    <td><?php echo $row['csrv_number']; ?></td>

                    <td>
                        <input type="hidden" name="asset_id" value="<?php echo $row['id']; ?>">
                        <input type="text" name="new_user" value="<?php echo htmlspecialchars($row['assigned_user']); ?>">
                    </td>

                    <td>
                        <input type="text" name="new_dept" value="<?php echo htmlspecialchars($row['assigned_dept']); ?>">
                    </td>

                    <td>
                        <button class="btn-update">Update</button>
                        <button type="button" class="btn-view"
                        onclick='showDetails(<?php echo json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                        View
                        </button>
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- MODAL -->
<div id="detailsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Full Asset Details</h3>
        <hr>
        <div id="detailsBody" class="details-grid"></div>
    </div>
</div>

<script>
function showDetails(data) {
    let body = document.getElementById('detailsBody');
    body.innerHTML = "";

    for (let key in data) {
        let value = data[key] ? data[key] : "N/A";

        if (key.toLowerCase().includes('bill')) {
            if (value !== "N/A") {
                value = `<a href="uploads/${value}" target="_blank">
                        <img src="uploads/${value}" style="max-width:150px; border-radius:6px;">
                    </a>`;
            }
        }

        body.innerHTML += `
            <div class="label">${key.replaceAll('_',' ').toUpperCase()}</div>
            <div>${value}</div>
        `;
    }

    document.getElementById('detailsModal').style.display = "block";
}

function closeModal() {
    document.getElementById('detailsModal').style.display = "none";
}

window.onclick = function(e) {
    if (e.target == document.getElementById('detailsModal')) {
        closeModal();
    }
}
</script>

</body>
</html>