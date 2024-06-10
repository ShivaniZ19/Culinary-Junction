<?php
session_start();
include('dbconnection.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Validate and sanitize recipe_id from URL
if (!isset($_GET['id'])) {
    echo "Error: Recipe ID is missing.";
    exit();
}

$recipe_id = intval($_GET['id']);
$current_user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session on login

// First, verify that the recipe belongs to the logged-in user
$sql = "SELECT picture_url FROM Recipes WHERE recipe_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $recipe_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $picture_url = $row['picture_url'];

    // Proceed to delete the recipe from the database
    $sql = "DELETE FROM Recipes WHERE recipe_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $recipe_id, $current_user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Check if the file exists and delete it
        if (file_exists($picture_url)) {
            unlink($picture_url);
        }
        echo "<p>Recipe deleted successfully.</p>";
        echo "<br><button onclick=\"location.href='dashboard4.php'\">Back to Dashboard</button>";
    } else {
        echo "<p>Error deleting recipe.</p>";
        echo "<br><button onclick=\"location.href='dashboard4.php'\">Back to Dashboard</button>";
    }
} else {
    echo "Recipe not found or you do not have permission to delete this recipe.";
}

$stmt->close();
$conn->close();
?>
