<?php
include 'includes/session.php';

if(isset($_POST['id'])){
    $id = $_POST['id'];
    
    // Requête pour récupérer les informations du client
    $sql = "SELECT prenom, nom FROM utilisateur WHERE idUtilisateur = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $client_result = $stmt->get_result();
    $client = $client_result->fetch_assoc();
    
    // Requête pour récupérer les commandes du client
    $sql = "SELECT c.idCommande, c.dateCommande, c.nombreArticles, c.statut, 
            o.titre as titre_oeuvre, o.prix as prix_unitaire,
            (o.prix * c.nombreArticles) as prix_total
            FROM commande c
            LEFT JOIN oeuvre o ON c.idOeuvre = o.idOeuvre 
            WHERE c.idClient = ? 
            ORDER BY c.dateCommande DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo '<h4>Commandes de ' . $client['prenom'] . ' ' . $client['nom'] . '</h4>';
    
    if($result->num_rows > 0){
        echo '<div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Œuvre</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        $total_global = 0;
        
        while($row = $result->fetch_assoc()){
            $status_class = '';
            switch($row['statut']) {
                case 'En attente':
                    $status_class = 'label-warning';
                    break;
                case 'Confirmée':
                    $status_class = 'label-primary';
                    break;
                case 'Expédiée':
                    $status_class = 'label-info';
                    break;
                case 'Livrée':
                    $status_class = 'label-success';
                    break;
            }
            
            $total_global += $row['prix_total'];
            
            echo '<tr>
                    <td>'.$row['idCommande'].'</td>
                    <td>'.date('d/m/Y H:i', strtotime($row['dateCommande'])).'</td>
                    <td>'.$row['titre_oeuvre'].'</td>
                    <td>'.$row['nombreArticles'].'</td>
                    <td>'.number_format($row['prix_unitaire'], 2, ',', ' ').' €</td>
                    <td>'.number_format($row['prix_total'], 2, ',', ' ').' €</td>
                    <td><span class="label '.$status_class.'">'.$row['statut'].'</span></td>
                    <td>
                        <a href="commandes.php?view='.$row['idCommande'].'" class="btn btn-info btn-xs" target="_blank"><i class="fa fa-eye"></i> Voir</a>
                    </td>
                </tr>';
        }
        
        echo '</tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-right">Total de toutes les commandes:</th>
                        <th colspan="3">'.number_format($total_global, 2, ',', ' ').' €</th>
                    </tr>
                </tfoot>
            </table>
        </div>';
        
        // Ajouter des statistiques d'achat
        $sql = "SELECT AVG(o.prix * c.nombreArticles) as panier_moyen,
                COUNT(c.idCommande) as nombre_commandes,
                MAX(c.dateCommande) as derniere_commande,
                MIN(c.dateCommande) as premiere_commande
                FROM commande c 
                LEFT JOIN oeuvre o ON c.idOeuvre = o.idOeuvre 
                WHERE c.idClient = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stats_result = $stmt->get_result();
        $stats = $stats_result->fetch_assoc();
        
        echo '<div class="row">
                <div class="col-md-12">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">Statistiques d\'achat</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-aqua"><i class="fa fa-shopping-cart"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Nombre de commandes</span>
                                            <span class="info-box-number">'.$stats['nombre_commandes'].'</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Panier moyen</span>
                                            <span class="info-box-number">'.number_format($stats['panier_moyen'], 2, ',', ' ').' €</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-yellow"><i class="fa fa-calendar"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Première commande</span>
                                            <span class="info-box-number">'.date('d/m/Y', strtotime($stats['premiere_commande'])).'</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-red"><i class="fa fa-calendar-check-o"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Dernière commande</span>
                                            <span class="info-box-number">'.date('d/m/Y', strtotime($stats['derniere_commande'])).'</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
            
    } else {
        echo '<div class="alert alert-info">
                <h4><i class="icon fa fa-info"></i> Information</h4>
                Ce client n\'a pas encore passé de commande.
              </div>';
    }
}
?>