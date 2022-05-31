<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Nuevo artista</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
<?php
session_start();
include 'func_aux.php';
$ok = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username'])) {
    $ok = true;
    $conn = connect();
    if ($_SERVER["REQUEST_METHOD"] == "GET" ) $id = clear_input($_GET["id"]);

    if ($_SERVER["REQUEST_METHOD"] == "POST" ) {
        $id = clear_input($_POST["id"]);
        // añadir un NUEVO artista (+rol)
        $nombre = clear_input($_POST['artista']);
        $ida = getnextida($conn);
        $stmt = $conn -> prepare('INSERT INTO dautores (ida, nombre) VALUES (?, ?)');
        $stmt->bind_param('is', $ida, $nombre);
        $stmt->execute();
        header("Refresh:0; url=edit_artista.php?id=".$id);
    }
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h4>Nuevo artista</h4>
        <a class="btn btn-link" href="edit_artista.php?id=<?php echo $id; ?>">Atrás</a>
        <a class="btn btn-link" href="logout.php">Salir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
          <label for="artista">Artista:</label>
          <input type="text" class="form-control" name="artista">
        </div>
        <input type="text" class="form-control" hidden="true" name="id" value="<?php echo $id; ?>">
        <button class="btn btn-primary mt-2 mb-5" name="add" type="submit">Añadir</button>
    </form>
</div>

<?php
    $conn->close();
} else {
    header("Location: logout.php");
}?>

</body>
</html>
