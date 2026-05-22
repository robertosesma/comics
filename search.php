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
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username'])) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h3>Resultado búsqueda</h3>
        <?php
        $conn = connect();

        // hay una fecha definida? -> búsqueda por mes
        if (isset($_GET["mes"])) {
            $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
            $m = date('m')-1;
            $fecha = $meses[$m]." ".date('Y');
        } else {
            $fecha = clear_input($_POST["fecha"]);
        }
        if (strlen($fecha)==0 && strlen($_SESSION["fecha"])>0) {
            $fecha = $_SESSION["fecha"];
        }

        $stmt = null;
        if (strlen($fecha)>0) {
            // obtener precio total y pagado del mes
            $stmt = $conn -> prepare("SELECT SUM(precio) AS total,
	                (SELECT SUM(precio) FROM listado WHERE fecha_compra LIKE ? AND comprado = 1) AS pagado
                FROM listado WHERE fecha_compra LIKE ?;");
            $stmt->bind_param('ss', $fecha, $fecha);
            $stmt->execute();
            $d = $stmt->get_result();
            $r = mysqli_fetch_array($d);
            $total = $r["total"];
            $pagado = $r["pagado"];
            $pendiente = $total - $pagado;
            // mostrar total/pagado/pendiente
            echo "<h5><b>TOTAL: ".number_format($total,2,",",".")."€</b></h5>";
            echo "<p>Pagado: ".number_format($pagado,2,",",".").
                "€ - Pendiente: ".number_format($pendiente,2,",",".")."€</p>";

            $_SESSION['fechacompra'] = true;
            $_SESSION['fecha'] = $fecha;
            $_SESSION['col'] = '';
            $_SESSION['artista'] = '';
        } else {
            // obtener resultado de la búsqueda
            $col = clear_input($_POST["col"]);
            $artista = clear_input($_POST["artista"]);
            $_SESSION['col'] = (strlen($col)>0 ? "%".$col."%" : "");
            $_SESSION['artista'] = (strlen($artista)>0 ? "%".$artista."%" : "");
            $_SESSION['fechacompra'] = false;
            $_SESSION['fecha'] = '';
        }
        ?>

        <a class="btn btn-link" href="listado.php?n=1">Atrás</a>
        <a class="btn btn-link" href="logout.php">Salir</a>
    </div>
</div>

<?php 
    $_SESSION['search'] = true;
    include 'tabla.php';
} else {
    header("Location: logout.php");
}?>

</body>
</html>
