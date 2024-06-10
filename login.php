<?php
session_start();
include('dbconnection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Checking if both fields are filled
    if (!isset($_POST['username']) || trim($_POST['username']) === '' || !isset($_POST['password']) || trim($_POST['password']) === '') {
        echo "Please fill in all fields.";
        exit; // Stopping further execution
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // the SQL statement
    $stmt = $conn->prepare("SELECT * FROM Users_Recipe WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            // Redirecting to dashboard or home page
            header("Location: dashboard4.php");
            exit; // Stopping further execution
        } else {
            echo "Invalid username or password.";
            echo "<br><button onclick=\"location.href='login.html'\">Try Again</button>";
        }
    } else {
        echo "User not found.";
    }

    $stmt->close();
}
?>