<?php
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username'])) { 
    $conn = connect();

    $stmt = null;
    $mes = false;
    if (isset($_SESSION['search']) && $_SESSION['search']) {
        if ($_SESSION['fechacompra']) {
            $mes = true;
            $stmt = $conn -> prepare("SELECT * FROM listado WHERE fecha_compra LIKE ?");
            $stmt->bind_param('s', $_SESSION['fecha']);
        } else {
            $col = $_SESSION['col'];
            $artista = $_SESSION['artista'];
            
            if (strlen($col)>0 && strlen($artista)>0) {
                $stmt = $conn -> prepare("SELECT * FROM listado WHERE coleccion LIKE ? AND autores LIKE ?");
                $stmt->bind_param('ss', $col, $artista);
            } elseif (strlen($col)>0) {
                $stmt = $conn -> prepare("SELECT * FROM listado WHERE coleccion LIKE ?");
                $stmt->bind_param('s', $col);
            } elseif (strlen($artista)>0) {
                $stmt = $conn -> prepare("SELECT * FROM listado WHERE autores LIKE ?");
                $stmt->bind_param('s', $artista);
            }
        }
    } else {
        if (isset($_SESSION['limit'])) {
            $stmt = $conn -> prepare("SELECT * FROM listado LIMIT ?, ?");
            $n = $_SESSION['limit']-1;
            $stmt->bind_param('ii', $n, $page);
        } else {
            $stmt = $conn -> prepare("SELECT * FROM listado");
        }
    }
    $stmt->execute();
    $d = $stmt->get_result(); 
    $nrows = $d->num_rows;

    if ($_SESSION['search']) {
        echo '<div class="container">';
        echo "<h6>Número de registros: ".$nrows."</h6>";
        echo '</div>';
    }
    ?>

    <div class="container">
        <?php if ($mes) {
            echo '<table cellpadding="0" cellspacing="0" border="0" class="table table-bordered">';
        } else {
            echo '<table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">';
        } ?>
            <thead class="thead-light">
                <tr>
                    <th>Portada</th>
                    <th>Colección</th>
                    <th>Núm.</th>
                    <?php if (!isMobile()) { ?>
                        <th>Título</th>
                        <th>Artistas</th>
                        <th class='text-right'>Precio</th>
                        <?php if ($mes) { ?>
                            <th><div class='text-center'>Fecha</div></th>
                            <th><div class='text-center'>Leído</div></th>
                        <?php } ?>
                    <?php } else { ?>
                        <th class='text-right'>Precio</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($r = mysqli_fetch_array($d)) { 
                    echo "<tr".($mes && $r["comprado"]==0 ? " class=table-secondary" : "").">";
                        echo "<td><img src='../portadas/".$r["id"].".jpg??=".time()."' width=50 height=75 class='img-rounded' alt=''></td>";
                        echo "<td><a href='ficha.php?id=".$r["id"]."'>".$r["coleccion"]."</a></td>";
                        echo "<td>".($r["vol"]>0 ? "(".$r["vol"].") " : "").$r["num"]."</td>";
                        if (!isMobile()) {
                            echo "<td>".$r["titulo"]."</td>";
                            echo "<td>".$r["autores"]."</td>";
                            echo "<td><div class='text-right'>".number_format($r["precio"],2,",",".")."€</div></td>";
                            if ($mes) {
                                echo "<td><div class='text-center'>".$r["fecha_compra"]."</td>";
                                echo "<td><div class='text-center'>".
                                    ($r["comprado"]==1 && $r["pendiente"]==0 ? "Sí" : "No")."</div></td>";
                            }
                        } else {
                            echo "<td><div class='text-right'>".number_format($r["precio"],2,",",".")."€</div></td>";
                        }
                    echo "</tr>";
                } ?>
            </tbody>
        </table>
    </div>

<?php
    header("Refresh:0");
    $d->free();
    $conn->close();
} else {
    header("Location: logout.php");
}?>
