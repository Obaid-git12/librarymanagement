# Library Management System

A complete library management system with role-based access control, student approval workflow, book borrowing, and charge management.

## Features

### Separate Registration Systems
- **Student Signup** (`signup.php`): Students register and wait for manager approval
- **Manager Signup** (`manager_signup.php`): Managers can create accounts instantly
- Separate database tables: `managers` and `students`

### Manager Dashboard
- **Manage Books**: Add, edit, search books
- **Student Approvals**: View and approve/reject student registrations
- **Borrow Requests**: Approve or reject book borrow requests
- Real-time notification badges for pending requests
- Secure logout functionality

### Student Dashboard
- **Browse Books**: Search and view available books
- **Borrow Books**: Request to borrow books (requires manager approval)
- **My Borrows**: Track borrow history and status
- **Charges**: View weekly/monthly charges and payment status
- Real-time charge calculations

### Charge System
- Weekly rental fee: $5.00 per book
- Automatic charge generation on borrow approval
- Track pending and paid charges
- Total pending charges display

## Setup Instructions

### 1. Database Setup - EASIEST METHOD

**Visit this page in your browser:**
```
http://localhost/your-project-folder/reset_database.php
```
This will automatically:
- Drop old `users` table
- Create new `managers` and `students` tables
- Create `borrow_requests` and `charges` tables
- Add default accounts

**OR use SQL file:**
```bash
mysql -u root -p < setup_database.sql
```

### 2. Test Your Setup
Visit `test_login.php` to verify:
- Database connection
- Managers and students tables exist
- Default accounts are created

### 3. Database Configuration
Update `db.php` if needed:
```php
$conn = mysqli_connect("localhost", "root", "", "library_db");
```

### 4. Default Accounts
**Manager:**
- Username: `admin`
- Password: `admin123`

**Student:**
- Username: `student`
- Password: `student123`
- Status: Approved ✅

## File Structure

- `login.php` - Main login page with role selection
- `signup.php` - Student registration (requires manager approval)
- `manager_signup.php` - Manager registration (instant approval)
- `index.php` - Manager dashboard with tabs
- `student_dashboard.php` - Student dashboard with tabs
- `manage_students.php` - Student approval handler
- `manage_borrows.php` - Borrow request handler
- `logout.php` - Logout handler
- `db.php` - Database connection
- `style.css` - Styling and animations
- `setup_database.sql` - Fresh database setup
- `reset_database.php` - PHP script to reset database (EASIEST)
- `test_login.php` - Database connection tester
- `TROUBLESHOOTING.md` - Detailed troubleshooting guide

## Usage Flow

### For Students:
1. Visit `signup.php` and create an account
2. Wait for manager approval (status will be "pending")
3. Once approved, login at `login.php` with Student role
4. Browse books and submit borrow requests
5. Track borrows and charges in dashboard

### For Managers:
1. Visit `manager_signup.php` to create account (instant approval)
2. Login at `login.php` with Manager role
3. **Student Approvals Tab**: Approve/reject new student registrations
4. **Borrow Requests Tab**: Approve/reject book borrow requests
5. **Manage Books Tab**: Add, edit, search books

## Troubleshooting

**Can't login?** Check `TROUBLESHOOTING.md` for detailed solutions.

**Quick checks:**
1. Visit `test_login.php` to verify database setup
2. Make sure you're selecting the correct role when logging in
3. For students: Check if your account is approved by the manager
4. Try default accounts: admin/admin123 (Manager) or student/student123 (Student)

## Database Tables

- `managers` - Manager accounts (instant approval)
- `students` - Student accounts (requires approval)
- `books` - Book inventory
- `borrow_requests` - Book borrow requests and history
- `charges` - Student charges (weekly/monthly/late fees)

## Charge System Details

- **Weekly Fee**: $5.00 per book (charged on approval)
- **Borrow Period**: 14 days
- **Status Tracking**: Pending/Paid
- **Automatic Calculation**: Total pending charges displayed

## Security Features

- Password hashing using PHP's `password_hash()`
- Session-based authentication
- Role-based access control
- Approval workflow for student accounts
- SQL injection protection (Note: Use prepared statements for production)

## Browser Compatibility

Works on all modern browsers with smooth animations and responsive design.
