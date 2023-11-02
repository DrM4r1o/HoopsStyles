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

function userIsComplete($userEmal, $bd)
{
    return $bd->query("SELECT complete FROM users WHERE email = '{$userEmal}'")->fetch()["complete"];
}

function getProductsInCategory($categorySearch, $bd)
{
    $query = "SELECT id FROM products WHERE id IN (SELECT idProduct FROM product_category WHERE idCategory = (SELECT id FROM categories WHERE id = '{$categorySearch}'))";
    return $bd->query($query)->fetchAll();
}