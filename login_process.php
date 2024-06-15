<?php
session_start();

// Database connection
$db = new SQLite3('property_database.db');

// Get credentials
$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

// Prepare and execute query
$stmt = $db->prepare('SELECT * FROM Users WHERE username = :username AND password = :password AND role_id = (SELECT role_id FROM Roles WHERE role_name = :role)');
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$stmt->bindValue(':password', $password, SQLITE3_TEXT);  // Note: Passwords should be hashed and verified properly in production
$stmt->bindValue(':role', $role, SQLITE3_TEXT);

$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

// Check credentials
if ($user) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $role;
    
    // Redirect based on role
    if ($role == 'Admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: user_dashboard.php');
    }
} else {
    // Redirect back to login page with error
    if ($role == 'Admin') {
        header('Location: index.php?error=1');
    } else {
        header('Location: user_login.php?error=1');
    }
}

$db->close();
?>
