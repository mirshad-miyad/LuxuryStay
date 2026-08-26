-- Replace the selected accommodation cover images.
UPDATE property_images pi
JOIN properties p ON p.id = pi.property_id
SET pi.image_path = CASE p.name
    WHEN 'Galle Fort Courtyard Hotel' THEN 'uploads/properties/galle-fort-courtyard-hotel.jpg'
    WHEN 'Batticaloa Lagoon Villa' THEN 'uploads/properties/batticaloa-lagoon-villa-v2.jpg'
    WHEN 'Jaffna Lagoon House' THEN 'uploads/properties/jaffna-lagoon-house.jpg'
END
WHERE pi.is_primary = 1
  AND p.name IN ('Galle Fort Courtyard Hotel', 'Batticaloa Lagoon Villa', 'Jaffna Lagoon House');
