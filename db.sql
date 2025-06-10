CREATE DATABASE IF NOT EXISTS webshop;
USE webshop;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    total_price DECIMAL(10,2),
    customer_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    username VARCHAR(50),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

INSERT INTO users (username, password, email, is_admin) VALUES
('admin', 'admin123', 'admin@webshop.nl', TRUE),
('john', 'password', 'john@email.com', FALSE),
('jane', '123456', 'jane@email.com', FALSE),
('test', 'test', 'test@test.com', FALSE);

INSERT INTO products (name, description, price, stock, image_url) VALUES
('Laptop Pro X1', 'Krachtige laptop voor professionals', 1299.99, 10, 'laptop.jpg'),
('Gaming Muis RGB', 'High-end gaming muis met RGB verlichting', 79.99, 25, 'mouse.jpg'),
('Mechanical Keyboard', 'Premium mechanisch toetsenbord', 149.99, 15, 'keyboard.jpg'),
('Webcam 4K Ultra', '4K webcam voor streaming en videocalls', 199.99, 8, 'webcam.jpg'),
('Headset Pro', 'Professionele headset met noise cancelling', 249.99, 12, 'headset.jpg'),
('Monitor 27" 4K', 'Ultra HD monitor voor gaming en werk', 399.99, 6, 'monitor.jpg');

INSERT INTO comments (product_id, username, comment) VALUES
(1, 'john', 'Geweldige laptop, zeer tevreden!'),
(1, 'jane', 'Snel en betrouwbaar, aanrader!'),
(2, 'test', 'Mooie RGB effecten en goede grip'),
(3, 'admin', 'Beste toetsenbord dat ik ooit heb gehad');