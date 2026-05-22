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
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['username'])
    && (isset($_GET['id']) || $_SERVER["REQUEST_METHOD"] == "POST")) {
    // Create connection
    $conn = connect();
    $new = 0;

    if ($_SERVER["REQUEST_METHOD"] == "POST" ) {
        $id = clear_input($_POST["id"]);

        if (isset($_POST['guardar'])) {
            // grabar los cambios en la ficha
            $col = (trim(clear_input($_POST["col"])) == "" ? NULL : clear_input($_POST["col"]));
            $vol = (trim(clear_input($_POST["vol"])) == "" ? NULL : clear_input($_POST["vol"]));
            $num = (trim(clear_input($_POST["num"])) == "" ? NULL : clear_input($_POST["num"]));
            $pags = (trim(clear_input($_POST["pags"])) == "" ? NULL : clear_input($_POST["pags"]));
            $precio = (trim(clear_input($_POST["precio"])) == "" ? NULL : 
                        str_replace(",",".",clear_input($_POST["precio"])));
            $titulo = (trim(clear_input($_POST["titulo"])) == "" ? NULL : clear_input($_POST["titulo"]));
            $genero = (trim(clear_input($_POST["genero"])) == "" ? NULL : clear_input($_POST["genero"]));
            $ed = (trim(clear_input($_POST["ed"])) == "" ? NULL : clear_input($_POST["ed"]));
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
            $stmt->bind_param('iiiidsiiiiiiiiii', $col, $vol, $num, $pags,
                $precio, $titulo, $genero, $aed, $med, $ed, $edorig, 
                $comprado, $pendiente, $mcompra, $acompra, $id);
            $stmt->execute();
            if ($new==0 && $_SESSION['fechacompra']) {
                header("Location: search.php");
            } else {
                header("Location: listado.php?id=".$id);
            } 
        }
        if (isset($_POST['nuevacol'])) {
            $col = clear_input($_POST["newcol"]);
            $idcol = getnextcol($conn);
            // añadir una NUEVA colección
            $stmt = $conn -> prepare('INSERT INTO dcol (cod, descrip) VALUES (?, ?)');
            $stmt->bind_param('is', $idcol, $col);
            $stmt->execute();
        }
        if (isset($_POST['nuevaed'])) {
            $ed = clear_input($_POST["newed"]);
            $ided = getnexted($conn);
            // añadir una NUEVA editorial
            $stmt = $conn -> prepare('INSERT INTO ded (cod, descrip) VALUES (?, ?)');
            $stmt->bind_param('is', $ided, $ed);
            $stmt->execute();
        }
    }

    // obtener diccionarios
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

    $new = 0;
    $title = '';
    $d = $autores = $contenidos = null;
    if ($_SERVER["REQUEST_METHOD"] == "GET" ) $id = clear_input($_GET["id"]);
    $ok = true;
    if ($id==0) {
        $new = 1;
        // añadir el nuevo registro
        $id = getnextid($conn);
        $stmt = $conn -> prepare('INSERT INTO ficha (id) VALUES (?)');
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $title = "Nueva ficha. Identificador: ".$id;
    } 
    if ($new == 0) {
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
        $d->free();

        $stmt = $conn -> prepare('SELECT * FROM ficha WHERE id = ?');
        $stmt->bind_param('i',  $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows>0) {
            // datos comic
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
    
    if ($ok) {?>

<div class="container">
    <div class="container p-3 my-3 border">
        <h4><?php echo $title; ?></h4>
        <?php if ($new!=1) {
            if ($_SESSION['fechacompra']) {
                echo '<a class="btn btn-link" href="search.php">Atrás</a>';
            } else {
                echo '<a class="btn btn-link" href="listado.php?id='.$id.'">Atrás</a>';
            }            
        } ?>
        <a class="btn btn-link" href="del_ficha.php?id=<?php echo $id; ?>">Borrar</a>
    </div>

    <div class="container-fluid">
    <?php if (!isMobile()) { ?>
        <!-- **** DESKTOP **** -->
        <div class="row">
            <div class="col-3">
                <!-- form para subir la portada: upload.php -->
                <form action="upload.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <?php echo "<img src='../portadas/".$id.".jpg?t=".time()." class='img-responsive' width=250 height=385 alt='portada'>"; ?>
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
                    <div class="input-group mb-2">
                        <div class="input-group-prepend"><span class="input-group-text">Colección</span></div>
                        <select name="col" class="custom-select">
                            <option> </option>
                            <?php while ($c = mysqli_fetch_array($dcol)) {
                                echo '<option '.($c["cod"]==$d["col"] ? "selected" : "").' value="'.$c["cod"].'">'.$c["descrip"].'</option>';
                            } ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <input type="text" class="form-control" name="newcol">
                        <div class="input-group-append">
                            <button type="submit" name="nuevacol" class="btn btn-success">Nueva col.</button>
                        </div>
                    </div>

                    <div class="input-group mt-3">
                        <div class="input-group-prepend"><span class="input-group-text">Vol</span></div>
                        <input type="text" class="form-control" name="vol" value="<?php echo $d["vol"] ?>">
                        <div class="input-group-prepend"><span class="input-group-text">Num</span></div>
                        <input type="text" class="form-control" name="num" value="<?php echo $d["num"] ?>">
                        <div class="input-group-prepend"><span class="input-group-text">Págs</span></div>
                        <input type="text" class="form-control" name="pags" value="<?php echo $d["pags"] ?>">
                        <div class="input-group-prepend"><span class="input-group-text">Precio</span></div>
                        <input type="text" class="form-control text-right" name="precio" value="<?php 
                            echo number_format($d["precio"],2,",",".") ?>">
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
                        <div class="input-group mt-2">
                            <input type="text" class="form-control" name="newed">
                            <div class="input-group-append">
                                <button type="submit" name="nuevaed" class="btn btn-success">Nueva edit.</button>
                            </div>
                        </div>
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
    <?php } else { ?>
        <!-- **** MOBILE **** -->
        <div class="row">
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

        <!-- form para modificar los datos de ficha: self -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="row">
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">Colección</span></div>
                    <select name="col" class="custom-select">
                        <option> </option>
                        <?php while ($c = mysqli_fetch_array($dcol)) {
                            echo '<option '.($c["cod"]==$d["col"] ? "selected" : "").' value="'.$c["cod"].'">'.$c["descrip"].'</option>';
                        } ?>
                    </select>
                </div>
                <div class="input-group mt-2">
                    <input type="text" class="form-control" name="newcol">
                    <div class="input-group-append">
                        <button type="submit" name="nuevacol" class="btn btn-success">Nueva col.</button>
                    </div>
                </div>

                <div class="input-group mt-2">
                    <div class="input-group-prepend"><span class="input-group-text">Vol</span></div>
                    <input type="text" class="form-control" name="vol" value="<?php echo $d["vol"] ?>">
                    <div class="input-group-prepend"><span class="input-group-text">Num</span></div>
                    <input type="text" class="form-control" name="num" value="<?php echo $d["num"] ?>">
                    <div class="input-group-prepend"><span class="input-group-text">Págs</span></div>
                    <input type="text" class="form-control" name="pags" value="<?php echo $d["pags"] ?>">
                </div>
                <div class="input-group mt-2">
                    <div class="input-group-prepend"><span class="input-group-text">Precio</span></div>
                    <input type="text" class="form-control text-right" name="precio" value="<?php 
                        echo number_format($d["precio"],2,",",".") ?>">
                    <div class="input-group-prepend"><span class="input-group-text">Género</span></div>
                    <select name="genero" class="custom-select">
                        <option> </option>
                        <?php while ($g = mysqli_fetch_array($dgen)) {
                            echo '<option '.($g["cod"]==$d["genero"] ? "selected" : "").' value="'.$g["cod"].'">'.$g["descrip"].'</option>';
                        } ?>
                    </select>
                </div>
            </div>
            <div class="form-group mt-2">
                <label for="titulo">Título:</label>
                <textarea class="form-control" rows="3" name="titulo"><?php echo $d["titulo"] ?></textarea>
            </div>

            <div class="row">
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">Editorial</span></div>
                    <select name="ed" class="custom-select">
                        <option> </option>
                        <?php while ($e = mysqli_fetch_array($ded)) {
                            echo '<option '.($e["cod"]==$d["ed"] ? "selected" : "").' value="'.$e["cod"].'">'.$e["descrip"].'</option>';
                        } ?>
                    </select>
                    <div class="input-group-prepend"><span class="input-group-text">Ed. Orig.</span></div>
                    <select name="edorig" class="custom-select">
                        <option> </option>
                        <?php mysqli_data_seek($ded, 0);
                            while ($e = mysqli_fetch_array($ded)) {
                            echo '<option '.($e["cod"]==$d["edorig"] ? "selected" : "").' value="'.$e["cod"].'">'.$e["descrip"].'</option>';
                        } ?>
                    </select>
                </div>
                <div class="input-group mt-2">
                    <input type="text" class="form-control" name="newed">
                    <div class="input-group-append">
                        <button type="submit" name="nuevaed" class="btn btn-success">Nueva edit.</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="input-group mt-2">
                    <div class="input-group-prepend"><span class="input-group-text">Fecha edición</span></div>
                    <select name="med" class="custom-select">
                        <option> </option>
                        <?php while ($m = mysqli_fetch_array($dmes)) {
                            echo '<option '.($m["cod"]==$d["med"] ? "selected" : "").' value="'.$m["cod"].'">'.$m["descrip"].'</option>';
                        } ?>
                    </select>
                    <input type="text" class="form-control text-right" name="aed" value="<?php echo $d["aed"] ?>">
                </div>
            </div>

            <div class="row">
                <div class="input-group mt-2">
                    <div class="input-group-prepend"><span class="input-group-text">Fecha compra</span></div>
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
            </div>

            <div class="row">
                <div class="input-group mt-2">
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
                </div>
            </div>

            <input type="text" class="form-control" hidden="true" name="id" value="<?php echo $id; ?>">
            <button class="btn btn-primary mt-2 mb-5" name="guardar" type="submit">Guardar</button>
        </form>

        <div class="row">
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

        <div class="row">
            <!-- form para añadir un contenido: add_content -->
            <form action="edit_content.php" class="form-inline" method="post">
                <label class="label h2 mr-4">Contenido</label>
                <input type="text" class="form-control" hidden="true" name="id" value="<?php echo $id; ?>">
                <button class="btn btn-success mt-2 mb-2" name="edit_content" type="submit">Editar</button>
            </form>
            <!-- tabla contenidos -->
            <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Colección</th>
                        <th>Núm.</th>
                        <th>Título</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($c = mysqli_fetch_array($contenidos)) { ?>
                    <tr>
                        <td><?php echo $c["coleccion"]; ?></td>
                        <td><?php echo ($c["vol"]>0 ? "(".$c["vol"].") " : "").$c["num"]; ?></td>
                        <td><?php echo $c["titulo"]; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>

<?php
        $dcol->free();
        $dgen->free();
        $dmes->free();
        $ded->free();
        $conn->close();
    } else {
        $conn->close();
        header("Location: logout.php");
    }
} else {
    header("Location: logout.php");
}
?>

<script>
// Add the following code if you want the name of the file appear on select
$(".custom-file-input").on("change", function() {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});
</script>

</body>
</html>
