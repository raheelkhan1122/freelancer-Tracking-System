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
$allowedStatus = ['Pending', 'In Progress', 'Completed'];

$baseSelect = "SELECT t.*, pr.Title AS ProjectTitle
               FROM TASK t
               JOIN PROJECT pr ON t.ProjectID = pr.ProjectID";

// Adds an "IsOverdue" flag so the frontend can highlight late tasks
function attachOverdueFlag($tasks) {
    $today = date('Y-m-d');
    foreach ($tasks as &$task) {
        $task['IsOverdue'] = ($task['Deadline'] && $task['Deadline'] < $today && $task['Status'] !== 'Completed');
    }
    return $tasks;
}

switch ($method) {

    case 'GET':

        if (isset($_GET['dropdown'])) {
            $result = mysqli_query($conn, "SELECT ProjectID, Title FROM PROJECT ORDER BY Title");
            $projects = [];
            while ($row = mysqli_fetch_assoc($result)) { $projects[] = $row; }
            echo json_encode(["success" => true, "message" => "Projects loaded", "data" => $projects]);
            break;
        }

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = mysqli_prepare($conn, "$baseSelect WHERE t.TaskID = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $task = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if ($task) {
                $withFlag = attachOverdueFlag([$task]);
                echo json_encode(["success" => true, "message" => "Task found", "data" => $withFlag[0]]);
            } else {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Task not found", "data" => []]);
            }
            break;
        }

        $search    = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status    = isset($_GET['status']) ? trim($_GET['status']) : '';
        $projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

        $conditions = [];
        $params = [];
        $types = '';

        if ($search !== '') {
            $conditions[] = "(t.Title LIKE ? OR pr.Title LIKE ?)";
            $like = "%$search%";
            $params[] = $like; $params[] = $like;
            $types .= "ss";
        }
        if ($status !== '' && in_array($status, $allowedStatus)) {
            $conditions[] = "t.Status = ?";
            $params[] = $status;
            $types .= "s";
        }
        if ($projectId > 0) {
            $conditions[] = "t.ProjectID = ?";
            $params[] = $projectId;
            $types .= "i";
        }

        $whereSql = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "$baseSelect $whereSql ORDER BY t.TaskID DESC";

        $stmt = mysqli_prepare($conn, $sql);
        if ($types !== '') mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $tasks = [];
        while ($row = mysqli_fetch_assoc($result)) { $tasks[] = $row; }
        $tasks = attachOverdueFlag($tasks);

        echo json_encode(["success" => true, "message" => "Tasks loaded", "data" => $tasks]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        $projectId = intval($input['ProjectID'] ?? 0);
        $title     = trim($input['Title'] ?? '');
        $desc      = trim($input['Description'] ?? '');
        $deadline  = !empty($input['Deadline']) ? $input['Deadline'] : null;
        $status    = trim($input['Status'] ?? 'Pending');

        if ($projectId <= 0 || $title === '' || !in_array($status, $allowedStatus)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Project, title and a valid status are required", "data" => []]);
            break;
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO TASK (ProjectID, Title, Description, Deadline, Status) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issss", $projectId, $title, $desc, $deadline, $status);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Task created successfully", "data" => ["TaskID" => mysqli_insert_id($conn)]]);
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn), "data" => []]);
        }
        break;

    case 'PUT':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Task id is required", "data" => []]);
            break;
        }
        $id = intval($_GET['id']);
        $input = json_decode(file_get_contents("php://input"), true);
        $projectId = intval($input['ProjectID'] ?? 0);
        $title     = trim($input['Title'] ?? '');
        $desc      = trim($input['Description'] ?? '');
        $deadline  = !empty($input['Deadline']) ? $input['Deadline'] : null;
        $status    = trim($input['Status'] ?? 'Pending');

        if ($projectId <= 0 || $title === '' || !in_array($status, $allowedStatus)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Project, title and a valid status are required", "data" => []]);
            break;
        }

        $stmt = mysqli_prepare($conn, "UPDATE TASK SET ProjectID = ?, Title = ?, Description = ?, Deadline = ?, Status = ? WHERE TaskID = ?");
        mysqli_stmt_bind_param($stmt, "issssi", $projectId, $title, $desc, $deadline, $status, $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Task updated successfully", "data" => []]);
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn), "data" => []]);
        }
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Task id is required", "data" => []]);
            break;
        }
        $id = intval($_GET['id']);
        $stmt = mysqli_prepare($conn, "DELETE FROM TASK WHERE TaskID = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Task deleted successfully", "data" => []]);
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
