<?php
session_start();

// Get the action, recipe ID, and user ID from the URL parameters
$action = $_GET['action'];
$recipe_id = $_GET['recipe_id'];
$user_id = $_GET['user_id'];

// Include the database connection
include('dbconnection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Switch based on the action to interact with the appropriate table
switch ($action) {
    case 'like':
        // Insert a like for the recipe by the user
        $stmt = $conn->prepare("INSERT INTO likes (user_id, recipe_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $user_id, $recipe_id);
        $stmt->execute();
        break;
    
    case 'fav':
        // Insert a favorite for the recipe by the user
        $stmt = $conn->prepare("INSERT INTO favourites (user_id, recipe_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $user_id, $recipe_id);
        $stmt->execute();
        break;
    
    case 'unlike':
        // Remove a like for the recipe by the user
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND recipe_id = ?");
        $stmt->bind_param('ii', $user_id, $recipe_id);
        $stmt->execute();
        break;
    
    case 'unfav':
        // Remove a favorite for the recipe by the user
        $stmt = $conn->prepare("DELETE FROM favourites WHERE user_id = ? AND recipe_id = ?");
        $stmt->bind_param('ii', $user_id, $recipe_id);
        $stmt->execute();
        break;
}

// Close the prepared statement
if ($stmt) {
    $stmt->close();
}

// Redirect back to the recipe page
header("Location: view_recipe.php?id=$recipe_id", true, 303);
exit;
?>
