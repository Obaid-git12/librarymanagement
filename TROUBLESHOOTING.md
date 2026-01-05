# Troubleshooting Guide

## Can't Login After Signup?

### Step 1: Check Database Setup

Visit `test_login.php` in your browser to check:
- Database connection status
- If users table exists
- List of all users and their status

### Step 2: Database Setup Options

**Option A: Fresh Install (Recommended)**
```sql
mysql -u root -p < setup_database.sql
```
This creates everything from scratch.

**Option B: Update Existing Database**
```sql
mysql -u root -p < update_database.sql
```
This adds missing columns to your existing database.

### Step 3: Common Issues

#### Issue: "User not found or incorrect role"
**Solution:** Make sure you're selecting the correct role (Manager/Student) when logging in.

#### Issue: "Account is pending approval"
**Solution:** 
1. Login as manager (username: `admin`, password: `admin123`)
2. Go to "Student Approvals" tab
3. Approve the pending student account
4. Now the student can login

#### Issue: "Invalid password"
**Solution:** 
- Make sure you're typing the password correctly
- Passwords are case-sensitive

#### Issue: Can't see any users in test_login.php
**Solution:** 
1. Run `setup_database.sql` to create default accounts
2. Or manually create a manager account:
```sql
INSERT INTO users (username, password, role, status) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'approved');
```

### Step 4: Test Default Accounts

**Manager Account:**
- Username: `admin`
- Password: `admin123`
- Role: Manager
- Status: Auto-approved

**Student Account:**
- Username: `student`
- Password: `student123`
- Role: Student
- Status: Auto-approved

### Step 5: Student Signup Flow

1. Student goes to `signup.php`
2. Fills out registration form
3. Account is created with status = "pending"
4. Manager logs in and approves the student
5. Student can now login

## Database Structure Check

Run this SQL to verify your tables:
```sql
USE library_db;
SHOW TABLES;
DESCRIBE users;
DESCRIBE books;
DESCRIBE borrow_requests;
DESCRIBE charges;
```

## Still Having Issues?

1. Check `test_login.php` to see all users
2. Make sure the user's status is "approved"
3. Make sure you're selecting the correct role when logging in
4. Clear your browser cache and cookies
5. Check PHP error logs for detailed errors
