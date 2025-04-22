<?php
    include 'includes/session.php';

    if(isset($_FILES['csv_file']['name'])){
        $filename = $_FILES['csv_file']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if($file_ext == "csv"){
            $file_tmp = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file_tmp, "r");

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {


                $firstname = $data[0];
		        $lastname = $data[1];
		        $password = password_hash($data[2], PASSWORD_DEFAULT);
		        $filename = $data[3];
				$set = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		        $voter = substr(str_shuffle($set), 0, 15);

		        $sql = "INSERT INTO voters (voters_id, password, firstname, lastname, photo) VALUES ('$voter', '$password', '$firstname', '$lastname', '$filename')";
                if($conn->query($sql)){
                    $_SESSION['success'] = 'Votant ajouté avec succés';
                }
                else{
                    $_SESSION['error'] = $conn->error;
                }

	}
}
}
	else{
		$_SESSION['error'] = "Remplissez d'abord le formulaire d'ajout.";
	}

            

    header('location: voters.php');
?>

// Rediriger vers la page 'voters.php' après l'importation
//header('Location: voters.php');
