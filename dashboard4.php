<?php
session_start();
include('dbconnection.php');



// Checking if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login2.html");
    exit;
}

// All recipes submitted by the user
$user_id = $_SESSION['user_id'];

// Initialize the sorting parameters
$sort_by_country = '';
$sort_by = '';
$search_term = '';

$countries = array(
    "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria",
    "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan",
    "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cabo Verde", "Cambodia",
    "Cameroon", "Canada", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo, Democratic Republic of the", "Congo, Republic of the",
    "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czechia", "Denmark", "Djibouti", "Dominica", "Dominican Republic",
    "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia", "Fiji", "Finland",
    "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea",
    "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq",
    "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait",
    "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg",
    "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico",
    "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru",
    "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "North Korea", "North Macedonia", "Norway", "Oman",
    "Pakistan", "Palau", "Palestine", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal",
    "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe",
    "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia",
    "South Africa", "South Korea", "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria",
    "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Timor-Leste", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey",
    "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "USA", "Uruguay", "Uzbekistan", "Vanuatu",
    "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"
);


// Check if the sorting parameters and search term are set in the URL
if (isset($_GET['sort_by_country'])) {
    $sort_by_country = $_GET['sort_by_country'];
}
if (isset($_GET['sort_by'])) {
    $sort_by = $_GET['sort_by'];
}
if (isset($_GET['search'])) {
    $search_term = $_GET['search'];
}

// Prepare the SQL query
// Initialize search terms
$search_term_recipe = isset($_GET['search_recipe']) ? $_GET['search_recipe'] : '';
$search_term_ingredient = isset($_GET['search_ingredient']) ? $_GET['search_ingredient'] : '';

// Prepare the SQL query
$sql = "SELECT
    Recipes.recipe_id,
    Recipes.recipe_name,
    Recipes.user_id,
    Recipes.picture_url,
    Recipes.country_of_origin,
    Recipes.ETA_hr,
    Recipes.ETA_min,
    Users_Recipe.username,
    COALESCE(COUNT(DISTINCT likes.index_id), 0) AS likes_count,
    COALESCE(COUNT(DISTINCT favourites.index_id), 0) AS favourites_count
FROM
    Recipes
INNER JOIN
    Users_Recipe ON Recipes.user_id = Users_Recipe.id
LEFT JOIN
    likes ON Recipes.recipe_id = likes.recipe_id
LEFT JOIN
    favourites ON Recipes.recipe_id = favourites.recipe_id
LEFT JOIN
    Ingredients ON Recipes.recipe_id = Ingredients.recipe_id
WHERE 1 = 1";

// Add the combination search conditions
if (!empty($search_term_recipe) && !empty($search_term_ingredient)) {
    $sql .= " AND Recipes.recipe_name LIKE '%$search_term_recipe%' AND Ingredients.ingredient_name LIKE '%$search_term_ingredient%'";
} elseif (!empty($search_term_recipe)) {
    $sql .= " AND Recipes.recipe_name LIKE '%$search_term_recipe%'";
} elseif (!empty($search_term_ingredient)) {
    $sql .= " AND Ingredients.ingredient_name LIKE '%$search_term_ingredient%'";
}

// Add the country filtering condition
if (!empty($sort_by_country)) {
    $sql .= " AND Recipes.country_of_origin = '$sort_by_country'";
}

// Add GROUP BY clause
$sql .= " GROUP BY Recipes.recipe_id, Users_Recipe.username, Recipes.recipe_name, Recipes.picture_url, Recipes.country_of_origin, Recipes.ETA_hr, Recipes.ETA_min";

// Add sorting conditions
switch ($sort_by) {
    case 'name_asc':
        $sql .= " ORDER BY Recipes.recipe_name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY Recipes.recipe_name DESC";
        break;
    case 'likes_desc':
        $sql .= " ORDER BY likes_count DESC";
        break;
    case 'favourites_desc':
        $sql .= " ORDER BY favourites_count DESC";
        break;
    default:
        $sql .= " ORDER BY Recipes.recipe_id DESC";
}

// Execute the SQL query
$result = $conn->query($sql);



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
            <img src="assets/logo.webp" alt="Website Logo">
        </div>
        <div class="topnav">
            <a href="recipe_form.html" class="post-recipe">Post a Recipe</a>
            <div class="dropdown">
                <button class="dropbtn">Profile <i class="fa fa-caret-down"></i></button>
                <div class="dropdown-content">
                    <a href="my_recipes.php?user_id=<?php echo $_SESSION['user_id']; ?>">My Recipes</a>
                    <a href="favourites.php">Favorites</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="search-container">
            <form action="dashboard4.php" method="GET" id="search-form" class="search-form">
                <input type="text" placeholder="Search by recipe name..." name="search_recipe" value="<?php echo isset($_GET['search_recipe']) ? $_GET['search_recipe'] : ''; ?>" class="search-input">
                <input type="text" placeholder="Search by ingredient name..." name="search_ingredient" value="<?php echo isset($_GET['search_ingredient']) ? $_GET['search_ingredient'] : ''; ?>" class="search-input">
                <button type="submit" class="search-button">Search</button>
                <div class="dropdown-container">
                    <select name="sort_by_country" onchange="this.form.submit();" class="sort-select">
                        <option value="">Filter by Country</option>
                        <?php foreach ($countries as $country) {
                            echo "<option value='$country'" . ($sort_by_country === $country ? ' selected' : '') . ">$country</option>";
                        } ?>
                    </select>
                    <select name="sort_by" onchange="this.form.submit();" class="sort-select">
                        <option value="">Sort By</option>
                        <option value="name_asc" <?php echo $sort_by === 'name_asc' ? 'selected' : ''; ?>>Recipe Name (A-Z)</option>
                        <option value="name_desc" <?php echo $sort_by === 'name_desc' ? 'selected' : ''; ?>>Recipe Name (Z-A)</option>
                        <option value="likes_desc" <?php echo $sort_by === 'likes_desc' ? 'selected' : ''; ?>>Most Liked Recipes</option>
                        <option value="favourites_desc" <?php echo $sort_by === 'favourites_desc' ? 'selected' : ''; ?>>Most Favourited Recipes</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="recipes-container">
            <?php if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='recipe-icon'>";
                    echo "<h3><a href='view_recipe.php?id=" . $row['recipe_id'] . "' style='color: #FFF; text-decoration: none;'>" . $row['recipe_name'] . "</a></h3>";
                    echo "<p>User: <a href='user_view.php?id=" . $row['user_id'] . "' style='color: #F8C8DC; text-decoration: none;'>" . $row['username'] . "</a></p>";
                    echo "<p>Country of Origin: " . $row['country_of_origin'] . "</p>";
                    echo "<p>Cooking Time: " . $row['ETA_hr'] . " hr(s) " . $row['ETA_min'] . " min(s)</p>";
                    echo "<img src='" . $row['picture_url'] . "' alt='Recipe Picture' class='recipe-image'>";
                    echo "<div class='recipe-stats'>";
                    echo "<p><i class='bi bi-hand-thumbs-up'></i> " . $row['likes_count'] . " likes</p>";
                    echo "<p><i class='bi bi-heart'></i> " . $row['favourites_count'] . " favourites</p>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "No recipes submitted yet.";
            } ?>
        </div>
    </main>

    <footer>
        <p>© 2024 Cullinary Junction. All rights reserved.</p>
    </footer>
</body>
</html>



