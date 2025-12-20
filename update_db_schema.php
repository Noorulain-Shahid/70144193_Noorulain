<?php
require('db_connect.php');

// 1. Modify products table to support BLOB images
// We first check if we need to change the column type. 
// Note: This might break existing image paths if we don't handle it, 
// but since we are "fixing" it, we assume we start fresh or convert.
// For safety, let's add a NEW column 'image_data' and keep 'image' for backward compat or path.
// Actually, user wants "upload to database", so let's use a new column.

$queries = [
    "ALTER TABLE products ADD COLUMN image_data LONGBLOB",
    "ALTER TABLE categories ADD COLUMN image_data LONGBLOB",
    "ALTER TABLE categories ADD COLUMN image_type VARCHAR(50)", 
    "ALTER TABLE products ADD COLUMN image_type VARCHAR(50)"
];

foreach ($queries as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Executed: $sql <br>";
    } else {
        echo "Error (or already exists): " . mysqli_error($conn) . " <br>";
    }
}

echo "Database schema updated for Image BLOB storage.";
?>
