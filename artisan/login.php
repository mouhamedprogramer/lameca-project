<?php
    session_start();
    include 'includes/conn.php';

    if(isset($_POST['login'])){
        $username = $_POST['username'];
        $password = $_POST['password'];


        $sql = "SELECT * FROM utilisateur WHERE email = ? and role = 'Artisan'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows < 1){
            $_SESSION['error'] = 'Impossible de trouver un compte avec cet email';
        }
        else{
            $row = $result->fetch_assoc();
            if(password_verify($password, $row['mot_de_passe'])){

				$_SESSION['artisan'] = $row['idUtilisateur'];
                
                // Stocker aussi le rôle pour les vérifications ultérieures
                $_SESSION['role'] = $row['role'];
                
                header('location: profil.php');
                exit();
            }
            else{
                $_SESSION['error'] = 'Mot de passe incorrect';
            }
        }
    }
    else{
        $_SESSION['error'] = 'Veuillez saisir vos identifiants d\'abord';
    }

    header('location: index.php');
    exit();
?>