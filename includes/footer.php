</main>
    </div>

    <!-- JavaScript for real-time updates -->
    <script>
        // Utility functions
        function formatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = date.getTime() - now.getTime();
            const diffMins = Math.round(diffMs / (1000 * 60));

            if (diffMins < -30) {
                return `Started ${Math.abs(diffMins)} min ago`;
            } else if (diffMins < 0) {
                return "LIVE NOW";
            } else if (diffMins < 60) {
                return `Starts in ${diffMins} min`;
            } else {
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        }

        function showLoading(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.innerHTML = '<div class="flex items-center justify-center py-4"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div></div>';
            }
        }

        function showError(elementId, message) {
            const element = document.getElementById(elementId);
            if (element) {
                element.innerHTML = `<div class="text-red-600 text-sm p-2">${message}</div>`;
            }
        }

        // AJAX helper function
        function makeRequest(url, method = 'GET', data = null) {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open(method, url, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                resolve(response);
                            } catch (e) {
                                resolve(xhr.responseText);
                            }
                        } else {
                            reject(new Error(`HTTP ${xhr.status}: ${xhr.statusText}`));
                        }
                    }
                };
                
                xhr.send(data);
            });
        }

        // Auto-refresh functionality
        function startAutoRefresh(callback, interval = 5000) {
            callback(); // Initial call
            return setInterval(callback, interval);
        }
    </script>
</body>
</html>
