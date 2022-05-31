<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar artistas</title>
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
        // añadir artista  + rol
        $ida = clear_input($_POST['artista_add']);
        $rol = clear_input($_POST['rol_add']);
        $stmt = $conn -> prepare('INSERT INTO autores (id, ida, rol) VALUES (?, ?, ?)');
        $stmt->bind_param('iii', $id, $ida, $rol);
        $stmt->execute();
    }

    // diccionario artistas
    $stmt = $conn -> prepare('SELECT * FROM dautores ORDER BY nombre');
    $stmt->execute();
    $dartista = $stmt->get_result();
    // diccionario roles
    $stmt = $conn -> prepare('SELECT * FROM drol');
    $stmt->execute();
    $drol = $stmt->get_result();
    // datos artistas y roles para el registro
    $stmt = $conn -> prepare('SELECT autores.id, autores.ida, drol.cod AS crol, drol.descrip AS rol, dautores.nombre
        FROM ((autores
        LEFT JOIN dautores ON(autores.ida = dautores.ida))
        LEFT JOIN drol ON(autores.rol = drol.cod))
        WHERE autores.id = ?
        ORDER BY autores.rol');
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
                    <h4>Editar artistas</h4>
                    <h5><?php echo $title; ?></h5>
                    <a class="btn btn-link" href="add_artista.php?id=<?php echo $id; ?>">Nuevo artista</a>
                    <a class="btn btn-link" href="ficha.php?id=<?php echo $id; ?>">Atrás</a>
                    <a class="btn btn-link" href="logout.php">Salir</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-8">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">Artista</span></div>
                        <select name="artista_add" class="custom-select">
                            <option> </option>
                            <?php while ($a = mysqli_fetch_array($dartista)) {
                                echo '<option value="'.$a["ida"].'">'.$a["nombre"].'</option>';
                            } ?>
                        </select>
                        <div class="input-group-prepend"><span class="input-group-text">Rol</span></div>
                        <select name="rol_add" class="custom-select">
                            <option> </option>
                            <?php while ($r = mysqli_fetch_array($drol)) {
                                echo '<option value="'.$r["cod"].'">'.$r["descrip"].'</option>';
                            } ?>
                        </select>
                    </div>
                    <input type="text" class="form-control" hidden="true" name="id" value="<?php echo $id; ?>">
                    <button class="btn btn-primary mt-2 mb-5" name="add" type="submit">Añadir</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-8">
                <!-- tabla artistas y rol -->
                <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered">
                    <tbody>
                    <?php while ($r = mysqli_fetch_array($d)) { ?>
                        <tr>
                            <td><?php echo $r["nombre"]; ?></td>
                            <td><?php echo $r["rol"]; ?></td>
                            <?php
                            $del = 'del_artista.php?id='.$id."&ida=".$r["ida"]."&rol=".$r["crol"];?>
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
    $dartista->free();
    $drol->free();
    $d->free();
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
