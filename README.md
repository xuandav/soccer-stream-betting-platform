# SoccerStream - Live Soccer Betting Platform

A complete PHP/HTML/CSS/JavaScript web application for live soccer streaming with real-time betting and chat functionality, designed for deployment on cPanel hosting.

## Features

### 🎯 Core Functionality
- **Live Soccer Streaming**: YouTube-like interface for watching matches
- **Real-time Betting System**: Live polls for each game with vote tracking
- **Live Chat**: Real-time messaging during matches (messages are temporary)
- **Admin Panel**: Complete match management system
- **Responsive Design**: Mobile-optimized for Android and all devices

### 📱 User Experience
- **Homepage**: Browse all live soccer matches
- **Game Pages**: Side-by-side video player and live chat
- **Betting Interface**: Real-time vote counting and percentages
- **Admin Dashboard**: Create, edit, and manage soccer events

## Installation Instructions

### 1. Database Setup
1. Create a MySQL database named `soccer_stream`
2. Import the database schema:
   ```sql
   mysql -u your_username -p soccer_stream < database/schema.sql
   ```

### 2. Configuration
1. Edit `config/database.php` with your database credentials:
   ```php
   $host = 'localhost';
   $dbname = 'soccer_stream';
   $username = 'your_db_username';
   $password = 'your_db_password';
   ```

### 3. File Upload to cPanel
Upload all files to your cPanel public_html directory:
```
public_html/
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── includes/
│   ├── header.php
│   └── footer.php
├── api/
│   ├── chat.php
│   └── poll.php
├── index.php
├── game.php
├── admin.php
└── README.md
```

### 4. Permissions
Ensure proper file permissions:
- PHP files: 644
- Directories: 755

## File Structure

### Core Pages
- `index.php` - Homepage with match listings
- `game.php` - Individual match page with video and chat
- `admin.php` - Admin panel for match management

### API Endpoints
- `api/chat.php` - Handle chat messages (GET/POST)
- `api/poll.php` - Handle betting polls (GET/POST)

### Configuration
- `config/database.php` - Database connection settings
- `database/schema.sql` - Database structure and sample data

### Templates
- `includes/header.php` - Common header with navigation
- `includes/footer.php` - Common footer with JavaScript utilities

## Database Schema

### Tables
1. **events** - Soccer match information
2. **polls** - Betting data for each match
3. **chat_messages** - Live chat messages (auto-cleanup)

### Key Features
- Foreign key relationships for data integrity
- Automatic timestamp tracking
- Message cleanup (1-hour expiry)
- Vote counting with real-time updates

## Usage

### For Users
1. Visit the homepage to see live matches
2. Click on any match to watch and participate
3. Join chat by choosing a username
4. Place bets on your favorite team
5. Watch real-time vote updates

### For Administrators
1. Access `/admin.php` for match management
2. Create new matches with team details
3. Edit existing matches and update status
4. Delete matches when needed
5. View live matches directly from admin panel

## Technical Features

### Frontend
- **Responsive Design**: Tailwind CSS for modern styling
- **Real-time Updates**: JavaScript polling for live data
- **Error Handling**: Comprehensive user feedback
- **Mobile Optimized**: Touch-friendly interface

### Backend
- **PHP 7.4+**: Modern PHP with PDO for database access
- **MySQL**: Relational database with proper indexing
- **RESTful APIs**: JSON responses for AJAX calls
- **Security**: Prepared statements and input validation

### Real-time Features
- Chat messages update every 3 seconds
- Poll results update every 5 seconds
- Automatic message cleanup (1-hour expiry)
- Live vote counting and percentage calculation

## Security Features

- **SQL Injection Protection**: PDO prepared statements
- **Input Validation**: Server-side validation for all inputs
- **XSS Prevention**: HTML escaping for user content
- **Message Limits**: Character limits and rate limiting
- **Data Cleanup**: Automatic removal of old messages

## Browser Compatibility

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Optimization

- **Efficient Queries**: Indexed database queries
- **Minimal JavaScript**: Vanilla JS without heavy frameworks
- **CDN Assets**: Tailwind CSS via CDN
- **Automatic Cleanup**: Database maintenance for chat messages

## Troubleshooting

### Common Issues
1. **Database Connection Error**: Check credentials in `config/database.php`
2. **Chat Not Working**: Verify API endpoints are accessible
3. **Polls Not Updating**: Check database permissions
4. **Video Not Playing**: Ensure video URLs are accessible

### Debug Mode
Add this to `config/database.php` for debugging:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## Support

For technical support or customization requests, please check:
1. Database connection settings
2. File permissions on server
3. PHP error logs in cPanel
4. Browser console for JavaScript errors

## License

This project is designed for educational and commercial use. Modify as needed for your specific requirements.
