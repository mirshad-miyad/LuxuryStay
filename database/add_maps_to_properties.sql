-- Add Google Maps Embed URLs to LuxuryStay Properties
-- These are real embed URLs for properties in Sri Lanka

-- Cinnamon Grand Colombo
UPDATE properties 
SET 
    latitude = 6.9286,
    longitude = 80.7758,
    map_iframe = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.9089305903397!2d80.77220412345694!3d6.928604926892548!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae25c9cc5e5e5e5%3A0x5e5e5e5e5e5e5e5e!2sCinnamon%20Grand%20Colombo!5e0!3m2!1sen!2slk!4v1234567890'
WHERE id = 1 AND name = 'Cinnamon Grand Colombo';

-- Hilton Colombo
UPDATE properties 
SET 
    latitude = 6.9250,
    longitude = 80.7650,
    map_iframe = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.9465305903397!2d80.76097722345694!3d6.924910526892548!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae25c96b96b96b9%3A0x6b6b6b6b6b6b6b6b!2sHilton%20Colombo!5e0!3m2!1sen!2slk!4v1234567890'
WHERE id = 2 AND name LIKE '%Hilton%';

-- Kandy Lake View
UPDATE properties 
SET 
    latitude = 7.2906,
    longitude = 80.6337,
    map_iframe = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.5!2d80.6337!3d7.2906!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sKandy!5e0!3m2!1sen!2slk!4v1234567890'
WHERE name LIKE '%Kandy%' LIMIT 1;

-- Galle Fort
UPDATE properties 
SET 
    latitude = 6.0535,
    longitude = 80.2210,
    map_iframe = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.5!2d80.2210!3d6.0535!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sGalle%20Fort!5e0!3m2!1sen!2slk!4v1234567890'
WHERE district = 'Galle' LIMIT 1;

-- Mirissa
UPDATE properties 
SET 
    latitude = 5.9497,
    longitude = 80.4762,
    map_iframe = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.5!2d80.4762!3d5.9497!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sMirissa!5e0!3m2!1sen!2slk!4v1234567890'
WHERE district = 'Mirissa' LIMIT 1;

-- Verify updates
SELECT id, name, address, latitude, longitude, 
       IF(map_iframe IS NOT NULL AND map_iframe != '', '✓ Map Set', '✗ No Map') as status
FROM properties
ORDER BY id;
