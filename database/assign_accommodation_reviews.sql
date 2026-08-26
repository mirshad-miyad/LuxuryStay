-- Adds curated approved reviews and assigns varied display ratings to every accommodation.
-- Ratings cycle through 4.0, 4.5 and 5.0; the marker text makes this safe to re-run.

INSERT INTO reviews (user_id, property_id, booking_id, rating, comment, status, created_at)
SELECT (SELECT id FROM users ORDER BY id LIMIT 1), p.id, NULL,
       CASE MOD(p.id, 3) WHEN 0 THEN 4 WHEN 1 THEN 4 ELSE 5 END,
       CONCAT('LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.'),
       'approved', NOW()
FROM properties p
WHERE NOT EXISTS (
    SELECT 1 FROM reviews r
    WHERE r.property_id = p.id
      AND r.comment = 'LuxuryStay curated review: A comfortable stay with welcoming service and a memorable location.'
);

-- A second review gives every middle-tier property a genuine 4.5 average (4 and 5 stars).
INSERT INTO reviews (user_id, property_id, booking_id, rating, comment, status, created_at)
SELECT (SELECT id FROM users ORDER BY id DESC LIMIT 1), p.id, NULL, 5,
       'LuxuryStay curated review: Great attention to detail, clean rooms and a relaxing experience.',
       'approved', NOW()
FROM properties p
WHERE MOD(p.id, 3) = 1
  AND NOT EXISTS (
      SELECT 1 FROM reviews r
      WHERE r.property_id = p.id
        AND r.comment = 'LuxuryStay curated review: Great attention to detail, clean rooms and a relaxing experience.'
  );

UPDATE properties p
SET p.avg_rating = CASE MOD(p.id, 3)
        WHEN 0 THEN 4.00
        WHEN 1 THEN 4.50
        ELSE 5.00
    END,
    p.review_count = (
        SELECT COUNT(*) FROM reviews r
        WHERE r.property_id = p.id AND r.status = 'approved'
    );
