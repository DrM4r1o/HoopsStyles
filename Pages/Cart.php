<?php

session_start();

if(!isset($_SESSION["email"])) header ("Location:./Login.php");

if(isset($_SESSION["end-order"])) $_SESSION["end-order"] = false;

$email = $_SESSION["email"];

include "../PHP/DataAccess.php";
include "../PHP/Header.php";
include "../PHP/Querys.php";

if(isset($_GET["remove"]))
{
    $idProduct = $_GET["remove"];
    deleteProductFromCart($idProduct, $email, $bd);
    updateNumberCart($email, $bd);

    if($_SESSION["num-products-in-cart"] == 0) header ("Location:../");
}

if(isset($_GET["products"]) && !isset($_GET["remove"]))
{
    foreach ($_GET["products"] as $productId) {
        $idOrderQuery = "SELECT id FROM orders WHERE user_id = (SELECT id FROM users WHERE email = '{$email}') AND active = 1";
        $idOrder = $bd->query($idOrderQuery)->fetch()["id"];
        $query = "UPDATE order_lines SET quantity = '{$_GET["quantity".$productId]}', linePrice = ((SELECT unit_price FROM PRODUCTS WHERE id = '{$productId}') * {$_GET["quantity".$productId]}) WHERE idOrder = '{$idOrder}' AND idProduct = '{$productId}'";
        $bd->query($query);
    }
    $_SESSION["end-order"] = true;
    header ("Location:../Pages/EndOrder.php");
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
        <link href="../css/Cart.css" rel="stylesheet" media="all" type="text/css"/>
    </head>
    <body>
        <?php
            echo headerNoCart("..");
        ?>
        <main>
            <form action="<?php echo $_SERVER["PHP_SELF"];?>" method="GET" class='products'>
                <?php
                if(count($productsInCart) == 0) echo "<h2 style='position: absolute; top: 10%; left: 50%; '>No products in cart</h2>";
                else {
                    foreach ($productsInCart as $productInCart) {
                        $product = $bd->query("SELECT id,name,unit_price,image FROM products WHERE id = '{$productInCart["idProduct"]}'")->fetch();
                        echo "<div class='product'>";
                        echo "    <img src='.".$product["image"]."' alt='product'/>";
                        echo "    <span class='name'>".implode(" ",explode("-",substr($product["name"], 0 ,strpos($product["name"], "."))))."</span>";
                        echo "    <span class='price'>".$product["unit_price"]."€</span>";
                        echo "    <div>";
                        echo "        <button class='remove' type='submit' name='remove' value='{$product["id"]}'>Remove</button>";
                        echo "        <input class='quantity' type='number' name='quantity{$product["id"]}' min='1' max='10' value='{$productInCart["quantity"]}'/>";
                        echo "    </div>";
                        echo "    <input type='hidden' name='products[]' value='{$product["id"]}' />";
                        echo "</div>";
                    }    
                }
                echo "</div>";
                ?>
            </form>
            <div class="summary">
                <h3>Finish Order</h3>
                <button class='buy' type='submit' name='buy' value='buy'>Proceed to payment</button>
            </div>
        </main>
    </body>
    <script>
        const buyButton = document.querySelector(".buy");

        buyButton.addEventListener("click", () => {
            const form = document.querySelector("form");
            form.submit();
        });
    </script>
</html>