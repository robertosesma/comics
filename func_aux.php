<?php
function connect(){
    require '../dbconfig.php';

    $con = mysqli_connect($dbconfig['server'],$dbconfig['username'],$dbconfig['password'],$dbconfig['db']);
    if(!$con){
        die("Fallo al conectar con la base de datos");
    }
    $con->query("SET NAMES 'utf8'");
    $con->query("SET CHARACTER SET utf8");
    $con->query("SET SESSION collation_connection = 'utf8_unicode_ci'");

    return $con;
}

function clear_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function getnextid($conn){
    $stmt = $conn -> prepare("SELECT MAX(id) AS max FROM ficha");
    $stmt->execute();
    $max = $stmt->get_result();
    $r = $max->fetch_assoc();
    $next = $r["max"]+1;
    return $next;
}

function getnextida($conn){
    $stmt = $conn -> prepare("SELECT MAX(ida) AS max FROM dautores");
    $stmt->execute();
    $max = $stmt->get_result();
    $r = $max->fetch_assoc();
    $next = $r["max"]+1;
    return $next;
}

function getnextidc($conn){
    $stmt = $conn -> prepare("SELECT MAX(idc) AS max FROM contenidos");
    $stmt->execute();
    $max = $stmt->get_result();
    $r = $max->fetch_assoc();
    $next = $r["max"]+1;
    return $next;
}

function getnextcol($conn){
    $stmt = $conn -> prepare("SELECT MAX(cod) AS max FROM dcol");
    $stmt->execute();
    $max = $stmt->get_result();
    $r = $max->fetch_assoc();
    $next = $r["max"]+1;
    return $next;
}

function getnexted($conn){
    $stmt = $conn -> prepare("SELECT MAX(cod) AS max FROM ded");
    $stmt->execute();
    $max = $stmt->get_result();
    $r = $max->fetch_assoc();
    $next = $r["max"]+1;
    return $next;
}

?>
