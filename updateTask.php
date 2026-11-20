<?php
    session_start();
    include("./include/tasks.php");

    // si l'utilisateur est pas connecté on le renvoie sur le login
    if (!isset($_SESSION['id'])) {
        header("Location: ./login.php"); exit();
    }

    // on vérifie que le formulaire a bien été envoyé
    if (isset($_POST['id']) && isset($_POST['title']) && $_POST['title'] != '') {
        // on accepte que les valeurs prévues dans la base
        $status = array_key_exists($_POST['status'] ?? '', getStatuts()) ? $_POST['status'] : 'new';
        $priority = in_array($_POST['priority'] ?? '', ['1', '2', '3']) ? $_POST['priority'] : 2;
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;     // date vide = pas d'échéance

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            updateTask($pdo, $_POST['id'], $_SESSION['id'], $_POST['title'], $_POST['description'] ?? '', $status, $priority, $due_date);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
        header("Location: ./task.php?id=".(int)$_POST['id']."&modifie=1"); exit();
    }
    header("Location: ./index.php"); exit();
?>
