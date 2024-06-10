<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Recipe</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link href="https://use.fontawesome.com/releases/v5.0.4/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #fff;
            line-height: 1.6;
            padding: 20px;
        }

        h1, h2, h3 {
            color: #fff;
        }

        p, li {
            font-size: 16px;
            padding: 5px 0;
        }

        a {
            color: #1a659e;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #1a659e;
            text-decoration: underline;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 20px auto;
            border-radius: 8px;
        }

        ul, ol {
            margin-left: 20px;
        }

        .action-buttons {
            margin-top: 20px;
            text-align: center;
        }

        .like-button, .fav-button {
            background-color: rgba(76, 175, 80, 0.8);
            color: white;
            padding: 10px 15px;
            margin-right: 10px;
            border-radius: 5px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .like-button:hover, .fav-button:hover {
            background-color: rgba(76, 175, 80, 1);
        }

        .bi-heart, .bi-hand-thumbs-up {
            margin-right: 5px;
        }

        .bi-heart-fill {
            color: red;
        }
    </style>
</head>

<body>

<header>
        <div class="logo">
            <img src="assets/logo.webp" alt="Recipe Sharing Website Logo">
        </div>
        <div class="topnav">
            <a href="dashboard4.php" class="post-recipe">Dashboard</a>
        </div>
    </header>

    <?php
    session_start();

    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $recipe_id = intval($_GET['id']);

        include 'dbconnection.php';

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $recipeQuery = "SELECT Recipes.*, Users_Recipe.username
                        FROM Recipes
                        INNER JOIN Users_Recipe ON Recipes.user_id = Users_Recipe.id
                        WHERE Recipes.recipe_id = $recipe_id";
        $recipeResult = $conn->query($recipeQuery);

        if ($recipeResult->num_rows > 0) {
            $recipeRow = $recipeResult->fetch_assoc();

            echo "<h1>" . htmlspecialchars($recipeRow['recipe_name']) . "</h1>";
            echo "<h3>by <a href='user_view.php?id=" . htmlspecialchars($recipeRow['user_id']) . "' style='color: white;'>" . htmlspecialchars($recipeRow['username']) . "</a></h3>";
            echo "<img src='" . htmlspecialchars($recipeRow['picture_url']) . "' alt='Recipe Picture' style='max-width: 200px;'><br />";
            echo "<p>Country of Origin: " . htmlspecialchars($recipeRow['country_of_origin']) . "</p>";
            echo "<p>Cooking Time: " . htmlspecialchars($recipeRow['ETA_hr']) . " hr(s) " . htmlspecialchars($recipeRow['ETA_min']) . " min(s)</p>";

            echo "<h2>Ingredients</h2>";
            echo "<ul>";
            $ingredientsQuery = "SELECT * FROM Ingredients WHERE recipe_id = $recipe_id ORDER BY ing_entry_id";
            $ingredientsResult = $conn->query($ingredientsQuery);
            while ($ingRow = $ingredientsResult->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($ingRow['ingredient_name']) . " - " . htmlspecialchars($ingRow['quantity']) . "</li>";
            }
            echo "</ul>";

            echo "<h2>Instructions</h2>";
            echo "<ol>";
            $instructionsQuery = "SELECT * FROM Instructions WHERE recipe_id = $recipe_id ORDER BY step_number";
            $instructionsResult = $conn->query($instructionsQuery);
            while ($insRow = $instructionsResult->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($insRow['instruction']) . "</li>";
            }
            echo "</ol>";

            // Check if the user is logged in
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];

                // Fetch likes
                $resultLike = $conn->query("SELECT * FROM likes WHERE recipe_id = $recipe_id");
                $likes = $resultLike->num_rows;

                // Check if the user has already liked the recipe
                $likedAlready = false;
                while ($rowLike = $resultLike->fetch_assoc()) {
                    if ($rowLike['user_id'] == $user_id) {
                        $likedAlready = true;
                        break;
                    }
                }

                // Fetch favorites
                $resultFav = $conn->query("SELECT * FROM favourites WHERE recipe_id = $recipe_id AND user_id = $user_id");
                $isFavourite = $resultFav->num_rows > 0;

                echo '<div class="action-buttons">';

                // Display like buttons based on whether the user has liked the recipe
                if ($likedAlready) {
                    echo "<a href='check_login.php?action=unlike&recipe_id=$recipe_id&user_id=$user_id' class='like-button'>";
                    echo "<i class='bi bi-hand-thumbs-up-fill'></i> ($likes)";
                    echo "</a>";
                } else {
                    echo "<a href='check_login.php?action=like&recipe_id=$recipe_id&user_id=$user_id' class='like-button'>";
                    echo "<i class='bi bi-hand-thumbs-up'></i> ($likes)";
                    echo "</a>";
                }

                // Display favorite buttons based on whether the user has favorited the recipe
                if ($isFavourite) {
                    echo "<a href='check_login.php?action=unfav&recipe_id=$recipe_id&user_id=$user_id' class='fav-button'>";
                    echo "<i class='bi bi-heart-fill'></i>";
                    echo "</a>";
                } else {
                    echo "<a href='check_login.php?action=fav&recipe_id=$recipe_id&user_id=$user_id' class='fav-button'>";
                    echo "<i class='bi bi-heart'></i>";
                    echo "</a>";
                }
                
                echo '</div>';
            } else {
                // Redirect to login page if the user is not logged in
                echo "<p>You must <a href='login.html'>log in</a> to like or favorite this recipe.</p>";
            }
        } else {
            echo "<p>Recipe not found.</p>";
        }

        $conn->close();
    } else {
        echo "<p>Invalid recipe ID.</p>";
    }
    ?>
</body>

</html>


