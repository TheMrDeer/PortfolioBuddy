<?php
session_start();
require_once __DIR__ . '/includes/dbaccess.php';

// Nur eingeloggte Admins dürfen hier rein
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$userRole = $_SESSION['user']['role'] ?? 'user';
if ($userRole !== 'admin') {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

// Datenbankverbindung
$db = new mysqli($host, $user, $pass, $db);
if ($db->connect_error) {
    die('Verbindungsfehler: ' . $db->connect_error);
}

$users = [];
$stats = ['total' => 0, 'admins' => 0, 'users' => 0];

$stmt = $db->prepare("SELECT id, fullname, email, role, created_at FROM users ORDER BY created_at DESC");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
        $stats['total']++;
        if ($row['role'] === 'admin') {
            $stats['admins']++;
        } else {
            $stats['users']++;
        }
    }
    $stmt->close();
}

$db->close();
?>
<!doctype html>
<html lang="en">
<?php
$pageTitle = 'Adminpanel - PortfolioBuddy';
$includeBootstrapJs = true;
include __DIR__ . '/includes/_head.php';
?>
<body class="bg-light">
<?php include __DIR__ . '/includes/_navbar.php'; ?>

<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 mb-1">Adminpanel</h1>
            <p class="text-muted mb-0">Übersicht aller Nutzerkonten und Rollen.</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge text-bg-secondary">Gesamt: <?= $stats['total'] ?></span>
            <span class="badge text-bg-primary">Admins: <?= $stats['admins'] ?></span>
            <span class="badge text-bg-success">Users: <?= $stats['users'] ?></span>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">E-Mail</th>
                            <th scope="col">Rolle</th>
                            <th scope="col">Registriert am</th>
                            <th scope="col" class="text-end">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Keine Nutzer vorhanden.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $userRow): ?>
                                <tr>
                                    <td><?= (int)$userRow['id'] ?></td>
                                    <td><?= htmlspecialchars($userRow['fullname'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($userRow['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php if ($userRow['role'] === 'admin'): ?>
                                            <span class="badge text-bg-primary">Admin</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-success">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($userRow['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="User actions">
                                            <button type="button" class="btn btn-outline-secondary" disabled>View</button>
                                            <button type="button" class="btn btn-outline-primary" disabled>Role</button>
                                            <button type="button" class="btn btn-outline-warning" disabled>Reset PW</button>
                                            <button type="button" class="btn btn-outline-danger" disabled>Deactivate</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
