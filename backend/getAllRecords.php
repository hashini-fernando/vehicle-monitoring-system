<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json'); // Ensure JSON response
$servername = "localhost";
$username = "root";
$password = "Hashini@123";
$dbname = "parking_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// latest 100 records
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $tableName = "station_table";
    $query = "SELECT * FROM $tableName ORDER BY first_scan_time DESC LIMIT 100";
    $result = $conn->query($query);

    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to fetch latest records"]);
    }
}

// history filtering
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Invalid input data"]);
        exit();
    }

    $stationNo = $data['stationNo'];
    $vehicleNo = $data['vehicleNo'];
    $firstScanTime = $data['firstScanTime'];
    $lastScanTime = $data['lastScanTime'];
    $duration = $data['duration'];

    $tableName = "station_table";
    $query = "SELECT * FROM $tableName WHERE vehicle_no = ? AND first_scan_time >= ? AND last_scan_time <= ? AND duration = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Failed to prepare SQL statement"]);
        exit();
    }

    $stmt->bind_param("ssss", $vehicleNo, $firstScanTime, $lastScanTime, $duration);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    } else {
        echo json_encode(["status" => "error", "message" => "SQL query execution failed"]);
    }

    $stmt->close();
}

$conn->close();
?>