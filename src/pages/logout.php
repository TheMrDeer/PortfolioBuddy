<?php
session_start();
session_unset();
session_destroy();
header('Location: /PortfolioBuddy/login.php');
    exit;
?>