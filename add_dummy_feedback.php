<?php
require('db_connect.php');

// Dummy feedback data
$user_id = 1; // Assuming admin is user_id 1
$user_name = 'Test User';
$email = 'test@example.com';
$message = 'This is a dummy feedback to test the functionality. Great service!';
$rating = 5;
$status = 'Pending';

$query = "INSERT INTO feedback (user_id, user_name, email, message, rating, status, created_at) 
          VALUES ('$user_id', '$user_name', '$email', '$message', '$rating', '$status', NOW())";

if (mysqli_query($conn, $query)) {
    echo "<h3>Success!</h3>";
    echo "<p>Dummy feedback added successfully.</p>";
    echo "<p><a href='admin-feedback.php'>Go to Feedback Page</a> to see it.</p>";
} else {
    echo "<h3>Error</h3>";
    echo "<p>Could not add feedback: " . mysqli_error($conn) . "</p>";
}
?>
