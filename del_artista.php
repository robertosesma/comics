<?php
session_start();
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username']) && isset($_GET['id'])) {
    $conn = connect();
    $id = clear_input($_GET["id"]);

    // eliminar el artista
    $stmt = $conn -> prepare("DELETE FROM autores WHERE id=? AND ida=? AND rol=?");
    $stmt->bind_param('iii',$id,clear_input($_GET["ida"]),clear_input($_GET["rol"]));
    $stmt->execute();

    $conn->close();
    header("Refresh:0; url=edit_artista.php?id=".$id);
}
exit();
?>
