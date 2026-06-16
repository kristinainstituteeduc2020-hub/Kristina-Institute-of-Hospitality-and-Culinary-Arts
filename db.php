<?php
// MySQL connection for Kristina Updates site
// Update credentials below to match your MySQL setup.

$db_host = 'localhost';
// CHANGE THIS to match the actual MySQL database name on your XAMPP.
// If you haven't created it yet, create a database named `kristina_updates` in phpMyAdmin.
$db_name = 'kristina_updates';
$db_user = 'root';
$db_pass = '';


$charset = 'utf8mb4';

$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$charset}";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$pdo = new PDO($dsn, $db_user, $db_pass, $options);

// Ensure table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('news','updates','advertisement','tesda_assessment','tesda_training') NOT NULL,
  title VARCHAR(140) NOT NULL,

  content TEXT NOT NULL,
  author_name VARCHAR(80) NULL,
  image_path VARCHAR(255) NULL,
  attachment_path VARCHAR(255) NULL,
  video_path VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Chatbot website tables (users + chat history)
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$pdo->exec("CREATE TABLE IF NOT EXISTS chat_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$pdo->exec("CREATE TABLE IF NOT EXISTS chat_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  conversation_id INT NOT NULL,
  role ENUM('user','assistant') NOT NULL,
  content TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (conversation_id) REFERENCES chat_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");


// Backward compatible: if table exists without video_path, add it
$colCheck = $pdo->query("SHOW COLUMNS FROM posts LIKE 'video_path'");
if($colCheck->rowCount() === 0){
  $pdo->exec("ALTER TABLE posts ADD COLUMN video_path VARCHAR(255) NULL AFTER attachment_path");
}

// Backward compatible: add TESDA category for dynamic category-driven cards
$colCheck2 = $pdo->query("SHOW COLUMNS FROM posts LIKE 'tesda_category'");
if($colCheck2->rowCount() === 0){
  $pdo->exec("ALTER TABLE posts ADD COLUMN tesda_category VARCHAR(80) NULL AFTER type");
}



