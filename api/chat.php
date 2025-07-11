<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Fetch chat messages for an event
        $event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
        
        if (!$event_id) {
            echo json_encode(['success' => false, 'error' => 'Event ID required']);
            exit;
        }
        
        // Clean up old messages (older than 1 hour)
        $stmt = $pdo->prepare("DELETE FROM chat_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->execute();
        
        // Fetch recent messages
        $stmt = $pdo->prepare("
            SELECT username, message, created_at 
            FROM chat_messages 
            WHERE event_id = ? 
            ORDER BY created_at ASC 
            LIMIT 100
        ");
        $stmt->execute([$event_id]);
        $messages = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
        
    } elseif ($method === 'POST') {
        // Send a new chat message
        $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        // Validation
        if (!$event_id) {
            echo json_encode(['success' => false, 'error' => 'Event ID required']);
            exit;
        }
        
        if (empty($username) || strlen($username) < 2) {
            echo json_encode(['success' => false, 'error' => 'Username must be at least 2 characters']);
            exit;
        }
        
        if (empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
            exit;
        }
        
        if (strlen($message) > 200) {
            echo json_encode(['success' => false, 'error' => 'Message too long (max 200 characters)']);
            exit;
        }
        
        if (strlen($username) > 20) {
            $username = substr($username, 0, 20);
        }
        
        // Check if event exists
        $stmt = $pdo->prepare("SELECT id FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Event not found']);
            exit;
        }
        
        // Insert message
        $stmt = $pdo->prepare("
            INSERT INTO chat_messages (event_id, username, message) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$event_id, $username, $message]);
        
        // Clean up old messages to keep only last 100 per event
        $stmt = $pdo->prepare("
            DELETE FROM chat_messages 
            WHERE event_id = ? 
            AND id NOT IN (
                SELECT id FROM (
                    SELECT id FROM chat_messages 
                    WHERE event_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 100
                ) AS recent_messages
            )
        ");
        $stmt->execute([$event_id, $event_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully'
        ]);
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
