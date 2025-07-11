<?php
require_once 'config/database.php';

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$event_id) {
    header('Location: index.php');
    exit;
}

// Fetch event details
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    if (!$event) {
        header('Location: index.php');
        exit;
    }
} catch(PDOException $e) {
    header('Location: index.php');
    exit;
}

$page_title = $event['team_a'] . ' vs ' . $event['team_b'] . ' - SoccerStream';
include 'includes/header.php';

function formatDateTime($dateString) {
    $date = new DateTime($dateString);
    return $date->format('n/j/Y, g:i:s A');
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
    <!-- Navigation -->
    <div class="flex items-center justify-between">
        <a href="index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            ← Back to Matches
        </a>
        <?php echo getStatusBadge($event['status']); ?>
    </div>

    <!-- Match Header -->
    <div class="text-center space-y-2">
        <h1 class="text-3xl font-bold text-gray-900">
            <?php echo htmlspecialchars($event['team_a']); ?> vs <?php echo htmlspecialchars($event['team_b']); ?>
        </h1>
        <div class="flex items-center justify-center space-x-4 text-gray-600">
            <span><?php echo htmlspecialchars($event['league']); ?></span>
            <span>•</span>
            <span><?php echo formatDateTime($event['start_time']); ?></span>
        </div>
    </div>

    <!-- Video Player and Chat Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Video Player - Takes 2/3 width on large screens -->
        <div class="lg:col-span-2">
            <div class="relative w-full bg-black rounded-lg overflow-hidden">
                <div id="video-container">
                    <video 
                        src="<?php echo htmlspecialchars($event['video_src']); ?>" 
                        controls 
                        autoplay 
                        muted 
                        class="w-full h-auto max-h-[60vh] object-cover"
                        style="aspect-ratio: 16/9;"
                        onerror="showVideoError()"
                    >
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
        
        <!-- Live Chat - Takes 1/3 width on large screens -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg border border-gray-200 h-full flex flex-col">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold flex items-center justify-between">
                        Live Chat
                        <span id="message-count" class="text-sm font-normal text-gray-500">0 messages</span>
                    </h2>
                </div>
                
                <div class="flex-1 flex flex-col p-4 space-y-4">
                    <!-- Username Setup -->
                    <div id="username-setup" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                Choose a username to start chatting:
                            </label>
                            <input 
                                type="text" 
                                id="username-input"
                                placeholder="Enter username..." 
                                maxlength="20"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <div id="username-error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        <button 
                            onclick="joinChat()" 
                            class="w-full bg-gray-900 text-white py-2 px-4 rounded-md hover:bg-gray-800 transition-colors"
                        >
                            Join Chat
                        </button>
                    </div>

                    <!-- Chat Interface -->
                    <div id="chat-interface" class="hidden flex-1 flex flex-col space-y-4">
                        <!-- Messages Area -->
                        <div id="messages-area" class="flex-1 overflow-y-auto space-y-2 max-h-80 min-h-40 border rounded p-3 bg-gray-50">
                            <div class="text-center text-gray-500 text-sm py-8">
                                No messages yet. Be the first to chat!
                            </div>
                        </div>

                        <!-- Message Input -->
                        <div class="space-y-2">
                            <div id="chat-error" class="text-red-500 text-sm hidden"></div>
                            <div class="flex gap-2">
                                <input 
                                    type="text" 
                                    id="message-input"
                                    placeholder="Type your message..." 
                                    maxlength="200"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    onkeypress="handleMessageKeyPress(event)"
                                />
                                <button 
                                    id="send-button"
                                    onclick="sendMessage()" 
                                    class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors disabled:opacity-50"
                                >
                                    Send
                                </button>
                            </div>
                            <p class="text-xs text-gray-500">
                                Chatting as <strong id="current-username"></strong> • Messages are temporary
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Poll -->
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="text-center mb-4">
                <h2 class="text-lg font-semibold">Place Your Bet</h2>
                <div id="vote-success" class="text-green-600 text-sm font-medium mt-2 hidden">
                    Thanks for voting! Results update live.
                </div>
            </div>
            
            <div id="poll-error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm hidden"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="poll-container">
                <!-- Poll options will be loaded here -->
            </div>
            
            <div id="poll-total" class="mt-4 pt-4 border-t text-center text-sm text-gray-500">
                <!-- Total votes will be displayed here -->
            </div>
        </div>
    </div>

    <!-- Match Info -->
    <div class="bg-gray-100 rounded-lg p-6 text-center">
        <h3 class="font-semibold mb-2">About This Match</h3>
        <p class="text-gray-600 text-sm">
            Watch the live stream above and place your bet on which team you think will win. 
            Vote counts are updated in real-time and you can see how other viewers are betting.
        </p>
    </div>
</div>

<script>
const eventId = <?php echo $event_id; ?>;
let currentUsername = '';
let hasVoted = false;
let chatInterval, pollInterval;

// Video error handling
function showVideoError() {
    document.getElementById('video-container').innerHTML = `
        <div class="p-8 text-center text-white bg-red-600">
            <h3 class="text-lg font-semibold mb-2">Stream Unavailable</h3>
            <p class="text-sm mb-4">The video stream failed to load. This might be due to network issues or the stream being temporarily unavailable.</p>
            <button onclick="location.reload()" class="px-4 py-2 bg-white text-red-600 rounded hover:bg-gray-100 transition-colors">
                Retry
            </button>
        </div>
    `;
}

// Chat functionality
function joinChat() {
    const username = document.getElementById('username-input').value.trim();
    const errorDiv = document.getElementById('username-error');
    
    if (username.length < 2) {
        errorDiv.textContent = 'Username must be at least 2 characters';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    currentUsername = username;
    document.getElementById('current-username').textContent = username;
    document.getElementById('username-setup').classList.add('hidden');
    document.getElementById('chat-interface').classList.remove('hidden');
    
    // Start fetching messages
    fetchMessages();
    chatInterval = setInterval(fetchMessages, 3000);
}

function fetchMessages() {
    makeRequest(`api/chat.php?event_id=${eventId}`)
        .then(data => {
            if (data.success) {
                displayMessages(data.messages);
                document.getElementById('message-count').textContent = `${data.messages.length} messages`;
            }
        })
        .catch(error => {
            console.error('Error fetching messages:', error);
        });
}

function displayMessages(messages) {
    const container = document.getElementById('messages-area');
    
    if (messages.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-500 text-sm py-8">No messages yet. Be the first to chat!</div>';
        return;
    }
    
    container.innerHTML = messages.map(msg => `
        <div class="text-sm">
            <div class="flex items-center gap-2 mb-1">
                <span class="font-medium text-blue-600">${escapeHtml(msg.username)}</span>
                <span class="text-xs text-gray-500">${formatTime(msg.created_at)}</span>
            </div>
            <p class="text-gray-900 break-words">${escapeHtml(msg.message)}</p>
        </div>
    `).join('');
    
    // Scroll to bottom
    container.scrollTop = container.scrollHeight;
}

function sendMessage() {
    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();
    const errorDiv = document.getElementById('chat-error');
    const sendButton = document.getElementById('send-button');
    
    if (!message) return;
    
    sendButton.disabled = true;
    sendButton.textContent = '...';
    
    const formData = new FormData();
    formData.append('event_id', eventId);
    formData.append('username', currentUsername);
    formData.append('message', message);
    
    fetch('api/chat.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            fetchMessages();
            errorDiv.classList.add('hidden');
        } else {
            errorDiv.textContent = data.error || 'Failed to send message';
            errorDiv.classList.remove('hidden');
        }
    })
    .catch(error => {
        errorDiv.textContent = 'Failed to send message';
        errorDiv.classList.remove('hidden');
    })
    .finally(() => {
        sendButton.disabled = false;
        sendButton.textContent = 'Send';
    });
}

function handleMessageKeyPress(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

// Poll functionality
function fetchPollResults() {
    makeRequest(`api/poll.php?event_id=${eventId}`)
        .then(data => {
            if (data.success) {
                displayPollResults(data.polls);
            }
        })
        .catch(error => {
            console.error('Error fetching poll results:', error);
        });
}

function displayPollResults(polls) {
    const container = document.getElementById('poll-container');
    const totalDiv = document.getElementById('poll-total');
    
    const totalVotes = polls.reduce((sum, poll) => sum + parseInt(poll.votes), 0);
    
    container.innerHTML = polls.map(poll => {
        const percentage = totalVotes ? ((poll.votes / totalVotes) * 100).toFixed(1) : '0';
        const buttonClass = hasVoted ? 'border-gray-300 text-gray-700' : 'bg-gray-900 text-white hover:bg-gray-800';
        
        return `
            <div class="space-y-2">
                <button 
                    onclick="vote('${poll.team_name}')" 
                    class="w-full h-12 text-base font-medium rounded-md border transition-colors ${buttonClass}"
                    ${hasVoted ? 'disabled' : ''}
                >
                    ${escapeHtml(poll.team_name)}
                </button>
                <div class="space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="font-medium">${percentage}%</span>
                        <span class="text-gray-500">${poll.votes} votes</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: ${percentage}%"></div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    if (totalVotes > 0) {
        totalDiv.innerHTML = `Total votes: ${totalVotes} • Updates every 5 seconds`;
        totalDiv.classList.remove('hidden');
    }
}

function vote(teamName) {
    if (hasVoted) return;
    
    const formData = new FormData();
    formData.append('event_id', eventId);
    formData.append('team_name', teamName);
    
    fetch('api/poll.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hasVoted = true;
            document.getElementById('vote-success').classList.remove('hidden');
            fetchPollResults();
        } else {
            document.getElementById('poll-error').textContent = data.error || 'Failed to vote';
            document.getElementById('poll-error').classList.remove('hidden');
        }
    })
    .catch(error => {
        document.getElementById('poll-error').textContent = 'Failed to vote';
        document.getElementById('poll-error').classList.remove('hidden');
    });
}

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    fetchPollResults();
    pollInterval = setInterval(fetchPollResults, 5000);
});

// Cleanup intervals when page unloads
window.addEventListener('beforeunload', function() {
    if (chatInterval) clearInterval(chatInterval);
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<?php include 'includes/footer.php'; ?>
