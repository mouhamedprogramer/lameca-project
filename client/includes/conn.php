<?php
$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "lameca";  


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}

$conn->set_charset("utf8");


function escape($conn, $data) {
    return $conn->real_escape_string($data);
}

date_default_timezone_set('Europe/Paris');

// Session start (à décommenter si vous utilisez les sessions)
// session_start();
?>