<?php
/**
 * file: get_stats.php
 * author: Iya
 * date: June 25, 2026
 * purpose: Compiles high-level statistical tallies, breakdown ratios, and tracking matrices for administrative analysis.
 */
include 'cors.php';
include 'db_config.php';

$host   = 'bchbyrvggka3okcjwmwv-mysql.services.clever-cloud.com';
$dbname = 'bchbyrvggka3okcjwmwv';
$dbuser = 'usdkgqrlhm5iiwtk';
$dbpass = 'dKzvf9Ns0GxUH041q5Hd';

try {
    // FIX: Variable names match ($dbname, $dbuser, $dbpass)
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    echo json_encode(["error" => "Connection error: " . $exception->getMessage()]);
    exit();
}

$id = $_GET['id'] ?? null;
$role = isset($_GET['role']) ? strtolower($_GET['role']) : null; 
$dept = $_GET['department'] ?? $_GET['dept'] ?? null;
$year = $_GET['year'] ?? date("Y");

$response = [];

// ================= ADMIN / SUPERADMIN LOGIC =================
if ($role === 'superadmin' || $role === 'admin') {
    $stmt1 = $conn->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt2 = $conn->query("SELECT ((SELECT COUNT(*) FROM leaves) + (SELECT COUNT(*) FROM overtimes)) as total");
    $totalFiled = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];

    $response = [
        "totalEmployees" => (int)$totalUsers,
        "total_users" => (int)$totalUsers, // Double key para sure sa frontend
        "totalFiled" => (int)$totalFiled,
        "total_filed" => (int)$totalFiled
    ];
} 
// ================= APPROVER LOGIC =================
else if ($role === 'approver') {
    $stmt1 = $conn->prepare("SELECT ((SELECT COUNT(*) FROM leaves WHERE department = :dept AND status = 'Pending') + (SELECT COUNT(*) FROM overtimes WHERE department = :dept AND status = 'Pending')) as total_pending");
    $stmt1->execute(['dept' => $dept]);
    $res1 = $stmt1->fetch(PDO::FETCH_ASSOC);

    $stmt2 = $conn->prepare("SELECT ((SELECT COUNT(*) FROM leaves WHERE department = :dept AND status IN ('Approved', 'Rejected')) + (SELECT COUNT(*) FROM overtimes WHERE department = :dept AND status IN ('Approved', 'Rejected'))) as total_processed");
    $stmt2->execute(['dept' => $dept]);
    $res2 = $stmt2->fetch(PDO::FETCH_ASSOC);

    $response = [
        "total_pending" => (int)($res1['total_pending'] ?? 0),
        "total_processed" => (int)($res2['total_processed'] ?? 0)
    ];
} 
// ================= EMPLOYEE LOGIC =================
else if ($role === 'employee') {
    $stmt1 = $conn->prepare("SELECT ((SELECT COUNT(*) FROM leaves WHERE employeeId = :id) + (SELECT COUNT(*) FROM overtimes WHERE employeeId = :id)) as total");
    $stmt1->execute(['id' => $id]);
    $response = ["total_requests" => (int)$stmt1->fetch(PDO::FETCH_ASSOC)['total']];
}

echo json_encode($response);
?>
