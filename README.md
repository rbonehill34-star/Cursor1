# Cursor1 Website

A modern, responsive website with database integration for XAMPP.

## Features

- **Modern Design**: Clean, professional interface with gradient backgrounds and smooth animations
- **Database Integration**: Connects to XAMPP MySQL database
- **Contact Form**: Functional contact form that stores submissions in the database
- **Responsive**: Works perfectly on desktop, tablet, and mobile devices
- **Security**: Input validation and prepared statements for database queries

## Setup Instructions

### 1. Database Setup

1. Start XAMPP and ensure MySQL is running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Import the `setup_database.sql` file to create the database and table structure
4. Or manually run the SQL commands in the `setup_database.sql` file

### 2. File Structure

```
cursor1/
├── config/
│   └── database.php          # Database connection configuration
├── assets/
│   └── css/
│       └── style.css         # Main stylesheet
├── index.php                 # Home page
├── contact.php              # Contact form page
├── setup_database.sql       # Database setup script
└── README.md               # This file
```

### 3. Database Configuration

The database connection is configured in `config/database.php`:
- Host: localhost
- Database: cursor1
- Username: root
- Password: (empty for default XAMPP setup)

### 4. Access the Website

1. Place all files in your XAMPP htdocs directory
2. Access via: http://localhost/cursor1/
3. Navigate between Home and Contact pages

## Pages

### Home Page (`index.php`)
- Welcome message
- Modern hero section with gradient background
- Feature cards highlighting database capabilities
- Navigation to contact page

### Contact Page (`contact.php`)
- Contact form with fields:
  - Name (required)
  - Email (required)
  - Telephone (optional)
  - Message (required)
- Form validation and error handling
- Success/error messages
- Database integration for storing form submissions

## Database Table

The `formresponse` table stores contact form submissions with the following structure:
- `id`: Auto-increment primary key
- `name`: User's full name
- `email`: User's email address
- `telephone`: User's phone number (optional)
- `message`: User's message
- `created_at`: Timestamp when record was created
- `updated_at`: Timestamp when record was last updated

## Customization

### Styling
- Modify `assets/css/style.css` to change colors, fonts, or layout
- The design uses CSS custom properties for easy color theming
- Responsive breakpoints are included for mobile optimization

### Database
- Update `config/database.php` if your XAMPP configuration differs
- Modify the table structure in `setup_database.sql` if needed

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Security Features

- Input validation and sanitization
- Prepared statements to prevent SQL injection
- Email format validation
- XSS protection with htmlspecialchars()

## Troubleshooting

1. **Database Connection Error**: Ensure XAMPP MySQL is running and the database exists
2. **Form Not Submitting**: Check that the `formresponse` table exists and has proper permissions
3. **Styling Issues**: Clear browser cache and ensure CSS file is accessible
4. **Permission Errors**: Ensure XAMPP has proper file permissions for the project directory
