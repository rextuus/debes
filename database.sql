DROP TABLE payment_action;
DROP TABLE bank_account;
DROP TABLE paypal_account;
DROP TABLE loan;
DROP TABLE debt;
DROP TABLE exchange;
DROP TABLE transaction;
DROP TABLE user;

CREATE USER 'debes_user'@'localhost' IDENTIFIED BY 'password';
CREATE USER 'debes_user'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON * . * TO 'debes_user'@'%';
FLUSH PRIVILEGES;
CREATE DATABASE debes;
