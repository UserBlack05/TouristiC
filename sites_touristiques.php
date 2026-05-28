<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$ville_id = isset($_GET['ville_id']) ? $_GET['ville_id'] : 0;

$query = "
    SELECT st.*, v.nomVille, d.nomDept, r.nomRegion
    FROM sites_touristiques st
    JOIN villes v ON st.numVille = v.numVille
    JOIN departements d ON v.idDept = d.idDept
    JOIN regions r ON d.idRegion = r.idRegion
";

if($ville_id) {
    $query .= " WHERE st.numVille = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$ville_id]);
} else {
    $stmt = $pdo->query($query);
}

$sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Sites Touristiques</h1>
<table>
    <tr>
        <th>Nom du site</th>
        <th>Description</th>
        <th>Ville</th>
        <th>Département</th>
        <th>Région</th>
        <th>Action</th>
    </tr>
    <?php foreach($sites as $site): ?>
    <tr>
        <td><?php echo htmlspecialchars($site['nomSiteTour']); ?></td>
        <td><?php echo htmlspecialchars($site['descSiteTour']); ?></td>
        <td><?php echo htmlspecialchars($site['nomVille']); ?></td>
        <td><?php echo htmlspecialchars($site['nomDept']); ?></td>
        <td><?php echo htmlspecialchars($site['nomRegion']); ?></td>
        <td><a href="offres.php?site_id=<?php echo $site['numSiteTour']; ?>" class="btn">Voir offres</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php require_once 'includes/footer.php'; ?>