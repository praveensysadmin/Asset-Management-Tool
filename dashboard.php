<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

$conn = mysqli_connect("localhost", "root", "", "asset_db");

// USER NAME (Safe Display)
$name = ($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '');
$name = trim($name);
if (empty($name)) $name = "User";

// STATS
$total_assets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM assets"))['total'];
$total_dept = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT assigned_dept) as total FROM assets"))['total'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT assigned_user) as total FROM assets"))['total'];

// --- SECURE SEARCH + FILTER LOGIC ---
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// Build the query dynamically
$query = "SELECT * FROM assets WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (serial_number LIKE ? OR csrv_number LIKE ?)";
    $search_val = "%$search%";
    $params[] = $search_val;
    $params[] = $search_val;
    $types .= "ss";
}

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

$query .= " ORDER BY id DESC LIMIT 10";

// Prepare and Execute
$stmt = mysqli_prepare($conn, $query);

if (!empty($types)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$recent_assets = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Kernex Dashboard</title>
<style>
/* CSS remains exactly the same as your original */
* { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', Arial, sans-serif; }
body { background:#eef2f7; }
.navbar { background:#1e293b; color:#fff; padding:18px 40px; display:flex; justify-content:space-between; align-items:center; }
.nav-left { font-size:20px; font-weight:600; color:#f97316; }
.nav-links a { color:#e2e8f0; text-decoration:none; margin:0 15px; font-size:16px; }
.nav-links a:hover { color:#fff; }
.user-box { font-size:15px; }
.logout-btn { background:#ef4444; color:#fff; padding:6px 12px; border-radius:5px; text-decoration:none; margin-left:10px; }
.logout-btn:hover { background:#dc2626; }
.container { width:90%; margin:25px auto; }
.cards { display:flex; gap:25px; margin-bottom:30px; }
.card { background:#fff; padding:20px; width:220px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.08); transition:0.2s; }
.card:hover { transform:translateY(-3px); }
.card h3 { font-size:14px; color:#666; }
.card p { font-size:26px; margin-top:8px; font-weight:bold; color:#1e293b; }
.table-box { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
.table-box h3 { margin-bottom:10px; }
.filter-bar { display:flex; gap:10px; margin-bottom:15px; }
.filter-bar input, .filter-bar select { padding:8px; border:1px solid #ccc; border-radius:5px; }
table { width:100%; border-collapse:collapse; margin-top:10px; text-align:center; }
th { background:#0f172a; color:#fff; padding:12px; font-size:14px; }
td { padding:12px; border-bottom:1px solid #e5e7eb; font-size:14px; }
tr:hover { background:#f1f5f9; }
.btn { padding:8px 14px; background:#2563eb; color:#fff; text-decoration:none; border-radius:6px; border:none; cursor:pointer; }
.btn:hover { background:#1d4ed8; }
.badge { color:#fff; padding:5px 10px; border-radius:12px; font-size:12px; }
</style>
</head>
<body>

<div class="navbar">
    <div class="nav-left">Kernex Microsystems</div>
    <div class="nav-links">
        <a href="#">Dashboard</a>
        <a href="add_asset.php">Add Asset</a>
        <a href="view_assets.php">View Assets</a>
    </div>
    <div class="user-box">
        <?php echo htmlspecialchars($name); ?>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="container">
    <div class="cards">
        <div class="card"><h3>Total Assets</h3><p><?php echo $total_assets; ?></p></div>
        <div class="card"><h3>Departments</h3><p><?php echo $total_dept; ?></p></div>
        <div class="card"><h3>Users Assigned</h3><p><?php echo $total_users; ?></p></div>
    </div>

    <div class="table-box">
        <h3>Recent Assets</h3>
        <form method="GET" class="filter-bar" id="filterForm">
            <input 
                type="text" 
                name="search" 
                placeholder="Search Serial / CSRV..."
                value="<?php echo htmlspecialchars($search); ?>"
                onkeyup="debounceSubmit()"
            >
            <select name="category" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Categories</option>
                <option value="CPU" <?php if($category=="CPU") echo "selected"; ?>>CPU</option>
                <option value="Monitor" <?php if($category=="Monitor") echo "selected"; ?>>Monitor Casings</option>
                <option value="Other" <?php if($category=="Other") echo "selected"; ?>>Other</option>
                <option value="Printer" <?php if($category=="Printer") echo "selected"; ?>>Printer</option>
            </select>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Serial</th>
                    <th>CSRV</th>
                    <th>User</th>
                    <th>Dept</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = mysqli_fetch_assoc($recent_assets)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td>
                        <?php
                        $cat = $row['category'];
                        $color = "#6b7280";
                        if ($cat == "CPU") $color = "#3b82f6";
                        elseif ($cat == "Monitor") $color = "#10b981";
                        elseif ($cat == "Laptop") $color = "#f59e0b";
                        elseif ($cat == "Printer") $color = "#ef4444";
                        ?>
                        <span class="badge" style="background:<?php echo $color; ?>">
                            <?php echo htmlspecialchars($cat); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($row['serial_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['csrv_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['assigned_user']); ?></td>
                    <td><?php echo htmlspecialchars($row['assigned_dept']); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <a href="view_assets.php" class="btn" style="margin-top:15px; display:inline-block;">View All</a>
    </div>
</div>

<script>
let timer;
function debounceSubmit() {
    clearTimeout(timer);
    timer = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
}
</script>

</body>
</html>
