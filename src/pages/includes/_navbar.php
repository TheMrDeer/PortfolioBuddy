<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/PortfolioBuddy/dashboard.php">PortfolioBuddy</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dashNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="dashNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>" href="/PortfolioBuddy/dashboard.php">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'positions.php') ? 'active' : '' ?>" href="/PortfolioBuddy/positions.php">Positions</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://i.pravatar.cc/28?u=<?= htmlspecialchars($_SESSION['user']['email'], ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" class="rounded-circle me-2" width="28" height="28">
                        <span><?= htmlspecialchars($_SESSION['user']['fullname'], ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileMenu">
                        <li><a class="dropdown-item <?= ($currentPage === 'profile.php') ? 'active' : '' ?>" href="/PortfolioBuddy/profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/PortfolioBuddy/logout.php" >Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>