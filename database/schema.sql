-- Soccer Stream Database Schema
-- Run this SQL script to create the required tables

CREATE DATABASE IF NOT EXISTS soccer_stream;
USE soccer_stream;

-- Events table for soccer matches
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_a VARCHAR(100) NOT NULL,
    team_b VARCHAR(100) NOT NULL,
    league VARCHAR(100) NOT NULL,
    start_time DATETIME NOT NULL,
    video_src TEXT NOT NULL,
    status ENUM('UPCOMING', 'LIVE', 'FINISHED') DEFAULT 'UPCOMING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Polls table for betting
CREATE TABLE polls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    votes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_event_team (event_id, team_name)
);

-- Chat messages table
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_time (event_id, created_at)
);

-- Insert sample data
INSERT INTO events (team_a, team_b, league, start_time, video_src, status) VALUES
('Manchester United', 'Liverpool', 'Premier League', NOW(), 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4', 'LIVE'),
('Barcelona', 'Real Madrid', 'La Liga', DATE_ADD(NOW(), INTERVAL 1 HOUR), 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4', 'LIVE'),
('Bayern Munich', 'Borussia Dortmund', 'Bundesliga', DATE_SUB(NOW(), INTERVAL 30 MINUTE), 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4', 'LIVE'),
('PSG', 'Marseille', 'Ligue 1', DATE_ADD(NOW(), INTERVAL 2 HOUR), 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4', 'UPCOMING');

-- Insert sample poll data
INSERT INTO polls (event_id, team_name, votes) VALUES
(1, 'Manchester United', 45),
(1, 'Liverpool', 38),
(2, 'Barcelona', 52),
(2, 'Real Madrid', 41),
(3, 'Bayern Munich', 33),
(3, 'Borussia Dortmund', 29),
(4, 'PSG', 18),
(4, 'Marseille', 12);
