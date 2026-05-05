CREATE TABLE deliveries (
    delivery_id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT,
    delivery_status ENUM('pending','on_the_way','delivered') DEFAULT 'pending',
    FOREIGN KEY (request_id) REFERENCES requests(request_id)
);
