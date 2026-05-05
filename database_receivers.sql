CREATE TABLE Receivers (
    receiver_id     INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    name            VARCHAR(100) NOT NULL,
    contact         VARCHAR(15) NOT NULL UNIQUE,
    email           VARCHAR(100) NOT NULL UNIQUE,
    address         TEXT,
    receiver_type   ENUM('NGO','individual') NOT NULL DEFAULT 'individual',
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

