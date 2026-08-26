-- Rebalance active accommodation room rates to LKR 25,000–150,000.
-- Higher rates are assigned to properties with premium facilities such as
-- private suites, pools, spas, beach access, and resort-level amenities.

UPDATE rooms r
JOIN properties p ON p.id = r.property_id
SET r.price_per_night = CASE
    -- Premium facilities: pool, spa, beach access, suites
    WHEN p.name = 'Cape Weligama' THEN 150000
    WHEN p.name = 'Heritance Kandalama' AND r.name LIKE '%Suite%' THEN 150000
    WHEN p.name = 'Heritance Kandalama' THEN 110000
    WHEN p.name = 'Jetwing Lighthouse Galle' THEN 115000
    WHEN p.name = 'Cinnamon Grand Colombo' AND r.name LIKE '%Suite%' THEN 120000
    WHEN p.name = 'Cinnamon Grand Colombo' THEN 75000

    -- Upscale villas and resorts
    WHEN p.name = 'Mirissa Cliffside Villa' THEN 95000
    WHEN p.name = 'Weligama Bay Boutique Stay' THEN 85000
    WHEN p.name = 'Galle Fort Courtyard Hotel' THEN 80000
    WHEN p.name = 'Batticaloa Lagoon Villa' THEN 80000
    WHEN p.name = 'Colombo Skyline Suites' THEN 75000
    WHEN p.name = 'Wilpattu Wilderness Camp' THEN 75000
    WHEN p.name = 'Dambulla Lakeview Resort' THEN 70000
    WHEN p.name = 'Kalpitiya Kite Beach Resort' THEN 70000
    WHEN p.name = 'Knuckles Mountain Retreat' THEN 65000
    WHEN p.name = '98 Acres Resort Ella' THEN 55000
    WHEN p.name = 'Haputale Tea Garden Villa' THEN 55000

    -- Comfortable guest houses and eco stays
    WHEN p.name = 'Ratnapura Rainforest Lodge' THEN 45000
    WHEN p.name = 'Jaffna Lagoon House' THEN 35000
    WHEN p.name = 'Mirissa Hills Guest House' THEN 25000
    ELSE LEAST(150000, GREATEST(25000, r.price_per_night))
END
WHERE r.status = 'active';

UPDATE rooms
SET weekend_price = LEAST(150000, price_per_night + 10000)
WHERE status = 'active';
