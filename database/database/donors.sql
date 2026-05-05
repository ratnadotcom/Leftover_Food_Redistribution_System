CREATE TABLE Donors (
    donor_id        INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    name            VARCHAR(100) NOT NULL,
    contact         VARCHAR(15) NOT NULL UNIQUE,
    email           VARCHAR(100) NOT NULL UNIQUE,
    address         TEXT,
    donor_type      ENUM('restaurant','individual','event') NOT NULL DEFAULT 'individual',
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);
