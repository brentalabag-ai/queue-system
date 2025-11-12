<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?error=Admin access required');
    exit();
}

$user = $_SESSION['user'];
$database = new Database();
$db = $database->getConnection();

$section = $_GET['section'] ?? 'dashboard';
?>