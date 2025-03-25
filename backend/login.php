<?php

header('Access-Control-Allow-Origin: http://localhost:3000'); 
header('Access-Control-Allow-Methods: GET, POST'); 
header('Access-Control-Allow-Headers: Content-Type'); 
header('Content-Type: application/json');
include('db.php');


$data = json_decode(file_get_contents("php://input"));



$name = $data->name;
$password = $data->password;


$sql = "SELECT * FROM users WHERE name = :name";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':name', $name);
$stmt->execute();


$user = $stmt->fetch(PDO::FETCH_ASSOC);


if ($user && $password === $user['password']) {
    echo json_encode([
        "status" => "success",
        "message" => "Login successful",
        "user" => $user
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid name or password"
    ]);
}
?>
