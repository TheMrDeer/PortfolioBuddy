<?php
session_start();
session_destroy();
header('Location: /PortfolioBuddy/login.php');
    exit;
?>