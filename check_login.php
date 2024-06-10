<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Perform the requested action
    if (isset($_GET['action']) && isset($_GET['recipe_id'])) {
        $action = $_GET['action'];
        $recipe_id = intval($_GET['recipe_id']);

        include 'dbconnection.php';

        switch ($action) {
            case 'like':
                // Insert a new like record
                $sql = "INSERT INTO likes (user_id, recipe_id) VALUES ($user_id, $recipe_id)";
                $conn->query($sql);
                break;
            case 'unlike':
                // Remove the like record
                $sql = "DELETE FROM likes WHERE user_id = $user_id AND recipe_id = $recipe_id";
                $conn->query($sql);
                break;
            case 'fav':
                // Insert a new favorite record
                $sql = "INSERT INTO favourites (user_id, recipe_id) VALUES ($user_id, $recipe_id)";
                $conn->query($sql);
                break;
            case 'unfav':
                // Remove the favorite record
                $sql = "DELETE FROM favourites WHERE user_id = $user_id AND recipe_id = $recipe_id";
                $conn->query($sql);
                break;
        }

        $conn->close();

        // Redirect back to the recipe page
        header("Location: view_recipe.php?id=$recipe_id");
        exit;
    }
} else {
    // User is not logged in, redirect to the login page
    header("Location: login.html");
    exit;
}
?>