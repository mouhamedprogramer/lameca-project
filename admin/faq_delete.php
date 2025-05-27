<?php
	include 'includes/session.php';

	if(isset($_POST['delete'])){
		$id = $_POST['id'];
		
		// Vérifier si la FAQ existe
		$check_sql = "SELECT idFaq FROM faq WHERE idFaq = '$id'";
		$check_query = $conn->query($check_sql);
		
		if($check_query->num_rows > 0){
			$sql = "DELETE FROM faq WHERE idFaq = '$id'";
			if($conn->query($sql)){
				$_SESSION['success'] = 'FAQ supprimée avec succès';
			}
			else{
				$_SESSION['error'] = 'Erreur lors de la suppression de la FAQ: ' . $conn->error;
			}
		}
		else{
			$_SESSION['error'] = 'FAQ introuvable';
		}
	}
	else{
		$_SESSION['error'] = 'Sélectionnez d\'abord une FAQ à supprimer';
	}

	header('location: faq.php');
?>