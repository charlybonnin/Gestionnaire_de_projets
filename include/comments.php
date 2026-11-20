<?php
    // à inclure APRES include/tasks.php (qui charge déjà bdd.php)

    function selectCommentsByTask($pdo, $task_id){
        // on récupère aussi le nom de l'auteur avec une jointure sur users
        $sql = "SELECT comments.*, users.username
                FROM comments
                JOIN users ON users.id = comments.user_id
                WHERE comments.task_id = :task_id
                ORDER BY comments.created_at ASC";
        $req = $pdo->prepare($sql);
        $req->execute(['task_id' => $task_id]);
        return $req->fetchAll();
    }
    function selectCommentById($pdo, $id){
        $sql = "SELECT * FROM comments WHERE id = :id";
        $req = $pdo->prepare($sql);
        $req->execute(['id' => $id]);
        return $req->fetch();                                   // un seul commentaire (ou false)
    }
    function addComment($pdo, $task_id, $user_id, $content, $parent_id = null){
        // $parent_id = null -> commentaire normal, sinon -> réponse au commentaire $parent_id
        $sql = "INSERT INTO comments (task_id, user_id, parent_id, content) VALUES (:task_id, :user_id, :parent_id, :content)";
        $req = $pdo->prepare($sql);
        $req->execute(['task_id' => $task_id, 'user_id' => $user_id, 'parent_id' => $parent_id, 'content' => $content]);
    }

    // range les commentaires : les commentaires principaux, avec leurs réponses dans ['replies']
    function groupCommentsWithReplies($comments){
        $principaux = [];
        foreach ($comments as $comment) {
            if ($comment['parent_id'] === null) {
                $comment['replies'] = [];
                $principaux[$comment['id']] = $comment;
            }
        }
        foreach ($comments as $comment) {
            if ($comment['parent_id'] !== null && isset($principaux[$comment['parent_id']])) {
                $principaux[$comment['parent_id']]['replies'][] = $comment;
            }
        }
        return $principaux;
    }
    function updateComment($pdo, $id, $user_id, $content){
        // seulement l'auteur du commentaire peut le modifier
        $sql = "UPDATE comments SET content = :content WHERE id = :id AND user_id = :user_id";
        $req = $pdo->prepare($sql);
        $req->execute(['content' => $content, 'id' => $id, 'user_id' => $user_id]);
    }
    function deleteComment($pdo, $id){
        // la vérification auteur / admin est faite dans deleteComment.php
        $sql = "DELETE FROM comments WHERE id = :id";
        $req = $pdo->prepare($sql);
        $req->execute(['id' => $id]);
    }

    // isAdmin() est dans include/tasks.php
    function canEditComment($comment){
        return $comment['user_id'] == $_SESSION['id'];
    }
    function canDeleteComment($comment){
        return $comment['user_id'] == $_SESSION['id'] || isAdmin();
    }
?>
