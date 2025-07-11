<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Fetch poll results for an event
        $event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
        
        if (!$event_id) {
            echo json_encode(['success' => false, 'error' => 'Event ID required']);
            exit;
        }
        
        // Fetch poll results
        $stmt = $pdo->prepare("
            SELECT team_name, votes 
            FROM polls 
            WHERE event_id = ? 
            ORDER BY team_name ASC
        ");
        $stmt->execute([$event_id]);
        $polls = $stmt->fetchAll();
        
        // If no polls exist, create them from the event teams
        if (empty($polls)) {
            $stmt = $pdo->prepare("SELECT team_a, team_b FROM events WHERE id = ?");
            $stmt->execute([$event_id]);
            $event = $stmt->fetch();
            
            if ($event) {
                // Create initial poll entries
                $stmt = $pdo->prepare("
                    INSERT INTO polls (event_id, team_name, votes) 
                    VALUES (?, ?, 0), (?, ?, 0)
                ");
                $stmt->execute([
                    $event_id, $event['team_a'],
                    $event_id, $event['team_b']
                ]);
                
                // Fetch the newly created polls
                $stmt = $pdo->prepare("
                    SELECT team_name, votes 
                    FROM polls 
                    WHERE event_id = ? 
                    ORDER BY team_name ASC
                ");
                $stmt->execute([$event_id]);
                $polls = $stmt->fetchAll();
            }
        }
        
        echo json_encode([
            'success' => true,
            'polls' => $polls
        ]);
        
    } elseif ($method === 'POST') {
        // Submit a vote
        $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
        $team_name = isset($_POST['team_name']) ? trim($_POST['team_name']) : '';
        
        // Validation
        if (!$event_id) {
            echo json_encode(['success' => false, 'error' => 'Event ID required']);
            exit;
        }
        
        if (empty($team_name)) {
            echo json_encode(['success' => false, 'error' => 'Team name required']);
            exit;
        }
        
        // Check if event exists
        $stmt = $pdo->prepare("SELECT id FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Event not found']);
            exit;
        }
        
        // Check if poll entry exists, if not create it
        $stmt = $pdo->prepare("
            SELECT votes FROM polls 
            WHERE event_id = ? AND team_name = ?
        ");
        $stmt->execute([$event_id, $team_name]);
        $poll = $stmt->fetch();
        
        if (!$poll) {
            // Create new poll entry
            $stmt = $pdo->prepare("
                INSERT INTO polls (event_id, team_name, votes) 
                VALUES (?, ?, 1)
            ");
            $stmt->execute([$event_id, $team_name]);
        } else {
            // Update existing poll
            $stmt = $pdo->prepare("
                UPDATE polls 
                SET votes = votes + 1, updated_at = CURRENT_TIMESTAMP 
                WHERE event_id = ? AND team_name = ?
            ");
            $stmt->execute([$event_id, $team_name]);
        }
        
        // Return updated poll results
        $stmt = $pdo->prepare("
            SELECT team_name, votes 
            FROM polls 
            WHERE event_id = ? 
            ORDER BY team_name ASC
        ");
        $stmt->execute([$event_id]);
        $polls = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'message' => 'Vote recorded successfully',
            'polls' => $polls
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
