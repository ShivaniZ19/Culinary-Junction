<?php
// Start the session
session_start();

// Include the database connection file
include 'dbconnection.php';

// Check if the user ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    // Sanitize the input to prevent SQL injection
    $user_id = intval($_GET['id']);

    // Query to get the user's details
    $userQuery = "SELECT username FROM Users_Recipe WHERE id = $user_id";
    $userResult = $conn->query($userQuery);

    if ($userResult->num_rows > 0) {
        $userRow = $userResult->fetch_assoc();
        $username = $userRow['username'];

        // Query to get the recipes posted by the user
        
        $recipesQuery = "SELECT
    Recipes.*,
    COUNT(likes.index_id) AS likes_count,
    COUNT(favourites.index_id) AS favourites_count,
    Users_Recipe.username
FROM
    Recipes
INNER JOIN
    Users_Recipe ON Recipes.user_id = Users_Recipe.id
LEFT JOIN
    likes ON Recipes.recipe_id = likes.recipe_id
LEFT JOIN
    favourites ON Recipes.recipe_id = favourites.recipe_id
WHERE
    Recipes.user_id = $user_id
GROUP BY
    Recipes.recipe_id";


        $recipesResult = $conn->query($recipesQuery);
    } else {
        echo "User not found.";
    }
} else {
    echo "Invalid user ID.";
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Recipes</title>
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
        <a href="dashboard4.php" class="home">Dashboard</a>
        </div>
    </header>
    <?php if (isset($username)): ?>
        <h1><?php echo htmlspecialchars($username); ?>'s Recipes</h1>

        <?php if ($recipesResult->num_rows > 0): ?>
            <div class="recipes-container">
                <?php while ($row = $recipesResult->fetch_assoc()): ?>
                    <div class="recipe-icon">
                    <h3><a href="view_recipe.php?id=<?php echo htmlspecialchars($row['recipe_id']); ?>" style="color: white; text-decoration: none;"><?php echo htmlspecialchars($row['recipe_name']); ?></a></h3>
                        <p>Country of Origin: <?php echo htmlspecialchars($row['country_of_origin']); ?></p>
                        <p>Cooking Time: <?php echo htmlspecialchars($row['ETA_hr']); ?> hr(s) <?php echo htmlspecialchars($row['ETA_min']); ?> min(s)</p>
                        <img src="<?php echo htmlspecialchars($row['picture_url']); ?>" alt="Recipe Picture" style="max-width: 200px;">
                        <p><i class="bi bi-hand-thumbs-up"></i> <?php echo $row['likes_count']; ?> likes</p>
                        <p><i class="bi bi-heart"></i> <?php echo $row['favourites_count']; ?> favourites</p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No recipes posted by this user yet.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>