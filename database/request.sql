CREATE TABLE Requests (
    request_id      INT PRIMARY KEY AUTO_INCREMENT,
    receiver_id     INT NOT NULL,
    food_id         INT NOT NULL,
    request_quantity INT NOT NULL CHECK (request_quantity > 0),
    request_time    DATETIME DEFAULT CURRENT_TIMESTAMP,
    status          ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (receiver_id) REFERENCES Receivers(receiver_id) ON DELETE CASCADE,
    FOREIGN KEY (food_id)     REFERENCES Food_Items(food_id)   ON DELETE CASCADE
);
