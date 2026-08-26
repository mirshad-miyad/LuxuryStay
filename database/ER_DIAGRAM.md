 LuxuryStay ER Diagram Explanation

## Entity Relationships

```
admins (1) ─── manages ───> system (properties, users, bookings)

owners (1) ───< owns >─── (N) properties
users (1) ───< makes >─── (N) bookings

properties (1) ───< has >─── (N) rooms
properties (1) ───< has >─── (N) property_images
properties (N) ───< linked >─── (N) amenities [via property_amenities]

rooms (1) ───< has >─── (N) room_images
rooms (1) ───< has >─── (N) room_availability
rooms (1) ───< receives >─── (N) bookings

users (1) ───< writes >─── (N) reviews
properties (1) ───< receives >─── (N) reviews

bookings ─── references ───> users, rooms, properties
```

## Tables Summary

| Table | Purpose | Key Relationships |
|-------|---------|-------------------|
| admins | System administrators | Standalone auth |
| owners | Property managers | → properties |
| users | Customers | → bookings, reviews, recently_viewed |
| properties | Accommodation listings | owner_id → owners |
| rooms | Bookable units | property_id → properties |
| bookings | Reservations | user_id, room_id, property_id |
| amenities | Facility catalog | M:N with properties |
| property_images | Gallery photos | property_id |
| room_images | Room photos | room_id |
| room_availability | Calendar blocks | room_id |
| reviews | Guest feedback | user_id, property_id |
| notifications | Alerts | user/owner/admin |
| offers | Promotions | property_id (optional) |
| recently_viewed | User browsing history | user_id, property_id |
| featured_destinations | Homepage highlights | Standalone |
| password_resets | Forgot password flow | email + role |

## Cardinality

- One **owner** can have many **properties**
- One **property** can have many **rooms**
- One **room** can have many **bookings** (non-overlapping dates enforced in application)
- One **user** can have many **bookings**
- One **property** can have many **amenities** (many-to-many)

## Indexes (Recommended for Production)

- `properties(district, status, property_type)`
- `bookings(room_id, check_in, check_out, status)`
- `reviews(property_id, status)`
