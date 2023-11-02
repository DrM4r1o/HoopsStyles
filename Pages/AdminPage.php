<?php

session_start();

include "../PHP/DataAccess.php";
include "../PHP/Header.php";
include "../PHP/Querys.php";


if($_SESSION["rol"] != "Admin") header ("Location:./UserProfile.php");

$userName = $_SESSION["name"];
$userEmail = $_SESSION["email"];

$text = "No option selected";

$numElement = 0;
$elemetsAdded = isset($_SESSION["elm-add"]) ? $_SESSION["elm-add"] : false;

if(isset($_POST["categories"])) $tableName = $_POST["categories"];
if(isset($_POST["products"])) $tableName = $_POST["products"];
if(isset($_POST["users"])) $tableName = $_POST["users"];

if(isset($_POST["tableName"])) $tableName = $_POST["tableName"];

if(isset($_POST["element"]))
{
    $sum = 0;
    if ($tableName == "Categories") $sum = 2;
    if ($tableName == "Products") $sum = 4;
    if ($tableName == "Users") $sum = 10;

    $i = 0;
    do {
        $idProduct = "";
        $names = $bd->query("DESCRIBE {$tableName}")->fetchAll(PDO::FETCH_COLUMN);
        $queryUpdate = "UPDATE {$tableName} SET ";
        for ($j = 0; $j < $sum; $j++)
        {
            if($j == 0) $idProduct = $_POST["element"][$i];
            else {
                if ($j > 1) $queryUpdate .= ", ";
                $queryUpdate .= "{$names[$j]} = '{$_POST["element"][$i]}'";
            }
            $i++;
        }
        $queryUpdate .= " WHERE id='{$idProduct}';";
        $bd->query($queryUpdate);

        if($tableName == "Products")
        {
            $idCategory = $bd->query("SELECT id FROM categories WHERE category = '{$_POST["category"][$i / $sum - 1]}'")->fetch();
            $oldCategory = $bd->query("SELECT idCategory FROM product_category WHERE idProduct = '{$idProduct}'")->fetch();

            $bd->query("DELETE FROM product_category WHERE idProduct = '{$idProduct}' AND idCategory = '{$oldCategory["idCategory"]}'");
            $bd->query("INSERT INTO product_category VALUES('{$idCategory["id"]}', '{$idProduct}')");

            
            $pos = $i / $sum - 1;
            if($_FILES["image{$pos}"]["name"] != "")
            {
                $nameCategory = $bd->query("SELECT category FROM categories WHERE id = '{$idCategory["id"]}'")->fetch()["category"];
                $imageRoute = "./Products/{$nameCategory}/{$_FILES["image{$pos}"]["name"]}";
                move_uploaded_file(($_FILES["image{$pos}"]["tmp_name"]), ".{$imageRoute}");
                $bd->query("UPDATE products SET image = '{$imageRoute}' WHERE id = '{$idProduct}'");
            }
        }

    } while ($i < count($_POST["element"]));
}

if(isset($_POST["newElement"]))
{
    $i = 0;
    $sum = 0;
    if($_POST["newElement"][0] != "")
    {
        if ($tableName == "Categories") $sum = 2;
        if ($tableName == "Products") $sum = 5;
        if ($tableName == "Users") $sum = 10;

        $pos = 0;
        do {
            $category = "";
            $queryInsert = "INSERT INTO $tableName VALUES(";
            for ($j = 0; $j < $sum; $j++)
            {
                if($j == 0) $idProduct = $_POST["newElement"][$j];
                if($tableName == "Products" && $j == $sum - 1) $category = $_POST["newElement"][$j];
                else
                {
                    if($j != 3)
                    {
                        $queryInsert .= "'".$_POST["newElement"][$j]."'";
                        if ($i != $sum - 1) $queryInsert .= ",";
                    }
                }
                $i++;
            }
            if($tableName == "Products")
            {
                $imageRoute = "./Products/{$category}/{$_FILES["newImage{$pos}"]["name"]}";
                move_uploaded_file(($_FILES["newImage{$pos}"]["tmp_name"]), ".{$imageRoute}");
                $queryInsert .= "'{$imageRoute}'";
            }
            $queryInsert .= ")";
            $bd->query($queryInsert);
            if($tableName == "Products")
            {
                $idCategory = $bd->query("SELECT id FROM categories WHERE category = '{$category}'")->fetch()["id"];
                $bd->query("INSERT INTO product_category VALUES('{$idCategory}', '{$idProduct}')");
            }

            $pos++;
        } while ($i < count($_POST["newElement"]));
    }
}

if(isset($_POST["remove"]))
{
    $tableName = $_POST["tableName"];
    $tableQuery = $tableName;
    if($tableName == "Categories" || $tableName == "Products") 
    {
        if($tableName == "Categories")
        {
            $noCategory = $bd->query("SELECT id FROM categories WHERE category = 'No category'")->fetch();
            if($noCategory == "")
            {
                $bd->query("INSERT INTO categories VALUES((SELECT create_id('No category')),'No category')");
                $noCategory = $bd->query("SELECT id FROM categories WHERE category = 'No category'")->fetch();
            }
            $productsInCategory = getProductsInCategory($_POST["remove"], $bd);
            foreach ($productsInCategory as $product) {
                $bd->query("INSERT INTO product_category VALUES('{$noCategory["id"]}', '{$product["id"]}')");
            }
            $tablePC = "Category";  
        } 
        if($tableName == "Products")
        {
            $tablePC = "Product";
            $bd->query("DELETE FROM order_lines WHERE idProduct = '{$_POST["remove"]}'");
        } 
        $bd->query("DELETE FROM product_category WHERE id{$tablePC} = '{$_POST["remove"]}'");
    }
    $queryDelete = "DELETE FROM $tableName WHERE id = '{$_POST["remove"]}'";
    $bd->query($queryDelete);
}

if(isset($tableName))
{
    $query = "SELECT * FROM {$tableName}";
    $tableName = ucfirst($tableName);
    if($tableName == "Categories")
    {
        $query = "SELECT * FROM {$tableName} WHERE category != 'No category'";
    }
    $data = $bd->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Admin Panel</title>
        <meta charset="UTF-8">
        <meta name="description" content="Admin">
        <meta name="author" content="Mario Esparza">
        <link rel="shortcut icon" href="../Others/Icon.png"/>
        <link href="../CSS/Admin_Page.css" rel="stylesheet" media="all" type="text/css"/>
        <link href="../CSS/Header.css" rel="stylesheet" media="all" type="text/css"/>
    </head>
    <body>
        <div class="panelSelector">
            <h1 style="color: black;"><a class='nombreLogo' href='../index.php' >HoopsStyle</a></h1>
            <form action="<?php echo $_SERVER["PHP_SELF"];?>" method="POST">
                <label class='panelOption'>
                    <img src="../Others/category.svg" alt="categories"></img>
                    <input type="radio" value="categories" name="categories"/>
                    Categories
                </label>
                <label class='panelOption'>
                    <img src="../Others/products.svg" alt="categories"></img>
                    <input type="radio" value="products" name="products"/>
                    Products
                </label>
                <label class='panelOption'>
                    <img src="../Others/users.svg" alt="categories"></img>
                    <input type="radio" value="users" name="users"/>
                    Users
                </label>
            </form>
        </div>
        <main>
            <div class="panelData">
                <h2><?php if(isset($tableName)) echo $tableName; else echo "No option selected";?></h2>
                <?php
                    if(isset($data))
                    {
                        echo "<form id='data' action='{$_SERVER["PHP_SELF"]}' method='POST' enctype='multipart/form-data'>";
                        echo "<table>";
                        echo "<tr>";
                        if($tableName == "Products") echo "<th class='imageContainer'></th>";
                        foreach($data[0] as $key => $value)
                        {
                            echo "<th>$key</th>";
                        }
                        if($tableName == "Products")
                        {
                            echo "<th>Category</th>";
                        }
                        echo "<th class='editContainer'></th>";
                        echo "<th class='removeContainer removeTh'></th>";
                        echo "</tr>";
                        foreach($data as $num => $row )
                        {
                            echo "<tr>";
                            if($tableName == "Products")
                            {
                                echo "
                                <td class='imageContainer'>
                                    <img class='image' src='.{$row["image"]}' alt='productImage'>
                                    <input type='file' id='changeImage' name='image{$num}' title=' ' accept='image/png, image/jpeg, image/webp' />
                                </td>   
                                ";
                            }
                            foreach($row as $key => $value)
                            {
                                echo "<td><input type='text' name='element[]' value='$value' readOnly='true'></td>";
                            }
                            if($tableName == "Products")
                            {
                                $queryCategory = "SELECT idCategory FROM product_category WHERE idProduct = '{$row["id"]}'";
                                $categoryId = $bd->query($queryCategory)->fetch();
                                $queryName = "SELECT category FROM categories WHERE id = '{$categoryId["idCategory"]}'";
                                $categoryName = $bd->query($queryName)->fetch();

                                $queryCategories = "SELECT category FROM categories WHERE category != '{$categoryName["category"]}'";
                                $categories = $bd->query($queryCategories)->fetchAll(PDO::FETCH_COLUMN);

                                echo "
                                <td>
                                    <select name='category[]'>
                                        <option value='{$categoryName["category"]}'>{$categoryName["category"]}</option>    
                                ";
                                
                                foreach ($categories as $category) {
                                    echo "<option value='{$category}' disabled>{$category}</option>";
                                }
                                echo "
                                    </select>
                                </td>
                                ";
                            }
                            echo "<td class='editContainer'><img class='edit' src='../Others/edit.svg' alt'edit'></img></td>";
                            echo "<td class='removeContainer'><button type='submit' name='remove' value='{$row["id"]}' class='remove' src='../Others/trash.svg' alt'remove'></td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                        echo "<input type='hidden' name='tableName' value='{$tableName}'>";
                        echo "</form>";
                        echo "<button id='add' class='actions' >Add {$tableName}</button>";
                        echo "<button id='submit' class='actions' type='submit'>Save Changes</button>";
                    }
                ?>
            </div>
        </main>
        <a href="./UserProfile.php"><img class="return" src="../Others/return_white.svg" alt=""></a>
    </body>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha256/0.10.1/sha256.min.js" integrity="sha512-wyQ8L68oINPpa6L8GkJbnEQNGRWEgcvCIDKzwQpSQAR1etTNtgKDC3vL3DnXczYA3ijrO0kc6bhW57m0Vu+u0g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha256/0.10.1/sha256.js" integrity="sha512-I10uxz+ewyO4jNEzkfqqBbhmUX1gAaL1NVIu1Ki+emGuoMjULGyzJnBolDVqV0WbQzVLjT8Ji5Q708qaQIwNPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        const removeButtons = document.querySelectorAll(".remove");
        const panelSelector = document.querySelectorAll("input[type=radio]");
        const add = document.querySelector("#add");
        const returnButton = document.querySelector(".return");
        const buttonSubmit = document.querySelector("#submit");
        const inputImages = document.querySelectorAll("input[type=file]");
        const tableName = "<?php if(isset($tableName)) {echo $tableName;} else {echo '';} ?>";
        let newImage = 0;
        let editing = false;

        if(buttonSubmit != null)
        {
            buttonSubmit.addEventListener("click", () => {
                const form = document.querySelector("#data");
                form.submit();
            });

        }

        removeButtons.forEach((button) => {
            button.addEventListener("click", (e) => {
                if(confirm('Are you sure you want to delete this element?'))
                {
                    const form = document.querySelector("form");
                    form.submit();
                } else
                {
                    e.preventDefault();
                }
            });
        });

        returnButton.addEventListener("click", () => {
            window.location.href = "./UserProfile.php";
        });

        inputImages.forEach((input) => {
            input.addEventListener("change", (e) => {
                e.target.classList.add("inserted");
            });
        });

        if(add != null)
        {
            add.addEventListener("click", (e) => {
                e.preventDefault();
                const tableName = "<?php if(isset($tableName)) {echo $tableName;} else {echo '';} ?>";
                const table = document.querySelector("table");
                const lastRowNameCell = table.rows[table.rows.length - 1].cells[1].querySelector("input").value;
                if(!editing && lastRowNameCell != "")
                {
                    const newRow = document.createElement("tr"); 
                    table.appendChild(newRow);
                    for(let i = 0; i < table.rows[0].cells.length; i++)
                    {
                        let newCell = document.createElement("td");
                        if(tableName == "Products" && i == 0)
                        {
                            const newInput = document.createElement("input");
                            newInput.type = "file";
                            newInput.name = `newImage${newImage}`;
                            newInput.title = " ";
                            newInput.accept = "image/png, image/jpeg, image/webp";
                            newCell.appendChild(newInput);
                            newImage++;
                        } else
                        {
                            if(i == (table.rows[0].cells.length - 2))
                            {
                                newRow.appendChild(addElements("editContainer", ["edit", "save"], "../Others/save.svg", "edit", true));
                                newCell = addElements("removeContainer", ["remove"], "../Others/trash.svg", "remove", false);
                                editing = true;
                                i++;
                            } else {
                                const newInput = document.createElement("input");
                                newInput.type = "text";
                                newInput.name = "newElement[]";
                                newInput.setAttribute("readOnly", "false");
                                newCell.appendChild(newInput);
                            }
                        }
                        newRow.appendChild(newCell);
                    }
                    invertInputs(newRow.querySelectorAll("input"))
                    addBorderColorNew(newRow);
                }
            });
        }

        const editButtons = document.querySelectorAll(".edit");

        for (let i = 0; i < panelSelector.length; i++) {
            const optionPanel = panelSelector[i];
            optionPanel.addEventListener("click", () => {
                optionPanel.parentElement.parentElement.submit();
            });   
        }

        for (let i = 0; i < editButtons.length; i++) {
            addEventEdit(editButtons[i]);
        }

        function addBorderColorNew(newRow)
        {
            var inputs = newRow.querySelectorAll('input');


            for (var i = 0; i < inputs.length; i++) {
                var input = inputs[i];
                if (
                    tableName == "Products" &&
                    input.type !== 'file' &&
                    input.type !== 'hidden' &&
                    (i !== 1 || input.type !== 'text')
                ) {
                    input.classList.add("newAdded");
                } else if(i !== 0)
                {
                    input.classList.add("newAdded");
                }
            }
        }

        function addEventEdit(targetElement) 
        {
            targetElement.addEventListener("click", (e) => {
                if(editing && e.target.classList.contains("save"))
                {
                    let valSelectInput = 0;
                    editing = false;
                    e.target.src = "../Others/edit.svg";
                    e.target.classList.remove("save");
                    if(tableName == "Products") valSelectInput = 1;
                    let elementHash = e.target.parentElement.parentElement.querySelectorAll("input")[valSelectInput];
                    let elementName = e.target.parentElement.parentElement.querySelectorAll("input")[valSelectInput + 1];
                    if(elementHash.value == "" && elementName.value != "")
                    {
                        let hash = sha256(elementName.value);
                        elementHash.value = hash.substring(0, 20);
                    }
                    invertInputs(e.target.parentElement.parentElement.querySelectorAll("input"))
                } else if(!editing) 
                {
                    e.target.src = "../Others/save.svg";
                    e.target.classList.add("save");
                    editing = true;
                    invertInputs(e.target.parentElement.parentElement.querySelectorAll("input"))
                }
            });
        }

        function addEventRemove(targetElement) 
        {
            targetElement.addEventListener("click", (e) => {
                if(confirm('Are you sure you want to delete this element?'))
                {
                    const table = document.querySelector("table");
                    table.removeChild(e.target.parentElement.parentElement);
                    editing = false;
                }
            });
        }

        function invertInputs(row)
        {
            if(tableName == "Products")
            {
                <?php echo "invertOptionsProducts(row[0].parentElement.parentElement);"; ?>
            }
            for (let i = 0; i < row.length; i++) {
                const input = row[i];
                if(tableName == "Products" && i > 1 || tableName != "Products" && i > 0)
                {
                    if (i > 0)
                    {
                        input.classList = "";
                        input.readOnly = input.readOnly ? false : true;
                        input.classList.add(input.readOnly ? "readOnly" : "editable");
                    }
                }
            }
        }

        function invertOptionsProducts(row)
        {
            const options = row.querySelectorAll("option");
            for (let i = 0; i < options.length; i++) {
                const option = options[i];
                if(editing)
                {
                    option.disabled = false;
                } else 
                {
                    if(row.querySelector("select").selectedOptions[0] == option)
                    {
                        option.disabled = false;
                    } else
                    {
                        option.disabled = true;
                    }
                }
            }
        }

        function addElements(cellClass, imgClasses, imgRoute, alt, isEdit)
        {
            const newCell = document.createElement("td");
            const newImg = document.createElement("img");
            newCell.classList.add(cellClass);
            newImg.classList.add(imgClasses[0], imgClasses[1]);
            newCell.appendChild(newImg);
            if(isEdit)
            {
               addEventEdit(newImg);
               newImg.src = imgRoute;
               newImg.alt = alt;
            } 
            else 
            {
                addEventRemove(newImg);
            }
            return newCell;
        }
    </script>
</html>
