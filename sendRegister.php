<?php
    include("./include/bdd.php");

    // on vérifie que le formulaire a bien été envoyé
    if (isset($_POST['login']) && isset($_POST['email']) && isset($_POST['password'])
        && $_POST['login'] != '' && $_POST['email'] != '' && $_POST['password'] != '') {
        $login = $_POST['login'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);    // on hash le mot de passe avant de l'enregistrer

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);

            // on regarde si le login ou l'email existe déjà
            $sql = "SELECT id FROM users WHERE username = :login OR email = :email";
            $req = $pdo->prepare($sql);
            $req->execute(['login' => $login, 'email' => $email]);

            if ($req->fetch()) {
                header("Location: ./register.php?erreur=1"); exit();
            }

            // role_id 2 = Utilisateur
            $sql = "INSERT INTO users (username, email, password, role_id) VALUES (:login, :email, :password, 2)";
            $req = $pdo->prepare($sql);
            $req->execute(['login' => $login, 'email' => $email, 'password' => $password]);

            header("Location: ./login.php?inscrit=1"); exit();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
    } else {
        header("Location: ./register.php"); exit();
    }
?>
