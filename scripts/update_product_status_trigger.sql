DELIMITER $$

CREATE TRIGGER after_transaction_item_insert
AFTER INSERT ON transaction_items
FOR EACH ROW
BEGIN
    DECLARE product_quantity INT;

    -- Get the current quantity of the product
    SELECT quantity INTO product_quantity
    FROM products
    WHERE product_id = NEW.product_id;

    -- If the quantity is 0, update the status to 'Out of Stock'
    IF product_quantity = 0 THEN
        UPDATE products
        SET status = 'Out of Stock'
        WHERE product_id = NEW.product_id;
    END IF;
END$$

DELIMITER ;
