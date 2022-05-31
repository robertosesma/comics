<?php
session_start();
include 'func_aux.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username']) && isset($_GET['id'])) {
    $conn = connect();
    $id = clear_input($_GET["id"]);

    // eliminar el artista
    $stmt = $conn -> prepare("DELETE FROM contenidos WHERE id=? AND idc=?");
    $stmt->bind_param('ii',$id,clear_input($_GET["idc"]));
    $stmt->execute();

    $conn->close();
    header("Refresh:0; url=edit_content.php?id=".$id);
}
exit();
?>
