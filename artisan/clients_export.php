<?php
include 'includes/session.php';
require('../tcpdf/tcpdf.php'); // ✅ Chemin vers TCPDF

if (isset($_GET['id'])) {
    $client_id = $_GET['id'];

    // Connexion + récupération infos client
    $sql = "SELECT u.*, c.idClient 
            FROM client c 
            JOIN utilisateur u ON c.idClient = u.idUtilisateur 
            WHERE c.idClient = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $client = $result->fetch_assoc();

        // Commandes
        $sql = "SELECT c.idCommande, c.dateCommande, c.nombreArticles, c.statut, 
                       o.titre as titre_oeuvre, o.prix as prix_unitaire,
                       (o.prix * c.nombreArticles) as prix_total
                FROM commande c 
                LEFT JOIN oeuvre o ON c.idOeuvre = o.idOeuvre 
                WHERE c.idClient = ? 
                ORDER BY c.dateCommande DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $commandes_result = $stmt->get_result();

        // Statistiques
        $sql = "SELECT COUNT(*) as nb_commandes, 
                       SUM(o.prix * c.nombreArticles) as total_depense,
                       MAX(c.dateCommande) as derniere_commande
                FROM commande c 
                LEFT JOIN oeuvre o ON c.idOeuvre = o.idOeuvre 
                WHERE c.idClient = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $stats_result = $stmt->get_result();
        $stats = $stats_result->fetch_assoc();

        // Création PDF
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);

        // Infos client
        $pdf->Cell(0, 10, 'INFORMATIONS CLIENT', 0, 1);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 8,
            "ID: {$client['idUtilisateur']}\n" .
            "Nom: {$client['nom']} {$client['prenom']}\n" .
            "Email: {$client['email']}\n" .
            "Téléphone: {$client['telephone']}\n" .
            "Adresse: {$client['adresse']}, {$client['ville']}, {$client['code_postal']}, {$client['pays']}\n" .
            "Naissance: {$client['date_naissance']}\n" .
            "Genre: {$client['genre']}\n" .
            "Inscription: {$client['date_creation']}"
        );

        // Statistiques
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'STATISTIQUES', 0, 1);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 8,
            "Nombre de commandes: {$stats['nb_commandes']}\n" .
            "Total dépensé: " . number_format($stats['total_depense'], 2) . " €\n" .
            "Dernière commande: {$stats['derniere_commande']}"
        );

        // Commandes
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'COMMANDES', 0, 1);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(20, 10, 'ID', 1);
        $pdf->Cell(30, 10, 'Date', 1);
        $pdf->Cell(50, 10, 'Œuvre', 1);
        $pdf->Cell(20, 10, 'Qté', 1);
        $pdf->Cell(30, 10, 'PU (€)', 1);
        $pdf->Cell(30, 10, 'Total (€)', 1);
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 11);
        if ($commandes_result->num_rows > 0) {
            while ($commande = $commandes_result->fetch_assoc()) {
                $pdf->Cell(20, 10, $commande['idCommande'], 1);
                $pdf->Cell(30, 10, $commande['dateCommande'], 1);
                $pdf->Cell(50, 10, $commande['titre_oeuvre'], 1);
                $pdf->Cell(20, 10, $commande['nombreArticles'], 1);
                $pdf->Cell(30, 10, number_format($commande['prix_unitaire'], 2), 1);
                $pdf->Cell(30, 10, number_format($commande['prix_total'], 2), 1);
                $pdf->Ln();
            }
        } else {
            $pdf->Cell(0, 10, 'Aucune commande trouvée', 1, 1);
        }

        ob_end_clean(); // vide la sortie AVANT d'envoyer le PDF
        // Téléchargement
        $pdf->Output('client_' . $client_id . '_' . date('Y-m-d') . '.pdf', 'D');
        exit;

    } else {
        $_SESSION['error'] = 'Client non trouvé';
        header('location: clients.php');
        exit;
    }

} else {
    $_SESSION['error'] = 'ID client non spécifié';
    header('location: clients.php');
    exit;
}
?>
