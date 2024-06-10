<?php
session_start();
include 'dbconnection.php';

// Check if the user is logged in, if not, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login2.html');
    exit();
}

// Ensure a recipe ID has been provided
if (!isset($_GET['id'])) {
    echo "Error: Recipe ID is missing.";
    exit();
}

$recipe_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Fetch the existing recipe data
$sql = "SELECT * FROM Recipes WHERE recipe_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $recipe_id, $user_id);
$stmt->execute();
$recipe_result = $stmt->get_result();
if ($recipe_result->num_rows == 0) {
    echo "No recipe found or you do not have permission to edit this recipe.";
    exit();
}
$recipe = $recipe_result->fetch_assoc();

// Fetch the ingredients
$ingredient_sql = "SELECT * FROM Ingredients WHERE recipe_id = ?";
$ingredient_stmt = $conn->prepare($ingredient_sql);
$ingredient_stmt->bind_param("i", $recipe_id);
$ingredient_stmt->execute();
$ingredients_result = $ingredient_stmt->get_result();

// Fetch the instructions
$instruction_sql = "SELECT * FROM Instructions WHERE recipe_id = ? ORDER BY step_number";
$instruction_stmt = $conn->prepare($instruction_sql);
$instruction_stmt->bind_param("i", $recipe_id);
$instruction_stmt->execute();
$instructions_result = $instruction_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Recipe</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="edit-recipe.css">
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

<h1>Edit Your Recipe</h1>
<form action="update_recipe.php?id=<?= $recipe_id ?>" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
    <div>
        <label for="recipe_name">Recipe Name:</label>
        <input type="text" id="recipe_name" name="recipe_name" required value="<?= htmlspecialchars($recipe['recipe_name']) ?>">
    </div>
    <div>
        <label for="country_of_origin">Country of Origin:</label>
        <select id="country_of_origin" name="country_of_origin" required>
    <option value="">Select Location</option>
    <?php
    $countries = [
        "Afghanistan", "Åland Islands", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica",
        "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain",
        "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", 
        "Bosnia and Herzegovina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", 
        "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", 
        "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", 
        "Comoros", "Congo", "Congo, The Democratic Republic of The", "Cook Islands", "Costa Rica", "Cote D'ivoire", "Croatia", 
        "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt", 
        "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", 
        "Fiji", "Finland", "France", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", 
        "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", 
        "Guernsey", "Guinea", "Guinea-bissau", "Guyana", "Haiti", "Heard Island and Mcdonald Islands", "Holy See (Vatican City State)", 
        "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran, Islamic Republic of", "Iraq", "Ireland", 
        "Isle of Man", "Israel", "Italy", "Jamaica", "Japan", "Jersey", "Jordan", "Kazakhstan", "Kenya", "Kiribati", 
        "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao People's Democratic Republic", 
        "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macao", 
        "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", 
        "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", 
        "Mongolia", "Montenegro", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", 
        "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", 
        "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Palestinian Territory, Occupied", "Panama", "Papua New Guinea", 
        "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", 
        "Rwanda", "Saint Helena", "Saint Kitts and Nevis", "Saint Lucia", "Saint Pierre and Miquelon", "Saint Vincent and The Grenadines", 
        "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", 
        "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and The South Sandwich Islands", "Spain", 
        "Sri Lanka", "Sudan", "Suriname", "Svalbard and Jan Mayen", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan", 
        "Tajikistan", "Tanzania, United Republic of", "Thailand", "Timor-leste", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", 
        "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", 
        "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", 
        "Viet Nam", "Virgin Islands, British", "Virgin Islands, U.S.", "Wallis and Futuna", "Western Sahara", "Yemen", "Zambia", "Zimbabwe"
    ];
    foreach ($countries as $country) {
        $selected = ($country == $recipe['country_of_origin']) ? 'selected' : '';
        echo "<option value=\"$country\" $selected>$country</option>";
    }
    ?>
</select>
    </div>
    <div>
        <label for="ETA_hr">ETA (Hours):</label>
        <input type="number" id="ETA_hr" name="ETA_hr" required min="0" value="<?= $recipe['ETA_hr'] ?>">
    </div>
    <div>
        <label for="ETA_min">ETA (Minutes):</label>
        <input type="number" id="ETA_min" name="ETA_min" required min="0" max="59" value="<?= $recipe['ETA_min'] ?>">
    </div>
    <div>
        <label for="picture">Upload Picture:</label>
        <input type="file" id="picture" name="picture" accept="image/*">
        <img src="<?= $recipe['picture_url'] ?>" alt="Current Image">
    </div>  
    <h2>Ingredients</h2>
    <div id="ingredientsContainer"></div>
    <button type="button" onclick="addIngredient()">Add Ingredient</button>
    <h2>Instructions</h2>
    <div id="instructionsContainer"></div>
    <button type="button" onclick="addInstruction()">Add Instruction</button>
    <br>
    <button type="submit" id="update-btn">Update Recipe</button>
</form>

<script>
    let ingredientCount = 0;
        let instructionCount = 0;

        function addIngredient(name = '', quantity = '') {
            ingredientCount++;
            let container = document.getElementById('ingredientsContainer');
            let inputHTML = `<div id="ingredient_${ingredientCount}">
                Ingredient: <input type="text" name="ingredient_name[]" value="${name}">
                Quantity: <input type="text" name="quantity[]" value="${quantity}">
                <button type="button" onclick="removeElement('ingredient_${ingredientCount}')">Remove</button>
            </div>`;
            container.insertAdjacentHTML('beforeend', inputHTML);
        }

        function addInstruction(step = '', instruction = '') {
            instructionCount++;
            let container = document.getElementById('instructionsContainer');
            let inputHTML = `<div id="instruction_${instructionCount}">
                Step Number: <input type="number" name="step_number[]" min="1" value="${step}">
                Instruction: <textarea name="instruction[]">${instruction}</textarea>
                <button type="button" onclick="removeElement('instruction_${instructionCount}')">Remove</button>
            </div>`;
            container.insertAdjacentHTML('beforeend', inputHTML);
        }

        function removeElement(id) {
            let element = document.getElementById(id);
            element.parentNode.removeChild(element);
        }

        function validateForm() {
            let stepNumbers = document.querySelectorAll('input[name="step_number[]"]');
            let steps = Array.from(stepNumbers).map(input => input.value);
            let stepSet = new Set(steps);
            if (steps.length !== stepSet.size) {
                alert("Duplicate step numbers found. Please ensure all step numbers are unique.");
                return false;
            }
            return true;
        }

        window.onload = function() {
            // Load initial ingredients and instructions
            <?php while ($row = $ingredients_result->fetch_assoc()) { ?>
                addIngredient("<?= htmlspecialchars($row['ingredient_name']) ?>", "<?= htmlspecialchars($row['quantity']) ?>");
            <?php } ?>

            <?php while ($row = $instructions_result->fetch_assoc()) { ?>
                addInstruction("<?= $row['step_number'] ?>", "<?= htmlspecialchars($row['instruction']) ?>");
            <?php } ?>
        };
</script>
</body>
</html>
