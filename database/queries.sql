-- 1. Show all available food
SELECT * 
FROM food_items 
WHERE status = 'available';

-- 2. Show donor with their food items
SELECT u.name AS donor_name, f.food_name, f.quantity, f.unit
FROM users u
JOIN food_items f ON u.user_id = f.donor_id;

-- 3. Show all receiver requests
SELECT r.request_id, u.name AS receiver_name, f.food_name, r.status
FROM requests r
JOIN users u ON r.receiver_id = u.user_id
JOIN food_items f ON r.food_id = f.food_id;

-- 4. Count total food items
SELECT COUNT(*) AS total_food_items 
FROM food_items;

-- 5. Group food by name (aggregation)
SELECT food_name, COUNT(*) AS total_quantity
FROM food_items
GROUP BY food_name;

-- 6. Show pending requests
SELECT * 
FROM requests 
WHERE status = 'pending';

-- 7. Show approved requests
SELECT * 
FROM requests 
WHERE status = 'approved';

-- 8. Show expired food
SELECT food_name, expiry_time
FROM food_items
WHERE expiry_time < NOW();

-- 9. Update food status (example)
UPDATE food_items 
SET status = 'requested' 
WHERE food_id = 1;

-- 10. Delete a food item (example)
DELETE FROM food_items 
WHERE food_id = 2;
