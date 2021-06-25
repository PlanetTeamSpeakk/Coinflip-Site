# Coinflip-Site
Coinflip site for CS class.

## Running it yourself
To run this site yourself, you need to set the `mysqli.default_port`, `mysqli.default_user` and `mysqli.default_password` variables in your php.ini file.  
Then run the following commands on MySQL to create the necessary database and tables:
- `CREATE DATABASE coinflip;`
- `USE coinflip;`
- `CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY NOT NULL, email VARCHAR(255) NOT NULL, username VARCHAR(16) NOT NULL, password CHAR(60) NOT NULL, balance BIGINT DEFAULT 100000 NOT NULL, created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, UNIQUE (email), UNIQUE (username));`
- `CREATE TABLE sessions (id VARCHAR(255) PRIMARY KEY NOT NULL, user INT NOT NULL, expires TIMESTAMP NOT NULL);`
- `CREATE TABLE coinflips (id INT AUTO_INCREMENT PRIMARY KEY NOT NULL, user INT NOT NULL, side ENUM("HEADS", "TAILS"), bet BIGINT NOT NULL, created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX (user));`
- `CREATE TABLE transactions (id INT AUTO_INCREMENT PRIMARY KEY NOT NULL, user INT NOT NULL, opponent INT NOT NULL, side ENUM("HEADS", "TAILS") NOT NULL, bet BIGINT NOT NULL, won TINYINT(1) NOT NULL, date TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL);`
