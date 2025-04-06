<?php
include 'db_connection.php';

// Initialize counts
$professor_count = $class_count = $branch_count = $module_count = 0;

// Query counts
$professor_sql = "SELECT COUNT(*) as count FROM teacher";
$class_sql = "SELECT COUNT(*) as count FROM salle";
$branch_sql = "SELECT COUNT(*) as count FROM branch";
$module_sql = "SELECT COUNT(*) as count FROM module";

$professor_result = $conn->query($professor_sql);
$class_result = $conn->query($class_sql);
$branch_result = $conn->query($branch_sql);
$module_result = $conn->query($module_sql);

if ($professor_result->num_rows > 0) {
    $professor_count = $professor_result->fetch_assoc()['count'];
}
if ($class_result->num_rows > 0) {
    $class_count = $class_result->fetch_assoc()['count'];
}
if ($branch_result->num_rows > 0) {
    $branch_count = $branch_result->fetch_assoc()['count'];
}
if ($module_result->num_rows > 0) {
    $module_count = $module_result->fetch_assoc()['count'];
}

$conn->close();
?>
