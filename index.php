<?php
require_once 'functions.php';

$message = '';
$error = '';

$book_search_term = isset($_GET['book_search']) ? $_GET['book_search'] : '';
$issued_book_search_term = isset($_GET['issued_book_search']) ? $_GET['issued_book_search'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_book'])) {
        $title = $_POST['title'];
        $author = $_POST['author'];
        $isbn = $_POST['isbn'];
        $quantity = $_POST['quantity'];
        if (addBook($title, $author, $isbn, $quantity)) {
            $message = "Book '$title' added successfully!";
        } else {
            $error = "Failed to add book or ISBN already exists.";
        }
    } elseif (isset($_POST['update_book'])) {
        $book_id = $_POST['book_id'];
        $title = $_POST['title'];
        $author = $_POST['author'];
        $isbn = $_POST['isbn'];
        $quantity = $_POST['quantity'];
        if (updateBook($book_id, $title, $author, $isbn, $quantity)) {
            $message = "Book '$title' updated successfully!";
        } else {
            $error = "Failed to update book. Check if quantity is less than currently issued or ISBN already exists.";
        }
    } elseif (isset($_POST['delete_book'])) {
        $book_id = $_POST['book_id'];
        if (deleteBook($book_id)) {
            $message = "Book deleted successfully!";
        } else {
            $error = "Failed to delete book. Ensure no copies are currently issued.";
        }
    } elseif (isset($_POST['issue_book'])) {
        $book_id = $_POST['book_id'];
        $member_id = $_POST['member_id'];
        $issue_date = date('Y-m-d');
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : date('Y-m-d', strtotime($issue_date . ' + 14 days'));

        if (issueBook($book_id, $member_id, $issue_date, $due_date)) {
            $message = "Book issued successfully!";
        } else {
            $error = "Failed to issue book. Check if book is available or member exists.";
        }
    } elseif (isset($_POST['return_book'])) {
        $issue_id = $_POST['issue_id'];
        if (returnBook($issue_id)) {
            $message = "Book returned successfully!";
        } else {
            $error = "Failed to return book.";
        }
    } elseif (isset($_POST['renew_book'])) {
        $issue_id = $_POST['issue_id'];
        $current_issue_res = $conn->query("SELECT due_date FROM issued_books WHERE issue_id = $issue_id");
        $current_issue = $current_issue_res ? $current_issue_res->fetch_assoc() : null;
        
        $base_date = ($current_issue && strtotime($current_issue['due_date']) < time()) ? date('Y-m-d') : ($current_issue['due_date'] ?? date('Y-m-d'));
        $new_due_date = date('Y-m-d', strtotime($base_date . ' + 14 days'));

        if (renewBook($issue_id, $new_due_date)) {
            $message = "Book renewed successfully until $new_due_date!";
        } else {
            $error = "Failed to renew book. It might already be returned or the new due date is not valid.";
        }
    }
}

$books = getAllBooks($book_search_term);
$members = getAllMembers();
$issued_books = getAllIssuedBooks($issued_book_search_term);

$edit_book = null;
if (isset($_GET['edit_book_id'])) {
    $edit_book_id = $_GET['edit_book_id'];
    $edit_book = getBookById($edit_book_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 p-4">
    <div class="container">
        <h1 class="text-4xl font-extrabold text-gray-800 text-center mb-8">
            📖 Library Management System
        </h1>

        <nav class="mb-8 flex justify-center space-x-4">
            <a href="index.php" class="btn-primary">Manage Books & Issues</a>
            <a href="members.php" class="btn-primary">Manage Members</a>
        </nav>

        <?php if ($message): ?>
            <div class="flash-message bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="flash-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <div class="section-card">
            <h2 class="text-3xl font-bold text-gray-700 mb-6">
                <?php echo $edit_book ? 'Edit Book' : 'Add New Book'; ?>
            </h2>
            <form action="index.php" method="POST" class="space-y-4">
                <?php if ($edit_book): ?>
                    <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($edit_book['book_id']); ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="title" class="block text-sm font-medium text-gray-700">Title:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($edit_book['title'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="author" class="block text-sm font-medium text-gray-700">Author:</label>
                    <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($edit_book['author'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="isbn" class="block text-sm font-medium text-gray-700">ISBN:</label>
                    <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($edit_book['isbn'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($edit_book['quantity'] ?? '0'); ?>" min="0" required>
                </div>
                <button type="submit" name="<?php echo $edit_book ? 'update_book' : 'add_book'; ?>" class="btn-success">
                    <?php echo $edit_book ? 'Update Book' : 'Add Book'; ?>
                </button>
                <?php if ($edit_book): ?>
                    <a href="index.php" class="btn-primary ml-2 inline-block">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="section-card">
            <h2 class="text-3xl font-bold text-gray-700 mb-6">Available Books</h2>
            <form action="index.php" method="GET" class="mb-6 flex space-x-2">
                <input type="text" name="book_search" placeholder="Search books by title, author, or ISBN..." value="<?php echo htmlspecialchars($book_search_term); ?>" class="flex-grow">
                <button type="submit" class="btn-primary">Search</button>
                <?php if (!empty($book_search_term)): ?>
                    <a href="index.php" class="btn-warning">Clear Search</a>
                <?php endif; ?>
            </form>

            <div class="overflow-x-auto">
                <table class="table-auto w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-6 py-3">ID</th>
                            <th class="px-6 py-3">Title</th>
                            <th class="px-6 py-3">Author</th>
                            <th class="px-6 py-3">ISBN</th>
                            <th class="px-6 py-3">Total Qty</th>
                            <th class="px-6 py-3">Available Qty</th>
                            <th class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($books)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No books found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($books as $book): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($book['book_id']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($book['isbn']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($book['quantity']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($book['available_quantity']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <a href="index.php?edit_book_id=<?php echo htmlspecialchars($book['book_id']); ?>" class="btn-warning text-xs">Edit</a>
                                        <form action="index.php" method="POST" class="inline-block" onsubmit="return confirmDelete('Are you sure you want to delete this book?');">
                                            <input type="hidden" name="delete_book" value="1">
                                            <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($book['book_id']); ?>">
                                            <button type="submit" class="btn-danger text-xs">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-card">
            <h2 class="text-3xl font-bold text-gray-700 mb-6">Issue Book</h2>
            <form action="index.php" method="POST" class="space-y-4">
                <div class="form-group">
                    <label for="issue_book_id" class="block text-sm font-medium text-gray-700">Select Book:</label>
                    <select id="issue_book_id" name="book_id" required>
                        <option value="">-- Select a Book --</option>
                        <?php foreach ($books as $book): ?>
                            <?php if ($book['available_quantity'] > 0): ?>
                                <option value="<?php echo htmlspecialchars($book['book_id']); ?>">
                                    <?php echo htmlspecialchars($book['title']); ?> (Available: <?php echo htmlspecialchars($book['available_quantity']); ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="issue_member_id" class="block text-sm font-medium text-gray-700">Select Member:</label>
                    <select id="issue_member_id" name="member_id" required>
                        <option value="">-- Select a Member --</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?php echo htmlspecialchars($member['member_id']); ?>">
                                <?php echo htmlspecialchars($member['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date (optional, defaults to 14 days):</label>
                    <input type="date" id="due_date" name="due_date" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <button type="submit" name="issue_book" class="btn-primary">Issue Book</button>
            </form>
        </div>

        <div class="section-card">
            <h2 class="text-3xl font-bold text-gray-700 mb-6">Issued Books Status</h2>
            <form action="index.php" method="GET" class="mb-6 flex space-x-2">
                <input type="text" name="issued_book_search" placeholder="Search issued books by title, member, or status..." value="<?php echo htmlspecialchars($issued_book_search_term); ?>" class="flex-grow">
                <button type="submit" class="btn-primary">Search</button>
                <?php if (!empty($issued_book_search_term)): ?>
                    <a href="index.php" class="btn-warning">Clear Search</a>
                <?php endif; ?>
            </form>

            <div class="overflow-x-auto">
                <table class="table-auto w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-6 py-3">Issue ID</th>
                            <th class="px-6 py-3">Book Title</th>
                            <th class="px-6 py-3">Member Name</th>
                            <th class="px-6 py-3">Issue Date</th>
                            <th class="px-6 py-3">Due Date</th>
                            <th class="px-6 py-3">Return Date</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Fine</th>
                            <th class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($issued_books)): ?>
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-gray-500">No books currently issued.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($issued_books as $issue): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($issue['issue_id']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($issue['book_title']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($issue['member_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($issue['issue_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                            $due_date_str = htmlspecialchars($issue['due_date']);
                                            $current_status = htmlspecialchars($issue['status']);
                                            if ($current_status == 'Overdue') {
                                                echo '<span class="status-badge bg-red-100 text-red-800">' . $due_date_str . '</span>';
                                            } else {
                                                echo $due_date_str;
                                            }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo $issue['return_date'] ? htmlspecialchars($issue['return_date']) : 'N/A'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge
                                            <?php
                                                if ($issue['status'] == 'Issued') echo 'bg-blue-100 text-blue-800';
                                                elseif ($issue['status'] == 'Returned') echo 'bg-green-100 text-green-800';
                                                elseif ($issue['status'] == 'Renewed') echo 'bg-yellow-100 text-yellow-800';
                                                elseif ($issue['status'] == 'Overdue') echo 'bg-red-100 text-red-800';
                                            ?>">
                                            <?php echo htmlspecialchars($issue['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                            if ($issue['fine_amount'] > 0) {
                                                echo '<span class="text-red-600 font-bold">$ ' . htmlspecialchars(number_format($issue['fine_amount'], 2)) . '</span>';
                                            } else {
                                                echo '$ 0.00';
                                            }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <?php if ($issue['status'] == 'Issued' || $issue['status'] == 'Renewed' || $issue['status'] == 'Overdue'): ?>
                                            <form action="index.php" method="POST" class="inline-block mr-1">
                                                <input type="hidden" name="return_book" value="1">
                                                <input type="hidden" name="issue_id" value="<?php echo htmlspecialchars($issue['issue_id']); ?>">
                                                <button type="submit" class="btn-success text-xs">Return</button>
                                            </form>
                                            <form action="index.php" method="POST" class="inline-block">
                                                <input type="hidden" name="renew_book" value="1">
                                                <input type="hidden" name="issue_id" value="<?php echo htmlspecialchars($issue['issue_id']); ?>">
                                                <button type="submit" class="btn-warning text-xs">Renew</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-gray-500 text-xs">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <script src="scripts.js"></script>
</body>
</html>
