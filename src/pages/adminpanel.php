<?php
session_start();
require_once __DIR__ . '/includes/dbaccess.php';
require_once __DIR__ . '/util/user_persistence_functions.php';

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

$alerts = [];
$users = [];
$stats = ['total' => 0, 'admins' => 0, 'users' => 0];

// Admin-Aktionen verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $targetId = (int)($_POST['user_id'] ?? 0);
    $selfId   = (int)($_SESSION['user']['id'] ?? 0);

    if ($targetId > 0) {
        if ($targetId === $selfId && in_array($action, ['delete', 'set_role'], true)) {
            $alerts[] = ['type' => 'danger', 'text' => 'Eigenes Konto kann hier nicht geändert/gelöscht werden.'];
        } else {
            if ($action === 'set_role') {
                $role = $_POST['role'] ?? 'user';
                $res = update_user_role($db, $targetId, $role);
                $alerts[] = [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['success'] ? 'Rolle aktualisiert.' : $res['error']
                ];
            } elseif ($action === 'reset_pw') {
                $tempPass = 'Temp' . bin2hex(random_bytes(3)) . '!';
                $hash = password_hash($tempPass, PASSWORD_DEFAULT);
                $res = update_user_password($db, $targetId, $hash);
                $alerts[] = [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['success'] ? "Passwort zurückgesetzt. Neues Passwort: {$tempPass}" : $res['error']
                ];
            } elseif ($action === 'delete') {
                $res = delete_user_account($db, $targetId);
                $alerts[] = [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['success'] ? 'Konto gelöscht.' : $res['error']
                ];
            }
        }
    }
}

$users = [];

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

    <?php foreach ($alerts as $alert): ?>
        <div class="alert alert-<?= htmlspecialchars($alert['type'], ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($alert['text'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

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
                                        <div class="d-flex justify-content-end gap-1 flex-wrap">
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="set_role">
                                                <input type="hidden" name="user_id" value="<?= (int)$userRow['id'] ?>">
                                                <input type="hidden" name="role" value="<?= $userRow['role'] === 'admin' ? 'user' : 'admin' ?>">
                                                <button class="btn btn-outline-primary btn-sm" type="submit" <?= ((int)$userRow['id'] === (int)($_SESSION['user']['id'] ?? 0)) ? 'disabled' : '' ?>>
                                                    <?= $userRow['role'] === 'admin' ? 'Zu User' : 'Zu Admin' ?>
                                                </button>
                                            </form>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="reset_pw">
                                                <input type="hidden" name="user_id" value="<?= (int)$userRow['id'] ?>">
                                                <button class="btn btn-outline-warning btn-sm" type="submit">Reset PW</button>
                                            </form>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Konto wirklich löschen?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?= (int)$userRow['id'] ?>">
                                                <button class="btn btn-outline-danger btn-sm" type="submit" <?= ((int)$userRow['id'] === (int)($_SESSION['user']['id'] ?? 0)) ? 'disabled' : '' ?>>Delete</button>
                                            </form>
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
