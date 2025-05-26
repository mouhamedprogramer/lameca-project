<?php
session_start();
require_once 'includes/conn.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['idUtilisateur'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Vérifier si un fichier a été uploadé
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu ou erreur d\'upload']);
    exit();
}

$file = $_FILES['photo'];
$idUtilisateur = $_SESSION['idUtilisateur'];

// Validation du fichier
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.']);
    exit();
}

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'Le fichier est trop volumineux (max 5MB).']);
    exit();
}

// Vérifier que c'est bien une image
$imageInfo = getimagesize($file['tmp_name']);
if ($imageInfo === false) {
    echo json_encode(['success' => false, 'message' => 'Le fichier n\'est pas une image valide.']);
    exit();
}

try {
    // Créer le dossier uploads s'il n'existe pas
    $uploadDir = 'uploads/profiles/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Générer un nom de fichier unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'profile_' . $idUtilisateur . '_' . time() . '.' . $extension;
    $filePath = $uploadDir . $fileName;
    
    // Récupérer l'ancienne photo pour la supprimer
    $query = "SELECT photo FROM Utilisateur WHERE idUtilisateur = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idUtilisateur);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $oldPhoto = mysqli_fetch_assoc($result)['photo'] ?? '';
    
    // Déplacer le fichier uploadé
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Redimensionner l'image si nécessaire
        if (resizeImage($filePath, 400, 400)) {
            // Mettre à jour la base de données
            $query = "UPDATE Utilisateur SET photo = ? WHERE idUtilisateur = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $filePath, $idUtilisateur);
            
            if (mysqli_stmt_execute($stmt)) {
                // Supprimer l'ancienne photo si elle existe
                if (!empty($oldPhoto) && file_exists($oldPhoto) && $oldPhoto !== $filePath) {
                    unlink($oldPhoto);
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Photo de profil mise à jour avec succès',
                    'photoUrl' => $filePath
                ]);
            } else {
                // Supprimer le fichier en cas d'erreur de base de données
                unlink($filePath);
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour en base de données']);
            }
        } else {
            unlink($filePath);
            echo json_encode(['success' => false, 'message' => 'Erreur lors du redimensionnement de l\'image']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement du fichier']);
    }
    
} catch (Exception $e) {
    error_log("Erreur upload photo: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}

/**
 * Redimensionne une image en conservant les proportions
 */
function resizeImage($filePath, $maxWidth, $maxHeight) {
    try {
        $imageInfo = getimagesize($filePath);
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        // Calculer les nouvelles dimensions
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        
        // Si l'image est déjà plus petite, ne pas la redimensionner
        if ($ratio >= 1) {
            return true;
        }
        
        $newWidth = round($originalWidth * $ratio);
        $newHeight = round($originalHeight * $ratio);
        
        // Créer une nouvelle image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Préserver la transparence pour PNG et GIF
        if ($mimeType == 'image/png' || $mimeType == 'image/gif') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Charger l'image source
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($filePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($filePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($filePath);
                break;
            default:
                return false;
        }
        
        // Redimensionner
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        // Sauvegarder la nouvelle image
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($newImage, $filePath, 85);
                break;
            case 'image/png':
                imagepng($newImage, $filePath, 6);
                break;
            case 'image/gif':
                imagegif($newImage, $filePath);
                break;
            case 'image/webp':
                imagewebp($newImage, $filePath, 85);
                break;
        }
        
        // Libérer la mémoire
        imagedestroy($newImage);
        imagedestroy($sourceImage);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Erreur redimensionnement: " . $e->getMessage());
        return false;
    }
}
?>