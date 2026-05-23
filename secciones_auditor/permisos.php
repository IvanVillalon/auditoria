 <h2>Permisos de Operarios</h2>
        <?php
    $sqlpermisos = $conexion->query( " SELECT id, usuario, permisos FROM usuarios");
        echo "<table border='1' cellpadding = '10'>";
        echo "<tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Permisos</th>
        </tr>";

        while ($row = $sqlpermisos->fetch_assoc()) {
            $permisos = json_decode($row['permisos'],true);

            echo "<tr>";
            echo "<td>{$row["id"]}</td>";
            echo "<td>{$row["usuario"]}</td>";
            echo "<td>";
            if ($permisos){
                foreach($permisos as $permiso => $valor){
                    if($valor){
                        echo "
                        <span style='
                        background:#198754;
                        color:white;
                        padding:4px 8px;
                        border-radius:8px;
                        margin:2px;
                        display:inline-block;
                        font-size:12px;
                        '>
                        $permiso
                        </span>
                        ";
                    }
                }
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
   