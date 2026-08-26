-- Adds six bookable accommodation listings to an existing LuxuryStay database.
-- Re-running this script is safe: records are created only when their names do not exist.

INSERT INTO properties (owner_id, name, description, address, city, province, district, property_type, contact_phone, contact_email, latitude, longitude, status, is_active, avg_rating, review_count, created_at, updated_at)
SELECT (SELECT id FROM owners ORDER BY id LIMIT 1), listing.name, listing.description, listing.address, listing.city, listing.province, listing.district, listing.property_type, listing.contact_phone, listing.contact_email, listing.latitude, listing.longitude, 'approved', 1, 0, 0, NOW(), NOW()
FROM (
    SELECT 'Galle Fort Courtyard Hotel' AS name, 'A refined heritage hotel inside Galle Fort, with elegant rooms and a peaceful inner courtyard.' AS description, '18, Church Street, Galle Fort' AS address, 'Galle' AS city, 'Southern' AS province, 'Galle' AS district, 'Hotel' AS property_type, '+94 91 224 5501' AS contact_phone, 'stay@gallecourtyard.lk' AS contact_email, 6.0261 AS latitude, 80.2170 AS longitude
    UNION ALL SELECT 'Mirissa Cliffside Villa', 'An intimate ocean-view villa with an infinity pool, tropical gardens and whale-watching access.', '42, Harbour Road, Mirissa', 'Mirissa', 'Southern', 'Matara', 'Villa', '+94 41 226 8190', 'hello@mirissacliffside.lk', 5.9491, 80.4716
    UNION ALL SELECT 'Wilpattu Wilderness Camp', 'A comfortable safari camp on the edge of Wilpattu, designed for unforgettable wildlife escapes.', '25, Wilpattu Junction, Nochchiyagama', 'Wilpattu', 'North Western', 'Puttalam', 'Resort', '+94 32 225 7144', 'reservations@wilpattucamp.lk', 8.4554, 80.0641
    UNION ALL SELECT 'Jaffna Lagoon House', 'A welcoming lagoon-side guest house where northern Sri Lankan culture and comfort meet.', '61, Lagoon View Road, Jaffna', 'Jaffna', 'Northern', 'Jaffna', 'Guest House', '+94 21 222 4678', 'stay@jaffnalagoon.lk', 9.6615, 80.0255
    UNION ALL SELECT 'Weligama Bay Boutique Stay', 'A chic coastal retreat just steps from Weligama Bay, ideal for surf trips and relaxed getaways.', '8, Bay View Lane, Weligama', 'Weligama', 'Southern', 'Matara', 'Hotel', '+94 41 225 1092', 'book@weligamabay.lk', 5.9742, 80.4298
    UNION ALL SELECT 'Knuckles Mountain Retreat', 'A secluded highland retreat with misty mountain views, guided hikes and fireside dining.', '16, Riverston Road, Matale', 'Matale', 'Central', 'Matale', 'Resort', '+94 66 224 7810', 'escape@knucklesretreat.lk', 7.5312, 80.7946
    UNION ALL SELECT 'Kalpitiya Kite Beach Resort', 'A breezy beach resort overlooking Kalpitiya Lagoon, perfect for kite-surfing and sunset escapes.', '34, Lagoon Drive, Kalpitiya', 'Kalpitiya', 'North Western', 'Puttalam', 'Resort', '+94 32 226 5090', 'stay@kalpitiyakite.lk', 8.2332, 79.7667
    UNION ALL SELECT 'Haputale Tea Garden Villa', 'A cosy villa surrounded by tea gardens, with valley views and freshly prepared local cuisine.', '10, Station Road, Haputale', 'Haputale', 'Uva', 'Badulla', 'Villa', '+94 57 226 4300', 'welcome@haputalevilla.lk', 6.7659, 80.9518
    UNION ALL SELECT 'Colombo Skyline Suites', 'Contemporary city suites with skyline views, rooftop dining and easy access to Colombo attractions.', '72, Galle Road, Colombo 03', 'Colombo', 'Western', 'Colombo', 'Hotel', '+94 11 245 7012', 'reservations@colomboskyline.lk', 6.9061, 79.8539
    UNION ALL SELECT 'Dambulla Lakeview Resort', 'A serene lakeside resort near Dambulla, offering spacious rooms, birdwatching and sunset dining.', '29, Kandalama Road, Dambulla', 'Dambulla', 'Central', 'Matale', 'Resort', '+94 66 228 4015', 'stay@dambullalakeview.lk', 7.8742, 80.6511
    UNION ALL SELECT 'Ratnapura Rainforest Lodge', 'An eco-friendly lodge surrounded by rainforest greenery, with guided walks and quiet river views.', '45, Forest Edge Road, Ratnapura', 'Ratnapura', 'Sabaragamuwa', 'Ratnapura', 'Guest House', '+94 45 223 6781', 'welcome@ratnapuralodge.lk', 6.6828, 80.3992
    UNION ALL SELECT 'Batticaloa Lagoon Villa', 'A relaxed private villa overlooking Batticaloa Lagoon, with fresh seafood and peaceful water views.', '14, Lagoon Park, Batticaloa', 'Batticaloa', 'Eastern', 'Batticaloa', 'Villa', '+94 65 222 9154', 'book@batticaloalagoon.lk', 7.7290, 81.6976
) AS listing
WHERE NOT EXISTS (SELECT 1 FROM properties p WHERE p.name = listing.name);

INSERT INTO property_images (property_id, image_path, is_primary, sort_order)
SELECT p.id, image.image_path, 1, 0
FROM properties p
JOIN (
    SELECT 'Galle Fort Courtyard Hotel' AS name, 'uploads/properties/galle-fort-courtyard-hotel.jpg' AS image_path
    UNION ALL SELECT 'Mirissa Cliffside Villa', 'uploads/properties/3/img_6a4a9d040aabc4.05966654.jpg'
    UNION ALL SELECT 'Wilpattu Wilderness Camp', 'uploads/properties/9/img_6a4a9f0b799364.97217717.jpg'
    UNION ALL SELECT 'Jaffna Lagoon House', 'uploads/properties/jaffna-lagoon-house.jpg'
    UNION ALL SELECT 'Weligama Bay Boutique Stay', 'uploads/properties/12/img_6a4a9ffa25b688.08760128.jpg'
    UNION ALL SELECT 'Knuckles Mountain Retreat', 'uploads/properties/13/img_6a4b06f55657e9.01726926.jpg'
    UNION ALL SELECT 'Kalpitiya Kite Beach Resort', 'uploads/properties/14/img_6a4b0eb6d82814.84705688.jpg'
    UNION ALL SELECT 'Haputale Tea Garden Villa', 'uploads/properties/15/img_6a4b2cb4eba5b9.39600898.jpg'
    UNION ALL SELECT 'Colombo Skyline Suites', 'uploads/properties/10/img_6a4a99c9c2f0c3.04528371.jpg'
    UNION ALL SELECT 'Dambulla Lakeview Resort', 'uploads/properties/1/images (1).jpg'
    UNION ALL SELECT 'Ratnapura Rainforest Lodge', 'uploads/properties/1/images (2).jpg'
    UNION ALL SELECT 'Batticaloa Lagoon Villa', 'uploads/properties/batticaloa-lagoon-villa-v2.jpg'
) AS image ON image.name = p.name
WHERE NOT EXISTS (SELECT 1 FROM property_images pi WHERE pi.property_id = p.id AND pi.is_primary = 1);

INSERT INTO rooms (property_id, name, description, price_per_night, weekend_price, max_guests, inventory, bed_type, status)
SELECT p.id, 'Deluxe Room', 'Comfortable stay with premium amenities.', price.price_per_night, price.price_per_night + 8000, 2, 2, 'King', 'active'
FROM properties p
JOIN (
    SELECT 'Galle Fort Courtyard Hotel' AS name, 285000 AS price_per_night
    UNION ALL SELECT 'Mirissa Cliffside Villa', 240000
    UNION ALL SELECT 'Wilpattu Wilderness Camp', 195000
    UNION ALL SELECT 'Jaffna Lagoon House', 150000
    UNION ALL SELECT 'Weligama Bay Boutique Stay', 225000
    UNION ALL SELECT 'Knuckles Mountain Retreat', 205000
    UNION ALL SELECT 'Kalpitiya Kite Beach Resort', 185000
    UNION ALL SELECT 'Haputale Tea Garden Villa', 170000
    UNION ALL SELECT 'Colombo Skyline Suites', 265000
    UNION ALL SELECT 'Dambulla Lakeview Resort', 210000
    UNION ALL SELECT 'Ratnapura Rainforest Lodge', 155000
    UNION ALL SELECT 'Batticaloa Lagoon Villa', 230000
) AS price ON price.name = p.name
WHERE NOT EXISTS (SELECT 1 FROM rooms r WHERE r.property_id = p.id);
