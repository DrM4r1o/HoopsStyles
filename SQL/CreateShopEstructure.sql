DROP DATABASE IF EXISTS shop;
CREATE DATABASE shop;

USE shop;

CREATE TABLE USERS (
  id VARCHAR(20) NOT NULL,
  dni VARCHAR(20) NOT NULL,
  email VARCHAR(32) NOT NULL,
  password VARCHAR(32) NOT NULL,
  first_name VARCHAR(32) NOT NULL,
  last_name VARCHAR(32) NOT NULL,
  role VARCHAR(32) NOT NULL,
  phone_number VARCHAR(20),
  address VARCHAR(255),
  complete BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (id),
  UNIQUE KEY (dni),
  UNIQUE KEY (email)
);

CREATE TABLE ORDERS (
  id VARCHAR(20) NOT NULL,
  user_id VARCHAR(20) NOT NULL,
  first_name VARCHAR(32) NOT NULL,
  last_name VARCHAR(32) NOT NULL,
  dni VARCHAR(20) NOT NULL,
  address VARCHAR(255) NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  active BOOLEAN NOT NULL DEFAULT TRUE,
  date DATE NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES USERS(id)
);

CREATE TABLE CATEGORIES (
  id VARCHAR(20) NOT NULL,
  category VARCHAR(16) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE PRODUCTS (
  id VARCHAR(20) NOT NULL,
  name VARCHAR(64) NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  image VARCHAR(255),
  PRIMARY KEY (id)
);

CREATE TABLE PRODUCT_CATEGORY (
  idCategory VARCHAR(20) NOT NULL,
  idProduct VARCHAR(20) NOT NULL,
  PRIMARY KEY (idCategory, idProduct),
  FOREIGN KEY (idCategory) REFERENCES CATEGORIES(id),
  FOREIGN KEY (idProduct) REFERENCES PRODUCTS(id)
);

CREATE TABLE ORDER_LINES (
  id VARCHAR(20) NOT NULL,
  idOrder VARCHAR(20) NOT NULL,
  idProduct VARCHAR(20) NOT NULL,
  quantity INT NOT NULL,
  linePrice DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (idOrder) REFERENCES ORDERS(id),
  FOREIGN KEY (idProduct) REFERENCES PRODUCTS(id)
);

-- Generic functions --

-- Function to crate an id for a new user
DROP FUNCTION IF EXISTS create_id;
DELIMITER //
CREATE FUNCTION create_id(diff VARCHAR(64))
RETURNS VARCHAR(20)
  BEGIN
    RETURN SHA2(CONCAT(diff, NOW()), 256);
  END
  //
DELIMITER ;

-- Add one categorie
DROP PROCEDURE IF EXISTS add_categorie;
DELIMITER //
CREATE PROCEDURE add_categorie(cName VARCHAR(20))
BEGIN
    INSERT INTO CATEGORIES (id, category)
    VALUES (create_id(CONCAT(cName)), cName);
END
//
DELIMITER ;

-- Procedure to link products to categories
DROP PROCEDURE IF EXISTS link_product;
DELIMITER //
CREATE PROCEDURE link_product(idCat VARCHAR(20), idProd VARCHAR(20))
BEGIN
  INSERT INTO PRODUCT_CATEGORY (idCategory, idProduct) VALUES (idCat, idProd);
END
//
DELIMITER ; 

-- Add one product
DROP PROCEDURE IF EXISTS add_product;
DELIMITER //
CREATE PROCEDURE add_product(pName VARCHAR(64), pPrice DECIMAL(10,2), pImage VARCHAR(255), pCate VARCHAR(20))
BEGIN
    INSERT INTO PRODUCTS (id, name, unit_price, image)
    VALUES (create_id(CONCAT(pName)), pName, pPrice, pImage);
    CALL link_product((SELECT id FROM CATEGORIES WHERE category = pCate LIMIT 1), (SELECT id FROM PRODUCTS WHERE name = pName LIMIT 1));
END
//
DELIMITER ;

-- Create order line
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

-- Procedure to add a new product to the cart
DROP PROCEDURE IF EXISTS add_product_to_cart;
DELIMITER //
CREATE PROCEDURE add_product_to_cart(IN idUser VARCHAR(64), IN selProduct VARCHAR(64), IN prodQuantity INT(2))
  BEGIN
        DECLARE selOrder VARCHAR(64) DEFAULT "";
        DECLARE selLine VARCHAR(64) DEFAULT "";

        SET selOrder = (SELECT id FROM ORDERS WHERE user_id = idUser AND active = TRUE LIMIT 1);
        IF(selOrder IS NULL) THEN
          SET selOrder = create_id(CONCAT('order', NOW()));
          CALL create_order(selOrder, idUser);
        END IF;
        SET selLine = (SELECT id FROM ORDER_LINES WHERE idOrder = selOrder AND idProduct = selProduct);
        IF(selLine IS NULL) THEN
          CALL create_order_line(selOrder, selProduct, prodQuantity);
        ELSE
          UPDATE ORDER_LINES SET quantity = quantity + prodQuantity, linePrice = linePrice + (SELECT unit_price FROM PRODUCTS WHERE id = selProduct) * prodQuantity WHERE id = selLine;
        END IF;
  END
  //
DELIMITER ;


-- Add Admin users
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

DROP FUNCTION IF EXISTS getPriceAllLines;
DELIMITER //
CREATE FUNCTION getPriceAllLines(idOrderOrigin VARCHAR(20))
RETURNS DECIMAL(10,2)
  BEGIN

    RETURN (SELECT SUM(linePrice) FROM order_lines WHERE idOrder = idOrderOrigin);
  END
  //
DELIMITER ;

-- Generic triggers --

-- Trigger to update the total price of an order when a new order line is inserted
DROP TRIGGER IF EXISTS AF_update_total_price;
DELIMITER //
CREATE TRIGGER AF_update_total_price AFTER INSERT
ON ORDER_LINES
FOR EACH ROW
BEGIN
  UPDATE ORDERS
  SET total_price = total_price + NEW.linePrice
  WHERE id = NEW.idOrder;
END; //
DELIMITER ;

DROP TRIGGER IF EXISTS AU_update_total_price;
DELIMITER //
CREATE TRIGGER AU_update_total_price AFTER UPDATE
ON ORDER_LINES
FOR EACH ROW
BEGIN
  UPDATE ORDERS
  SET total_price = (SELECT getPriceAllLines(NEW.idOrder))
  WHERE id = NEW.idOrder;
END; //
DELIMITER ;

CALL insert_admin_users();