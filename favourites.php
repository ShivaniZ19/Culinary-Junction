<?php
session_start();
include('dbconnection.php');

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login2.html");
    exit;
}

// Fetch the user_id from the session
$user_id = $_SESSION['user_id'];

// Query to retrieve recipes favorited by the user

$sql = "SELECT
    Recipes.recipe_id,
    Recipes.recipe_name,
    Recipes.picture_url,
    Recipes.country_of_origin,
    Recipes.ETA_hr,
    Recipes.ETA_min,
    COUNT(likes.index_id) AS likes_count,
    COUNT(favourites.index_id) AS favourites_count
FROM
    Recipes
LEFT JOIN likes ON Recipes.recipe_id = likes.recipe_id
LEFT JOIN favourites ON Recipes.recipe_id = favourites.recipe_id
WHERE
    favourites.user_id = ?
GROUP BY
    Recipes.recipe_id";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favourites</title>
    <!-- Add your CSS file link here -->
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link href="https://use.fontawesome.com/releases/v5.0.4/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
<header>
<div class="logo">
    <a href="dashboard4.php">
        <img src="assets/logo.webp" alt="Recipe Sharing Website Logo">
    </a>
</div>
        <div class="topnav">
        <a href="dashboard4.php" class="home">Home</a>
            <a href="recipe_form.html" class="post-recipe">Post a Recipe</a>
            <div class="dropdown">
                <button class="dropbtn">Profile <i class="fa fa-caret-down"></i></button>
                <div class="dropdown-content">
                <a href="dashboard4.php">Home</a>
                    <a href="my_recipes.php?user_id=<?php echo $_SESSION['user_id']; ?>">My Recipes</a>
                    <a href="favourites.php">Favorites</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <h1>My Favourite Recipes</h1>

    <div class="recipes-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Display each favorited recipe
                echo "<div class='recipe-icon'>";
                echo "<h3><a href='view_recipe.php?id=" . htmlspecialchars($row['recipe_id']) . "' style='color: white; text-decoration: none;'>" . htmlspecialchars($row['recipe_name']) . "</a></h3>";
                echo "<p>Country of Origin: " . htmlspecialchars($row['country_of_origin']) . "</p>";
                echo "<p>Cooking Time: " . htmlspecialchars($row['ETA_hr']) . " hr(s) " . htmlspecialchars($row['ETA_min']) . " min(s)</p>";
                echo "<img src='" . htmlspecialchars($row['picture_url']) . "' alt='Recipe Picture' style='max-width: 200px;'>";
                echo "<p><i class='bi bi-hand-thumbs-up'></i> " . $row['likes_count'] . " likes</p>";
                echo "<p><i class='bi bi-heart'></i> " . $row['favourites_count'] . " favourites</p>";
                echo "</div>";
            }
        } else {
            echo "No recipes favorited yet.";
        }
        ?>
    </div>

</body>

</html>

<?php
// Close the statement and connection
$stmt->close();
$conn->close();
?>
