<?php

session_start();

include "../PHP/DataAccess.php";
include "../PHP/Header.php";


if($_SESSION["rol"] != "Admin") header ("Location:./UserProfile.php");

$userName = $_SESSION["name"];
$userEmail = $_SESSION["email"];
$userPass = $_SESSION["password"];

$text = "No option selected";

$numElement = 0;
$elemetsAdded = isset($_SESSION["elm-add"]) ? $_SESSION["elm-add"] : false;

if(isset($_GET["categories"])) $tableName = $_GET["categories"];
if(isset($_GET["products"])) $tableName = $_GET["products"];
if(isset($_GET["users"])) $tableName = $_GET["users"];

if(isset($_GET["tableName"])) $tableName = $_GET["tableName"];

if(isset($_GET["element"]))
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
            if($j == 0) $idProduct = $_GET["element"][$i];
            else {
                if ($j > 1) $queryUpdate .= ", ";
                $queryUpdate .= "{$names[$j]} = '{$_GET["element"][$i]}'";
            }
            $i++;
        }
        $queryUpdate .= " WHERE id='{$idProduct}';";
        $bd->query($queryUpdate);

        if($tableName == "Products")
        {
            $idCategory = $bd->query("SELECT id FROM categories WHERE category = '{$_GET["category"][$i / $sum - 1]}'")->fetch();
            $oldCategory = $bd->query("SELECT idCategory FROM product_category WHERE idProduct = '{$idProduct}'")->fetch();

            $bd->query("DELETE FROM product_category WHERE idProduct = '{$idProduct}' AND idCategory = '{$oldCategory["idCategory"]}'");
            $bd->query("INSERT INTO product_category VALUES('{$idCategory["id"]}', '{$idProduct}')");
        }

    } while ($i < count($_GET["element"]));
}

if(isset($_GET["newElement"]))
{
    $i = 0;
    $sum = 0;
    if ($tableName == "Categories") $sum = 2;
    if ($tableName == "Products") $sum = 4;
    if ($tableName == "Users") $sum = 10;

    do {
        $queryInsert = "INSERT INTO $tableName VALUES(";
        for ($j = 0; $j < $sum; $j++)
        {
            $queryInsert .= "'".$_GET["newElement"][$j]."'";
            if ($i != $sum - 1) $queryInsert .= ",";
            $i++;
        }
        $queryInsert .= ")";
        $bd->query($queryInsert);
    } while ($i < count($_GET["newElement"]));
}

if(isset($_GET["remove"]))
{
    $tableName = $_GET["tableName"];
    $tableQuery = $tableName;
    if($tableName == "Categories" || $tableName == "Products") 
    {
        if($tableName == "Categories") $tablePC = "Category";
        if($tableName == "Products") $tablePC = "Product";
        $bd->query("DELETE FROM product_category WHERE id{$tablePC} = '{$_GET["remove"]}'");
    }
    $queryDelete = "DELETE FROM $tableName WHERE id = '{$_GET["remove"]}'";
    $bd->query($queryDelete);
}

if(isset($tableName))
{
    $query = "SELECT * FROM {$tableName}";
    $data = $bd->query($query)->fetchAll(PDO::FETCH_ASSOC);
    $tableName = ucfirst($tableName);
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
            <form action="<?php echo $_SERVER["PHP_SELF"];?>" method="GET">
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
                        echo "<form id='data' action='{$_SERVER["PHP_SELF"]}' method='GET'>";
                        echo "<table>";
                        echo "<tr>";
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
                        foreach($data as $row)
                        {
                            echo "<tr>";
                            foreach($row as $key => $value)
                            {
                                // if($key == "complete" && $value == "1") $value = "true";
                                // if($key == "complete" && $value == "0") $value = "false";
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
        <img class="return" src="../Others/return_white.svg" alt="">
    </body>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha256/0.10.1/sha256.min.js" integrity="sha512-wyQ8L68oINPpa6L8GkJbnEQNGRWEgcvCIDKzwQpSQAR1etTNtgKDC3vL3DnXczYA3ijrO0kc6bhW57m0Vu+u0g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha256/0.10.1/sha256.js" integrity="sha512-I10uxz+ewyO4jNEzkfqqBbhmUX1gAaL1NVIu1Ki+emGuoMjULGyzJnBolDVqV0WbQzVLjT8Ji5Q708qaQIwNPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        const removeButtons = document.querySelectorAll(".remove");
        const panelSelector = document.querySelectorAll("input[type=radio]");
        const add = document.querySelector("#add");
        const returnButton = document.querySelector(".return");
        const buttonSubmit = document.querySelector("#submit");
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

        if(add != null)
        {
            add.addEventListener("click", (e) => {
                e.preventDefault();
                const table = document.querySelector("table");
                const lastRowNameCell = table.rows[table.rows.length - 1].cells[1].querySelector("input").value;
                if(!editing && lastRowNameCell != "")
                {
                    const newRow = document.createElement("tr"); 
                    table.appendChild(newRow);
                    for(let i = 0; i < table.rows[0].cells.length; i++)
                    {
                        let newCell = document.createElement("td");
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
                        newRow.appendChild(newCell);
                    }
                    invertInputs(newRow.querySelectorAll("input"))
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

        function addEventEdit(targetElement) 
        {
            targetElement.addEventListener("click", (e) => {
                if(editing && e.target.classList.contains("save"))
                {
                    editing = false;
                    e.target.src = "../Others/edit.svg";
                    e.target.classList.remove("save");
                    let elementName = e.target.parentElement.parentElement.querySelectorAll("input")[1];
                    let elementHash = e.target.parentElement.parentElement.querySelectorAll("input")[0];
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
            const tableName = "<?php if(isset($tableName)) {echo $tableName;} else {echo '';} ?>";
            if(tableName == "Products")
            {
                <?php echo "invertInputsProducts(row[0].parentElement.parentElement);"; ?>
            } else {
                let j = 0;
                for (let i = 0; i < row.length; i++) {
                    const input = row[i];
                    if (i > 0)
                    {
                        input.readOnly = input.readOnly ? false : true;
                    }
                }
            }
        }

        function invertInputsProducts(row)
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

            row = row.querySelectorAll("input");
            let j = 0;
            for (let i = 0; i < row.length; i++) {
                const input = row[i];
                if (i > 0)
                {
                    input.readOnly = input.readOnly ? false : true;
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
