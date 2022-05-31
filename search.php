<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Listado</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>

<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username'])) {
    $conn = connect();

    $lista_mes = false;
    if (isset($_GET["mes"])) {
        $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
            "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        $m = date('m')-1;
        $fecha = $meses[$m]." ".date('Y');
    } else {
        $fecha = clear_input($_POST["fecha"]);
    }
    if (strlen($fecha)>0) {
        $lista_mes = true;
        // total ítems comprados
        $stmt = $conn -> prepare("SELECT SUM(precio) AS precio FROM listado WHERE fecha_compra LIKE ? AND comprado = 1");
        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        $d = $stmt->get_result();
        $r = mysqli_fetch_array($d);
        $pagado = $r["precio"];
        // total mes
        $stmt = $conn -> prepare("SELECT SUM(precio) AS precio FROM listado WHERE fecha_compra LIKE ?");
        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        $d = $stmt->get_result();
        $r = mysqli_fetch_array($d);
        $total = $r["precio"];
        $pendiente = $total - $pagado;
        // obtener listado mes
        $stmt = $conn -> prepare("SELECT * FROM listado WHERE fecha_compra LIKE ?");
        $stmt->bind_param('s', $fecha);
    } else {
        // obtener resultado de la búsqueda
        $col = clear_input($_POST["col"]);
        $artista = clear_input($_POST["artista"]);
        if (strlen($col)>0 && strlen($artista)>0) {
            $col = "%".$col."%";
            $artista = "%".$artista."%";
            $stmt = $conn -> prepare("SELECT * FROM listado WHERE coleccion LIKE ? AND autores LIKE ?");
            $stmt->bind_param('ss', $col, $artista);
        }
        if (strlen($col)>0) {
            $col = "%".$col."%";
            $stmt = $conn -> prepare("SELECT * FROM listado WHERE coleccion LIKE ?");
            $stmt->bind_param('s', $col);
        }
        if (strlen($artista)>0) {
            $artista = "%".$artista."%";
            $stmt = $conn -> prepare("SELECT * FROM listado WHERE autores LIKE ?");
            $stmt->bind_param('s', $artista);
        }
    }
    $stmt->execute();
    $d = $stmt->get_result();
    $nrows = $d->num_rows;
} else {
    $ok = false;
}
?>

<?php if ($ok){ ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h3>Resultado búsqueda</h3>
        <?php
        if ($lista_mes) {
            echo "<h5><b>TOTAL: ".number_format($total,2,",",",")."€</b></h5>";
            echo "<p>Pagado: ".number_format($pagado,2,",",",")."€ - Pendiente: ".number_format($pendiente,2,",",",")."€</p>";
        }
        echo "<h6>Número de registros: ".$nrows."</h6>";
        ?>
        <a class="btn btn-link" href="listado.php?n=1">Atrás</a>
        <a class="btn btn-link" href="logout.php">Salir</a>
    </div>
</div>

<div class="container">
    <?php if ($lista_mes) { ?>
        <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Colección</th>
                    <th>Vol.</th>
                    <th>Núm.</th>
                    <th>Título</th>
                    <th>Artistas</th>
                    <th><div class='text-right'>Precio</div></th>
                    <th><div class='text-center'>Fecha</div></th>
                    <th><div class='text-center'>Leído</div></th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($r = mysqli_fetch_array($d)) {
                    if ($r["comprado"]==1) {
                        echo "<tr>";
                    } else {
                        echo "<tr class=table-secondary>";
                    } ?>
                        <td><?php echo "<a href='ficha.php?id=".$r["id"]."'>".$r["coleccion"]."</a>"; ?></td>
                        <td><?php echo $r["vol"]; ?></td>
                        <td><?php echo $r["num"]; ?></td>
                        <td><?php echo $r["titulo"]; ?></td>
                        <td><?php echo $r["autores"]; ?></td>
                        <td><div class='text-right'><?php echo number_format($r["precio"],2,",",".")."€"; ?></td>
                        <td><div class='text-center'><?php echo $r["fecha_compra"]; ?></td>
                        <td><div class='text-center'><?php echo ($r["comprado"]==1 && $r["pendiente"]==0 ? "Sí" : "No"); ?></div></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Colección</th>
                    <th>Vol.</th>
                    <th>Núm.</th>
                    <th>Título</th>
                    <th>Artistas</th>
                    <th>Precio</div></th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($r = mysqli_fetch_array($d)) { ?>
                    <tr>
                        <td><?php echo "<a href='ficha.php?id=".$r["id"]."'>".$r["coleccion"]."</a>"; ?></td>
                        <td><?php echo $r["vol"]; ?></td>
                        <td><?php echo $r["num"]; ?></td>
                        <td><?php echo $r["titulo"]; ?></td>
                        <td><?php echo $r["autores"]; ?></td>
                        <td><div class='text-right'><?php echo number_format($r["precio"],2,",",",")."€"; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</div>

<?php
    location.reload();
    $conn->close(); ?>
<?php } else {
    header("Location: logout.php");
}?>

</body>
</html>
