<?php
session_start();
require_once '../config/database.php';

if ($_POST['action'] === 'staff_login') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Simple password check
        if ($password === $user['password']) {
            $_SESSION['user'] = [
                'id' => $user['user_id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
            
            if ($user['role'] === 'admin') {
                header('Location: ../admin-dashboard.php');
            } else {
                header('Location: ../cashier-dashboard.php');
            }
            exit();
        } else {
            header('Location: ../index.php');
            exit();
        }
    } else {
        header('Location: ../index.php?');
        exit();
    }
} 
elseif ($_POST['action'] === 'student_login') {
    $studentId = $_POST['studentId'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM students WHERE student_id = :student_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $studentId);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['student'] = $student;
        header('Location: ../student-dashboard.php');
        exit();
    } else {
        header('Location: ../index.php');
        exit();
    }
} 
elseif ($_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ../index.php');
    exit();
}
?>