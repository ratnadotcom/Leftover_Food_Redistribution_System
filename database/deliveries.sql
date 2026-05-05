CREATE TABLE Deliveries (
    delivery_id     INT PRIMARY KEY AUTO_INCREMENT,
    request_id      INT NOT NULL UNIQUE,            -- 1:1 with Requests
    delivery_person VARCHAR(100) NOT NULL,
    contact         VARCHAR(15),
    pickup_time     DATETIME,
    delivery_time   DATETIME,
    status          ENUM('assigned','in_progress','completed') NOT NULL DEFAULT 'assigned',
    FOREIGN KEY (request_id) REFERENCES Requests(request_id) ON DELETE CASCADE
);
