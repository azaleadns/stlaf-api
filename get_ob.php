<?php
/**
 * file: get_ob.php
 * author: Iya
 * date: June 25, 2026
 * purpose: Fetches specific official business and field-duty assignment sheets from database registries.
 */
include 'cors.php';
include 'db_config.php';

$empId = trim($_GET['employeeId'] ?? '');
$year  = trim($_GET['year'] ?? date('Y'));

if ($empId === '') {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id, purpose, date, time_in, time_out, status
        FROM ob_logs
        WHERE employee_id = ?
          AND YEAR(date) = ?
        ORDER BY date DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed", "mysql_error" => $conn->error]);
    exit;
}

$stmt->bind_param("si", $empId, $year);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    if (!isset($row['status']) || $row['status'] === '') $row['status'] = 'Pending';
    $data[] = $row;
}

echo json_encode($data);
?>