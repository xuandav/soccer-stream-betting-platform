<?php
require_once 'config/database.php';

$page_title = 'Admin Panel - SoccerStream';
include 'includes/header.php';

// Handle form submissions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    $stmt = $pdo->prepare("
                        INSERT INTO events (team_a, team_b, league, start_time, video_src, status) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $_POST['team_a'],
                        $_POST['team_b'],
                        $_POST['league'],
                        $_POST['start_time'],
                        $_POST['video_src'],
                        $_POST['status']
                    ]);
                    $message = 'Match created successfully!';
                    break;
                    
                case 'update':
                    $stmt = $pdo->prepare("
                        UPDATE events 
                        SET team_a = ?, team_b = ?, league = ?, start_time = ?, video_src = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $_POST['team_a'],
                        $_POST['team_b'],
                        $_POST['league'],
                        $_POST['start_time'],
                        $_POST['video_src'],
                        $_POST['status'],
                        $_POST['event_id']
                    ]);
                    $message = 'Match updated successfully!';
                    break;
                    
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
                    $stmt->execute([$_POST['event_id']]);
                    $message = 'Match deleted successfully!';
                    break;
            }
        }
    } catch(PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Fetch all events
try {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY start_time ASC");
    $events = $stmt->fetchAll();
} catch(PDOException $e) {
    $events = [];
    $error = "Failed to load events: " . $e->getMessage();
}

// Get event for editing if requested
$editing_event = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$edit_id]);
    $editing_event = $stmt->fetch();
}

function formatDateTime($dateString) {
    $date = new DateTime($dateString);
    return $date->format('Y-m-d\TH:i');
}

function getStatusBadge($status) {
    $classes = [
        'LIVE' => 'bg-red-600 text-white',
        'UPCOMING' => 'bg-gray-600 text-white',
        'FINISHED' => 'bg-green-600 text-white'
    ];
    
    $class = $classes[$status] ?? 'bg-gray-600 text-white';
    return "<span class='px-2 py-1 text-xs font-medium rounded {$class}'>{$status}</span>";
}
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Admin Panel</h1>
            <p class="text-gray-600">Manage soccer matches and events</p>
        </div>
        <?php if (!isset($_GET['create']) && !$editing_event): ?>
            <a href="?create=1" class="bg-gray-900 text-white px-4 py-2 rounded-md hover:bg-gray-800 transition-colors">
                Create New Match
            </a>
        <?php endif; ?>
    </div>

    <?php if ($message): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-green-700"><?php echo htmlspecialchars($message); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>

    <!-- Create/Edit Form -->
    <?php if (isset($_GET['create']) || $editing_event): ?>
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-xl font-semibold mb-4">
                <?php echo $editing_event ? 'Edit Match' : 'Create New Match'; ?>
            </h2>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="<?php echo $editing_event ? 'update' : 'create'; ?>">
                <?php if ($editing_event): ?>
                    <input type="hidden" name="event_id" value="<?php echo $editing_event['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="team_a" class="block text-sm font-medium text-gray-700 mb-1">Team A</label>
                        <input 
                            type="text" 
                            id="team_a" 
                            name="team_a" 
                            value="<?php echo $editing_event ? htmlspecialchars($editing_event['team_a']) : ''; ?>"
                            placeholder="e.g., Manchester United"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div>
                        <label for="team_b" class="block text-sm font-medium text-gray-700 mb-1">Team B</label>
                        <input 
                            type="text" 
                            id="team_b" 
                            name="team_b" 
                            value="<?php echo $editing_event ? htmlspecialchars($editing_event['team_b']) : ''; ?>"
                            placeholder="e.g., Liverpool"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="league" class="block text-sm font-medium text-gray-700 mb-1">League</label>
                        <input 
                            type="text" 
                            id="league" 
                            name="league" 
                            value="<?php echo $editing_event ? htmlspecialchars($editing_event['league']) : ''; ?>"
                            placeholder="e.g., Premier League"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select 
                            id="status" 
                            name="status" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="UPCOMING" <?php echo ($editing_event && $editing_event['status'] === 'UPCOMING') ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="LIVE" <?php echo ($editing_event && $editing_event['status'] === 'LIVE') ? 'selected' : ''; ?>>Live</option>
                            <option value="FINISHED" <?php echo ($editing_event && $editing_event['status'] === 'FINISHED') ? 'selected' : ''; ?>>Finished</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                    <input 
                        type="datetime-local" 
                        id="start_time" 
                        name="start_time" 
                        value="<?php echo $editing_event ? formatDateTime($editing_event['start_time']) : ''; ?>"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>

                <div>
                    <label for="video_src" class="block text-sm font-medium text-gray-700 mb-1">Video Stream URL</label>
                    <input 
                        type="url" 
                        id="video_src" 
                        name="video_src" 
                        value="<?php echo $editing_event ? htmlspecialchars($editing_event['video_src']) : ''; ?>"
                        placeholder="https://example.com/stream.mp4"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <p class="text-sm text-gray-500 mt-1">Enter the direct URL to the video stream</p>
                </div>

                <div class="flex gap-2 pt-4">
                    <button 
                        type="submit" 
                        class="bg-gray-900 text-white px-4 py-2 rounded-md hover:bg-gray-800 transition-colors"
                    >
                        <?php echo $editing_event ? 'Update Match' : 'Create Match'; ?>
                    </button>
                    <a 
                        href="admin.php" 
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Events List -->
    <div class="space-y-4">
        <h2 class="text-xl font-semibold text-gray-900">All Matches (<?php echo count($events); ?>)</h2>
        
        <?php if (empty($events)): ?>
            <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
                <p class="text-gray-600">No matches found. Create your first match!</p>
            </div>
        <?php else: ?>
            <div class="grid gap-4">
                <?php foreach ($events as $event): ?>
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <?php echo htmlspecialchars($event['team_a']); ?> vs <?php echo htmlspecialchars($event['team_b']); ?>
                            </h3>
                            <?php echo getStatusBadge($event['status']); ?>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">League</p>
                                <p class="text-gray-900"><?php echo htmlspecialchars($event['league']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Start Time</p>
                                <p class="text-gray-900"><?php echo (new DateTime($event['start_time']))->format('n/j/Y g:i A'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Match ID</p>
                                <p class="text-gray-900 font-mono text-sm"><?php echo $event['id']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Video Source</p>
                                <p class="text-gray-900 text-sm truncate"><?php echo htmlspecialchars($event['video_src']); ?></p>
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <a 
                                href="?edit=<?php echo $event['id']; ?>" 
                                class="bg-blue-600 text-white px-3 py-1 text-sm rounded hover:bg-blue-700 transition-colors"
                            >
                                Edit
                            </a>
                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this match?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <button 
                                    type="submit" 
                                    class="bg-red-600 text-white px-3 py-1 text-sm rounded hover:bg-red-700 transition-colors"
                                >
                                    Delete
                                </button>
                            </form>
                            <a 
                                href="game.php?id=<?php echo $event['id']; ?>" 
                                target="_blank"
                                class="bg-green-600 text-white px-3 py-1 text-sm rounded hover:bg-green-700 transition-colors"
                            >
                                View Live
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
