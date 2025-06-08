CREATE TABLE users (
  user_id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(100) NOT NULL,
  npm VARCHAR(12) NOT NULL UNIQUE,
  fullname VARCHAR(100) NOT NULL,
  birth_date DATE NOT NULL,
  profile_picture_path VARCHAR(255) NULL DEFAULT NULL,
  role ENUM('ADMIN', 'STUDENT') NOT NULL
);

CREATE TABLE mata_kuliah (
  course_id INT PRIMARY KEY AUTO_INCREMENT,
  course_name VARCHAR(255) NOT NULL UNIQUE,
  category ENUM('UMUM', 'MIPA', 'TEKNIK') NOT NULL,
  semester INT NOT NULL
);

CREATE TABLE sesi_tutor (
  session_id INT PRIMARY KEY AUTO_INCREMENT,
  requested_by_student_id INT NOT NULL,
  tutor_id INT NULL,
  course_id INT NOT NULL,
  session_date DATE NOT NULL,
  session_time TIME NOT NULL,
  material TEXT NOT NULL,
  status ENUM('PENDING', 'ACCEPTED', 'REJECTED', 'COMPLETED') NOT NULL DEFAULT 'PENDING',
  FOREIGN KEY (requested_by_student_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (tutor_id) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (course_id) REFERENCES mata_kuliah(course_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE enrollment (
  enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,        
  session_id INT NOT NULL,     
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  status ENUM('ACTIVE', 'CANCELLED') NOT NULL DEFAULT 'ACTIVE',
  attendance_status ENUM('PENDING', 'HADIR', 'TIDAK_HADIR') NOT NULL DEFAULT 'PENDING',
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (session_id) REFERENCES sesi_tutor(session_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX idx_sesi_tutor_tutor_id ON sesi_tutor(tutor_id);
CREATE INDEX idx_sesi_tutor_status ON sesi_tutor(status);
CREATE INDEX idx_sesi_tutor_datetime ON sesi_tutor(session_date, session_time);
CREATE INDEX idx_enrollment_user_session ON enrollment(user_id, session_id);
CREATE INDEX idx_enrollment_session_id ON enrollment(session_id);