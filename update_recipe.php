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

// Fetch existing recipe data to get current picture URL if no new picture is uploaded
$existing_pic_url = "";
$sql = "SELECT picture_url FROM Recipes WHERE recipe_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $recipe_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $existing_pic_url = $row['picture_url'];
} else {
    echo "No existing recipe found for the provided ID.";
    exit(); // Or handle this case appropriately
}

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
$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["picture"]["name"]);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$uploadOk = 1;

// Check if image file is an actual image
if (!empty($_FILES["picture"]["tmp_name"]) && file_exists($_FILES["picture"]["tmp_name"])) {
    $check = getimagesize($_FILES["picture"]["tmp_name"]);
    if ($check !== false) {
        // File is an image
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["picture"]["size"] > 500000) {
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
            $target_file = $existing_pic_url; // Use existing picture if upload fails
        }
    } else {
        $target_file = $existing_pic_url;
    }
} else {
    // No file was uploaded
    $target_file = $existing_pic_url;
}

// Begin transaction
$conn->autocommit(FALSE);

try {
    // Update the Recipes table including the picture_url
    $sql = "UPDATE Recipes SET recipe_name = ?, country_of_origin = ?, ETA_hr = ?, ETA_min = ?, picture_url = ? WHERE recipe_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt->execute([$recipe_name, $country_of_origin, $ETA_hr, $ETA_min, $target_file, $recipe_id, $user_id])) {
        throw new Exception("Error updating recipe: " . $conn->error);
    }

    // Update ingredients
    $sql = "DELETE FROM Ingredients WHERE recipe_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt->execute([$recipe_id])) {
        throw new Exception("Error deleting old ingredients: " . $conn->error);
    }

    foreach ($ingredients as $index => $ingredient_name) {
        $sql = "INSERT INTO Ingredients (recipe_id, ingredient_name, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt->execute([$recipe_id, $ingredient_name, $quantities[$index]])) {
            throw new Exception("Error inserting ingredient: " . $conn->error);
        }
    }

    // Update instructions
    $sql = "DELETE FROM Instructions WHERE recipe_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt->execute([$recipe_id])) {
        throw new Exception("Error deleting old instructions: " . $conn->error);
    }

    foreach ($step_numbers as $index => $step_number) {
        $sql = "INSERT INTO Instructions (recipe_id, step_number, instruction) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt->execute([$recipe_id, $step_number, $instructions[$index]])) {
            throw new Exception("Error inserting instruction: " . $conn->error);
        }
    }

    // Commit transaction
    $conn->commit();
    echo "Recipe updated successfully!";
    echo "<br><button onclick=\"location.href='dashboard4.php'\">Back to Dashboard</button>";
} catch (Exception $e) {
    // Roll back the transaction in case of any errors
    $conn->rollback();
    echo $e->getMessage();
} finally {
    // Re-enable autocommit and close the connection
    $conn->autocommit(TRUE);
    $conn->close();
}

?>
