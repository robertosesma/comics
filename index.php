<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
<?php
include 'func_aux.php';

$Err = "";
$user = $pswd = "";
$conn = connect();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = clear_input($_POST["user"]);
    $pswd = clear_input($_POST["pswd"]);

    $stmt = $conn -> prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $users = $stmt->get_result();
    $nrows = $users->num_rows;
    if ($nrows > 0) {
        $r = $users->fetch_assoc();
        if (password_verify($pswd, $r["password"])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user;
            header("Location: listado.php?n=1");
        } else {
            $Err = "La contraseña es incorrecta";
        }
    } else {
        $Err = "El usuario no existe";
    }
    $users->free();
}
$conn->close();
?>

<div class="container">
    <div class="jumbotron bg-dark text-white">
        <h1>comics DB</h1>
        <p>Indique nombre de usuario y contraseña.</p>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
            <label for="user">Usuario:</label>
            <input type="text" class="form-control" name="user" required>
        </div>
        <div class="form-group">
            <label for="pswd">Contraseña:</label>
            <input type="password" class="form-control" name="pswd" required>
            <span class="error text-danger"><?php echo $Err;?></span>
        </div>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
</div>

</body>
</html>
