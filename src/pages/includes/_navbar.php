<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);

$avatarUrl = 'https://i.pravatar.cc/28?u=' . urlencode($_SESSION['user']['email'] ?? 'guest'); // Standard-Avatar

// Projektroot (eine Ebene über /includes)
$projectRoot = dirname(__DIR__);

// Verzeichnis mit Profilbildern dieses Users
$avatarDirFs = $projectRoot . '/user_uploads/' . $_SESSION['user']['id'] . '/profilepicture';

// Bilddateien im Ordner suchen (jpg/png/gif/webp)
$files = glob($avatarDirFs . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);

if ($files && count($files) > 0) {
    // Neueste Datei nehmen (sortiert nach Änderungszeit)
    usort($files, function ($a, $b) {
        return filemtime($b) <=> filemtime($a);
    });
    $FileName = basename($files[0]);

    // URL fürs <img>-Tag bauen (nicht Filesystem-Pfad!)
    $avatarUrl = '/user_uploads/' . $_SESSION['user']['id'] . "/profilepicture" . "/" . $FileName;
}

?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/dashboard.php">PortfolioBuddy</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dashNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="dashNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>" href="/dashboard.php">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'positions.php') ? 'active' : '' ?>" href="/positions.php">Positions</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                            alt="Avatar"
                            class="rounded-circle me-2"
                            width="28"
                            height="28">
                        <span><?= htmlspecialchars($_SESSION['user']['fullname'], ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileMenu">
                        <li><a class="dropdown-item <?= ($currentPage === 'profile.php') ? 'active' : '' ?>" href="/profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/logout.php" >Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
