-- Add new featured and regular properties to LuxuryStay
-- This adds 5 new properties to give you more options

INSERT INTO properties (owner_id, name, description, address, city, district, property_type, map_iframe, contact_phone, contact_email, latitude, longitude, policies, featured, status, is_active) VALUES

-- New Featured Properties
(1, 'Unawatuna Beach Resort', 'Beachfront luxury resort with pristine sand and crystal-clear waters. Perfect for beach lovers and families seeking a tropical paradise.', 'Beach Road, Unawatuna, Galle', 'Unawatuna', 'Galle', 'Resort', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.5!2d80.2762!3d6.0250!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sUnawatuna%20Beach!5e0!3m2!1sen!2slk!4v1234567890', '+94777890123', 'info@unawatuna.lk', 6.0250, 80.2762, 'Check-in 3PM, Check-out 11AM. No pets allowed.', 1, 'approved', 1),

(1, 'Sigiriya Rock Palace Boutique', 'Ancient heritage meets modern luxury near the iconic Sigiriya Rock. Spectacular views and cultural immersion.', 'Dambulla Road, Sigiriya', 'Sigiriya', 'Matale', 'Hotel', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3950.0!2d80.7617!3d7.9421!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sSigiriya!5e0!3m2!1sen!2slk!4v1234567890', '+94771234589', 'bookings@sigiriya.lk', 7.9421, 80.7617, 'Check-in 2PM, Check-out 12PM. Early check-in available on request.', 1, 'approved', 1),

-- Regular Properties
(1, 'Ella Cloud Nine Villa', 'Romantic hillside villa with misty mountain views. Ideal for couples and nature enthusiasts.', 'High Country Road, Ella', 'Ella', 'Badulla', 'Villa', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3959.5!2d81.0500!3d6.8667!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sElla!5e0!3m2!1sen!2slk!4v1234567890', '+94776543210', 'contact@ellaclouds.lk', 6.8667, 81.0500, 'Check-in 3PM, Check-out 11AM.', 0, 'approved', 1),

(1, 'Negombo Lagoon Escape', 'Serene lagoon-view property with water sports and fresh seafood. Perfect for adventure seekers.', 'Lagoon Street, Negombo', 'Negombo', 'Gampaha', 'Resort', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3948.0!2d79.8397!3d7.2081!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sNegombo%20Lagoon!5e0!3m2!1sen!2slk!4v1234567890', '+94777654321', 'stay@negombolagoon.lk', 7.2081, 79.8397, 'Check-in 2PM, Check-out 11AM.', 0, 'approved', 1),

(1, 'Anuradhapura Heritage Lodge', 'Historic boutique hotel near ancient Buddhist temples. Experience rich cultural heritage and spiritual peace.', 'Temple Road, Anuradhapura', 'Anuradhapura', 'Anuradhapura', 'Guest House', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3947.0!2d80.6137!3d8.3352!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sAnuradhapura!5e0!3m2!1sen!2slk!4v1234567890', '+94771234567', 'reservations@anuradhaherita.lk', 8.3352, 80.6137, 'Check-in 1PM, Check-out 12PM. Vegetarian meals available.', 0, 'approved', 1);

-- Verify new properties
SELECT id, name, district, featured, status FROM properties ORDER BY id DESC LIMIT 10;
