<?php
ob_start(); // Start output buffering

include('dbconnection.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all fields are filled
    if (empty($_POST['email']) || empty($_POST['password']) || empty($_POST['full_name']) || empty($_POST['username'])) {
        echo "Please fill in all fields.";
        exit;
    }

    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];

    // Check if the email or username already exists
    $stmt = $conn->prepare("SELECT * FROM Users_Recipe WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Email or username already exists. Please try again with a different email or username.";
        echo "<br><button onclick=\"location.href='register.html'\">Try Again</button>";
        $stmt->close();
        $conn->close();
        exit;
    }

    // Prepare the SQL statement for inserting data
    $stmt = $conn->prepare("INSERT INTO Users_Recipe (email, password, full_name, username) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $hashed_password, $full_name, $username);
    if ($stmt->execute()) {
        // Redirect to login page after successful registration
        header("Location: login1.html");
        exit;
    } else {
        // Error handling and try again button
        echo "Error: " . $stmt->error;
        echo "<br><button onclick=\"location.href='register.html'\">Try Again</button>";
    }

    $stmt->close();
}

$conn->close();
?>
