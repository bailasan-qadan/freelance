CREATE TABLE users (
    user_id VARCHAR(10) PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(10) NOT NULL,
    country VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    role ENUM('Client', 'Freelancer') NOT NULL,
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    profile_photo VARCHAR(255),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    Programming VARCHAR(100),
    Design VARCHAR(100),
    Writing VARCHAR(100),
    Marketing VARCHAR(100)
);

CREATE TABLE services (
    service_id VARCHAR(10) PRIMARY KEY,
    freelancer_id VARCHAR(10) NOT NULL,
    title VARCHAR(200) NOT NULL,
    category VARCHAR(100) NOT NULL,
    subcategory VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    delivery_time INT NOT NULL,
    revisions_included INT NOT NULL,
    image_1 VARCHAR(255) NOT NULL,
    image_2 VARCHAR(255),
    image_3 VARCHAR(255),
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    featured_status ENUM('Yes', 'No') NOT NULL DEFAULT 'No',
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (freelancer_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE orders (
    order_id VARCHAR(10) PRIMARY KEY,
    client_id VARCHAR(10) NOT NULL,
    freelancer_id VARCHAR(10) NOT NULL,
    service_id VARCHAR(10) NOT NULL,
    service_title VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    delivery_time INT NOT NULL,
    revisions_included INT NOT NULL,
    requirements TEXT NOT NULL,
    deliverable_notes TEXT,
    status ENUM(
        'Pending',
        'In Progress',
        'Delivered',
        'Completed',
        'Revision Requested',
        'Cancelled'
    ) NOT NULL DEFAULT 'Pending',
    payment_method VARCHAR(50) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expected_delivery DATE NOT NULL,
    completion_date TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (freelancer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE RESTRICT
);

CREATE TABLE revision_requests (
    revision_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(10) NOT NULL,
    revision_notes TEXT NOT NULL,
    revision_file VARCHAR(255),
    request_status ENUM('Pending', 'Accepted', 'Rejected') NOT NULL DEFAULT 'Pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    response_date TIMESTAMP NULL DEFAULT NULL,
    freelancer_response TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

CREATE TABLE file_attachments (
    file_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(10) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    file_type ENUM('requirement', 'deliverable', 'revision') NOT NULL,
    upload_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);


-- USERS
INSERT INTO users (
    user_id, first_name, last_name, email, password,
    phone, country, city, role
) VALUES
(
    '1000000001', 'Ali', 'Hassan',
    'ali.client@example.com',
    '$2y$10$clienthash',
    '0591234567', 'Palestine', 'Ramallah', 'Client'
),
(
    '1000000002', 'Sara', 'Khaled',
    'sara.freelancer@example.com',
    '$2y$10$freelancerhash',
    '0597654321', 'Palestine', 'Nablus', 'Freelancer'
);


-- CATEGORIES
INSERT INTO categories (category_name) VALUES
('Web Development'),
('Graphic Design'),
('Writing & Translation'),
('Digital Marketing'),
('Video & Animation');


-- SERVICES
INSERT INTO services (
    service_id, freelancer_id, title, category, subcategory,
    description, price, delivery_time, revisions_included, image_1
) VALUES
(
    '2000000001',
    '1000000002',
    'Professional Logo Design',
    'Graphic Design',
    'Logo Design',
    'I will design a professional and modern logo for your business.',
    150.00,
    5,
    3,
    '/uploads/services/2000000001/image_01.jpg'
),
(
    '2000000002',
    '1000000002',
    'Responsive Website Development',
    'Web Development',
    'Frontend Development',
    'I will build a responsive website using HTML and CSS.',
    500.00,
    10,
    5,
    '/uploads/services/2000000002/image_01.jpg'
);
