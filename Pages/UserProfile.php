<?php

session_start();

include "../PHP/DataAccess.php";
include "../PHP/Header.php";
include "../PHP/Querys.php";


if(!isset($_SESSION["email"])) header("Location:./Login.php");

$userName = isset($_SESSION["name"]) ? $_SESSION["name"] : "";
$userEmail = $_SESSION["email"];
$userPass = $_SESSION["password"];

if(isset($_POST["logout"]))
{
    $_SESSION = array();
    session_destroy();
    header("Location:../index.php");
}

$queryLastOrders = "SELECT id FROM orders WHERE user_id = (SELECT id FROM users WHERE email = '{$userEmail}') AND active = 0 ORDER BY id DESC LIMIT 3";
$resultLastOrders = $bd->query($queryLastOrders)->fetchAll();


?>

<!DOCTYPE html>
<html>
    <head>
        <title>Profile</title>
        <meta charset="UTF-8">
        <meta name="description" content="Profile">
        <meta name="author" content="Mario Esparza">
        <meta name="view-transition" content="same-origin">
        <link rel="shortcut icon" href="../Others/Icon.png" type="image/x-icon">
        <link href="../CSS/Login_Success.css" rel="stylesheet" media="all" type="text/css"/>
        <link href="../CSS/Header.css" rel="stylesheet" media="all" type="text/css"/>
    </head>
    <body>
        <?php
            echo headerNoSearch("..");
        ?>
        <h1>
            <?php
                if($userName == "") echo "Welcome";
                else echo "Welcome, {$userName}";
            ?> 
        </h1>
        <main>
            <div class="principal">
                <form action="<?php echo $_SERVER["PHP_SELF"];?>" method="POST">
                    <div class="datosLogin">
                        <div class="campoDatos">
                            <h2>Profile info</h2>
                            <p>Name: <?php echo $userName ?> </p>
                            <p>Email: <?php echo $userEmail ?> </p>
                            <p>Password:
                                <span id="password" style="display: none;"> <?php echo $userPass ?> </span>
                                <span id="passwordText">
                                    <?php
                                        for($i = 0; $i < strlen($userPass); $i++)
                                        {
                                            echo "*";
                                        }
                                        
                                    ?>
                                </span>
                            </p>
                            <?php
                                if(userIsComplete($userEmail, $bd) == 0)
                                {
                                    echo "<a href='EditProfile.php' class='login_button redirect' style='background-color: rgb(218, 195, 96);'>Complete your profile</a>";
                                } else
                                {
                                    echo "<a href='EditProfile.php' class='login_button redirect' style='background-color: black;'>Edit Profile</a>";
                                }
                                if($_SESSION["rol"] == "Admin")
                                {
                                    echo "<a href='AdminPage.php' class='login_button redirect' style='background-color: black;'>Admin page</a>";
                                }
                            ?>
                            <input type="submit" class="login_button" value="Logout" name="logout" style="background-color: red !important;" />
                        </div>
                    </div>
                </form>
            </div>
            <div class="principal">
                <form action="<?php echo $_SERVER["PHP_SELF"];?>" method="POST">
                    <div class="datosLogin">
                        <div class="campoDatos">
                            <h2>Last Orders</h2>
                            <?php
                                if(count($resultLastOrders) > 0)
                                {
                                    echo 
                                    "
                                    <table>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Order date</th>
                                            <th>Order total</th>
                                        </tr>
                                    ";
                                    foreach($resultLastOrders as $order)
                                    {
                                        $queryOrder = "SELECT date,total_price FROM orders WHERE id = '{$order["id"]}'";
                                        $resultOrder = $bd->query($queryOrder)->fetch();
                                        echo "<tr>";
                                        echo "    <td><p>{$order["id"]}</p></td>";
                                        echo "    <td><p>{$resultOrder["date"]}</p></td>";
                                        echo "    <td><p>{$resultOrder["total_price"]}€</p></td>";
                                        echo "</tr>";
                                    }
                                    echo "</table>";
                                }
                                else
                                {
                                    echo "<p>No orders found</p>";
                                }
                            ?>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </body>
    <script>
        const edit = document.getElementById("edit");
        const admin = document.getElementById("admin");
        const buttons = [edit, admin];
        buttons.forEach(button => {
            button.addEventListener("click", () => {
                if(button.value == "Complete your profile" || button.value == "Edit Profile")
                {
                    window.navigation.navigate("EditProfile.php");
                } else if(button.value == "Admin page")
                {
                    window.navigation.navigate("AdminPage.php");
                }
            });
        });
    </script>
</html>