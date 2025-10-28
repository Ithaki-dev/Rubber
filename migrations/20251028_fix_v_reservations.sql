-- Migration: recreate v_reservations_complete with explicit columns to avoid syntax errors and duplicate names
-- Run: mysql -u <user> -p carpooling_db < migrations/20251028_fix_v_reservations.sql

DROP VIEW IF EXISTS v_reservations_complete;
CREATE OR REPLACE VIEW v_reservations_complete AS
SELECT
  res.*, -- reservation fields (id, passenger_id, ride_id, seats_requested, status, total_cost, created_at, updated_at)
  u.first_name AS passenger_first_name,
  u.last_name  AS passenger_last_name,
  u.email      AS passenger_email,
  u.phone      AS passenger_phone,
  u.photo_path AS passenger_photo,

  -- Ride fields (explicit, avoid selecting r.* to prevent duplicate column names with res.*)
  r.ride_name,
  r.departure_location,
  r.arrival_location,
  r.ride_date,
  r.ride_time,
  r.cost_per_seat,
  r.available_seats,
  r.total_seats,
  r.departure_lat,
  r.departure_lng,
  r.arrival_lat,
  r.arrival_lng,

  -- Driver and vehicle info
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
