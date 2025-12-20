<?php
require('db_connect.php');

echo "<h2>Fixing Database and Adding Dummy Feedback</h2>";

// 1. Fix the Table Structure
$alter_queries = [
    "ALTER TABLE feedback ADD COLUMN status VARCHAR(20) DEFAULT 'Pending'",
    "ALTER TABLE feedback ADD COLUMN reply TEXT DEFAULT NULL",
    "ALTER TABLE feedback ADD COLUMN reply_date TIMESTAMP NULL DEFAULT NULL"
];

echo "<h3>Step 1: Updating Table Structure...</h3>";
foreach ($alter_queries as $sql) {
    // We use try-catch or suppress errors because columns might already exist
    try {
        if (@mysqli_query($conn, $sql)) {
            echo "<p style='color:green'>Executed: $sql</p>";
        } else {
            $error = mysqli_error($conn);
            // If error is "Duplicate column name", that's fine.
            if (strpos($error, 'Duplicate column name') !== false) {
                 echo "<p style='color:gray'>Column already exists (Skipped): $sql</p>";
            } else {
                 echo "<p style='color:red'>Error: $error</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>Exception: " . $e->getMessage() . "</p>";
    }
}

// 2. Insert Dummy Data
echo "<h3>Step 2: Inserting Dummy Data...</h3>";

$user_id = 1; // Assuming admin is user_id 1
$user_name = 'Test User';
$email = 'test@example.com';
$message = 'This is a dummy feedback to test the functionality. Great service!';
$rating = 5;
$status = 'Pending';

$query = "INSERT INTO feedback (user_id, user_name, email, message, rating, status, created_at) 
          VALUES ('$user_id', '$user_name', '$email', '$message', '$rating', '$status', NOW())";

if (mysqli_query($conn, $query)) {
    echo "<h3 style='color:green'>Success!</h3>";
    echo "<p>Dummy feedback added successfully.</p>";
    echo "<p><strong><a href='admin-feedback.php'>Click here to go to Feedback Page</a></strong></p>";
} else {
    echo "<h3 style='color:red'>Error Inserting Data</h3>";
    echo "<p>Could not add feedback: " . mysqli_error($conn) . "</p>";
}
?>
