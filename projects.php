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
$allowedStatus = ['Pending', 'In Progress', 'Completed', 'Cancelled'];

$sortMap = [
    'newest'        => 'p.ProjectID DESC',
    'oldest'        => 'p.ProjectID ASC',
    'title_asc'     => 'p.Title ASC',
    'title_desc'    => 'p.Title DESC',
    'deadline_asc'  => 'p.Deadline ASC',
    'deadline_desc' => 'p.Deadline DESC',
    'status_asc'    => 'p.Status ASC'
];

$baseSelect = "SELECT p.*, c.ClientName
               FROM PROJECT p
               JOIN CLIENT c ON p.ClientID = c.ClientID";

switch ($method) {

    case 'GET':

        if (isset($_GET['dropdown'])) {
            $result = mysqli_query($conn, "SELECT ClientID, ClientName FROM CLIENT ORDER BY ClientName");
            $clients = [];
            while ($row = mysqli_fetch_assoc($result)) { $clients[] = $row; }
            echo json_encode(["success" => true, "message" => "Clients loaded", "data" => $clients]);
            break;
        }

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = mysqli_prepare($conn, "$baseSelect WHERE p.ProjectID = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $project = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if ($project) {
                echo json_encode(["success" => true, "message" => "Project found", "data" => $project]);
            } else {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Project not found", "data" => []]);
            }
            break;
        }

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $sortKey = isset($_GET['sort']) && isset($sortMap[$_GET['sort']]) ? $_GET['sort'] : 'newest';
        $orderBy = $sortMap[$sortKey];

        $conditions = [];
        $params = [];
        $types = '';

        if ($search !== '') {
            $conditions[] = "(p.Title LIKE ? OR c.ClientName LIKE ?)";
            $like = "%$search%";
            $params[] = $like; $params[] = $like;
            $types .= "ss";
        }
        if ($status !== '' && in_array($status, $allowedStatus)) {
            $conditions[] = "p.Status = ?";
            $params[] = $status;
            $types .= "s";
        }

        $whereSql = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "$baseSelect $whereSql ORDER BY $orderBy";

        $stmt = mysqli_prepare($conn, $sql);
        if ($types !== '') mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $projects = [];
        while ($row = mysqli_fetch_assoc($result)) { $projects[] = $row; }
        echo json_encode(["success" => true, "message" => "Projects loaded", "data" => $projects]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        $clientId = intval($input['ClientID'] ?? 0);
        $title    = trim($input['Title'] ?? '');
        $desc     = trim($input['Description'] ?? '');
        $deadline = !empty($input['Deadline']) ? $input['Deadline'] : null;
        $status   = trim($input['Status'] ?? 'Pending');

        if ($clientId <= 0 || $title === '' || !in_array($status, $allowedStatus)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Client, title and a valid status are required", "data" => []]);
            break;
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO PROJECT (ClientID, Title, Description, Deadline, Status) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issss", $clientId, $title, $desc, $deadline, $status);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Project created successfully", "data" => ["ProjectID" => mysqli_insert_id($conn)]]);
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn), "data" => []]);
        }
        break;

    case 'PUT':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Project id is required", "data" => []]);
            break;
        }
        $id = intval($_GET['id']);
        $input = json_decode(file_get_contents("php://input"), true);
        $clientId = intval($input['ClientID'] ?? 0);
        $title    = trim($input['Title'] ?? '');
        $desc     = trim($input['Description'] ?? '');
        $deadline = !empty($input['Deadline']) ? $input['Deadline'] : null;
        $status   = trim($input['Status'] ?? 'Pending');

        if ($clientId <= 0 || $title === '' || !in_array($status, $allowedStatus)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Client, title and a valid status are required", "data" => []]);
            break;
        }

        $stmt = mysqli_prepare($conn, "UPDATE PROJECT SET ClientID = ?, Title = ?, Description = ?, Deadline = ?, Status = ? WHERE ProjectID = ?");
        mysqli_stmt_bind_param($stmt, "issssi", $clientId, $title, $desc, $deadline, $status, $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Project updated successfully", "data" => []]);
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn), "data" => []]);
        }
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Project id is required", "data" => []]);
            break;
        }
        $id = intval($_GET['id']);
        $stmt = mysqli_prepare($conn, "DELETE FROM PROJECT WHERE ProjectID = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Project deleted successfully", "data" => []]);
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
