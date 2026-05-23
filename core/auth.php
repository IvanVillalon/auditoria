<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verificarLogin($rol = null) {
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    if ($rol && $_SESSION['rol'] != $rol) {
        header("Location: login.php");
        exit();
    }
}