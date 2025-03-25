<?php

// connect backend and frontend
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "Hashini@123";
$dbname = "parking_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

$stationId = isset($_GET['stationId']) ? $_GET['stationId'] : null;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100; 

if (!$stationId) {
    echo json_encode(["status" => "error", "message" => "Station ID is required"]);
    exit();
}

//  table name based on the station ID
$tableName = '';
switch (strtolower(trim($stationId))) {
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

$query = "SELECT * FROM $tableName ORDER BY first_scan_time DESC LIMIT ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Failed to prepare SQL statement"]);
    exit();
}


$stmt->bind_param("i", $limit);


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