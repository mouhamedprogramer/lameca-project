<?php
include 'includes/conn.php'; // Connexion BDD
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $portfolio = $_POST['portfolio'] ?? '';
    $idArtisan = $_POST['idArtisan'] ?? '';

    if ($portfolio !== '' && is_numeric($idArtisan)) {
        $stmt = $conn->prepare("UPDATE artisan SET portfolio = ? WHERE idArtisan = ?");
        $stmt->bind_param('si', $portfolio, $idArtisan);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Texte de présentation mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Données invalides.";
    }
} else {
    $_SESSION['error'] = "Méthode non autorisée.";
}

// Redirection vers la page portfolio
header('Location: profil.php');
exit;
?>
