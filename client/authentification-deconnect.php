<?php
// logout.php
session_start();
require_once 'includes/conn.php';

// Supprimer le token de la base de données si l'utilisateur est connecté
if (isset($_SESSION['idUtilisateur']) && isset($_COOKIE['remember_token'])) {
    $userId = $_SESSION['idUtilisateur'];
    $token = $_COOKIE['remember_token'];
    
    // Supprimer le token spécifique de la base de données
    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ? AND token = ?");
    $stmt->bind_param("is", $userId, $token);
    $stmt->execute();
    $stmt->close();
}

// Supprimer le cookie de mémorisation
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Détruire la session
session_unset();
session_destroy();

// Supprimer le cookie de session si il existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Rediriger vers la page de connexion
header("Location: login.php?message=disconnected");
exit();
?>