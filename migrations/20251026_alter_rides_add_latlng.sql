-- Migration: add lat/lng columns to existing `rides` table for map support
-- Up: add columns
ALTER TABLE `rides`
  ADD COLUMN `departure_lat` DECIMAL(10,7) NULL AFTER `departure_location`,
  ADD COLUMN `departure_lng` DECIMAL(10,7) NULL AFTER `departure_lat`,
  ADD COLUMN `arrival_lat` DECIMAL(10,7) NULL AFTER `arrival_location`,
  ADD COLUMN `arrival_lng` DECIMAL(10,7) NULL AFTER `arrival_lat`;

-- Down: remove columns (rollback)
-- ALTER TABLE `rides`
--   DROP COLUMN `departure_lat`,
--   DROP COLUMN `departure_lng`,
--   DROP COLUMN `arrival_lat`,
--   DROP COLUMN `arrival_lng`;

-- Notes:
-- 1) Run the Up section to apply: mysql -uuser -p dbname < migrations/20251026_alter_rides_add_latlng.sql
-- 2) If you use a migration tool, split up the Up and Down into your tool's format.
-- 3) Backup the database before running this migration (use scripts/backup_db.sh in this repo).
