<?php
$host     = "localhost";
$username = "root";
$password = "";
$database = "freelance_project_tracking";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    http_response_code(500);
    die(json_encode(["success" => false, "message" => "Database connection failed: " . mysqli_connect_error(), "data" => []]));
}

mysqli_set_charset($conn, "utf8mb4");
?>
