<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'SoccerStream - Live Soccer Betting'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .transition-all {
            transition: all 0.3s ease;
        }
        .hover\:shadow-lg:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans">
    <div class="min-h-screen">
        <header class="border-b bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">SoccerStream</h1>
                        <p class="text-gray-600">Live Soccer with Betting</p>
                    </div>
                    <nav class="flex items-center space-x-4">
                        <a href="index.php" class="text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors">
                            Home
                        </a>
                        <a href="admin.php" class="text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors">
                            Admin
                        </a>
                    </nav>
                </div>
            </div>
        </header>
        <main class="container mx-auto px-4 py-6">
