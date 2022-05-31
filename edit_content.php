<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar contenidos</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
<?php
session_start();
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username'])) {
    $conn = connect();
    if ($_SERVER["REQUEST_METHOD"] == "POST" ) $id = clear_input($_POST["id"]);
    if ($_SERVER["REQUEST_METHOD"] == "GET" ) $id = clear_input($_GET["id"]);

    // obtener título
    $stmt = $conn -> prepare('SELECT dcol.descrip AS coleccion,
        CONCAT_WS(" ", CONCAT("vol.", ficha.vol), ficha.num) as numero
        FROM (ficha
        LEFT JOIN dcol ON(ficha.col = dcol.cod))
        WHERE ficha.id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $d = $stmt->get_result();
    $r = mysqli_fetch_array($d);
    $title = $r["coleccion"];
    $title = $title.(strlen($r["numero"])>0 ? ' '.$r["numero"] : '');
    $title = $title.' (id:'.$id.')';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
        // añadir contenido
        $idc = getnextidc($conn);
        $vol = (trim(clear_input($_POST["vol"])) == "" ? NULL : clear_input($_POST["vol"]));
        $num = (trim(clear_input($_POST["num"])) == "" ? NULL : clear_input($_POST["num"]));
        $mes = (trim(clear_input($_POST["mes"])) == "" ? NULL : clear_input($_POST["mes"]));
        $anyo = (trim(clear_input($_POST["anyo"])) == "" ? NULL : clear_input($_POST["anyo"]));
        $stmt = $conn -> prepare('INSERT INTO contenidos (id,idc,vol,num,col,mes,anyo,titulo)
                                VALUES (?,?,?,?,?,?,?,?)');
        $stmt->bind_param('iiiiiiis', $id, $idc, $vol, $num, clear_input($_POST["col"]),
                $mes, $anyo, clear_input($_POST["titulo"]));
        $stmt->execute();
    }

    // diccionario colecciones
    $stmt = $conn -> prepare('SELECT * FROM dcol ORDER BY descrip');
    $stmt->execute();
    $dcol = $stmt->get_result();
    // diccionario meses
    $stmt = $conn -> prepare('SELECT * FROM dmeses');
    $stmt->execute();
    $dmes = $stmt->get_result();
    // datos artistas y roles para el registro
    $stmt = $conn -> prepare('SELECT id, idc, dcol.descrip AS coleccion, titulo,
        vol, num, dmeses.descrip as mes, anyo
        FROM ((contenidos
        LEFT JOIN dcol ON(contenidos.col = dcol.cod))
        LEFT JOIN dmeses ON(contenidos.mes = dmeses.cod))
        WHERE contenidos.id = ?
        ORDER BY contenidos.idc, contenidos.vol, contenidos.num');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $d = $stmt->get_result();
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container-fluid">
        <div class="row">
            <div class="col-8">
                <div class="container p-3 my-3 border">
                    <h4>Editar contenidos</h4>
                    <h5><?php echo $title; ?></h5>
                    <a class="btn btn-link" href="add_col.php?id=<?php echo $id; ?>&orig=1">Nueva colección</a>
                    <a class="btn btn-link" href="ficha.php?id=<?php echo $id; ?>">Atrás</a>
                    <a class="btn btn-link" href="logout.php">Salir</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-8">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">Colección</span></div>
                        <select name="col" class="custom-select">
                            <option> </option>
                            <?php while ($c = mysqli_fetch_array($dcol)) {
                                echo '<option value="'.$c["cod"].'">'.$c["descrip"].'</option>';
                            } ?>
                        </select>
                    </div>
                    <div class="input-group mt-2">
                        <div class="input-group-prepend"><span class="input-group-text">Vol.</span></div>
                        <input type="text" class="form-control" name="vol">
                        <div class="input-group-prepend"><span class="input-group-text">Num.</span></div>
                        <input type="text" class="form-control" name="num">
                        <div class="input-group-prepend"><span class="input-group-text">Fecha ed.</span></div>
                        <select name="mes" class="custom-select">
                            <option> </option>
                            <?php while ($m = mysqli_fetch_array($dmes)) {
                                echo '<option value="'.$m["cod"].'">'.$m["descrip"].'</option>';
                            } ?>
                        </select>
                        <input type="text" class="form-control" name="anyo">
                    </div>
                    <div class="input-group mt-2">
                        <div class="input-group-prepend"><span class="input-group-text">Título</span></div>
                        <input type="text" class="form-control" name="titulo">
                    </div>
                    <input type="text" class="form-control" hidden="true" name="id" value="<?php echo $id; ?>">
                    <button class="btn btn-primary mt-2 mb-5" name="add" type="submit">Añadir</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-8">
                <!-- tabla contenidos -->
                <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Colección</th>
                            <th>Vol.</th>
                            <th>Núm.</th>
                            <th>Título</th>
                            <th>Mes</th>
                            <th>Año</th>
                            <th> </th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($r = mysqli_fetch_array($d)) { ?>
                        <tr>
                            <td><?php echo $r["coleccion"]; ?></td>
                            <td><?php echo $r["vol"]; ?></td>
                            <td><?php echo $r["num"]; ?></td>
                            <td><?php echo $r["titulo"]; ?></td>
                            <td><?php echo $r["mes"]; ?></td>
                            <td><?php echo $r["anyo"]; ?></td>
                            <?php
                            $del = 'del_content.php?id='.$id."&idc=".$r["idc"];?>
                            <td><a onClick="javascript: return confirm('¿Confirma que desea borrar?');" href=<?php echo $del ?>>x</a></td><tr>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
    $dcol->free();
    $dmes->free();
    $d->free();
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
