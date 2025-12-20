<?php
require('db_connect.php');

// Add new columns to feedback table if they don't exist
$queries = [
    "ALTER TABLE feedback ADD COLUMN status VARCHAR(20) DEFAULT 'Pending'",
    "ALTER TABLE feedback ADD COLUMN reply TEXT DEFAULT NULL",
    "ALTER TABLE feedback ADD COLUMN reply_date TIMESTAMP NULL DEFAULT NULL"
];

foreach ($queries as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Executed: $sql <br>";
    } else {
        // Ignore errors if columns already exist
        echo "Note: " . mysqli_error($conn) . " <br>";
    }
}

echo "Feedback table updated successfully. <a href='admin-feedback.php'>Go back to Feedback</a>";
?>
