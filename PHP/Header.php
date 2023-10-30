<?php

$numProductsInCart = 0;

if(isset($_SESSION["email"]))
{
    $queryNumProductsInCart = "SELECT COUNT(*) FROM order_lines WHERE idOrder = (SELECT id FROM orders WHERE user_id = (SELECT id FROM users WHERE email = '{$_SESSION["email"]}') AND ACTIVE = 1)";
    $resultNumProductsInCart = $bd->query($queryNumProductsInCart)->fetch();
    $numProductsInCart = $resultNumProductsInCart[0];
}

$_SESSION["num-products-in-cart"] = $numProductsInCart;

function mainHeader() {
    return 
    "
    <header>
        <a class='nombreLogo' href='./index.php' >HoopsStyle</a>
        <div id='header-items'>
            <div id='searchBar'>
                <form id='formSearch' action='index.php' method='GET'>
                    <input type='search' name='search' placeholder='Search...'/>
                    <input type='image' src='./Others/Lens.png' name='submit-search'/>
                </form>
            </div>
            <div id='cart'>
                <a href='./Pages/Cart.php'><img id='cartImg' src='./Others/Cart.png' alt='cart'></a>
                <span id='numberCart'> {$_SESSION["num-products-in-cart"]} </span>
            </div>
            <a href='./'>All Products</a>
            <a href='./Pages/Login.php'>
                <img id='userImg' src='./Others/User.png' alt=''>
            </a>
        </div>
    </header>
    ";
}

function headerNoSearch($posIndex) {
    return
    "
    <header>
        <a class='nombreLogo' href='".$posIndex."/index.php' >HoopsStyle</a>
        <div id='header-items'>
            <div id='searchBar'>
            </div>
            <div id='cart'>
                <a href='".$posIndex."/Pages/Cart.php'><img id='cartImg' src='".$posIndex."/Others/Cart.png' alt='cart'></a>
                <span id='numberCart'> {$_SESSION["num-products-in-cart"]} </span>
            </div>
            <a href='".$posIndex."/'>All Products</a>
            <a href='".$posIndex."/Pages/Login.php'>
                <img id='userImg' src='".$posIndex."/Others/User.png' alt=''>
            </a>
        </div>
    </header>
    ";
}

function headerNoCart($posIndex) {
    return
    "
    <header>
        <a class='nombreLogo' href='".$posIndex."/index.php' >HoopsStyle</a>
        <div id='header-items'>
            <div id='searchBar'>
            </div>
            <div id='cart'>
            </div>
            <a href='".$posIndex."/'>All Products</a>
            <a href='".$posIndex."/Pages/Login.php'>
                <img id='userImg' src='".$posIndex."/Others/User.png' alt=''>
            </a>
        </div>
    </header>
    ";
}