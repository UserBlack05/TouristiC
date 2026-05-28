<?php
// souscription_offre.php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=1');
    exit;
}

$matPers = $_SESSION['user_id'];
$userType = $_SESSION['user_type'];
$isAdmin = ($userType == 4);
$isResponsableSite = ($userType == 2);
$isClient = ($userType == 1);

$success = '';
$error = '';

// Traitement de la désinscription (pour admin ou responsable)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['desinscrire'])) {
    if ($isAdmin || $isResponsableSite) {
        $matPersClient = $_POST['matPers'];
        $numOffre = $_POST['numOffre'];
        
        $stmt = $pdo->prepare("DELETE FROM souscrire WHERE matPers = ? AND numOffre = ?");
        if ($stmt->execute([$matPersClient, $numOffre])) {
            $success = "Client désinscrit de l'offre avec succès !";
        } else {
            $error = "Erreur lors de la désinscription";
        }
    }
}

// Paramètres de filtrage
$client_filter = isset($_GET['client']) ? $_GET['client'] : '';
$offre_filter = isset($_GET['offre']) ? $_GET['offre'] : '';
$site_filter = isset($_GET['site']) ? $_GET['site'] : '';

// Construction de la requête en fonction du type d'utilisateur
if ($isClient) {
    // Client : ne voit que ses propres souscriptions
    $query = "
        SELECT s.matPers, s.numOffre, 
               p.nomPers, p.prenomPers, p.telPers, p.loginPers,
               o.prixOffre, o.descOffre, o.created_at as date_offre,
               st.nomSiteTour, st.descSiteTour,
               v.nomVille, d.nomDept, r.nomRegion
        FROM souscrire s
        JOIN personnes p ON s.matPers = p.matPers
        JOIN offres o ON s.numOffre = o.numOffre
        JOIN sites_touristiques st ON o.numSiteTour = st.numSiteTour
        JOIN villes v ON st.numVille = v.numVille
        JOIN departements d ON v.idDept = d.idDept
        JOIN regions r ON d.idRegion = r.idRegion
        WHERE s.matPers = ?
        
    ";
    $params = [$matPers];
    
} elseif ($isResponsableSite) {
    // Responsable site : voit les souscriptions pour ses sites
    $query = "
        SELECT s.matPers, s.numOffre, 
               p.nomPers, p.prenomPers, p.telPers, p.loginPers,
               o.prixOffre, o.descOffre, o.created_at as date_offre,
               st.nomSiteTour, st.descSiteTour, st.numSiteTour,
               v.nomVille, d.nomDept, r.nomRegion
        FROM souscrire s
        JOIN personnes p ON s.matPers = p.matPers
        JOIN offres o ON s.numOffre = o.numOffre
        JOIN sites_touristiques st ON o.numSiteTour = st.numSiteTour
        JOIN villes v ON st.numVille = v.numVille
        JOIN departements d ON v.idDept = d.idDept
        JOIN regions r ON d.idRegion = r.idRegion
        WHERE 1=1
    ";
    $params = [];
    
    // Filtres supplémentaires
    if (!empty($client_filter)) {
        $query .= " AND (p.nomPers LIKE ? OR p.prenomPers LIKE ? OR p.loginPers LIKE ?)";
        $search_term = "%$client_filter%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if (!empty($offre_filter)) {
        $query .= " AND o.descOffre LIKE ?";
        $params[] = "%$offre_filter%";
    }
    
    if (!empty($site_filter)) {
        $query .= " AND st.numSiteTour = ?";
        $params[] = $site_filter;
    }
    
    
    
} elseif ($isAdmin) {
    // Admin : voit toutes les souscriptions
    $query = "
        SELECT s.matPers, s.numOffre,
               p.nomPers, p.prenomPers, p.telPers, p.loginPers,
               o.prixOffre, o.descOffre, o.created_at as date_offre,
               st.nomSiteTour, st.descSiteTour, st.numSiteTour,
               v.nomVille, d.nomDept, r.nomRegion
        FROM souscrire s
        JOIN personnes p ON s.matPers = p.matPers
        JOIN offres o ON s.numOffre = o.numOffre
        JOIN sites_touristiques st ON o.numSiteTour = st.numSiteTour
        JOIN villes v ON st.numVille = v.numVille
        JOIN departements d ON v.idDept = d.idDept
        JOIN regions r ON d.idRegion = r.idRegion
        WHERE 1=1
    ";
    $params = [];
    
    // Filtres
    if (!empty($client_filter)) {
        $query .= " AND (p.nomPers LIKE ? OR p.prenomPers LIKE ? OR p.loginPers LIKE ?)";
        $search_term = "%$client_filter%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if (!empty($offre_filter)) {
        $query .= " AND o.descOffre LIKE ?";
        $params[] = "%$offre_filter%";
    }
    
    if (!empty($site_filter)) {
        $query .= " AND st.numSiteTour = ?";
        $params[] = $site_filter;
    }
    
    
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$souscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pour les responsables, récupérer la liste des sites pour le filtre
$sites = [];
if ($isResponsableSite || $isAdmin) {
    if ($isResponsableSite) {
        $stmt = $pdo->prepare("
            SELECT st.numSiteTour, st.nomSiteTour 
            FROM sites_touristiques st
            ORDER BY st.nomSiteTour
        ");
        $stmt->execute();
    } else {
        $stmt = $pdo->query("
            SELECT st.numSiteTour, st.nomSiteTour 
            FROM sites_touristiques st
            ORDER BY st.nomSiteTour
        ");
    }
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Statistiques
$total_souscriptions = count($souscriptions);
$total_montant = 0;
$clients_uniques = [];

foreach ($souscriptions as $s) {
    $total_montant += $s['prixOffre'];
    $clients_uniques[$s['matPers']] = true;
}

require_once 'includes/header.php';
?>

<h1>
    <?php 
    if ($isClient) {
        echo "Mes souscriptions aux offres";
    } elseif ($isResponsableSite) {
        echo "Gestion des souscriptions - Responsable de site";
    } else {
        echo "Gestion des souscriptions - Administrateur";
    }
    ?>
</h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($isResponsableSite || $isAdmin): ?>
    <!-- Formulaire de filtres pour responsables et admin -->
    <div style="border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 5px;">
        <h3>Filtrer les souscriptions</h3>
        <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
            <div class="form-group">
                <label>Client :</label>
                <input type="text" name="client" placeholder="Nom, prénom ou login" value="<?php echo htmlspecialchars($client_filter); ?>">
            </div>
            
            <div class="form-group">
                <label>Offre :</label>
                <input type="text" name="offre" placeholder="Description de l'offre" value="<?php echo htmlspecialchars($offre_filter); ?>">
            </div>
            
            <?php if (!empty($sites)): ?>
            <div class="form-group">
                <label>Site touristique :</label>
                <select name="site">
                    <option value="">Tous les sites</option>
                    <?php foreach ($sites as $site): ?>
                        <option value="<?php echo $site['numSiteTour']; ?>" <?php echo ($site_filter == $site['numSiteTour']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($site['nomSiteTour']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div style="display: flex; gap: 10px; align-items: flex-end;">
                <button type="submit" class="btn">Filtrer</button>
                <a href="souscription_offre.php" class="btn">Réinitialiser</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- Résumé statistique -->
<div style="background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
        <div>
            <strong>Total souscriptions :</strong><br>
            <span style="font-size: 24px;"><?php echo $total_souscriptions; ?></span>
        </div>
        <?php if (!$isClient): ?>
        <div>
            <strong>Clients distincts :</strong><br>
            <span style="font-size: 24px;"><?php echo count($clients_uniques); ?></span>
        </div>
        <?php endif; ?>
        <div>
            <strong>Montant total :</strong><br>
            <span style="font-size: 24px;"><?php echo formatPrice($total_montant); ?></span>
        </div>
    </div>
</div>

<?php if (empty($souscriptions)): ?>
    <div class="alert alert-info">
        <?php 
        if ($isClient) {
            echo "Vous n'avez pas encore souscrit à des offres. <a href='souscrire_offres.php'>Découvrir les offres</a>";
        } else {
            echo "Aucune souscription trouvée.";
        }
        ?>
    </div>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table style="min-width: 100%;">
            <thead>
                <tr>
                    <?php if (!$isClient): ?>
                    <th>Client</th>
                    <th>Contact</th>
                    <?php endif; ?>
                    <th>Site touristique</th>
                    <th>Offre</th>
                    <th>Prix</th>
                    
                    <?php if ($isResponsableSite || $isAdmin): ?>
                    <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($souscriptions as $s): ?>
                <tr>
                    <?php if (!$isClient): ?>
                    <td>
                        <strong><?php echo htmlspecialchars($s['prenomPers'] . ' ' . $s['nomPers']); ?></strong><br>
                        <small>Login: <?php echo htmlspecialchars($s['loginPers']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($s['telPers']); ?></td>
                    <?php endif; ?>
                    
                    <td>
                        <strong><?php echo htmlspecialchars($s['nomSiteTour']); ?></strong><br>
                        <small><?php echo htmlspecialchars($s['nomVille'] . ', ' . $s['nomDept']); ?></small>
                    </td>
                    
                    <td>
                        <?php echo htmlspecialchars(substr($s['descOffre'], 0, 100)); ?>
                        <?php if (strlen($s['descOffre']) > 100): ?>...<?php endif; ?>
                    </td>
                    
                    <td style="text-align: right; white-space: nowrap;">
                        <strong><?php echo formatPrice($s['prixOffre']); ?></strong>
                    </td>
                    
                    
                    
                    <?php if ($isResponsableSite || $isAdmin): ?>
                    <td style="white-space: nowrap;">
                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir désinscrire ce client de cette offre ?');">
                            <input type="hidden" name="matPers" value="<?php echo $s['matPers']; ?>">
                            <input type="hidden" name="numOffre" value="<?php echo $s['numOffre']; ?>">
                            <input type="hidden" name="desinscrire" value="1">
                            <button type="submit" class="btn" style="background-color: #dc3545; padding: 5px 10px; font-size: 12px;">Désinscrire</button>
                        </form>
                        <a href="details_offre.php?id=<?php echo $s['numOffre']; ?>" class="btn" style="padding: 5px 10px; font-size: 12px;">Voir offre</a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Détails supplémentaires pour les clients -->
    <?php if ($isClient): ?>
    <div style="margin-top: 30px; padding: 20px; background-color: #e8f4f8; border-radius: 5px;">
        <h3>📌 Informations sur vos souscriptions</h3>
        <p>
            Vous avez souscrit à <strong><?php echo $total_souscriptions; ?></strong> offre(s) pour un montant total de <strong><?php echo formatPrice($total_montant); ?></strong>.
        </p>
        <p>
            <a href="offres.php" class="btn">➕ Souscrire à de nouvelles offres</a>
        </p>
    </div>
    <?php endif; ?>
    
    <!-- Export CSV pour admin et responsable -->
    <?php if ($isResponsableSite || $isAdmin): ?>
    <div style="margin-top: 20px; text-align: right;">
        <a href="export_souscriptions.php?<?php echo http_build_query($_GET); ?>" class="btn">📥 Exporter en CSV</a>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>