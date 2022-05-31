<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Nueva colección</title>
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
    if ($_SERVER["REQUEST_METHOD"] == "GET" ) {
        $id = clear_input($_GET["id"]);
        $orig = clear_input($_GET["orig"]);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" ) {
        $id = clear_input($_POST["id"]);
        $orig = clear_input($_POST["orig"]);
        // añadir una NUEVA colección
        $stmt = $conn -> prepare('INSERT INTO dcol (cod, descrip) VALUES (?, ?)');
        $stmt->bind_param('is', getnextcol($conn), clear_input($_POST['col']));
        $stmt->execute();
        if ($orig==0) header("Refresh:0; url=ficha.php?id=".$id);
        if ($orig==1) header("Refresh:0; url=edit_content.php?id=".$id);
    }
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h4>Nueva colección</h4>
        <?php
        if ($orig==0) $back = "ficha.php?id=".$id;
        if ($orig==1) $back = "edit_content.php?id=".$id;
        ?>
        <a class="btn btn-link" href="<?php echo $back; ?>">Atrás</a>
        <a class="btn btn-link" href="logout.php">Salir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
          <label for="artista">Colección:</label>
          <input type="text" class="form-control" name="col">
        </div>
        <input type="text" class="form-control" hidden="true" name="id" value="<?php echo $id; ?>">
        <input type="text" class="form-control" hidden="true" name="orig" value="<?php echo $orig; ?>">
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
