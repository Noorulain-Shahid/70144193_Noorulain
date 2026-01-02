<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Connect to the same database as admin panel
require_once '../admin/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $username = $conn->real_escape_string(trim($data['username']));
    $fullName = $conn->real_escape_string(trim($data['full_name']));
    $email = $conn->real_escape_string(trim($data['email']));
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $phone = $conn->real_escape_string(trim($data['phone']));
    
    // Check if email already exists
    $checkQuery = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($checkQuery);
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already registered'
        ]);
        exit;
    }
    
    // Insert new user into database
    $query = "INSERT INTO users (username, full_name, email, password, phone, created_at) 
              VALUES ('$username', '$fullName', '$email', '$password', '$phone', NOW())";
    
    if ($conn->query($query)) {
        // Start session and log user in
        session_start();
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_name'] = $fullName;
        $_SESSION['user_email'] = $email;
        
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully',
            'user' => [
                'id' => $conn->insert_id,
                'username' => $username,
                'email' => $email,
                'full_name' => $fullName
            ],
            'session_token' => session_id()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create account: ' . $conn->error
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?>
