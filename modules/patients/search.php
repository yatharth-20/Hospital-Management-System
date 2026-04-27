<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

check_login();

$query = $_GET['q'] ?? '';
$results = [];

if (!empty($query)) {
    $search = "%$query%";
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE name LIKE ? OR id = ? OR contact LIKE ?");
    $stmt->execute([$search, $query, $search]);
    $results = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT * FROM patients ORDER BY created_at DESC LIMIT 10");
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Search - HMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <h2>HMS Admin</h2>
            <nav>
                <ul class="nav-links">
                    <li><a href="../../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="search.php" class="active"><i class="fas fa-search"></i> Patient Search</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Patient Search / Records</h1>
                <form action="search.php" method="GET" style="display: flex; gap: 0.5rem;">
                    <input type="text" name="q" placeholder="Search by name, ID or contact..." 
                           value="<?php echo htmlspecialchars($query); ?>" 
                           style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 6px; width: 300px;">
                    <button type="submit" class="btn" style="width: auto; padding: 0.5rem 1rem;">Search</button>
                </form>
            </header>

            <div class="stat-card">
                <h3><?php echo !empty($query) ? 'Search Results' : 'Recent Patients'; ?></h3>
                <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th>ID</th>
                            <th>Name</th>
                            <th>Date of Birth</th>
                            <th>Contact</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $p): ?>
                        <tr>
                            <td style="padding: 1rem; border-bottom: 1px solid #eee;">#<?php echo $p['id']; ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($p['name']); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #eee;"><?php echo $p['dob']; ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($p['contact']); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #eee;">
                                <a href="view_ehr.php?id=<?php echo $p['id']; ?>" class="btn" style="padding: 5px 10px; width: auto; font-size: 0.8rem;">View EHR</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($results)): ?>
                            <tr><td colspan="5" style="padding: 2rem; text-align: center;">No patients found matching your search.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
