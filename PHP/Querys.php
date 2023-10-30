<?php

function updateOrderPrice($userEmail, $bd)
{
    $queryUpdatePrice = "
    UPDATE orders
    SET total_price = (SELECT SUM(linePrice) FROM order_lines WHERE idOrder = (SELECT id FROM orders WHERE user_id = (SELECT id FROM users WHERE email = '{$userEmail}') AND active = 1))
    WHERE user_id = (SELECT id FROM users WHERE email = '{$userEmail}')
    AND active = 1
    ";
    $bd->query($queryUpdatePrice);
}

function deleteProductFromCart($productId, $userEmail, $bd)
{
    $queryDelete = "DELETE FROM order_lines WHERE idOrder = (SELECT id FROM orders WHERE user_id = (SELECT id FROM users WHERE email = '{$userEmail}') AND active = 1) AND idProduct = '{$productId}'";
    $bd->query($queryDelete);
    updateOrderPrice($userEmail, $bd);
}

function updateNumberCart($userEmail, $bd)
{
    $queryUpdateNumCart = "SELECT id FROM order_lines WHERE idOrder = (SELECT id FROM orders WHERE user_id = (SELECT id FROM users WHERE email = '{$userEmail}')) GROUP BY idProduct";
    $_SESSION["num-products-in-cart"] = $bd->query($queryUpdateNumCart)->rowCount();
}