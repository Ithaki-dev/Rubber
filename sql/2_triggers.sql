-- =========================================
-- TRIGGERS - CARPOOLING SYSTEM
-- Gestionan automáticamente los asientos disponibles
-- =========================================

USE carpooling_db;

-- =========================================
-- TRIGGER 1: Validar asientos antes de insertar
-- =========================================

DROP TRIGGER IF EXISTS before_reservation_insert;

DELIMITER $$

CREATE TRIGGER before_reservation_insert
BEFORE INSERT ON reservations
FOR EACH ROW
BEGIN
    DECLARE available INT;
    
    SELECT available_seats INTO available
    FROM rides
    WHERE id = NEW.ride_id;
    
    IF NEW.seats_requested > available THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No hay suficientes asientos disponibles';
    END IF;
END$$

DELIMITER ;

-- =========================================
-- TRIGGER 2: Actualizar asientos después de insertar
-- =========================================

DROP TRIGGER IF EXISTS after_reservation_insert;

DELIMITER $$

CREATE TRIGGER after_reservation_insert
AFTER INSERT ON reservations
FOR EACH ROW
BEGIN
    IF NEW.status = 'pending' OR NEW.status = 'accepted' THEN
        UPDATE rides
        SET available_seats = available_seats - NEW.seats_requested
        WHERE id = NEW.ride_id;
    END IF;
END$$

DELIMITER ;

-- =========================================
-- TRIGGER 3: Actualizar asientos al cambiar estado
-- =========================================

DROP TRIGGER IF EXISTS after_reservation_update;

DELIMITER $$

CREATE TRIGGER after_reservation_update
AFTER UPDATE ON reservations
FOR EACH ROW
BEGIN
    -- Si se cancela o rechaza, devolver los asientos
    IF (OLD.status = 'pending' OR OLD.status = 'accepted') 
       AND (NEW.status = 'cancelled' OR NEW.status = 'rejected') THEN
        UPDATE rides
        SET available_seats = available_seats + OLD.seats_requested
        WHERE id = OLD.ride_id;
    END IF;
END$$

DELIMITER ;
