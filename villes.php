<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$dept_id = isset($_GET['dept_id']) ? $_GET['dept_id'] : 0;

if($dept_id) {
    $stmt = $pdo->prepare("
        SELECT v.*, d.nomDept, r.nomRegion
        FROM villes v
        JOIN departements d ON v.idDept = d.idDept
        JOIN regions r ON d.idRegion = r.idRegion
        WHERE v.idDept = ?
        ORDER BY v.nomVille
    ");
    $stmt->execute([$dept_id]);
    $villes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT nomDept FROM departements WHERE idDept = ?");
    $stmt->execute([$dept_id]);
    $dept = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("
        SELECT v.*, d.nomDept, r.nomRegion
        FROM villes v
        JOIN departements d ON v.idDept = d.idDept
        JOIN regions r ON d.idRegion = r.idRegion
        ORDER BY v.nomVille
    ");
     $stmt->execute();
    $villes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h1>Villes <?php echo isset($dept) ? 'du département ' . htmlspecialchars($dept['nomDept']) : ''; ?></h1>
<table>
    <tr>
        <th>ID</th>
        <th>Nom de la ville</th>
        <th>Département</th>
        <th>Région</th>
        <th>Action</th>
    </tr>
    <?php foreach($villes as $ville): ?>
    <tr>
        <td><?php echo $ville['numVille']; ?></td>
        <td><?php echo htmlspecialchars($ville['nomVille']); ?></td>
        <td><?php echo htmlspecialchars($ville['nomDept']); ?></td>
        <td><?php echo htmlspecialchars($ville['nomRegion']); ?></td>
        <td>
            <a href="sites_touristiques.php?ville_id=<?php echo $ville['numVille']; ?>" class="btn">Sites</a>
            <a href="hebergements.php?ville_id=<?php echo $ville['numVille']; ?>" class="btn">Hébergements</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php require_once 'includes/footer.php'; ?>