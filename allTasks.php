<?php
    include('./include/tasks.php');

    $statuts = getStatuts();
    $priorites = [1 => 'Basse', 2 => 'Normale', 3 => 'Haute'];

    // on récupère les filtres envoyés par le formulaire (méthode GET, donc dans l'URL)
    // on garde seulement les valeurs autorisées
    $filtres = [
        'priority'  => isset($priorites[$_GET['priority'] ?? '']) ? (int)$_GET['priority'] : '',
        'status'    => isset($statuts[$_GET['status'] ?? '']) ? $_GET['status'] : '',
        'user_id'   => isAdmin() && ctype_digit($_GET['user_id'] ?? '') ? (int)$_GET['user_id'] : '',   // filtre réservé à l'admin
        'date_from' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date_from'] ?? '') ? $_GET['date_from'] : '',
        'date_to'   => preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date_to'] ?? '') ? $_GET['date_to'] : '',
    ];
    // est-ce qu'au moins un filtre est rempli ?
    $filtreActif = count(array_filter($filtres)) > 0;

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);

        // l'admin voit les taches de tout le monde, les autres seulement les leurs (géré dans la fonction)
        $result = selectTasksFiltered($pdo, $filtres, $_SESSION['id']);
        $personnes = isAdmin() ? selectTaskOwners($pdo) : [];
    } catch (\PDOException $e) {
        error_log($e->getMessage());
        die("Erreur de connexion à la base de données.");
    }
?>

<a href="./addTask.php">Ajouter une tache</a>

<!-- filtres : on peut en remplir plusieurs, ils se cumulent -->
<form method="get" action="./index.php" class="filters">
    <div class="form">
        <label for="f-priority">Priorité</label>
        <select name="priority" id="f-priority">
            <option value="">Toutes</option>
            <?php foreach ($priorites as $valeur => $texte) { ?>
                <option value="<?= $valeur ?>" <?= $filtres['priority'] === $valeur ? 'selected' : '' ?>><?= $texte ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="form">
        <label for="f-status">Statut</label>
        <select name="status" id="f-status">
            <option value="">Tous</option>
            <?php foreach ($statuts as $valeur => $texte) { ?>
                <option value="<?= $valeur ?>" <?= $filtres['status'] === $valeur ? 'selected' : '' ?>><?= $texte ?></option>
            <?php } ?>
        </select>
    </div>

    <?php if (isAdmin()) { ?>
        <!-- seulement pour l'admin : les autres ne voient que leurs taches -->
        <div class="form">
            <label for="f-user">Personne</label>
            <select name="user_id" id="f-user">
                <option value="">Tout le monde</option>
                <?php foreach ($personnes as $personne) { ?>
                    <option value="<?= $personne['id'] ?>" <?= $filtres['user_id'] === (int)$personne['id'] ? 'selected' : '' ?>><?= htmlspecialchars($personne['username']) ?></option>
                <?php } ?>
            </select>
        </div>
    <?php } ?>

    <div class="form">
        <label for="f-from">Échéance du</label>
        <input type="date" name="date_from" id="f-from" value="<?= $filtres['date_from'] ?>" />
    </div>
    <div class="form">
        <label for="f-to">au</label>
        <input type="date" name="date_to" id="f-to" value="<?= $filtres['date_to'] ?>" />
    </div>

    <div class="filters-actions">
        <input type="submit" value="Filtrer" />
        <?php if ($filtreActif) { ?>
            <a href="./index.php">Réinitialiser</a>
        <?php } ?>
    </div>
</form>

<p class="filters-count">
    <?= count($result) ?> tâche<?= count($result) > 1 ? 's' : '' ?><?= $filtreActif ? ' correspondant aux filtres' : '' ?>
</p>

<?php if (count($result) == 0) { ?>
    <p>Aucune tâche ne correspond à ces filtres.</p>
<?php } else { ?>
    <table style="border:solid 1px #111">
        <tr>
            <th>Titre</th>
            <th>Description</th>
            <th>Statut</th>
            <th>Priorité</th>
            <th>Échéance</th>
            <?php if (isAdmin()) { ?><th>Personne</th><?php } ?>
            <th></th>
        </tr>
        <?php foreach ($result as $task) { ?>
            <tr style="border:solid 1px #111">
                <td><a href="./task.php?id=<?= $task['id'] ?>"><?= htmlspecialchars($task['title']) ?></a></td>
                <td><?= htmlspecialchars($task['description'] ?? '') ?></td>
                <td><?= $statuts[$task['status']] ?></td>
                <td><?= $priorites[$task['priority']] ?? $task['priority'] ?></td>
                <td><?= $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : '-' ?></td>

                <?php if (isAdmin()) { ?>
                    <!-- colonne en plus pour l'admin : à qui est la tache -->
                    <td><?= htmlspecialchars($task['username']) ?></td>
                <?php } ?>

                <!-- on peut supprimer seulement ses propres taches -->
                <td>
                    <?php if ($task['user_id'] == $_SESSION['id']) { ?>
                        <a href="./deleteTask.php?id=<?= $task['id'] ?>">supprimer</a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>
