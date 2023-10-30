USE shop;

-- Procedure to insert admin users into the USERS table
DROP PROCEDURE IF EXISTS insert_admin_users;
DELIMITER //
CREATE PROCEDURE insert_admin_users()
BEGIN
  INSERT INTO USERS (id, dni, email, password, first_name, last_name, role, phone_number, address, complete)
  VALUES
    (create_id('admin1@example.com'), '99999999A', 'admin1@example.com', 'password1', 'John', 'Doe', 'Admin', '555-1234', '123 Main St',TRUE),
    (create_id('hp151lolxd@gmail.com'), '49740780Z', 'hp151lolxd@gmail.com', 'password', 'Mario', 'Esparza', 'Admin', '653060929', 'C/ Fontanars, 38',TRUE);
END
//
DELIMITER ;

-- Procedure to insert regular users into the USERS table
DROP PROCEDURE IF EXISTS insert_regular_users;
DELIMITER //
CREATE PROCEDURE insert_regular_users()
BEGIN
  SET @i = 0;
  WHILE @i < 18 DO
    INSERT INTO USERS (id, dni, email, password, first_name, last_name, role, phone_number,address,complete)
    VALUES
      (create_id(CONCAT('user', @i, '@example.com')), CONCAT('1234567', @i, 'A'), CONCAT('user', @i, '@example.com'), 'password', 'User', CONCAT(@i), 'User', '555-1234', CONCAT(@i, ' Main St'),TRUE);
    SET @i = @i + 1;
  END WHILE;
END
//
DELIMITER ;

-- Procedure to insert categories into the CATEGORIES table
DROP PROCEDURE IF EXISTS insert_categories;
DELIMITER //
CREATE PROCEDURE insert_categories()
BEGIN
  SET @i = 0;
  WHILE @i < 10 DO
    INSERT INTO CATEGORIES (id, category)
    VALUES
      (create_id(CONCAT('category', @i)), CONCAT('Category', @i));
    SET @i = @i + 1;
  END WHILE;
END
//
DELIMITER ;

-- Procedure to insert products into the PRODUCTS table
DROP PROCEDURE IF EXISTS insert_products;
DELIMITER //
CREATE PROCEDURE insert_products()
BEGIN
  SET @i = 0;
  WHILE @i < 4 DO
    INSERT INTO PRODUCTS (id, name, unit_price, image)
    VALUES
      (create_id(CONCAT('product', @i)), CONCAT('Product', @i), RAND() * 100, CONCAT('./Products/Product', @i, '.webp'));
    SET @i = @i + 1;
  END WHILE;
END
//
DELIMITER ;

-- Procedure to link products to categories in the PRODUCT_CATEGORY table
DROP PROCEDURE IF EXISTS link_products_to_categories;
DELIMITER //
CREATE PROCEDURE link_products_to_categories()
BEGIN
    DECLARE idCat VARCHAR(64) DEFAULT "";
    DECLARE idProd VARCHAR(64) DEFAULT "";
    DECLARE FINALIZADO BOOLEAN DEFAULT FALSE;

    DECLARE C_Cursor CURSOR FOR 
        SELECT id FROM categories;
    DECLARE CONTINUE HANDLER 
        FOR NOT FOUND SET FINALIZADO = TRUE;

    OPEN C_Cursor;
        REPEAT
            FETCH C_Cursor INTO idCat;
            SET idProd = (SELECT id FROM PRODUCTS ORDER BY RAND() LIMIT 1);
            INSERT INTO PRODUCT_CATEGORY (idCategory, idProduct)
            VALUES
                (idCat, idProd),
                (idCat, (SELECT id FROM PRODUCTS WHERE id != idProd ORDER BY RAND() LIMIT 1));
        UNTIL FINALIZADO = TRUE END REPEAT;
    CLOSE C_Cursor;
END
//
DELIMITER ; 

-- Proceduro to insert new ORDER
DROP PROCEDURE IF EXISTS create_order;
DELIMITER //
CREATE PROCEDURE create_order(IN selOrder VARCHAR(64), IN idUser VARCHAR(64))
  BEGIN
    INSERT INTO ORDERS (id, user_id, first_name, last_name, dni, address, date, total_price)
    VALUES (
      selOrder,
      idUser,
      (SELECT first_name FROM USERS WHERE id = idUser),
      (SELECT last_name FROM USERS WHERE id = idUser),
      (SELECT dni FROM USERS WHERE id = idUser),
      (SELECT address FROM USERS WHERE id = idUser),
      NOW(),
      0.00
    );
  END
  //
DELIMITER ;

-- Procedure to insert new ORDER_LINES
DROP PROCEDURE IF EXISTS create_order_line;
DELIMITER //
CREATE PROCEDURE create_order_line(IN selOrder VARCHAR(64), IN selProduct VARCHAR(64), IN prodQuantity INT(2))
  BEGIN
    INSERT INTO ORDER_LINES (id, idOrder, idProduct, quantity, linePrice)
    VALUES (
      create_id(CONCAT('order_line', selOrder)), 
      selOrder, 
      selProduct, 
      prodQuantity, 
      (SELECT unit_price FROM PRODUCTS WHERE id = selProduct) * prodQuantity
    );
  END
  //
DELIMITER ;

-- Procedure to create orders asociated with users
DROP PROCEDURE IF EXISTS create_orders;
DELIMITER //
CREATE PROCEDURE create_random_orders()
BEGIN

  DECLARE idUser VARCHAR(64) DEFAULT "";
  DECLARE selOrder VARCHAR(64) DEFAULT "";
  DECLARE selProduct VARCHAR(64) DEFAULT "";
  DECLARE prodQuantity INT(2) DEFAULT 0;
  DECLARE FINALIZADO BOOLEAN DEFAULT FALSE;
  
  DECLARE C_Cursor CURSOR FOR 
    SELECT id FROM USERS;
  DECLARE CONTINUE HANDLER 
    FOR NOT FOUND SET FINALIZADO = TRUE;
  
  OPEN C_Cursor;
    REPEAT
      FETCH C_Cursor INTO idUser;

        SET selOrder = create_id(CONCAT('order', idUser, NOW(), RAND()));
        SET selProduct = (SELECT id FROM products ORDER BY RAND() LIMIT 1);
        SET prodQuantity = (SELECT FLOOR(RAND() * 10) + 1);

        CALL create_order(selOrder, idUser);
        CALL create_order_line(selOrder, selProduct, prodQuantity);

        UPDATE ORDERS SET active = FALSE WHERE id = selOrder;

    UNTIL FINALIZADO = TRUE END REPEAT;
  CLOSE C_Cursor;
  UPDATE ORDERS SET active = TRUE WHERE id = selOrder;
END
//
DELIMITER ;


CALL insert_admin_users();
CALL insert_regular_users();
CALL insert_categories();
CALL insert_products();
CALL link_products_to_categories();
CALL create_random_orders();