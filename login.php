<?php
session_start();
require_once __DIR__ . '/core/db.php';
/*require_once "services/historial_service.php";*/
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $clave = trim($_POST['clave']);

    $stmt = $conexion->prepare("SELECT 
    u.id,
    u.usuario,
    u.rol,
    u.permisos,
    u.clave,
    u.id_sucursal,
    s.id_sucursal,
    s.nombre AS nombre_sucursal
    FROM usuarios u
    LEFT JOIN sucursales s ON u.id_sucursal = s.id_sucursal
    WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {

        $datos = $resultado->fetch_assoc();
        if(password_verify($clave,$datos['clave'])){
        session_regenerate_id(true);
        
        $_SESSION['usuario'] = $datos['usuario'];
        $_SESSION['rol'] = $datos['rol'];
        $_SESSION['id'] = $datos['id'];

        $_SESSION['permisos'] = json_decode($datos['permisos'], true);
        
        $_SESSION['sucursal_nombre'] = $datos['nombre_sucursal'];
        $_SESSION['sucursal'] = $datos['id_sucursal'];

        $_SESSION['ultima_actividad'] = time();


/*        registrarHistorial( 
            $conexion,
            $_SESSION['id'],
            'login',
            'Inicio de sesion',
            'usuarios',
            null,
            $_SESSION['sucursal']

        );*/
        $tiempo_inactividad = 1800; // 30 minutos

        if ($datos['rol'] == 'vendedor') {
            header("Location: vendedor.php");
        } elseif ($datos['rol'] == 'auditor') {
            header("Location: auditor.php");
        } elseif($datos['rol'] == 'admin'){
            header("Location: admin/admin.php");
        }
        else{
            echo "Rol invalido";
        }
        exit();
    } else {
        $error= "Usuario o contraseña incorrectos. Intenta nuevamente.";
    }
}else
        {
        $error= "Usuario o contraseña incorrectos. Intenta nuevamente.";
    }
    
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }
    </style>
</head>

<body>

<div class="container d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow p-4" style="width: 350px;">

        <h3 class="text-center mb-4">Iniciar Sesión</h3>

        <form method="post">

            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" name="usuario" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Clave</label>
                <input type="password" name="clave" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Ingresar
            </button>

        </form>
        <?php if ($error): ?>
    <div class="alert alert-danger text-center">
        <?= $error ?>
    </div>
<?php endif; ?>
    </div>

</div>

</body>
</html>
