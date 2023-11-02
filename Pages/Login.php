<?php

    include "../PHP/DataAccess.php";

    session_start();

    if(isset($_SESSION["email"])) header ("Location:./UserProfile.php");

    $error = false;
    
    if ($_SERVER["REQUEST_METHOD"] === "POST")
    {
        
        if(isset($_POST["submit-login"]))
        {   
            $queryData;
            $inputEmail = $_POST["email"];
            $inputPass = $_POST["password"];
            
            $query = "SELECT email,first_name,role FROM users WHERE email = '{$inputEmail}' AND password ='{$inputPass}'";
            $queryData = $bd->query($query)->fetch();
            if(empty($queryData)) $error = true;
            
            if(!$error)
            {
                $_SESSION["email"] = $queryData["email"];
                $_SESSION["name"] = $queryData["first_name"];
                $_SESSION["rol"] = $queryData["role"];
                header("Location:../index.php");
            }
        }
        if(isset($_POST["submit-register"]))
        {   
            $error = true;
            $dni = $_POST["dni"];
            $inputEmail = $_POST["email"];
            $inputPass = $_POST["password"];

            $query = "INSERT INTO users (id, dni, email, password, first_name, last_name, role, phone_number, address) VALUES ((SELECT create_id('{$inputEmail}')),'".$dni."','".$inputEmail."','".$inputPass."','','','User','','')";
            try {
                $bd->query($query);
                $error = false;
            } catch (Exception $e) {
                $error = true;
            }

            if(!$error)
            {
                $_SESSION["email"] = $inputEmail;
                $_SESSION["name"] = $queryData["first_name"];
                $_SESSION["rol"] = "User";
    
                header("Location:../index.php");
            }
            $errorMessage = "The email or DNI is already in use";
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Login</title>
        <meta charset="UTF-8">
        <meta name="description" content="campoDatos">
        <meta name="author" content="Mario Esparza">
        <link rel="shortcut icon" href="../Others/Icon.png" type="image/x-icon">
        <link href="../CSS/Login.css" rel="stylesheet" media="all" type="text/css"/>
        <link href="../CSS/Header.css" rel="stylesheet" media="all" type="text/css"/>
    </head>
    <body>
        <header>         
            <a class='nombreLogo' href='../index.php' >HoopsStyle</a>
        </header>
        <main>
            <div class="principal">
                <h1>Welcome!</h1>
                <div id="containerForms">
                    <form class="<?php if(isset($errorMessage)) echo "hide" ?>" action="<?php echo $_SERVER["PHP_SELF"];?>" method="POST">
                        <div class="login forms">
                            <div class="loginData">
                                <h2>Login</h2>
                                <input class="login_user" type="text" label="Usuario" name="email" size="28.5" placeholder="Email" required/>
                                <br><input class="login_user" type="password" name="password" size="28.5" placeholder="Password" required>
                                <br><div class="keepSession">
                                    <br><input type="checkbox" id="keep" name="logged" checked>
                                    <br><label class="logged" for="keep">Keep session</label><br>
                                </div>
                                <br><input type="submit" name="submit-login" class="login_button" value="Continue"/>
                            </div>
                            <hr>
                            <div class="changeRegister">
                                <label class="lablel">Don't have an account yet?</label>
                                <br><br><button id="goRegister">Register now!</label>
                            </div>
                        </div>
                    </form>
                    <form class="defaultHidden <?php if(isset($errorMessage)) echo "show" ?>" action="<?php echo $_SERVER["PHP_SELF"];?>" method="POST">
                        <div class="register forms">
                            <h2>Register</h2>
                            <input class="register_user" type="text" label="Usuario" name="email" size="28.5" placeholder="Email"/>
                            <br><input class="register_user" type="text" label="DNI" name="dni" size="28.5" placeholder="DNI"/>
                            <br><input class="register_user passRegister" type="password" name="password" size="28.5" placeholder="Passworrd">
                            <br><input class="register_user passRegister" type="password" name="password-confirm" size="28.5" placeholder="Confirm password"/>
                            <span class="<?php if(isset($errorMessage)) echo "show"; else echo "hide" ?> errorRegister">
                                <?php
                                    if(isset($errorMessage)) echo $errorMessage;
                                ?>
                            </span>
                            <br><input id="sumbitRegister" type="submit" name="submit-register" value="Confirm"/>
                            <input id="sumbitRegister" type="hidden" name="submit-register" value="Confirm"/>
                            <hr>
                            <div class="changeLogin">
                                <label class="lablel">Do you already have an account? Login</label>
                                <br><button id="returnLogin">Return</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        <script>
            const registerForm = document.querySelectorAll("form")[1];
            

            registerForm.addEventListener("submit", function(e){
                e.preventDefault();
                const spanError = document.querySelector(".errorRegister");
                const inputsPassword = document.querySelectorAll(".passRegister");
                const inputEmail = e.target[0];
                const inputDNI = e.target[1];

                if(correctDNI(inputDNI) && correctEmail(inputEmail) && correctPasswords(inputsPassword[0], inputsPassword[1]))
                {
                    e.target.submit();
                }
                else
                {
                    spanError.classList.add("show");
                    spanError.textContent = "Some fields are incorrect";
                }
            });

            document.addEventListener("animationend", animateAppear)

            document.getElementById("goRegister").addEventListener("click", function(e) {
                e.preventDefault();
                removeAnimations();
                document.getElementsByTagName("form")[1].classList.add("hide");
                document.getElementsByTagName("form")[0].classList.add("animateHidden");
                document.getElementsByTagName("form")[1].classList.add("showNow");
                document.getElementById("containerForms").classList.add("rotateCardForward");
            });

            document.getElementById("returnLogin").addEventListener("click", function(e) { 
                e.preventDefault();
                removeAnimations();
                document.getElementsByTagName("form")[0].classList.add("hide");
                document.getElementsByTagName("form")[1].classList.add("animateHidden");
                document.getElementsByTagName("form")[0].classList.add("showNow");
                document.getElementById("containerForms").classList.add("rotateCardReverse");

            });

            function removeAnimations()
            {
                document.getElementsByTagName("form")[0].classList = "";
                document.getElementsByTagName("form")[1].classList = "";
                document.getElementById("containerForms").classList = "";
            }

            function animateAppear(e) 
            {
                if(e.animationName == "fade-out")
                {
                    let originForm = [...e.target.parentElement.children].indexOf(e.target);
                    document.getElementsByTagName("form")[originForm].classList.add("hide");
                }
                if(e.animationName == "fade-in")
                {
                    let originForm = [...e.target.parentElement.children].indexOf(e.target);
                    
                }
                if(e.animationName == "rotateForward" || e.animationName == "rotateReverse")
                {
                    let targetForm = document.getElementsByClassName("showNow")[0];
                    targetForm.classList.add("animateShow");
                    targetForm.classList.add("show");
                    targetForm.classList.remove("showNow");
                }
            }

            function correctEmail(emailInput) 
            {
                const email = emailInput.value;
                const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                const correct = emailRegex.test(email);
                if(!correct) emailInput.classList.add("error");
                else emailInput.classList.remove("error");
                return correct;
            }

            function correctPasswords(passwordInput, confirmPasswordInput)
            {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                let correct = false;

                if(password.value != confirmPassword.value || password.value == "" || confirmPassword.value == "")
                {
                    passwordInput.classList.add("error");
                    confirmPasswordInput.classList.add("error");
                    correct = false;
                }
                else
                {
                    passwordInput.classList.remove("error");
                    confirmPasswordInput.classList.remove("error");
                    correct = true;
                }

                return correct;
            }

            function correctDNI(dniInput)
            {
                const dni = dniInput.value;
                const dniRegex = /^[0-9]{8}[TRWAGMYFPDXBNJZSQVHLCKE]$/i;
                let correct = dniRegex.test(dni);

                if(correct)
                {
                    const letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
                    const numero = dni.slice(0, 8);
                    const letraCalculada = letras[numero % 23].toUpperCase();
                    const letraDNI = dni.charAt(8).toUpperCase();
    
                    if(letraCalculada != letraDNI)
                    {
                        dniInput.classList.add("error");
                        correct = false;
                    }
                    else
                    {
                        dniInput.classList.remove("error");
                    }
                }

                return correct;
            }
        </script>
    </body>
</html>