CREATE DATABASE test_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE test_api;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (name, price, description) VALUES
('Телефон', 25000, 'Смартфон с отличной камерой'),
('Ноутбук', 78000, 'Игровой ноутбук'),
('Планшет', 32000, 'Для учебы и фильмов'),
('Монитор', 15000, '27 дюймов, IPS'),
('Мышь', 1500, 'Беспроводная мышь'),
('Клавиатура', 2500, 'Механическая'),
('Наушники', 4000, 'Bluetooth'),
('Колонка', 3500, 'Портативная'),
('Телевизор', 52000, '4K Smart TV'),
('Часы', 12000, 'Умные часы');
