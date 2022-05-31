<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Nueva editorial</title>
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
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST" ) {
        $id = clear_input($_POST["id"]);
        // añadir una NUEVA editorial
        $stmt = $conn -> prepare('INSERT INTO ded (cod, descrip) VALUES (?, ?)');
        $stmt->bind_param('is', getnexted($conn), clear_input($_POST['ed']));
        $stmt->execute();
        header("Refresh:0; url=ficha.php?id=".$id);
    }
}
?>

<?php if ($ok) { ?>
<div class="container">
    <div class="container p-3 my-3 border">
        <h4>Nueva editorial</h4>
        <a class="btn btn-link" href="ficha.php?id=<?php echo $id; ?>">Atrás</a>
        <a class="btn btn-link" href="logout.php">Salir</a>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
          <label for="artista">Editorial:</label>
          <input type="text" class="form-control" name="ed">
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
