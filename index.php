<?php

session_start();

include "./PHP/DataAccess.php";
include "./PHP/Header.php";


$queryProds = "SELECT id,name,unit_price,image FROM products";
$filter = false;
$noProducts = false;
$error = "";
$filtersAdd = 0;

if(!isset($_SESSION["start"])) $_SESSION["position"] = 0;

$_SESSION["start"] = true;

if ($_SERVER["REQUEST_METHOD"] === "GET")
{
    if(!isset($_GET["search"])) 
    {
        if(isset($_GET["minPrice"]) && isset($_GET["minPrice"])) 
        {
            $queryProds = "SELECT id,name,unit_price,image FROM products WHERE unit_price > ". $_GET["minPrice"] ." AND unit_price <= ". $_GET["maxPrice"];
            if($_GET["minPrice"] == 0 && $_GET["maxPrice"] == 0)
            {
                $queryProds = "SELECT id,name,unit_price,image FROM products";       
                $filtersAdd--; 
            }
            $filter = true;
            $filtersAdd++;
        }

        $categoriesSelected = array();
        for ($i=0; $i < 6; $i++) 
        { 
            if(isset($_GET["categorie".$i])) array_push($categoriesSelected, $_GET["categorie".$i]);
        }
        if(count($categoriesSelected) > 0)
        {
            if(!in_array("All", $categoriesSelected))
            {
                $first = true;
                $queryProds = "id IN (SELECT idProduct FROM product_category";
                foreach ($categoriesSelected as $catSelected) {
                    if($first)
                    {
                        $queryProds =  $queryProds." WHERE ";
                        $first = false;
                    } else {
                        $queryProds =  $queryProds." OR ";
                    }
                    $queryProds =  $queryProds."idCategory IN (SELECT id FROM categories WHERE category ='". $catSelected ."')";
                }
                $queryProds =  $queryProds.")";
                $filtersAdd++;
            }
            if($filtersAdd == 2)
            {
                $queryProds = "SELECT id,name,unit_price,image FROM products WHERE unit_price > ". $_GET["minPrice"] ." AND unit_price <= ". $_GET["maxPrice"] ." AND ".$queryProds;
            } else if($filtersAdd == 1)
            {
                $queryProds = "SELECT id,name,unit_price,image FROM products WHERE ".$queryProds;
            }
        }
    } else
    {
        $queryProds = "SELECT id,name,unit_price,image FROM products WHERE name LIKE '%".$_GET["search"]."%'";
    }
    $_SESSION["filter-products"] = $queryProds;
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if(isset($_POST["productId"]))
    {
        if(!isset($_SESSION["email"])) header ("Location:./Pages/Login.php");
        
        $productId = $_POST["productId"];
        $quantity = $_POST["quantity" . $productId];
        $email = $_SESSION["email"];
        $query = "CALL add_product_to_cart((SELECT id FROM users WHERE email = '{$email}'), '{$productId}', '{$quantity}')";

        $bd->query($query);
        $_SESSION["num-products-in-cart"] = $bd->query("SELECT id FROM order_lines WHERE idOrder = (SELECT id FROM orders WHERE user_id = (SELECT id FROM users WHERE email = '{$email}') AND active = true) GROUP BY idProduct")->rowCount(); 
        $_SESSION["position"] = $_POST["{$_POST['productId']}"];

        unset($_POST["productId"]);
        $queryProds = $_SESSION["filter-products"];
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <title>HoopsStyle</title>
        <meta charset="UTF-8">
        <meta name="description" content="">
        <meta name="author" content="Mario Esparza">
        <link rel="shortcut icon" href="./Others/Icon.png" type="image/x-icon">
        <link href="./CSS/Header.css" rel="stylesheet" media="all" type="text/css"/>
        <link href="./CSS/Products.css" rel="stylesheet" media="all" type="text/css"/>
    </head>
    <body>
        <?php
            echo mainHeader();

            if($bd->query($queryProds)->rowCount() == 0)
            {
                if(!$filter) 
                {
                    session_start();
                    $_SESSION["products-created"] = false;
                    header("Location:./PHP/CreateProducts.php");
                } else
                {
                    $error = "<h2 style='position: absolute; top: 10%; left: 50%; '>No products found</h2>";
                    $noProducts = true;
                }
            } else $products = $bd->query($queryProds);
            $queryCateg = "SELECT * FROM Categories ORDER BY CASE WHEN category = 'No category' THEN 1 ELSE 0 END, category";
            $categories = $bd->query($queryCateg);
        ?>
        <main>
            <div class="categories">
                <form action="<?php echo $_SERVER["PHP_SELF"];?>" method="GET">
                    <div class='categorie'>
                        <label><input type='checkbox' name='categorie' value='All'/>All</label>
                    </div>
                    <?php
                        $count = 0;
                        foreach ($categories as $categorie) {
                            echo "<div class='categorie'>
                                    <label><input type='checkbox' name='categorie".$count."' value='".$categorie["category"]."'/>".$categorie["category"]."</label>
                                  </div>";
                            $count++;
                        }
                    ?>
                    <hr>
                    <label for="price">Filter by price (€)</label>
                    <div id="rangePrices">
                        <label for="minPrice">Min</label>
                        <input type="range" class="priceRange" id="minPriceRange" name="minPrice" min="0" max="250" value="0" step="1" />
                        <output id="minValue" class="value"></output>
                        <label for="maxPrice">Max</label>
                        <input type="range" class="priceRange" id="maxPriceRange" name="maxPrice" min="0" max="250" value="0" step="1" />
                        <output id="maxValue" class="value"></output>
                    </div>
                    <input id="filter" type='submit' value='Filter'/>
                </form>
            </div>
            <form class="products" action="<?php echo $_SERVER["PHP_SELF"];?>" method="POST">
                <?php
                    if($noProducts) echo $error;
                    else {
                        foreach ($products as $i=>$product) {
                                echo "<div class='product'>";
                                echo "    <img src='".$product["image"]."' alt='product'/>";
                                if(implode(" ",explode("-",substr($product["name"], 0 ,strpos($product["name"], ".")))) == "")
                                {
                                    echo "    <span class='name'>".$product["name"]."</span>";
                                } 
                                else
                                {
                                    echo "    <span class='name'>".implode(" ",explode("-",substr($product["name"], 0 ,strpos($product["name"], "."))))."</span>";
                                }
                                echo "    <span class='price'>".$product["unit_price"]."€</span>";
                                echo "    <div>";
                                echo "        <button class='addToCart' type='submit' name='productId' value='".$product["id"]."'>Add to cart</button>";
                                echo "        <input class='quantity' type='number' name='quantity".$product['id']."' min='0' max='10' value='0'/>";
                                echo "    </div>";
                                echo "    <input class='position' type='hidden' name='".$product["id"]."' value='' />";
                                echo "</div>";
                        }    
                    } 
                ?>
            </form>
        </main>
        <script>
            const quantityInputs = document.querySelectorAll(".quantity");
            const positionInputs = document.querySelectorAll(".position");
            const addButtons = document.querySelectorAll(".addToCart");
            const checkboxes = document.querySelectorAll("input[type=checkbox]");
            const minValue = document.getElementById("minValue");
            const minInput = document.getElementById("minPriceRange");

            const maxValue = document.getElementById("maxValue");
            const maxInput = document.getElementById("maxPriceRange");
            
            minValue.textContent = minInput.value;
            maxValue.textContent = maxInput.value;

            minInput.addEventListener("input", (event) => {
                minValue.textContent = event.target.value;
                maxInput.value = event.target.value;
                maxValue.textContent = event.target.value;
            });
            maxInput.addEventListener("input", (event) => {
                maxValue.textContent = event.target.value;
            });

            positionInputs.forEach((inputSel) => {
                inputSel.value = inputSel.parentElement.children.item(0).getBoundingClientRect().top;
            });

            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener("change", (event) => {
                    if(event.target.value == "All")
                    {
                        checkboxes.forEach((checkbox) => {
                            checkbox.checked = false;
                        });
                        event.target.checked = true;
                    } else
                    {
                        checkboxes.forEach((checkbox) => {
                            if(checkbox.value == "All") checkbox.checked = false;
                        });
                    }
                });
            });

            quantityInputs.forEach((input) => {
                validateQuantity(input);
                input.addEventListener("input", (event) => {
                    validateQuantity(input);
                });
            });

            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }

            window.addEventListener("load", () => {
                const y = "<?php echo $_SESSION["position"];?>";
                window.scrollTo(0, y);
            })

            function validateQuantity(targetInput) {
                if(targetInput.value == 0) 
                {
                    targetInput.parentElement.children.item(0).classList.add("disabled");
                }
                else
                {
                    targetInput.parentElement.children.item(0).classList.remove("disabled");
                }
            }


            // if (document.startViewTransition) {
            //     window.navigation.addEventListener("navigate", (e) => {
            //         const toUrl = new URL(e.destination.url);
                    
            //         if(location.origin != toUrl.origin) return;
        
            //         e.intercept({
            //             async handler() {
            //                 const response = await fetch(toUrl.pathname);
            //                 const html = await response.text();
            //                 console.log(html);
        
            //                 document.startViewTransition(() => {
            //                     document.body.innerHTML = html;
            //                 });
            //             }
            //         });
            //     });
            // };
        </script>
    </body>
</html>