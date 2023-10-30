<?php
    include "../PHP/DataAccess.php";

    session_start();

    if(isset($_SESSION["email"])) header ("Location:../Pages/UserProfile.php");

    $error = false;
    
    if ($_SERVER["REQUEST_METHOD"] === "POST")
    {
        if(isset($_POST["submit-login"]))
        {   
            $queryData;
            $inputEmail = $_POST["email"];
            $inputPass = $_POST["password"];
            
            $query = "SELECT email,password,first_name,role FROM users WHERE email = '{$inputEmail}' AND password ='{$inputPass}'";
            $queryData = $bd->query($query)->fetch();
            if(empty($queryData)) $error = true;
            
            if(!$error)
            {
                $_SESSION["email"] = $queryData["email"];
                $_SESSION["password"] = $queryData["password"];
                $_SESSION["name"] = $queryData["first_name"];
                $_SESSION["rol"] = $queryData["role"];
                header("Location:../index.php");
            }
        }
        if(isset($_POST["submit-register"]))
        {   
            $queryData;
            $sent = true;
            $correctQuery = true;

            $inputEmail = $_POST["email"];
            $inputPass = $_POST["password"];
            $inputConfirmPass = $_POST["password-confirm"];
            
            $bd = new PDO($conexionBD, $user, $password);
            $query = "INSERT INTO users (id, dni, email, password, first_name, last_name, role, phone_number, address) VALUES ((SELECT create_id_user()),'".$dni."','".$userEmail."','".$userPass."','','','User','','')";
            
            try {
                $correcto = $bd->query($query);
            } catch (Throwable $th) {
                throw $th;
            }
            header("Location:/");
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
                <h1>Bienvenido!</h1>
                <div id="containerForms">
                    <form action="<?php echo $_SERVER["PHP_SELF"];?>" method="POST">
                        <div class="login forms">
                            <div class="loginData">
                                <h2>Iniciar Sesión</h2>
                                <input class="login_user" type="text" label="Usuario" name="email" size="28.5" placeholder="Correo Electronico" required/>
                                <br><input class="login_user" type="password" name="password" size="28.5" placeholder="Contraseña" required>
                                <br><div class="keepSession">
                                    <br><input type="checkbox" id="keep" name="logged" checked>
                                    <br><label class="logged" for="keep">Mantener la sesión</label><br>
                                </div>
                                <br><input type="submit" name="submit-login" class="login_button" value="Continuar"/>
                            </div>
                            <hr>
                            <div class="changeRegister">
                                <label class="lablel">¿No tienes cuenta aún?</label>
                                <br><br><button id="goRegister">Registrate ahora!</label>
                            </div>
                        </div>
                    </form>
                    <form class="defaultHidden" action="<?php echo $_SERVER["PHP_SELF"];?>" method="POST">
                        <div class="register forms">
                            <h2>Registro</h2>
                            <input class="register_user" type="text" label="Usuario" name="email" size="28.5" placeholder="Nombre de usuario"/>
                            <br><input class="register_user" type="password" name="password" size="28.5" placeholder="Contraseña">
                            <br><input class="register_user" type="password" name="password-confirm" size="28.5" placeholder="Confirmar contraseña"/>
                            <br><input id="sumbitRegister" type="submit" name="submit-register" value="Confirmar"/>
                            <hr>
                            <div class="changeLogin">
                                <label class="lablel">¿Ya tienes cuenta? Inicia Sesión</label>
                                <br><button id="returnLogin">Volver</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        <script>
            document.addEventListener("animationend", animateAppear)

            document.getElementById("goRegister").addEventListener("click", function(e){
                e.preventDefault();
                removeAnimations();
                document.getElementsByTagName("form")[1].classList.add("hide");
                document.getElementsByTagName("form")[0].classList.add("animateHidden");
                document.getElementsByTagName("form")[1].classList.add("showNow");
                document.getElementById("containerForms").classList.add("rotateCardForward");
            });

            document.getElementById("returnLogin").addEventListener("click", function(e){
                e.preventDefault();
                removeAnimations();
                document.getElementsByTagName("form")[0].classList.add("hide");
                document.getElementsByTagName("form")[1].classList.add("animateHidden");
                document.getElementsByTagName("form")[0].classList.add("showNow");
                document.getElementById("containerForms").classList.add("rotateCardReverse");

            });

            function removeAnimations(){
                document.getElementsByTagName("form")[0].classList = "";
                document.getElementsByTagName("form")[1].classList = "";
                document.getElementById("containerForms").classList = "";
            }

            function animateAppear(e) {
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
        </script>
    </body>
</html>