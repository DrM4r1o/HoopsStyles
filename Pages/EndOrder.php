<?php

session_start();

if(!isset($_SESSION["email"])) header ("Location:../Pages/Login.php");
if(!isset($_SESSION["end-order"])) header ("Location:../Pages/Cart.php");

$email = $_SESSION["email"];

include "../PHP/DataAccess.php";
include "../PHP/Header.php";

if(isset($_GET["confirm"])) {
    $bd->query("UPDATE orders SET active = 0 WHERE user_id = (SELECT id FROM users WHERE email = '{$email}') AND active = 1");
    
    $to      = $email;
    $subject = 'Order confirmation';
    $message = 'Your order has been confirmed correctly and will be processed!';
    mail($to, $subject, $message, "From: ribino6634@zamaneta.com");

    header ("Location:../index.php");
}

$productsInCart = $bd->query(
    "SELECT idProduct, quantity
    FROM order_lines
    WHERE idOrder = (SELECT id FROM orders WHERE user_id = (SELECT id FROM users WHERE email = '{$email}') AND active = 1)"
)->fetchAll();


?>

<!DOCTYPE html>
<html>
    <head>
        <title>Cart</title>
        <meta charset="UTF-8">
        <meta name="description" content="">
        <meta name="author" content="Mario Esparza">
        <link rel="shortcut icon" href="../Others/Icon.png" type="image/x-icon">
        <link href="../css/Header.css" rel="stylesheet" media="all" type="text/css"/>
        <link href="../css/End_Order.css" rel="stylesheet" media="all" type="text/css"/>
    </head>
    <body>
        <?php
            echo headerNoCart("..");
        ?>
        <main>
            <form action="<?php echo $_SERVER["PHP_SELF"];?>" method="GET" class='products'>
                <h1>Order Summary</h1>
                <?php
                if(count($productsInCart) == 0) echo "<h2 style='position: absolute; top: 10%; left: 50%; '>No products in cart</h2>";
                else {
                    echo "<div class='summary'>";
                    foreach ($productsInCart as $productInCart) {
                        $product = $bd->query("SELECT id,name,unit_price,image FROM products WHERE id = '{$productInCart["idProduct"]}'")->fetch();
                        echo "<p class='name'>".implode(" ",explode("-",substr($product["name"], 0 ,strpos($product["name"], "."))))."<strong> x {$productInCart["quantity"]}</strong></p>";
                    }
                    $orderPrice = $bd->query("SELECT total_price FROM orders WHERE user_id = (SELECT id FROM users WHERE email = '{$email}') AND active = 1")->fetch()["total_price"];
                    echo "</div>";
                    echo "<h3 class='price'>Total: {$orderPrice}€</h3>";
                }
                ?>
                <input type="hidden" name="confirm">
            </form>
            <div class="buttons">
                <button class='cancel' type='submit' name='cancel' value=''>Cancel</button>
                <button class='confirm' type='submit'value=''>Confirm</button>
            </div>
        </main>
    </body>
    <script>
        document.querySelector(".cancel").addEventListener("click", function(){
            window.location.href = "../Pages/Cart.php";
        });
        document.querySelector(".confirm").addEventListener("click", function(e){
            e.preventDefault();
            document.querySelector("form").submit();
        });
    </script>
</html>