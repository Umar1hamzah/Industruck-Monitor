<?php
header('Content-Type: application/json');
require_once '../controllers/AuthController.php';
require_once '../models/Report.php';
require_once '../config/database.php'; // Added Database include

$auth = new AuthController();
$auth->requireRole(['admin', 'manager']);

$database = new Database();
$db = $database->getConnection();
$reportModel = new Report($db);

$summary = $reportModel->getSummary();
$violations = $reportModel->getViolationsByType();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5; // Default 5 trips per page
$offset = ($page - 1) * $limit;

$recent_trips = $reportModel->getRecentTrips($limit, $offset);
$total_trips = $reportModel->getTotalTripsCount();

echo json_encode([
   'success' => true,
   'summary' => $summary,
   'violations' => $violations,
   'recent_trips' => $recent_trips,
   'total_trips' => $total_trips,
   'page' => $page,
   'limit' => $limit
]);
?>