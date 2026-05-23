 <h2>Registrar Clientes</h2>
        <form action="registroclientes.php" method="POST">
            Nombre:
            <input type="text" name="nombre" placeholder="Nombre del cliente" required><br><br>
            Rut: 
            <input type="text" name="rut_cliente" placeholder="Rut del cliente" required><br><br>
            Apellido:
            <input type="text" name="apellido" placeholder="Apellido del cliente" required><br><br>
            Correo electrónico:
            <input type="email" name="correo" placeholder="Correo electrónico" required><br><br>
            <input type="submit" value="Registrar Cliente">

        </form>