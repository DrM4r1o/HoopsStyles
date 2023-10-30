<?php

include "./DataAccess.php";

session_start();

if(isset($_SESSION["products-created"])) header ("Location:../index.php");

$dir = "../Products";
$files = array_slice(scandir($dir), 2);

for ($i = 0; $i < count($files); $i++) {
    $query = "CALL add_categorie('".$files[$i]."')";
    $bd->query($query);

    foreach (array_slice(scandir($dir."/".$files[$i]), 2) as $product) {
        $query = "CALL add_product('".$product."', ". rand(50,220).".".rand(0,99) .", './Products/".$files[$i]."/".$product."','".$files[$i]."')";
        echo $query . "<br>";
        $bd->query($query);
    }
}

$_SESSION["products-created"] = true;
header ("Location:../index.php");