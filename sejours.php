<?php
// sejours.php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Paramètres de filtrage
$matPers = $_SESSION['user_id'];
// Construction de la requête
if ($_SESSION['user_type'] == 4 || $_SESSION['user_type'] == 3) {
$query = "
    SELECT s.*, h.nomHeb, h.adrHeb, v.nomVille, d.nomDept, r.nomRegion,
           p.nomPers as client_nom, p.prenomPers as client_prenom
    FROM sejours s
    JOIN hebergements h ON s.idHeb = h.idHeb
    JOIN villes v ON h.numVille = v.numVille
    JOIN departements d ON v.idDept = d.idDept
    JOIN regions r ON d.idRegion = r.idRegion
    JOIN personnes p ON s.matPers = p.matPers
    WHERE 1=1
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$sejours = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($_SESSION['user_type'] == 1){
    $query = "
    SELECT s.*, h.nomHeb, h.adrHeb, v.nomVille, d.nomDept, r.nomRegion,
           p.nomPers as client_nom, p.prenomPers as client_prenom
    FROM sejours s
    JOIN hebergements h ON s.idHeb = h.idHeb
    JOIN villes v ON h.numVille = v.numVille
    JOIN departements d ON v.idDept = d.idDept
    JOIN regions r ON d.idRegion = r.idRegion
    JOIN personnes p ON s.matPers = p.matPers
    WHERE 1=1
    AND s.matPers = ?
";

$stmt = $pdo->prepare($query);
$stmt->execute([$matPers]);
$sejours = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Récupérer les hébergements pour le filtre
$hebergements = $pdo->query("
    SELECT h.idHeb, h.nomHeb, v.nomVille 
    FROM hebergements h
    JOIN villes v ON h.numVille = v.numVille
    ORDER BY h.nomHeb
")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les villes pour le filtre
$villes = $pdo->query("
    SELECT v.numVille, v.nomVille, d.nomDept 
    FROM villes v
    JOIN departements d ON v.idDept = d.idDept
    ORDER BY d.nomDept, v.nomVille
")->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<h1>Tous les séjours</h1>

<!-- Formulaire de filtres -->


<?php if (empty($sejours)): ?>
    <div class="alert alert-info">Aucun séjour trouvé.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Hébergement</th>
                <th>Localisation</th>
                <th>Client</th>
                <th>Période</th>
                <th>Durée</th>
                <th>Prix</th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sejours as $sejour): 
                $date1 = new DateTime($sejour['dateDebutSej']);
                $date2 = new DateTime($sejour['dateFinSej']);
                $duree = $date1->diff($date2)->days;
            ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($sejour['nomHeb']); ?></strong>
                </td>
                <td>
                    <?php echo htmlspecialchars($sejour['nomVille'] . ', ' . $sejour['nomDept']); ?><br>
                    <small><?php echo htmlspecialchars($sejour['nomRegion']); ?></small>
                </td>
                
               <td>
                    <?php echo htmlspecialchars($sejour['client_nom'] . ' ' . $sejour['client_prenom']); ?>
                </td>
                <td>
                    Du <?php echo date('d/m/Y', strtotime($sejour['dateDebutSej'])); ?><br>
                    Au <?php echo date('d/m/Y', strtotime($sejour['dateFinSej'])); ?>
                </td>
                <td style="text-align: center;"><?php echo $duree; ?> jours</td>
                <td style="text-align: right;"><?php echo formatPrice($sejour['prixSej']); ?></td>
                
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>