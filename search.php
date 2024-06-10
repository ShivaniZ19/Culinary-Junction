<?php
session_start();
include('dbconnection.php');

// Checking if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Calling the handleSearch function
handleSearch();

// Defining the handleSearch function
function handleSearch()
{
    global $conn;

    // Search parameter
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

    // SQL query for searching recipes based on the name
    $sql = "SELECT Recipes.*, Users_Recipe.username, COUNT(recipe_fav_like.id) AS likes_count
            FROM Recipes
            INNER JOIN Users_Recipe ON Recipes.user_id = Users_Recipe.id
            LEFT JOIN recipe_fav_like ON Recipes.recipe_id = recipe_fav_like.recipe_id AND recipe_fav_like.type = 'like'
            WHERE Recipes.recipe_name LIKE '%$searchTerm%'
            GROUP BY Recipes.recipe_id";

    $result = $conn->query($sql);

    // Search results
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='recipe-icon'>";
            echo "<h3><a href='view_recipe.php?id=" . $row['recipe_id'] . "'>" . $row['recipe_name'] . "</a></h3>";
            echo "<p>User: <a href='user_view.php?id=" . $row['user_id'] . "'>" . $row['username'] . "</a></p>";
            echo "<p>Country of Origin: " . $row['country_of_origin'] . "</p>";
            echo "<p>Cooking Time: " . $row['ETA_hr'] . " hr(s) " . $row['ETA_min'] . " min(s)</p>";
            echo "<img src='" . $row['picture_url'] . "' alt='Recipe Picture' style='max-width: 200px;'>";
            echo "<p><i class='bi bi-hand-thumbs-up'></i> " . $row['likes_count'] . "</p>";
            echo "</div>";
        }
    } else {
        echo "No recipes found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Recipes</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <header>
        <!-- Header content here -->
    </header>

    <div class="recipes-container">
        <!-- Search results will be displayed here -->
    </div>

    <footer>
        <!-- Footer content here -->
    </footer>
</body>
</html>