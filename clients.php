<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once('../db.php');

$method = $_SERVER['REQUEST_METHOD'];
$emailPattern = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

switch ($method) {

    case 'GET':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = mysqli_prepare($conn, "SELECT * FROM CLIENT WHERE ClientID = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $client = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if ($client) {
                echo json_encode(["success" => true, "message" => "Client found", "data" => $client]);
            } else {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Client not found", "data" => []]);
            }
            break;
        }

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit  = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
        $offset = ($page - 1) * $limit;

        $whereSql = '';
        $params = [];
        $types = '';
        if ($search !== '') {
            $whereSql = "WHERE ClientName LIKE ? OR Email LIKE ?";
            $like = "%$search%";
            $params = [$like, $like];
            $types = "ss";
        }

        $countStmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM CLIENT $whereSql");
        if ($types !== '') mysqli_stmt_bind_param($countStmt, $types, ...$params);
        mysqli_stmt_execute($countStmt);
        $total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'];

        $listSql = "SELECT * FROM CLIENT $whereSql ORDER BY ClientID DESC LIMIT ? OFFSET ?";
        $listStmt = mysqli_prepare($conn, $listSql);
        $listParams = array_merge($params, [$limit, $offset]);
        $listTypes = $types . "ii";
        mysqli_stmt_bind_param($listStmt, $listTypes, ...$listParams);
        mysqli_stmt_execute($listStmt);
        $result = mysqli_stmt_get_result($listStmt);

        $clients = [];
        while ($row = mysqli_fetch_assoc($result)) { $clients[] = $row; }

        echo json_encode([
            "success" => true,
            "message" => "Clients loaded",
            "data" => [
                "records" => $clients,
                "pagination" => [
                    "total" => $total,
                    "page" => $page,
                    "limit" => $limit,
                    "totalPages" => (int) ceil($total / $limit)
                ]
            ]
        ]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        $name  = trim($input['ClientName'] ?? '');
        $email = trim($input['Email'] ?? '');
        $phone = trim($input['Phone'] ?? '');

        if ($name === '' || $email === '') {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Client name and email are required", "data" => []]);
            break;
        }
        if (!preg_match($emailPattern, $email)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid email format", "data" => []]);
            break;
        }

        $checkStmt = mysqli_prepare($conn, "SELECT ClientID FROM CLIENT WHERE Email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt))) {
            http_response_code(409);
            echo json_encode(["success" => false, "message" => "A client with this email already exists", "data" => []]);
            break;
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO CLIENT (ClientName, Email, Phone) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $phone);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Client created successfully", "data" => ["ClientID" => mysqli_insert_id($conn)]]);
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn), "data" => []]);
        }
        break;

    case 'PUT':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Client id is required", "data" => []]);
            break;
        }
        $id = intval($_GET['id']);
        $input = json_decode(file_get_contents("php://input"), true);
        $name  = trim($input['ClientName'] ?? '');
        $email = trim($input['Email'] ?? '');
        $phone = trim($input['Phone'] ?? '');

        if ($name === '' || $email === '') {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Client name and email are required", "data" => []]);
            break;
        }
        if (!preg_match($emailPattern, $email)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid email format", "data" => []]);
            break;
        }

        $checkStmt = mysqli_prepare($conn, "SELECT ClientID FROM CLIENT WHERE Email = ? AND ClientID != ?");
        mysqli_stmt_bind_param($checkStmt, "si", $email, $id);
        mysqli_stmt_execute($checkStmt);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt))) {
            http_response_code(409);
            echo json_encode(["success" => false, "message" => "A client with this email already exists", "data" => []]);
            break;
        }

        $stmt = mysqli_prepare($conn, "UPDATE CLIENT SET ClientName = ?, Email = ?, Phone = ? WHERE ClientID = ?");
        mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $phone, $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Client updated successfully", "data" => []]);
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn), "data" => []]);
        }
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Client id is required", "data" => []]);
            break;
        }
        $id = intval($_GET['id']);
        $stmt = mysqli_prepare($conn, "DELETE FROM CLIENT WHERE ClientID = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Client deleted successfully", "data" => []]);
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn), "data" => []]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method not allowed", "data" => []]);
        break;
}

mysqli_close($conn);
?>
