CREATE DATABASE IF NOT EXISTS database1;
USE database1;

CREATE TABLE branch (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    max_classes INT
);

CREATE TABLE module (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    branch_id INT,
    FOREIGN KEY (branch_id) REFERENCES branch(id)
);

CREATE TABLE teacher (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255)
);

CREATE TABLE module_teacher (
    id INT PRIMARY KEY AUTO_INCREMENT,
    module_id INT,
    teacher_id INT,
    annee_universit VARCHAR(255),
    FOREIGN KEY (module_id) REFERENCES module(id),
    FOREIGN KEY (teacher_id) REFERENCES teacher(id)
);

CREATE TABLE salle (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255)
);

CREATE TABLE periode (
    id INT PRIMARY KEY AUTO_INCREMENT,
    debut_time TIME,
    fin_time TIME,
    name_periode VARCHAR(255),
    periode_category INT
);

CREATE TABLE type_seance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255)
);

CREATE TABLE schedule (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch_id INT,
    salle_id INT,
    periode_id INT,
    type_seance_id INT,
    module_id INT,
    semester INT,
    Monday BOOLEAN,
    Tuesday BOOLEAN,
    Wednesday BOOLEAN,
    Thursday BOOLEAN,
    Friday BOOLEAN,
    Saturday BOOLEAN,
    year INT,
    FOREIGN KEY (branch_id) REFERENCES branch(id),
    FOREIGN KEY (salle_id) REFERENCES salle(id),
    FOREIGN KEY (periode_id) REFERENCES periode(id),
    FOREIGN KEY (type_seance_id) REFERENCES type_seance(id),
    FOREIGN KEY (module_id) REFERENCES module(id)
);

CREATE TABLE user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255),
    password VARCHAR(255)
);

CREATE TABLE schedule_archive (
    id INT PRIMARY KEY AUTO_INCREMENT,
    original_id INT,
    branch_id INT,
    module_id INT,
    salle_id INT,
    periode_id INT,
    type_seance_id INT,
    semester INT,
    year INT,
    Monday BOOLEAN,
    Tuesday BOOLEAN,
    Wednesday BOOLEAN,
    Thursday BOOLEAN,
    Friday BOOLEAN,
    Saturday BOOLEAN,
    archived_date DATETIME
);