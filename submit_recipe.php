<?php
session_start();
// Check if the user is logged in, if not, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login2.html');
    exit();
}

include 'dbconnection.php';
$user_id = $_SESSION['user_id'];

// error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

function processIngredients($conn, $recipe_id, $ingredients, $quantities) {
    $sql = "INSERT INTO Ingredients (recipe_id, ingredient_name, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    foreach ($ingredients as $index => $ingredient_name) {
        if (!$stmt->execute([$recipe_id, $ingredient_name, $quantities[$index]])) {
            $conn->rollback();
            throw new Exception("Error inserting ingredient: " . $conn->error);
        }
    }
}

function processInstructions($conn, $recipe_id, $step_numbers, $instructions) {
    $sql = "INSERT INTO Instructions (recipe_id, step_number, instruction) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    foreach ($instructions as $index => $instruction) {
        if (!$stmt->execute([$recipe_id, $step_numbers[$index], $instruction])) {
            $conn->rollback();
            throw new Exception("Error inserting instruction: " . $conn->error);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve POST data
    $recipe_name = trim($_POST['recipe_name']);
    $country_of_origin = trim($_POST['country_of_origin']);
    $ETA_hr = (int) $_POST['ETA_hr'];
    $ETA_min = (int) $_POST['ETA_min'];
    $ingredients = $_POST['ingredient_name'];
    $quantities = $_POST['quantity'];
    $step_numbers = $_POST['step_number'];
    $instructions = $_POST['instruction'];

    // File upload handling
    $target_dir = "Uploads/";
    $target_file = $target_dir . basename($_FILES["picture"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image
    $check = getimagesize($_FILES["picture"]["tmp_name"]);
    if ($check !== false) {
        // File is an image
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["picture"]["size"] > 700000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Attempt to move the uploaded file
    if ($uploadOk == 1) {
        if (!move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
            echo "Sorry, there was an error uploading your file.";
            $uploadOk = 0;
        }
    }

    // Begin transaction
    $conn->autocommit(FALSE);

    try {
        // Insert into Recipes table including the picture_url
        $sql = "INSERT INTO Recipes (user_id, recipe_name, country_of_origin, ETA_hr, ETA_min, picture_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$user_id, $recipe_name, $country_of_origin, $ETA_hr, $ETA_min, $target_file])) {
            $recipe_id = $conn->insert_id;

            // Process ingredients
            processIngredients($conn, $recipe_id, $ingredients, $quantities);

            // Process instructions
            processInstructions($conn, $recipe_id, $step_numbers, $instructions);

            // Commit transaction
            $conn->commit();
            echo "Recipe submitted successfully!";
            echo "<br><button onclick=\"location.href='dashboard4.php'\">Back to Dashboard</button>";
        } else {
            throw new Exception("Error inserting recipe: " . $conn->error);
            echo "<br><button onclick=\"location.href='recipe_form.html.php'\">Please Try Again</button>";
        }
    } catch (Exception $e) {
        // Roll back the transaction in case of any errors
        $conn->rollback();
        echo $e->getMessage();
    } finally {
        // Re-enable autocommit and close the connection
        $conn->autocommit(TRUE);
        $conn->close();
    }
}
?>
