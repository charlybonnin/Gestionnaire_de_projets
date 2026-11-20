<?php
    // affiche UN commentaire ou UNE réponse
    // variables attendues : $c (le commentaire), $task, $editCommentId,
    //                       $estReponse (true / false), $auteurParent (nom de l'auteur du commentaire d'origine, pour une réponse)
?>
<div class="<?= $estReponse ? 'comment comment-reply' : 'comment' ?>" id="comment-<?= $c['id'] ?>">
    <p class="comment-header">
        <?php if ($estReponse) { ?>
            <span class="badge badge-reply">↳ Réponse</span>
        <?php } else { ?>
            <span class="badge badge-comment">Commentaire</span>
        <?php } ?>
        <strong><?= htmlspecialchars($c['username']) ?></strong>
        <?php if ($estReponse) { ?>
            a répondu à <strong><?= htmlspecialchars($auteurParent) ?></strong>
        <?php } ?>
        le <?= date('d/m/Y à H:i', strtotime($c['created_at'])) ?>
        <?= $c['updated_at'] != $c['created_at'] ? '(modifié)' : '' ?>
    </p>

    <?php if ($editCommentId == $c['id'] && canEditComment($c)) { ?>
        <!-- formulaire de modification -->
        <form action="updateComment.php" method="post">
            <input type="hidden" name="id" value="<?= $c['id'] ?>" />
            <div class="form">
                <textarea name="content" required><?= htmlspecialchars($c['content']) ?></textarea>
            </div>
            <input type="submit" value="Enregistrer" />
            <a href="./task.php?id=<?= $task['id'] ?>#comment-<?= $c['id'] ?>">Annuler</a>
        </form>
    <?php } else { ?>
        <p><?= nl2br(htmlspecialchars($c['content'])) ?></p>
        <p class="comment-actions">
            <!-- répondre : une réponse à une réponse va dans la même discussion -->
            <a href="./task.php?id=<?= $task['id'] ?>&reply=<?= $c['parent_id'] ?? $c['id'] ?>#reply-form">Répondre</a>
            <?php if (canEditComment($c)) { ?>
                <a href="./task.php?id=<?= $task['id'] ?>&editComment=<?= $c['id'] ?>#comment-<?= $c['id'] ?>">Modifier</a>
            <?php } ?>
            <?php if (canDeleteComment($c)) { ?>
                <a href="./deleteComment.php?id=<?= $c['id'] ?>" onclick="return confirm('<?= $estReponse ? 'Supprimer cette réponse ?' : 'Supprimer ce commentaire et ses réponses ?' ?>')">Supprimer</a>
            <?php } ?>
        </p>
    <?php } ?>
</div>
