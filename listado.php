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
        <h3>Cómics</h3>
        <?php 
        $conn = connect();

        // obtener el total de precio
        $stmt = $conn -> prepare("SELECT SUM(precio) AS total FROM listado;");
        $stmt->execute();
        $d = $stmt->get_result();
        $r = mysqli_fetch_array($d);
        $total = $r["total"];

        // obtener el número total de registros
        $stmt = $conn -> prepare("SELECT COUNT(*) AS ntot FROM listado");
        $stmt->execute();
        $d = $stmt->get_result();
        $r = mysqli_fetch_array($d);
        $ntot = $r["ntot"];

        $n = "";
        $page = 10;     // tamaño de página: 10 a piñón fijo
        // se pasa n
        if (isset($_GET["n"])) {
            $n = clear_input($_GET["n"]);
            if ($n == $ntot) $n = $n - $page + 1;
        } else {
            // si se pasa id, posicionar en la fila del id
            if (isset($_GET["id"])) {
                $id = clear_input($_GET["id"]);
                // obtener todos los registros y encontrar la fila de id
                $stmt = $conn -> prepare("SELECT * FROM listado");
                $stmt->execute();
                $d = $stmt->get_result();
                $n = 1;
                while ($r = mysqli_fetch_array($d)) {
                    if ($id == $r["id"]) {
                        break;
                    }
                    $n = $n + 1;
                }
            }
        }

        // posiciones
        $ini = $n;
        $fin = $n + $page - 1;
        $next = $fin + 1;
        $prev = $ini - $page;
        if ($prev < 0) $prev = 1;
        if ($ini < 0) $ini = 1;
        if ($next >= $ntot) $next = $fin;

        echo "<h6>Registros ".$ini." a ".$fin." de ".$ntot." (".number_format($total,2,",",".")."€)</h6>";?>
        <a class="btn btn-link" href="listado.php?n=1"><<</a>
        <a class="btn btn-link" <?php echo 'href="listado.php?n='.$prev.'"';?>><</a>
        <a class="btn btn-link" <?php echo 'href="listado.php?n='.$next.'"';?>>></a>
        <a class="btn btn-link" <?php echo 'href="listado.php?n='.$ntot.'"';?>>>></a><br>
        <a class="btn btn-link" href="ficha.php?id=0">Nuevo</a>
        <a class="btn btn-link" href="search.php?mes=1">Lista mes</a>
        <a class="btn btn-link" href="logout.php">Salir</a>
    </div>
</div>

<div class="container">
    <form action="search.php" method="post">
        <div class="input-group mt-2 mb-3">
            <input type="text" class="form-control" name="col" placeholder="Colección">
            <input type="text" class="form-control" name="artista" placeholder="Artistas">
            <input type="text" class="form-control" name="fecha" placeholder="Fecha compra">
            <div class="input-group-append">
                <button class="btn btn-outline-primary" name="buscar" type="submit">Buscar</button>
            </div>
        </div>
    </form>
</div>

<?php 
    $_SESSION['fechacompra'] = false;
    $_SESSION['search'] = false;
    $_SESSION['fecha'] = "";
    $_SESSION['limit'] = $n;
    include 'tabla.php';
} else {
    header("Location: logout.php");
}?>

</body>
</html>
