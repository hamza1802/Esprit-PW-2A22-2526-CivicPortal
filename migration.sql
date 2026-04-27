-- 1. Ensure `users` table `role` is not null and has a default value.
ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'citizen';

-- 2. Modify `transport_type` table to migrate photo_url to BLOB
ALTER TABLE transport_type ADD COLUMN image_blob LONGBLOB;
ALTER TABLE transport_type ADD COLUMN image_mime VARCHAR(50);
ALTER TABLE transport_type DROP COLUMN photo_url;

-- 3. The `addTicket` INSERT query reference (Updated to use user_id):
-- INSERT INTO ticket (user_id, ref, citizenName, idTrajet, issuedAt, status) VALUES (?, ?, ?, ?, NOW(), 'Valid');

-- 4. The `addTransport` INSERT query reference:
-- INSERT INTO transport (name, type, capacity, status, idTransportType) VALUES (?, ?, ?, ?, ?);
