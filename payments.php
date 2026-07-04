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
$allowedStatus  = ['Pending', 'Cleared', 'Failed', 'Refunded'];
$allowedMethods = ['PayPal', 'Stripe', 'Bank Transfer', 'Crypto', 'Cash'];

$baseSelect = "SELECT pay.*, pr.Title AS ProjectTitle
               FROM PAYMENT pay
               JOIN PROJECT pr ON pay.ProjectID = pr.ProjectID";

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
            $stmt = mysqli_prepare($conn, "$baseSelect WHERE pay.PaymentID = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $payment = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if ($payment) {
                echo json_encode(["success" => true, "message" => "Payment found", "data" => $payment]);
            } else {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Payment not found", "data" => []]);
            }
            break;
        }

        $search    = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status    = isset($_GET['status']) ? trim($_GET['status']) : '';
        $method_   = isset($_GET['method']) ? trim($_GET['method']) : '';
        $projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

        $conditions = [];
        $params = [];
        $types = '';

        if ($search !== '') {
            $conditions[] = "pr.Title LIKE ?";
            $params[] = "%$search%";
            $types .= "s";
        }
        if ($status !== '' && in_array($status, $allowedStatus)) {
            $conditions[] = "pay.Status = ?";
            $params[] = $status;
            $types .= "s";
        }
        if ($method_ !== '' && in_array($method_, $allowedMethods)) {
            $conditions[] = "pay.Method = ?";
            $params[] = $method_;
            $types .= "s";
        }
        if ($projectId > 0) {
            $conditions[] = "pay.ProjectID = ?";
            $params[] = $projectId;
            $types .= "i";
        }

        $whereSql = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "$baseSelect $whereSql ORDER BY pay.PaymentID DESC";

        $stmt = mysqli_prepare($conn, $sql);
        if ($types !== '') mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $payments = [];
        while ($row = mysqli_fetch_assoc($result)) { $payments[] = $row; }
        echo json_encode(["success" => true, "message" => "Payments loaded", "data" => $payments]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        $projectId = intval($input['ProjectID'] ?? 0);
        $amount    = floatval($input['Amount'] ?? 0);
        $payDate   = !empty($input['PaymentDate']) ? $input['PaymentDate'] : null;
        $method_   = trim($input['Method'] ?? 'Bank Transfer');
        $status    = trim($input['Status'] ?? 'Pending');

        if ($projectId <= 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "A valid project is required", "data" => []]);
            break;
        }
        if ($amount <= 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Amount must be greater than zero", "data" => []]);
            break;
        }
        if (!in_array($method_, $allowedMethods)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid payment method", "data" => []]);
            break;
        }
        if (!in_array($status, $allowedStatus)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid payment status", "data" => []]);
            break;
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO PAYMENT (ProjectID, Amount, PaymentDate, Method, Status) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "idsss", $projectId, $amount, $payDate, $method_, $status);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Payment recorded successfully", "data" => ["PaymentID" => mysqli_insert_id($conn)]]);
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn), "data" => []]);
        }
        break;

    case 'PUT':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Payment id is required", "data" => []]);
            break;
        }
        $id = intval($_GET['id']);
        $input = json_decode(file_get_contents("php://input"), true);
        $projectId = intval($input['ProjectID'] ?? 0);
        $amount    = floatval($input['Amount'] ?? 0);
        $payDate   = !empty($input['PaymentDate']) ? $input['PaymentDate'] : null;
        $method_   = trim($input['Method'] ?? 'Bank Transfer');
        $status    = trim($input['Status'] ?? 'Pending');

        if ($projectId <= 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "A valid project is required", "data" => []]);
            break;
        }
        if ($amount <= 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Amount must be greater than zero", "data" => []]);
            break;
        }
        if (!in_array($method_, $allowedMethods)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid payment method", "data" => []]);
            break;
        }
        if (!in_array($status, $allowedStatus)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid payment status", "data" => []]);
            break;
        }

        $stmt = mysqli_prepare($conn, "UPDATE PAYMENT SET ProjectID = ?, Amount = ?, PaymentDate = ?, Method = ?, Status = ? WHERE PaymentID = ?");
        mysqli_stmt_bind_param($stmt, "idsssi", $projectId, $amount, $payDate, $method_, $status, $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Payment updated successfully", "data" => []]);
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn), "data" => []]);
        }
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Payment id is required", "data" => []]);
            break;
        }
        $id = intval($_GET['id']);
        $stmt = mysqli_prepare($conn, "DELETE FROM PAYMENT WHERE PaymentID = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Payment deleted successfully", "data" => []]);
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
