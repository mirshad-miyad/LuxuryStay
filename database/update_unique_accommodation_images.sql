-- Assign a unique primary image to every active accommodation.
UPDATE property_images pi
JOIN properties p ON p.id = pi.property_id
SET pi.image_path = CASE p.name
    WHEN '98 Acres Resort Ella' THEN 'uploads/properties/stock-mountain-retreat.jpg'
    WHEN 'Jetwing Lighthouse Galle' THEN 'uploads/properties/stock-city-hotel.jpg'
    WHEN 'Galle Fort Courtyard Hotel' THEN 'uploads/properties/stock-beach-villa.jpg'
    WHEN 'Batticaloa Lagoon Villa' THEN 'uploads/properties/batticaloa-lagoon-villa-v2.jpg'
    ELSE pi.image_path
END
WHERE pi.is_primary = 1
  AND p.name IN ('98 Acres Resort Ella', 'Jetwing Lighthouse Galle', 'Galle Fort Courtyard Hotel', 'Batticaloa Lagoon Villa');
