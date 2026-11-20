<?php
    session_start();
    include("./include/tasks.php");
    include("./include/comments.php");

    // si l'utilisateur est pas connecté on le renvoie sur le login
    if (!isset($_SESSION['id'])) {
        header("Location: ./login.php"); exit();
    }

    if (isset($_POST['task_id']) && isset($_POST['content']) && trim($_POST['content']) != '') {
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            // on vérifie que l'utilisateur a le droit de voir la tache (la sienne, ou admin)
            if (selectTaskForView($pdo, $_POST['task_id'], $_SESSION['id'])) {
                $parent_id = null;
                // si c'est une réponse : le commentaire parent doit exister et être sur la même tache
                if (!empty($_POST['parent_id'])) {
                    $parent = selectCommentById($pdo, $_POST['parent_id']);
                    if (!$parent || $parent['task_id'] != $_POST['task_id']) {
                        header("Location: ./task.php?id=".(int)$_POST['task_id']); exit();
                    }
                    // une réponse à une réponse est rattachée au commentaire principal (un seul niveau)
                    $parent_id = $parent['parent_id'] ?? $parent['id'];
                }
                addComment($pdo, $_POST['task_id'], $_SESSION['id'], trim($_POST['content']), $parent_id);
            }
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
        // pour une réponse on revient directement sur la discussion
        $ancre = !empty($parent_id) ? '#comment-'.(int)$parent_id : '';
        header("Location: ./task.php?id=".(int)$_POST['task_id'].$ancre); exit();
    }
    header("Location: ./index.php"); exit();
?>
