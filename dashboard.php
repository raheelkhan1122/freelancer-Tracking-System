<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once('../db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed", "data" => []]);
    exit();
}

function countRows($conn, $sql) {
    $row = mysqli_fetch_assoc(mysqli_query($conn, $sql));
    return (int) $row['total'];
}

function statusCounts($conn, $table, $column, $statuses) {
    $counts = [];
    foreach ($statuses as $status) {
        $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM $table WHERE $column = ?");
        mysqli_stmt_bind_param($stmt, "s", $status);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        $counts[$status] = (int) $row['total'];
    }
    return $counts;
}

// ---- Stat cards ----
$totalClients   = countRows($conn, "SELECT COUNT(*) AS total FROM CLIENT");
$totalProjects  = countRows($conn, "SELECT COUNT(*) AS total FROM PROJECT");
$totalTasks     = countRows($conn, "SELECT COUNT(*) AS total FROM TASK");
$completedProjects = countRows($conn, "SELECT COUNT(*) AS total FROM PROJECT WHERE Status = 'Completed'");

$earnedRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(Amount), 0) AS total FROM PAYMENT WHERE Status = 'Cleared'"));
$totalEarnings = (float) $earnedRow['total'];

$pendingRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(Amount), 0) AS total FROM PAYMENT WHERE Status = 'Pending'"));
$pendingPayments = (float) $pendingRow['total'];

// ---- Chart data (counts per status, drawn with Canvas on the frontend) ----
$projectStatusChart = statusCounts($conn, 'PROJECT', 'Status', ['Pending', 'In Progress', 'Completed', 'Cancelled']);
$taskStatusChart    = statusCounts($conn, 'TASK', 'Status', ['Pending', 'In Progress', 'Completed']);
$paymentStatusChart = statusCounts($conn, 'PAYMENT', 'Status', ['Pending', 'Cleared', 'Failed', 'Refunded']);

// ---- Recent activity ----
$latestClients = [];
$result = mysqli_query($conn, "SELECT ClientID, ClientName, Email, CreatedDate FROM CLIENT ORDER BY ClientID DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) { $latestClients[] = $row; }

$latestProjects = [];
$result = mysqli_query($conn,
    "SELECT p.ProjectID, p.Title, p.Status, c.ClientName
     FROM PROJECT p JOIN CLIENT c ON p.ClientID = c.ClientID
     ORDER BY p.ProjectID DESC LIMIT 5"
);
while ($row = mysqli_fetch_assoc($result)) { $latestProjects[] = $row; }

$latestTasks = [];
$result = mysqli_query($conn,
    "SELECT t.TaskID, t.Title, t.Status, t.Deadline, pr.Title AS ProjectTitle
     FROM TASK t JOIN PROJECT pr ON t.ProjectID = pr.ProjectID
     ORDER BY t.TaskID DESC LIMIT 5"
);
while ($row = mysqli_fetch_assoc($result)) { $latestTasks[] = $row; }

$latestPayments = [];
$result = mysqli_query($conn,
    "SELECT pay.PaymentID, pay.Amount, pay.Method, pay.Status, pr.Title AS ProjectTitle
     FROM PAYMENT pay JOIN PROJECT pr ON pay.ProjectID = pr.ProjectID
     ORDER BY pay.PaymentID DESC LIMIT 5"
);
while ($row = mysqli_fetch_assoc($result)) { $latestPayments[] = $row; }

echo json_encode([
    "success" => true,
    "message" => "Dashboard data loaded",
    "data" => [
        "stats" => [
            "totalClients"      => $totalClients,
            "totalProjects"     => $totalProjects,
            "totalTasks"        => $totalTasks,
            "totalEarnings"     => $totalEarnings,
            "pendingPayments"   => $pendingPayments,
            "completedProjects" => $completedProjects
        ],
        "charts" => [
            "projectStatus" => $projectStatusChart,
            "taskStatus"    => $taskStatusChart,
            "paymentStatus" => $paymentStatusChart
        ],
        "recent" => [
            "clients"  => $latestClients,
            "projects" => $latestProjects,
            "tasks"    => $latestTasks,
            "payments" => $latestPayments
        ]
    ]
]);

mysqli_close($conn);
?>
