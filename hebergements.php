<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$ville_id = isset($_GET['ville_id']) ? $_GET['ville_id'] : 0;

$query = "
     SELECT h.*, v.nomVille, d.nomDept, r.nomRegion
    FROM hebergements h
    JOIN villes v ON h.numVille = v.numVille
    JOIN departements d ON v.idDept = d.idDept
    JOIN regions r ON d.idRegion = r.idRegion
";


if($ville_id) {
    $query .= " WHERE h.numVille = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$ville_id]);
} else {
    $stmt = $pdo->query($query);
}

$hebergements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Hébergements</h1>
<table>
    <tr>
        <th>Nom</th>
        <th>Adresse</th>
        <th>Ville</th>
        <th>Departement</th>
        <th>Region</th>
        <th>Action</th>
       
    </tr>
    <?php foreach($hebergements as $heb): ?>
    <tr>
        <td><?php echo htmlspecialchars($heb['nomHeb']); ?></td>
        <td><?php echo htmlspecialchars($heb['adrHeb']); ?></td>
        <td><?php echo htmlspecialchars($heb['nomVille']); ?></td>
        <td><?php echo htmlspecialchars($heb['nomDept']); ?></td>
        <td><?php echo htmlspecialchars($heb['nomRegion']); ?></td>
         <td><a href="/Mini_projet_1/projet_tourisme/rsh/ajouter_sejour.php?heb_id=<?php echo $heb['idHeb']; ?>" class="btn">reserver</a></td>

        
    </tr>
    <?php endforeach; ?>
</table>

<?php require_once 'includes/footer.php'; ?>