CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;
CREATE TABLE IF NOT EXISTS books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(225) UNIQUE NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    available_quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    phone VARCHAR(20) UNIQUE,
    email VARCHAR(255) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS issued_books (
    issue_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE,
    status ENUM('Issued', 'Returned', 'Renewed', 'Overdue') DEFAULT 'Issued',
    fine_amount DECIMAL(10, 2) DEFAULT 0.00, -- New column for fines
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE
);
INSERT INTO books (title, author, isbn, quantity, available_quantity) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 5, 5),
('1984', 'George Orwell', '978-0451524935', 3, 3),
('To Kill a Mockingbird', 'Harper Lee', '978-0446310789', 7, 7)
ON DUPLICATE KEY UPDATE title=title;

INSERT INTO members (name, address, phone, email) VALUES
('Alice Wonderland', '123 Rabbit Hole, Wonderland', '111-222-3333', 'alice@example.com'),
('Bob The Builder', '456 Construction Site, Builderton', '444-555-6666', 'bob@example.com')
ON DUPLICATE KEY UPDATE name=name;
