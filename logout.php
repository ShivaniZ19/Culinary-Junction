<?php
session_start(); // Resuming the existing session

$_SESSION = array();

session_destroy();

// Redirecting to Home page
header('Location: Index.php');
exit;
?>