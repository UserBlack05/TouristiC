<?php
// admin/utilisateurs.php
require_once './config/database.php';
require_once './includes/functions.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 4) {
    header('Location: ../login.php?error=2');
    exit;
}

$success = '';
$error = '';

// Traitement de la suppression
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $matPers = $_GET['delete'];
    
    // Ne pas permettre de supprimer son propre compte
    if ($matPers == $_SESSION['user_id']) {
        $error = "Vous ne pouvez pas supprimer votre propre compte";
    } else {
        // Vérifier si l'utilisateur a des références
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM souscrire WHERE matPers = ?");
        $stmt->execute([$matPers]);
        $souscriptions = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM sejours WHERE matPers = ?");
        $stmt->execute([$matPers]);
        $sejours = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM hebergements WHERE matPers = ?");
        $stmt->execute([$matPers]);
        $hebergements = $stmt->fetchColumn();
        
        if ($souscriptions > 0 || $sejours > 0 || $hebergements > 0) {
            $error = "Impossible de supprimer cet utilisateur car il a des références (souscriptions, séjours ou hébergements)";
        } else {
            $stmt = $pdo->prepare("DELETE FROM personnes WHERE matPers = ?");
            if ($stmt->execute([$matPers])) {
                $success = "Utilisateur supprimé avec succès";
            } else {
                $error = "Erreur lors de la suppression";
            }
        }
    }
}

// Paramètres de filtrage
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Construction de la requête
$query = "
    SELECT p.*, tp.roleTypePers 
    FROM personnes p
    JOIN type_personnes tp ON p.idTypePers = tp.idTypePers
    WHERE 1=1
";
$params = [];

if (!empty($type_filter)) {
    $query .= " AND p.idTypePers = ?";
    $params[] = $type_filter;
}

if (!empty($search)) {
    $query .= " AND (p.nomPers LIKE ? OR p.prenomPers LIKE ? OR p.loginPers LIKE ? OR p.telPers LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les types pour le filtre
$types = $pdo->query("SELECT * FROM type_personnes ORDER BY idTypePers")->fetchAll(PDO::FETCH_ASSOC);

require_once './includes/header.php';
?>

<h1>Gestion des utilisateurs</h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div style="margin-bottom: 20px;">
    <a href="ajouter_utilisateur.php" class="btn">Ajouter un utilisateur</a>
</div>

<!-- Formulaire de filtres -->
<div style="border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 5px;">
    <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
        <div class="form-group">
            <label>Type d'utilisateur :</label>
            <select name="type">
                <option value="">Tous les types</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?php echo $type['idTypePers']; ?>" <?php echo ($type_filter == $type['idTypePers']) ? 'selected' : ''; ?>>
                        <?php 
                        $libelle = match($type['roleTypePers']) {
                            'pers_client' => 'Client',
                            'pers_rst' => 'Responsable site',
                            'pers_rsh' => 'Responsable hébergement',
                            'admin' => 'Administrateur',
                            default => $type['roleTypePers']
                        };
                        echo $libelle;
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Recherche :</label>
            <input type="text" name="search" placeholder="Nom, prénom, login, téléphone..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        
        <div style="display: flex; gap: 10px; align-items: flex-end;">
            <button type="submit" class="btn">Filtrer</button>
            <a href="utilisateurs.php" class="btn">Réinitialiser</a>
        </div>
    </form>
</div>

<?php if (empty($utilisateurs)): ?>
    <div class="alert alert-info">Aucun utilisateur trouvé.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom & Prénom</th>
                <th>Téléphone</th>
                <th>Login</th>
                <th>Type</th>
                <th>Date inscription</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utilisateurs as $user): 
                $type_libelle = match($user['roleTypePers']) {
                    'pers_client' => 'Client',
                    'pers_rst' => 'Responsable site',
                    'pers_rsh' => 'Responsable hébergement',
                    'admin' => 'Administrateur',
                    default => $user['roleTypePers']
                };
                
                $type_color = match($user['idTypePers']) {
                    1 => '#28a745',
                    2 => '#007bff',
                    3 => '#fd7e14',
                    4 => '#dc3545',
                    default => '#6c757d'
                };
            ?>
            <tr>
                <td><?php echo $user['matPers']; ?></td>
                <td><?php echo htmlspecialchars($user['prenomPers'] . ' ' . $user['nomPers']); ?></td>
                <td><?php echo htmlspecialchars($user['telPers']); ?></td>
                <td><?php echo htmlspecialchars($user['loginPers']); ?></td>
                <td>
                    <span style="background-color: <?php echo $type_color; ?>; color: white; padding: 3px 8px; border-radius: 3px;">
                        <?php echo $type_libelle; ?>
                    </span>
                </td>
                <td><?php echo formatDate($user['created_at']); ?></td>
                <td>
                    
                    <?php if ($user['matPers'] != $_SESSION['user_id']): ?>
                    <a href="?delete=<?php echo $user['matPers']; ?>" 
                       class="btn" 
                       style="background-color: #dc3545;"
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                        Supprimer
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Statistiques -->
    
<?php endif; ?>

<?php require_once './includes/footer.php'; ?>