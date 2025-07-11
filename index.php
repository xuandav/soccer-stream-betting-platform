<?php
require_once 'config/database.php';

$page_title = 'SoccerStream - Live Soccer Matches';
include 'includes/header.php';

// Fetch all events
try {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY start_time ASC");
    $events = $stmt->fetchAll();
} catch(PDOException $e) {
    $events = [];
    $error = "Failed to load events: " . $e->getMessage();
}

function formatTime($dateString) {
    $date = new DateTime($dateString);
    $now = new DateTime();
    $diff = $now->diff($date);
    $diffInMinutes = ($date->getTimestamp() - $now->getTimestamp()) / 60;
    
    if ($diffInMinutes < -30) {
        return "Started " . abs(round($diffInMinutes)) . " min ago";
    } elseif ($diffInMinutes < 0) {
        return "LIVE NOW";
    } elseif ($diffInMinutes < 60) {
        return "Starts in " . round($diffInMinutes) . " min";
    } else {
        return $date->format('H:i');
    }
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
    <div class="text-center">
        <h1 class="text-3xl font-bold mb-2 text-gray-900">Live Soccer Matches</h1>
        <p class="text-gray-600">Watch live and bet on your favorite teams</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <?php if (empty($events)): ?>
            <div class="col-span-full text-center py-12">
                <h2 class="text-xl font-semibold mb-2 text-gray-900">No Live Matches</h2>
                <p class="text-gray-600">Check back later for live soccer events</p>
            </div>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <a href="game.php?id=<?php echo $event['id']; ?>" class="block">
                    <div class="bg-white rounded-lg border border-gray-200 hover:shadow-lg transition-all duration-200 cursor-pointer group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <?php echo getStatusBadge($event['status']); ?>
                                <span class="text-xs text-gray-500"><?php echo htmlspecialchars($event['league']); ?></span>
                            </div>
                            <h3 class="text-lg font-semibold group-hover:text-blue-600 transition-colors mb-3">
                                <?php echo htmlspecialchars($event['team_a']); ?> vs <?php echo htmlspecialchars($event['team_b']); ?>
                            </h3>
                            <div class="space-y-2">
                                <p class="text-sm font-medium text-gray-600">
                                    <?php echo formatTime($event['start_time']); ?>
                                </p>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Click to watch & bet</span>
                                    <span class="text-blue-600 font-medium group-hover:underline">
                                        Watch Live →
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-refresh the page every 30 seconds to update match times
setTimeout(function() {
    location.reload();
}, 30000);
</script>

<?php include 'includes/footer.php'; ?>
