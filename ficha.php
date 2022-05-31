<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Ficha</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</head>

<body>
<?php
include 'func_aux.php';
$ok = true;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])
    && (isset($_GET['id']) || $_SERVER["REQUEST_METHOD"] == "POST")) {
    // Create connection
    $conn = connect();

    if ($_SERVER["REQUEST_METHOD"] == "POST" ) {
        $id = clear_input($_POST["id"]);

        // grabar los cambios
        $vol = (trim(clear_input($_POST["vol"])) == "" ? NULL : clear_input($_POST["vol"]));
        $num = (trim(clear_input($_POST["num"])) == "" ? NULL : clear_input($_POST["num"]));
        $aed = (trim(clear_input($_POST["aed"])) == "" ? NULL : clear_input($_POST["aed"]));
        $med = (trim(clear_input($_POST["med"])) == "" ? NULL : clear_input($_POST["med"]));
        $edorig = (trim(clear_input($_POST["edorig"])) == "" ? NULL : clear_input($_POST["edorig"]));
        $acompra = (trim(clear_input($_POST["acompra"])) == "" ? NULL : clear_input($_POST["acompra"]));
        $mcompra = (trim(clear_input($_POST["mcompra"])) == "" ? NULL : clear_input($_POST["mcompra"]));
        $comprado = (clear_input($_POST["comprado"]=="comprado") == 1 ? 1 : 0);
        $pendiente = (clear_input($_POST["pendiente"]=="pendiente") == 1 ? 1 : 0);

        $stmt = $conn -> prepare('UPDATE ficha SET col=?, vol=?, num=?, pags=?, precio=?,
            titulo=?, genero=?, aed=?, med=?, ed=?, edorig=?,
            comprado=?, pendiente=?, mcompra=?, acompra=? WHERE id=?');
        $stmt->bind_param('iiiidsiiiiiiiiii', clear_input($_POST["col"]),
            $vol, $num, clear_input($_POST["pags"]), str_replace(",",".",clear_input($_POST["precio"])),
            clear_input($_POST["titulo"]), clear_input($_POST["genero"]),
            $aed, $med, clear_input($_POST["ed"]), $edorig, $comprado, $pendiente,
            $mcompra, $acompra, $id);
        $stmt->execute();
        header("Location: listado.php?id=".$id);
    }
    if ($_SERVER["REQUEST_METHOD"] == "GET" ) {
        $id = clear_input($_GET["id"]);

        $stmt = $conn -> prepare('SELECT * FROM dcol ORDER BY descrip');
        $stmt->execute();
        $dcol = $stmt->get_result();
        $stmt = $conn -> prepare('SELECT * FROM dgenero');
        $stmt->execute();
        $dgen = $stmt->get_result();
        $stmt = $conn -> prepare('SELECT * FROM dmeses');
        $stmt->execute();
        $dmes = $stmt->get_result();
        $stmt = $conn -> prepare('SELECT * FROM ded');
        $stmt->execute();
        $ded = $stmt->get_result();

        if ($id==0) {
            $new = 1;
            // añadir el nuevo registro
            $id = getnextid($conn);
            $stmt = $conn -> prepare('INSERT INTO ficha (id) VALUES (?)');
            $stmt->bind_param('i',$id);
            $stmt->execute();
            $title = "Nueva ficha. Identificador: ".$id;
        } else {
            $new = 0;
            // Título
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

            $stmt = $conn -> prepare('SELECT * FROM ficha WHERE id = ?');
            $stmt->bind_param('i',  $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows>0) {
                $d = mysqli_fetch_array($result);
                // autores
                $stmt = $conn -> prepare('SELECT dautores.nombre AS nombre, drol.descrip AS rol
                    FROM ((autores
                    LEFT JOIN dautores ON(autores.ida = dautores.ida))
                    LEFT JOIN drol ON(autores.rol = drol.cod))
                    WHERE autores.id = ?
                    ORDER BY autores.rol');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $autores = $stmt->get_result();
                // contenidos
                $stmt = $conn -> prepare('SELECT id, idc, dcol.descrip AS coleccion,
                    vol, num, titulo, CONCAT_WS(" ",dmeses.descrip, anyo) AS fecha
                    FROM ((contenidos
                    LEFT JOIN dcol ON (contenidos.col = dcol.cod))
                    LEFT JOIN dmeses ON (contenidos.mes = dmeses.cod))
                    WHERE id = ?
                    ORDER BY col, vol, num');
                $stmt->bind_param('i',  $id);
                $stmt->execute();
                $contenidos = $stmt->get_result();
            } else {
                $ok = false;
            }
        }
    }
} else {
    $ok = false;
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h4><?php echo $title; ?></h4>
        <a class="btn btn-link" href="add_col.php?id=<?php echo $id; ?>&orig=0">Nueva colección</a>
        <a class="btn btn-link" href="add_ed.php?id=<?php echo $id; ?>">Nueva editorial</a>
        <br>
        <a class="btn btn-link" href="del_ficha.php?id=<?php echo $id; ?>">Borrar</a>
        <?php if ($new!=1) {
            echo '<a class="btn btn-link" href="listado.php?id='.$id.'">Atrás</a>';
        } ?>
        <a class="btn btn-link" href="logout.php">Salir</a>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-3">
                <!-- form para subir la portada: upload.php -->
                <form action="upload.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <?php echo "<img src='../portadas/".$id.".jpg' class='img-responsive' width=250 height=385 alt='portada'>"; ?>
                        <div class="custom-file mt-2">
                            <input type="file" class="custom-file-input" accept=".jpg,.jpeg,.png" name="portada" id="portada">
                            <label class="custom-file-label" for="portada">Escoger portada</label>
                            <input type="text" class="form-control" hidden="true" name="idportada" value="<?php echo $id; ?>">
                            <button class="btn btn-secondary mt-2 mb-5" name="subir" type="submit">Subir</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-9">
                <!-- form para modificar los datos de ficha: self -->
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">Colección</span></div>
                        <select name="col" class="custom-select">
                            <option> </option>
                            <?php while ($c = mysqli_fetch_array($dcol)) {
                                echo '<option '.($c["cod"]==$d["col"] ? "selected" : "").' value="'.$c["cod"].'">'.$c["descrip"].'</option>';
                            } ?>
                        </select>
                    </div>

                    <div class="input-group mt-3">
                        <div class="input-group-prepend"><span class="input-group-text">Vol</span></div>
                        <input type="text" class="form-control" name="vol" value="<?php echo $d["vol"] ?>">
                        <div class="input-group-prepend"><span class="input-group-text">Num</span></div>
                        <input type="text" class="form-control" name="num" value="<?php echo $d["num"] ?>">
                        <div class="input-group-prepend"><span class="input-group-text">Págs</span></div>
                        <input type="text" class="form-control" name="pags" value="<?php echo $d["pags"] ?>">
                        <div class="input-group-prepend"><span class="input-group-text">Precio</span></div>
                        <input type="text" class="form-control text-right" name="precio" value="<?php echo number_format($d["precio"],2,",",".") ?>">
                        <div class="input-group-append"><span class="input-group-text">€</span></div>
                    </div>

                    <div class="form-group mt-2">
                        <label for="titulo">Título:</label>
                        <textarea class="form-control" rows="3" name="titulo"><?php echo $d["titulo"] ?></textarea>
                    </div>

                    <div class="input-group mt-3">
                        <div class="input-group-prepend"><span class="input-group-text">Género</span></div>
                        <select name="genero" class="custom-select">
                            <option> </option>
                            <?php while ($g = mysqli_fetch_array($dgen)) {
                                echo '<option '.($g["cod"]==$d["genero"] ? "selected" : "").' value="'.$g["cod"].'">'.$g["descrip"].'</option>';
                            } ?>
                        </select>
                        <div class="input-group-prepend"><span class="input-group-text">Fecha edición</span></div>
                        <select name="med" class="custom-select">
                            <option> </option>
                            <?php while ($m = mysqli_fetch_array($dmes)) {
                                echo '<option '.($m["cod"]==$d["med"] ? "selected" : "").' value="'.$m["cod"].'">'.$m["descrip"].'</option>';
                            } ?>
                        </select>
                        <input type="text" class="form-control text-right" name="aed" value="<?php echo $d["aed"] ?>">
                    </div>

                    <div class="input-group mt-3">
                        <div class="input-group-prepend"><span class="input-group-text">Editorial</span></div>
                        <select name="ed" class="custom-select">
                            <option> </option>
                            <?php while ($e = mysqli_fetch_array($ded)) {
                                echo '<option '.($e["cod"]==$d["ed"] ? "selected" : "").' value="'.$e["cod"].'">'.$e["descrip"].'</option>';
                            } ?>
                        </select>
                        <div class="input-group-prepend"><span class="input-group-text">Ed. Original</span></div>
                        <select name="edorig" class="custom-select">
                            <option> </option>
                            <?php mysqli_data_seek($ded, 0);
                                while ($e = mysqli_fetch_array($ded)) {
                                echo '<option '.($e["cod"]==$d["edorig"] ? "selected" : "").' value="'.$e["cod"].'">'.$e["descrip"].'</option>';
                            } ?>
                        </select>
                    </div>

                    <div class="input-group mt-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="comprado" id="comprado"
                                value="comprado" <?php echo ($d["comprado"]==1 ? "checked" : ""); ?>>
                            <label class="custom-control-label" for="comprado">Comprado</label>
                        </div>
                        <div class="custom-control custom-checkbox ml-3">
                            <input type="checkbox" class="custom-control-input" name="pendiente" id="pendiente"
                                value="pendiente" <?php echo ($d["pendiente"]==1 ? "checked" : ""); ?>>
                            <label class="custom-control-label" for="pendiente">Pendiente</label>
                        </div>
                        <div class="input-group-prepend ml-3"><span class="input-group-text">Fecha compra</span></div>
                        <select name="mcompra" class="custom-select ">
                            <option> </option>
                            <?php mysqli_data_seek($dmes, 0);
                            while ($m = mysqli_fetch_array($dmes)) {
                                $selected = ($m["cod"]==$d["mcompra"] ? "selected" : "");
                                echo '<option '.$selected.' value="'.$m["cod"].'">'.$m["descrip"].'</option>';
                            } ?>
                        </select>
                        <input type="text" class="form-control text-right" name="acompra" value="<?php echo $d["acompra"] ?>">
                    </div>
                    <input type="text" class="form-control" hidden="true" name="id" value="<?php echo $id; ?>">
                    <button class="btn btn-primary mt-2 mb-5" name="guardar" type="submit">Guardar</button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-4">
                <!-- form para añadir un autor: add_artista -->
                <form action="edit_artista.php" class="form-inline" method="post">
                    <label class="label h2 mr-4">Artistas</label>
                    <input type="text" class="form-control" hidden="true" name="id" value="<?php echo $id; ?>">
                    <button class="btn btn-success mt-2 mb-2" name="edit_artista" type="submit">Editar</button>
                </form>
                <!-- tabla autores -->
                <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">
                    <tbody>
                    <?php while ($a = mysqli_fetch_array($autores)) { ?>
                        <tr>
                            <td><?php echo $a["nombre"]; ?></td>
                            <td><?php echo $a["rol"]; ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="col-8">
                <!-- form para añadir un contenido: add_content -->
                <form action="edit_content.php" class="form-inline" method="post">
                    <label class="label h2 mr-4">Contenido</label>
                    <input type="text" class="form-control" hidden="true" name="id" value="<?php echo $id; ?>">
                    <button class="btn btn-success mt-2 mb-2" name="edit_content" type="submit">Editar</button>
                </form>
                <!-- tabla contenidos / eliminar contenido: del_contenido.php -->
                <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Colección</th>
                            <th>Vol.</th>
                            <th>Núm.</th>
                            <th>Título</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($c = mysqli_fetch_array($contenidos)) { ?>
                        <tr>
                            <td><?php echo $c["coleccion"]; ?></td>
                            <td><?php echo $c["vol"]; ?></td>
                            <td><?php echo $c["num"]; ?></td>
                            <td><?php echo $c["titulo"]; ?></td>
                            <td><?php echo $c["fecha"]; ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Add the following code if you want the name of the file appear on select
$(".custom-file-input").on("change", function() {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});
</script>

<?php
    location.reload();
    $dcol->free();
    $dgen->free();
    $dmes->free();
    $ded->free();
    $d->free();
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
