-- Migration: recreate v_rides_complete and v_reservations_complete to include new columns (e.g. lat/lng)
-- Run: mysql -u <user> -p carpooling_db < migrations/20251028_update_views.sql

DROP VIEW IF EXISTS v_rides_complete;
CREATE OR REPLACE VIEW v_rides_complete AS
SELECT
  r.*,
  u.first_name AS driver_first_name,
  u.last_name  AS driver_last_name,
  u.email      AS driver_email,
  u.phone      AS driver_phone,
  u.photo_path AS driver_photo,
  v.plate_number,
  v.color      AS vehicle_color,
  v.brand      AS vehicle_brand,
  v.model      AS vehicle_model,
  v.year       AS vehicle_year,
  v.photo_path AS vehicle_photo,
  (r.total_seats - r.available_seats) AS reserved_seats
FROM rides r
INNER JOIN users u ON r.driver_id = u.id
INNER JOIN vehicles v ON r.vehicle_id = v.id;

DROP VIEW IF EXISTS v_reservations_complete;
CREATE OR REPLACE VIEW v_reservations_complete AS
SELECT
  res.*,
  u.first_name AS passenger_first_name,
  u.last_name  AS passenger_last_name,
  u.email      AS passenger_email,
  u.phone      AS passenger_phone,
  u.photo_path AS passenger_photo,
  r.*,
  d.first_name AS driver_first_name,
  d.last_name  AS driver_last_name,
  d.email      AS driver_email,
  d.phone      AS driver_phone,
  v.brand      AS vehicle_brand,
  v.model      AS vehicle_model,
  v.plate_number AS vehicle_plate
FROM reservations res
INNER JOIN users u ON res.passenger_id = u.id
INNER JOIN rides r ON res.ride_id = r.id
INNER JOIN users d ON r.driver_id = d.id
INNER JOIN vehicles v ON r.vehicle_id = v.id;
