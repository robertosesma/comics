<?php
session_start();
include 'func_aux.php';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true
    && isset($_SESSION['username']) &&
    isset($_POST['subir']) && isset($_POST['idportada'])) {
    $id = clear_input($_POST['idportada']);

    $ext = strtolower(pathinfo("tmp/".basename($_FILES["portada"]["name"]),PATHINFO_EXTENSION));
    // Check if image file is an actual image or fake image
    if(getimagesize($_FILES["portada"]["tmp_name"]) !== false) {
        // la imagen se copia en la carpeta portadas y su nombre es el id
        $upload_img = '../portadas/'.$id.".".$ext;
        // copiar la imagen temporal a la carpeta portadas definitiva
        echo $_FILES["portada"]["tmp_name"];
        echo $upload_img;
        if (move_uploaded_file($_FILES["portada"]["tmp_name"], $upload_img)) {
            // una vez copiada la imagen, volver a la ficha
            // Refresh:0 fuerza un refresco de la imagen de la portada
            header("Refresh:0; url=ficha.php?id=".$id);
        } else {
            echo "<h1>Se produjo un error subiendo la imagen</h1>";
        }
    } else {
        echo "<h1>ERROR: el archivo no es una imagen</h1>";
    }
} else {
    header("Location: logout.php");
}
?>
