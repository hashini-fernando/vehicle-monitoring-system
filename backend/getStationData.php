<?php


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json'); 


$servername = "localhost";
$username = "root";
$password = "Hashini@123";
$dbname = "parking_system";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}


$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid input data"]);
    exit();
}


$stationId = $data['stationId']; 
$vehicleNo = $data['vehicleNo'];
$firstScanTime = $data['firstScanTime'];
$lastScanTime = $data['lastScanTime'];
$duration = $data['duration'];


$tableName = '';
switch (strtolower(trim($stationId))) {
    case '0':
        $tableName = 'station_table';
        break;
    case '1':
        $tableName = 'station_1_table';
        break;
    case '2':
        $tableName = 'station_2_table';
        break;
    case '3':
        $tableName = 'station_3_table';
        break;
    default:
        echo json_encode(["status" => "error", "message" => "Invalid station ID"]);
        exit();
}


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
        
        $row['first_scan_time'] = date('Y-m-d H:i:s', strtotime($row['first_scan_time']));
        $row['last_scan_time'] = date('Y-m-d H:i:s', strtotime($row['last_scan_time']));
        $data[] = $row;
    }
    echo json_encode($data); 
} else {
    echo json_encode(["status" => "error", "message" => "SQL query execution failed"]);
}

$stmt->close();
$conn->close();
?>