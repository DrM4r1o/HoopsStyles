<?php

session_start();

include "../PHP/DataAccess.php";
include "../PHP/Header.php";


if(!isset($_SESSION["email"])) header("Location:./Login.php");

$userEmail = $_SESSION["email"];
$userName = isset($_SESSION["name"]) ? $_SESSION["name"] : "";

if(isset($_POST["edit"]))
{
    echo "entro";
    $actualPassword = $bd->query("SELECT password FROM users WHERE email = '{$userEmail}'")->fetch()["password"];
    if($_POST["user-pass"] === "") $_POST["user-pass"] = $actualPassword;

    $complete = "1";
    if($_POST["name"] === "" || $_POST["lastname"] === "" || $_POST["email"] === "" || $_POST["user-pass"] === "" || $_POST["phone"] === "" || $_POST["address"] === "")
    {
        $complete = "0";
    }

    $queryUpdate = "UPDATE users SET first_name = '{$_POST["name"]}', email = '{$_POST["email"]}', last_name = '{$_POST["lastname"]}', phone_number = '{$_POST["phone"]}', address = '{$_POST["address"]}', password = '{$_POST["user-pass"]}', complete = {$complete} WHERE email = '{$userEmail}'";
    $bd->query($queryUpdate);
    $_SESSION["email"] = $_POST["email"];
    $_SESSION["name"] = $_POST["name"];
    header("Location:UserProfile.php");
}

$userData = $bd->query("SELECT * FROM users WHERE email = '{$_SESSION["email"]}'")->fetch();

$userName = $userData["first_name"];



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
                                <input type="text" name="name" value="<?php echo $userData["first_name"] ?>" placeholder="Name">
                                <img src="../Others/IconsEdit/name.svg" alt="name">
                            </div>
                            <img class="alert" src="../Others/IconsEdit/alert-triangle.svg" alt="name">
                        </div>
                        <div class="data">
                            <div class="dataInput">
                                <input type="text" name="lastname" value="<?php echo $userData["last_name"] ?>"  placeholder="Last Name">
                                <img src="../Others/IconsEdit/last_name.svg" alt="lastname">
                            </div>
                            <img class="alert" src="../Others/IconsEdit/alert-triangle.svg" alt="name">
                        </div>
                        <div class="data">
                            <div class="dataInput">
                                <input type="text" name="email" value="<?php echo $userData["email"] ?>" placeholder="Email">
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
                                <input type="text" name="phone" value="<?php echo $userData["phone_number"] ?>"  placeholder="Phone Number">
                                <img src="../Others/IconsEdit/phone.svg" alt="phone">
                            </div>
                            <img class="alert" src="../Others/IconsEdit/alert-triangle.svg" alt="name">
                        </div>
                        <div class="data">
                            <div class="dataInput">
                                <input type="text" name="address" value="<?php echo $userData["address"] ?>"  placeholder="Address">
                                <img src="../Others/IconsEdit/home.svg" alt="address">
                            </div>
                            <img class="alert" src="../Others/IconsEdit/alert-triangle.svg" alt="name">
                        </div>
                        <button class="confirm" type="submit">Confirm</button>
                        <input type="hidden" name="edit" value="Confirm" name="edit">
                    </div>
                </div>
                <a href="./UserProfile.php"><img class="return" src="../Others/return.svg" alt=""></a>
            </form>
            <span class="errorRegister"></span>
        </main>
    </body>
    <script>
        const returnButton = document.querySelector(".return");
        const passwordImg = document.querySelector(".passwordImg");
        const inputs = document.querySelectorAll(".dataInput input");
        const form = document.querySelector("form");

        for(let i = 0; i < inputs.length; i++)
        {
            if(inputs[i].value !== "" && inputs[i].type !== "password")
            {
                inputs[i].parentElement.parentElement.querySelector(".alert").src = "../Others/IconsEdit/check.svg";
            }
        }

        passwordImg.addEventListener("click", () => {
            const passwordInput = document.querySelector(".password");
            if(passwordInput.type === "password")
            {
                passwordInput.type = "text";
                passwordImg.src = "../Others/IconsEdit/lock-open.svg";
            }
            else
            {
                passwordInput.type = "password";
                passwordImg.src = "../Others/IconsEdit/lock.svg";
            }
        });

        form.addEventListener("submit", function(e){
            e.preventDefault();
            const spanError = document.querySelector(".errorRegister");
            const inputEmail = form[2];

            if(correctEmail(inputEmail))
            {
                e.target.submit();
            }
            else
            {
                spanError.classList.add("show");
                spanError.textContent = "Some fields are incorrect";
            }
        });

        function correctEmail(emailInput) 
        {
            const email = emailInput.value;
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            const correct = emailRegex.test(email);
            if(!correct) emailInput.parentElement.classList.add("error");
            else emailInput.classList.remove("error");
            return correct;
        }
    </script>
</html>
