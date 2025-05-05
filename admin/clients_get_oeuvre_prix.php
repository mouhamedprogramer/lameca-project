<?php
include 'includes/session.php';

if(isset($_POST['oeuvre_id'])) {
    $oeuvre_id = $_POST['oeuvre_id'];
    
    // Récupérer le prix de l'œuvre
    $sql = "SELECT prix FROM oeuvre WHERE idOeuvre = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $oeuvre_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['prix' => $row['prix']]);
    } else {
        echo json_encode(['error' => 'Œuvre non trouvée']);
    }
} else {
    echo json_encode(['error' => 'ID œuvre non spécifié']);
}
?>