-- ============================================================
-- University Library Management System (ULMS)
-- Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS ulms_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ulms_db;

-- ─────────────────────────────────────────────
-- TABLE: users
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    role        ENUM('admin','librarian','student') NOT NULL DEFAULT 'student',
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- TABLE: books
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS books (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title            VARCHAR(255) NOT NULL,
    author           VARCHAR(150) NOT NULL,
    isbn             VARCHAR(20)  DEFAULT NULL UNIQUE,
    category         VARCHAR(100) NOT NULL,
    total_copies     INT UNSIGNED NOT NULL DEFAULT 1,
    available_copies INT UNSIGNED NOT NULL DEFAULT 1,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- TABLE: loans
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS loans (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    book_id       INT UNSIGNED NOT NULL,
    student_id    INT UNSIGNED NOT NULL,
    issued_by     INT UNSIGNED NOT NULL,
    issue_date    DATE         NOT NULL,
    due_date      DATE         NOT NULL,
    return_date   DATE         DEFAULT NULL,
    status        ENUM('active','returned','overdue') NOT NULL DEFAULT 'active',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_loan_book    FOREIGN KEY (book_id)    REFERENCES books(id)  ON DELETE RESTRICT,
    CONSTRAINT fk_loan_student FOREIGN KEY (student_id) REFERENCES users(id)  ON DELETE RESTRICT,
    CONSTRAINT fk_loan_issued  FOREIGN KEY (issued_by)  REFERENCES users(id)  ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- TABLE: reservations
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reservations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    book_id         INT UNSIGNED NOT NULL,
    student_id      INT UNSIGNED NOT NULL,
    reserved_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status          ENUM('pending','fulfilled','cancelled') NOT NULL DEFAULT 'pending',
    fulfilled_at    DATETIME     DEFAULT NULL,
    CONSTRAINT fk_res_book    FOREIGN KEY (book_id)    REFERENCES books(id) ON DELETE RESTRICT,
    CONSTRAINT fk_res_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- TABLE: fines
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS fines (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id       INT UNSIGNED NOT NULL,
    student_id    INT UNSIGNED NOT NULL,
    amount        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    overdue_days  INT UNSIGNED  NOT NULL DEFAULT 0,
    status        ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
    settled_at    DATETIME      DEFAULT NULL,
    settled_by    INT UNSIGNED  DEFAULT NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fine_loan    FOREIGN KEY (loan_id)    REFERENCES loans(id) ON DELETE RESTRICT,
    CONSTRAINT fk_fine_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_fine_settled FOREIGN KEY (settled_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- TABLE: activity_logs
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS activity_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED  DEFAULT NULL,
    username    VARCHAR(50)   DEFAULT NULL,
    role        VARCHAR(20)   DEFAULT NULL,
    action      VARCHAR(100)  NOT NULL,
    description TEXT          DEFAULT NULL,
    ip_address  VARCHAR(45)   DEFAULT NULL,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- SEED: Run setup.php after importing schema.sql
-- This creates the admin account with a proper bcrypt hash.
-- Access http://localhost/library-system/setup.php
-- ─────────────────────────────────────────────

-- ─────────────────────────────────────────────
-- INDEXES for performance
-- ─────────────────────────────────────────────
CREATE INDEX idx_loans_student   ON loans(student_id);
CREATE INDEX idx_loans_book      ON loans(book_id);
CREATE INDEX idx_loans_status    ON loans(status);
CREATE INDEX idx_fines_student   ON fines(student_id);
CREATE INDEX idx_fines_status    ON fines(status);
CREATE INDEX idx_res_student     ON reservations(student_id);
CREATE INDEX idx_res_status      ON reservations(status);
CREATE INDEX idx_logs_user       ON activity_logs(user_id);
CREATE INDEX idx_logs_created    ON activity_logs(created_at);
