<?php

function tienePermiso($permiso) {
    return isset($_SESSION['permisos']) && in_array($permiso, $_SESSION['permisos']);
}