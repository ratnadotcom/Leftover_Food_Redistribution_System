CREATE TABLE Food_Items (
    food_id         INT PRIMARY KEY AUTO_INCREMENT,
    donor_id        INT NOT NULL,
    food_name       VARCHAR(100) NOT NULL,
    quantity        INT NOT NULL CHECK (quantity > 0),
    unit            VARCHAR(20) DEFAULT 'kg',       -- kg, packets, plates
    prepared_time   DATETIME NOT NULL,
    expiry_time     DATETIME NOT NULL,
    status          ENUM('available','reserved','distributed') NOT NULL DEFAULT 'available',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES Donors(donor_id) ON DELETE CASCADE
);
