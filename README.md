📚 Library Management System
A comprehensive and visually appealing web-based Library Management System designed to streamline the process of managing books, members, and book transactions within a library setting. This system includes features for adding, updating, and deleting books and members, as well as handling book issuance, returns with fine calculations, and renewals.

✨ Features
Book Management (CRUD):

Add new books with details like title, author, ISBN, and quantity.

View a list of all books with their total and available quantities.

Edit existing book details.

Delete books (only if no copies are currently issued).

Search books by title, author, or ISBN.

Member Management (CRUD):

Register new library members with their name, address, phone, and email.

View a list of all registered members.

Edit member information.

Delete members (only if no books are currently issued to them).

Search members by name, phone, or email.

View a detailed history of all books issued to a specific member.

Book Transactions:

Issue Book: Assign an available book to a member, with an option for a custom due date.

Return Book: Mark a book as returned. Automatically calculates and displays fines for overdue books.

Renew Book: Extend the due date for currently issued or overdue books.

Status Tracking:

Real-time status updates for issued books: 'Issued', 'Returned', 'Renewed', 'Overdue'.

Automatic flagging of overdue books.

User Interface:

Clean, intuitive, and responsive design with enhanced CSS for a modern user experience.

Flash messages for feedback on operations.

🛠️ Technologies Used
Frontend: HTML5, CSS3 (Tailwind CSS via CDN), JavaScript

Backend: PHP (Server-side scripting)

Database: MySQL (managed via phpMyAdmin)

Local Server: XAMPP (Apache + MySQL + PHP)

🚀 Getting Started
Follow these steps to set up and run the project on your local machine.

Prerequisites
Before you begin, ensure you have the following installed:

XAMPP: A free and open-source cross-platform web server solution package. Download from https://www.apachefriends.org/index.html.

Git: A distributed version control system. Download from https://git-scm.com/downloads.

1. Install XAMPP
Download and install XAMPP for your operating system.

Once installed, open the XAMPP Control Panel.

Start the Apache and MySQL modules. Ensure their status indicators turn green.

2. Database Setup
Access phpMyAdmin in your web browser by navigating to: http://localhost/phpmyadmin/

Import the Database Schema:

You'll need the library_db.sql file. This file contains the SQL commands to create the library_db database and its tables (books, members, issued_books).

In phpMyAdmin, click the "Import" tab.

Click "Choose File" and select your library_db.sql file.

Click the "Go" button at the bottom of the page to execute the SQL commands.

3. Project Files Setup
Clone the Repository:
Open your terminal or command prompt and navigate to your XAMPP's htdocs directory.

Windows: cd C:\xampp\htdocs\

macOS: cd /Applications/XAMPP/htdocs/

Linux: cd /opt/lampp/htdocs/

Clone this repository into the htdocs directory:

git clone <your-repository-url> library_management

(Replace <your-repository-url> with the actual URL of this Git repository)

Alternatively, if you've downloaded a ZIP file of the project, extract the library_management folder directly into your XAMPP htdocs directory.

4. Run the Application
Ensure Apache and MySQL are running in your XAMPP Control Panel.

Open your web browser and navigate to the project:
http://localhost/library_management/

💻 Repository Usage (Git)
This project uses Git for version control.

Clone:

git clone <your-repository-url>

Add Changes:

git add .

Commit Changes:

git commit -m "Your descriptive commit message"

Push Changes:

git push origin main

📚 How to Use
Manage Books & Issues (index.php):

Use the "Add New Book" section to add new entries.

The "Available Books" table lists all books, allowing you to edit or delete them.

The "Issue Book" section lets you issue books to registered members.

The "Issued Books Status" table shows all transactions, enabling returns and renewals.

Use the search bars to filter books and issued records.

Manage Members (members.php):

Use the "Add New Member" section to register new library members.

The "Registered Members" table lists all members, allowing you to edit or delete them.

Click "View History" next to a member to see their issued book history.

Use the search bar to filter members.

🤝 Contributing
Contributions are welcome! If you find a bug or want to add a feature, please feel free to open an issue or submit a pull request.

