<?php
include('header.php');
include('./include/tasks.php');
include('./include/comments.php');

// si l'utilisateur est pas connecté on le renvoie sur le login
if (!isset($_SESSION['id'])) {
    header("Location: ./login.php"); exit();
}

if (!isset($_GET['id'])) {
    header("Location: ./index.php"); exit();
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $task = selectTaskForView($pdo, $_GET['id'], $_SESSION['id']);   // la sienne, ou n'importe laquelle pour l'admin
    $comments = $task ? selectCommentsByTask($pdo, $task['id']) : [];
} catch (\PDOException $e) {
    error_log($e->getMessage());
    die("Erreur de connexion à la base de données.");
}

// la tache existe pas ou appartient à quelqu'un d'autre
if (!$task) {
    header("Location: ./index.php"); exit();
}

$statuts = getStatuts();
$priorites = [1 => 'Basse', 2 => 'Normale', 3 => 'Haute'];

// seul le propriétaire peut modifier / supprimer la tache (l'admin peut juste la voir)
$isOwner = $task['user_id'] == $_SESSION['id'];

// on affiche le formulaire seulement si on a cliqué sur "Modifier"
$modeModif = isset($_GET['modifier']) && $isOwner;

// id du commentaire qu'on est en train de modifier (s'il y en a un)
$editCommentId = $_GET['editComment'] ?? null;

// id du commentaire auquel on est en train de répondre (s'il y en a un)
$replyToId = $_GET['reply'] ?? null;

// commentaires principaux avec leurs réponses rangées dedans
$threads = groupCommentsWithReplies($comments);
$nbReponses = count($comments) - count($threads);
?>

<p><a href="./index.php">← Retour aux tâches</a></p>

<div class="task-page">

    <!-- colonne de gauche : infos de la tache -->
    <aside class="task-infos">
        <table>
            <tr><td>Propriétaire</td><td><?= htmlspecialchars($task['username']) ?></td></tr>
            <tr><td>Statut</td><td><?= $statuts[$task['status']] ?></td></tr>
            <tr><td>Priorité</td><td><?= $priorites[$task['priority']] ?? $task['priority'] ?></td></tr>
            <tr><td>Échéance</td><td><?= $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : '-' ?></td></tr>
            <tr><td>Créée le</td><td><?= date('d/m/Y H:i', strtotime($task['created_at'])) ?></td></tr>
            <tr><td>Modifiée le</td><td><?= date('d/m/Y H:i', strtotime($task['updated_at'])) ?></td></tr>
        </table>
        <?php if ($isOwner) { ?>
            <p><a href="./deleteTask.php?id=<?= $task['id'] ?>">Supprimer la tâche</a></p>
        <?php } ?>
    </aside>

    <!-- colonne du milieu : récap de la tache puis commentaires -->
    <main class="task-main">

        <section class="task-recap">
            <?php if (isset($_GET['modifie'])) { echo '<p>Tâche modifiée</p>'; } ?>

            <?php if (!$modeModif) { ?>
                <!-- lecture seule -->
                <h1><?= htmlspecialchars($task['title']) ?></h1>
                <p><?= nl2br(htmlspecialchars($task['description'] ?? '')) ?></p>
                <?php if ($isOwner) { ?>
                    <a href="./task.php?id=<?= $task['id'] ?>&modifier=1">Modifier</a>
                <?php } ?>

            <?php } else { ?>
                <!-- formulaire de modification -->
                <form action="updateTask.php" method="post">
                    <input type="hidden" name="id" value="<?= $task['id'] ?>" />
                    <div class="form">
                        <label for="title">titre</label>
                        <input type="text" name="title" id="title" maxlength="150" required value="<?= htmlspecialchars($task['title']) ?>" />
                    </div>
                    <div class="form">
                        <label for="description">description</label>
                        <textarea name="description" id="description"><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form">
                        <label for="status">statut</label>
                        <select name="status" id="status">
                            <?php foreach ($statuts as $valeur => $texte) { ?>
                                <option value="<?= $valeur ?>" <?= $task['status'] == $valeur ? 'selected' : '' ?>><?= $texte ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form">
                        <label for="priority">priorité</label>
                        <select name="priority" id="priority">
                            <?php foreach ($priorites as $valeur => $texte) { ?>
                                <option value="<?= $valeur ?>" <?= $task['priority'] == $valeur ? 'selected' : '' ?>><?= $texte ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form">
                        <label for="due_date">échéance</label>
                        <input type="date" name="due_date" id="due_date" value="<?= $task['due_date'] ?>" />
                    </div>
                    <input type="submit" value="Enregistrer" />
                    <a href="./task.php?id=<?= $task['id'] ?>">Annuler</a>
                </form>
            <?php } ?>
        </section>

        <section class="task-comments">
            <h2>Commentaires (<?= count($threads) ?>)
                <?php if ($nbReponses > 0) { ?><small>· <?= $nbReponses ?> réponse<?= $nbReponses > 1 ? 's' : '' ?></small><?php } ?>
            </h2>

            <?php if (count($comments) == 0) { echo '<p>Aucun commentaire pour le moment.</p>'; } ?>

            <?php foreach ($threads as $comment) { ?>
                <div class="comment-thread">
                    <?php
                        // le commentaire principal
                        $c = $comment; $estReponse = false; $auteurParent = null;
                        include('./include/commentItem.php');
                    ?>

                    <?php if (count($comment['replies']) > 0 || $replyToId == $comment['id']) { ?>
                        <div class="comment-replies">
                            <?php foreach ($comment['replies'] as $reply) {
                                // les réponses, en retrait sous le commentaire
                                $c = $reply; $estReponse = true; $auteurParent = $comment['username'];
                                include('./include/commentItem.php');
                            } ?>

                            <?php if ($replyToId == $comment['id']) { ?>
                                <!-- formulaire de réponse (différent de l'ajout d'un commentaire) -->
                                <form action="sendComment.php" method="post" class="reply-form" id="reply-form">
                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>" />
                                    <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>" />
                                    <div class="form">
                                        <label for="reply-content">Répondre à <?= htmlspecialchars($comment['username']) ?></label>
                                        <textarea name="content" id="reply-content" required autofocus></textarea>
                                    </div>
                                    <input type="submit" value="Répondre" />
                                    <a href="./task.php?id=<?= $task['id'] ?>#comment-<?= $comment['id'] ?>">Annuler</a>
                                </form>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <!-- ajouter un commentaire -->
            <form action="sendComment.php" method="post">
                <input type="hidden" name="task_id" value="<?= $task['id'] ?>" />
                <div class="form">
                    <label for="content">Ajouter un commentaire</label>
                    <textarea name="content" id="content" required></textarea>
                </div>
                <input type="submit" value="Envoyer" />
            </form>
        </section>

    </main>
</div>

<?php
include('footer.php');
?>
