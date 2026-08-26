-- LuxuryStay owner-side accommodation management enhancements
-- Run this on an existing database if the application has not applied these
-- columns/tables automatically through ensureOwnerFeatureSchema().

ALTER TABLE properties
    ADD COLUMN IF NOT EXISTS city VARCHAR(100) NULL AFTER address,
    ADD COLUMN IF NOT EXISTS province VARCHAR(100) NULL AFTER city,
    ADD COLUMN IF NOT EXISTS contact_phone VARCHAR(30) NULL AFTER map_iframe,
    ADD COLUMN IF NOT EXISTS contact_email VARCHAR(150) NULL AFTER contact_phone,
    ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,8) NULL AFTER contact_email,
    ADD COLUMN IF NOT EXISTS longitude DECIMAL(11,8) NULL AFTER latitude,
    ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER status,
    ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL AFTER is_active;

ALTER TABLE rooms
    ADD COLUMN IF NOT EXISTS weekend_price DECIMAL(12,2) NULL AFTER price_per_night,
    ADD COLUMN IF NOT EXISTS inventory INT NOT NULL DEFAULT 1 AFTER max_guests;

ALTER TABLE property_images
    ADD COLUMN IF NOT EXISTS sort_order INT NOT NULL DEFAULT 0 AFTER is_primary;

CREATE TABLE IF NOT EXISTS room_amenities (
    room_id INT NOT NULL,
    amenity_id INT NOT NULL,
    PRIMARY KEY (room_id, amenity_id),
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
);
