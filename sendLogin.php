<?php
    session_start();
    include("./include/bdd.php");

    // on vérifie que le formulaire a bien été envoyé
    if (isset($_POST['login']) && isset($_POST['password'])) {
        $login = $_POST['login'];
        $password = $_POST['password'];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);

            $sql = "SELECT * FROM users WHERE username = :login";   // Ecrit la requête
            $req = $pdo->prepare($sql);                             // Prépare la requête avant envoi
            $req->execute(['login' => $login]);                     // Execute la requête
            $utilisateur = $req->fetch();                           // Récupère l'utilisateur (ou false si il existe pas)
        //    echo  "SELECT * FROM users WHERE username = $login";

       // print_r($utilisateur);

        // echo password_verify($password, $utilisateur['password']);
            // le mot de passe est hashé dans la base donc on utilise password_verify
            if ($utilisateur && password_verify($password, $utilisateur['password'])) {
                $_SESSION['id'] = $utilisateur['id'];
                $_SESSION['username'] = $utilisateur['username'];
                $_SESSION['role_id'] = $utilisateur['role_id'];

                header("Location: ./index.php"); exit();
            } else {
                header("Location: ./login.php?erreur=1"); exit();
            }
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
    } else {
        header("Location: ./login.php"); exit();
    }
?>
