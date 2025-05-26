<?php
// Page pour charger les détails d'une œuvre en AJAX
session_start();
require_once 'includes/conn.php';

$idOeuvre = intval($_GET['id']);
// Récupérer les détails complets de l'œuvre avec toutes ses photos
// et afficher dans un format modal
?>