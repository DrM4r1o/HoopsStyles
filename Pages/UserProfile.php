<?php

session_start();

include "../PHP/DataAccess.php";
include "../PHP/Header.php";


if(!isset($_SESSION["name"])) header("Location:./Login.php");

$userData = $bd->query("SELECT * FROM users WHERE email = '{$_SESSION["email"]}'")->fetch();

$userName = $userData["first_name"];
// $userEmail = $_SESSION["email"];
// $userPass = $_SESSION["password"];

if(isset($_POST["confirm"]))
{
    $queryUpdate = "UPDATE users SET name = '{$_POST["name"]}', email = '{$_POST["email"]}', password = '{$_POST["password"]}' WHERE email = '{$userEmail}'";
    $bd->query($queryUpdate);
    $_SESSION["name"] = $_POST["name"];
    $_SESSION["email"] = $_POST["email"];
    $_SESSION["password"] = $_POST["password"];
    header("Location:UserProfile.php");
}


?>

<!DOCTYPE html>
<html>
    <head>
        <title>Edit Profile</title>
        <meta charset="UTF-8">
        <meta name="description" content="Profile">
        <meta name="author" content="Mario Esparza">
        <link rel="shortcut icon" href="../Others/Icon.png" type="image/x-icon">
        <link href="../CSS/Header.css" rel="stylesheet" media="all" type="text/css"/>
        <link href="../CSS/Edit_Profile.css" rel="stylesheet" media="all" type="text/css"/>
    </head>
    <body>
        <?php
            echo headerNoSearch("..");
        ?>
        <h1>Welcome, <?php echo $userName ?> </h1>
        <main>
            <form action="<?php echo $_SERVER["PHP_SELF"];?>" method="POST">
                <div class="datos">
                    <div class="campoDatos">
                        <h2>Edit profile</h2>
                        <div class="data">
                            <div class="dataInput">
                                <input type="text" name="user-name" value="<?php echo $userData["first_name"] ?>" placeholder="Name">
                                <img src="../Others/IconsEdit/name.svg" alt="name">
                            </div>
                            <img class="alert" src="../Others/IconsEdit/alert-triangle.svg" alt="name">
                        </div>
                        <div class="data">
                            <div class="dataInput">
                                <input type="text" name="user-lastname" value="<?php echo $userData["last_name"] ?>"  placeholder="Last Name">
                                <img src="../Others/IconsEdit/last_name.svg" alt="lastname">
                            </div>
                            <img class="alert" src="../Others/IconsEdit/alert-triangle.svg" alt="name">
                        </div>
                        <div class="data">
                            <div class="dataInput">
                                <input type="text" name="user-email" value="<?php echo $userData["email"] ?>" placeholder="Email">
                                <img src="../Others/IconsEdit/inbox.svg" alt="email">
                            </div>
                            <img class="alert" src="../Others/IconsEdit/alert-triangle.svg" alt="name">
                        </div>
                        <div class="data">
                            <div class="dataInput">
                                <input class="password" type="password" name="user-pass" value="" placeholder="New Password">
                                <img class="passwordImg" src="../Others/IconsEdit/lock.svg" alt="password">
                            </div>
                            <img class="alert" src="../Others/IconsEdit/question-mark.svg" alt="name">
                        </div>
                        <div class="data">
                            <div class="dataInput">
                                <input type="text" name="user-phone" value="<?php echo $userData["phone_number"] ?>"  placeholder="Phone Number">
                                <img src="../Others/IconsEdit/phone.svg" alt="phone">
                            </div>
                            <img class="alert" src="../Others/IconsEdit/alert-triangle.svg" alt="name">
                        </div>
                        <div class="data">
                            <div class="dataInput">
                                <input type="text" name="user-direction" value="<?php echo $userData["address"] ?>"  placeholder="Address">
                                <img src="../Others/IconsEdit/home.svg" alt="address">
                            </div>
                            <img class="alert" src="../Others/IconsEdit/alert-triangle.svg" alt="name">
                        </div>
                        <input class="confirm" type="text" value="Confirm" name="edit"/>
                    </div>
                </div>
                <img class="return" src="../Others/return.svg" alt="">
            </form>
        </main>
    </body>
    <script>
        const returnButton = document.querySelector(".return");
        const passwordImg = document.querySelector(".passwordImg");
        const inputs = document.querySelectorAll(".dataInput input");

  