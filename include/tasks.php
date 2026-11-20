<?php
    include("./include/bdd.php");

    function selectAllTasks($pdo){
        $sql = "SELECT * FROM tasks";                   // Ecrit la requête
        $req = $pdo->prepare($sql);                     // Prépare la requête avant envoi
        $req->execute();                                // Execute la requête
        $tasks = $req->fetchAll();                      // Récupération du résultat complet dans un tableau associatif
        
        return $tasks;                                  // retourne le résultat
    }
    function selectTasksByUser($pdo, $user_id){
        $sql = "SELECT * FROM tasks WHERE user_id = :user_id";  // Ecrit la requête
        $req = $pdo->prepare($sql);                             // Prépare la requête avant envoi
        $req->execute(['user_id' => $user_id]);                 // Execute la requête
        $tasks = $req->fetchAll();                              // Récupération du résultat complet dans un tableau associatif

        return $tasks;                                          // retourne le résultat
    }
    function deleteTask($pdo, $id, $user_id){
        // on vérifie aussi le user_id pour pas supprimer la tache de quelqu'un d'autre
        $sql= "DELETE FROM tasks WHERE id = :id AND user_id = :user_id";
        $req = $pdo->prepare($sql);
        $req->execute(['id' => $id, 'user_id' => $user_id]);
    }
    function addTask($pdo, $user_id, $title, $description){
        $sql = "INSERT INTO tasks (user_id, title, description) VALUES (:user_id, :title, :description)";
        $req = $pdo->prepare($sql);
        $req->execute(['user_id' => $user_id, 'title' => $title, 'description' => $description]);
    }
    function selectTaskById($pdo, $id, $user_id){
        // on vérifie le user_id pour pas afficher la tache de quelqu'un d'autre
        $sql = "SELECT * FROM tasks WHERE id = :id AND user_id = :user_id";
        $req = $pdo->prepare($sql);
        $req->execute(['id' => $id, 'user_id' => $user_id]);
        return $req->fetch();                                   // une seule tache (ou false si elle existe pas)
    }
    function updateTask($pdo, $id, $user_id, $title, $description, $status, $priority, $due_date){
        $sql = "UPDATE tasks SET title = :title, description = :description, status = :status,
                priority = :priority, due_date = :due_date
                WHERE id = :id AND user_id = :user_id";
        $req = $pdo->prepare($sql);
        $req->execute([
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'priority' => $priority,
            'due_date' => $due_date,
            'id' => $id,
            'user_id' => $user_id
        ]);
    }
    // liste des statuts : valeur en base => texte affiché (utilisée partout dans le site)
    function getStatuts(){
        return [
            'new'       => 'Nouveau',
            'doing'     => 'En cours',
            'waiting'   => 'En attente',
            'done'      => 'Terminé',
            'abandoned' => 'Abandonné',
        ];
    }
    // role_id 1 = Administrateur
    function isAdmin(){
        return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
    }
    function selectAllTasksWithUser($pdo){
        // pour l'admin : toutes les taches avec le nom de leur propriétaire
        $sql = "SELECT tasks.*, users.username
                FROM tasks
                JOIN users ON users.id = tasks.user_id
                ORDER BY users.username, tasks.id";
        $req = $pdo->prepare($sql);
        $req->execute();
        return $req->fetchAll();
    }
    // liste des taches avec des filtres CUMULATIFS (chaque filtre rempli ajoute un "AND")
    // $filtres peut contenir : priority, status, user_id (admin seulement), date_from, date_to
    function selectTasksFiltered($pdo, $filtres, $user_id){
        $sql = "SELECT tasks.*, users.username
                FROM tasks
                JOIN users ON users.id = tasks.user_id
                WHERE 1 = 1";                       // 1 = 1 permet d'ajouter les "AND ..." à la suite
        $params = [];

        if (!isAdmin()) {
            // un utilisateur normal ne voit que ses taches
            $sql .= " AND tasks.user_id = :me";
            $params['me'] = $user_id;
        } elseif (!empty($filtres['user_id'])) {
            // l'admin peut filtrer par personne
            $sql .= " AND tasks.user_id = :user_id";
            $params['user_id'] = $filtres['user_id'];
        }
        if (!empty($filtres['priority'])) {
            $sql .= " AND tasks.priority = :priority";
            $params['priority'] = $filtres['priority'];
        }
        if (!empty($filtres['status'])) {
            $sql .= " AND tasks.status = :status";
            $params['status'] = $filtres['status'];
        }
        // échéance entre deux dates (on peut remplir une seule des deux)
        if (!empty($filtres['date_from'])) {
            $sql .= " AND tasks.due_date >= :date_from";
            $params['date_from'] = $filtres['date_from'];
        }
        if (!empty($filtres['date_to'])) {
            $sql .= " AND tasks.due_date <= :date_to";
            $params['date_to'] = $filtres['date_to'];
        }

        $sql .= isAdmin() ? " ORDER BY users.username, tasks.id" : " ORDER BY tasks.id";
        $req = $pdo->prepare($sql);
        $req->execute($params);
        return $req->fetchAll();
    }
    // les personnes qui ont au moins une tache (pour la liste du filtre "personne")
    function selectTaskOwners($pdo){
        $sql = "SELECT DISTINCT users.id, users.username
                FROM users
                JOIN tasks ON tasks.user_id = users.id
                ORDER BY users.username";
        $req = $pdo->prepare($sql);
        $req->execute();
        return $req->fetchAll();
    }
    function selectTaskForView($pdo, $id, $user_id){
        // l'admin peut voir toutes les taches, les autres seulement les leurs
        $sql = "SELECT tasks.*, users.username
                FROM tasks
                JOIN users ON users.id = tasks.user_id
                WHERE tasks.id = :id";
        $params = ['id' => $id];
        if (!isAdmin()) {
            $sql .= " AND tasks.user_id = :user_id";
            $params['user_id'] = $user_id;
        }
        $req = $pdo->prepare($sql);
        $req->execute($params);
        return $req->fetch();
    }
?>
