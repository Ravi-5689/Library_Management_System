<?php
require_once 'db_connect.php';

define('FINE_PER_DAY', 1.00);

function addBook($title, $author, $isbn, $quantity) {
    global $conn;
    $title = $conn->real_escape_string($title);
    $author = $conn->real_escape_string($author);
    $isbn = $conn->real_escape_string($isbn);
    $quantity = (int)$quantity;
    $available_quantity = $quantity;
    $sql = "INSERT INTO books (title, author, isbn, quantity, available_quantity) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $title, $author, $isbn, $quantity, $available_quantity);
    return $stmt->execute();
}

function getAllBooks($search_term = '') {
    global $conn;
    $search_term = $conn->real_escape_string($search_term);
    $sql = "SELECT * FROM books";
    if (!empty($search_term)) {
        $sql .= " WHERE title LIKE '%$search_term%' OR author LIKE '%$search_term%' OR isbn LIKE '%$search_term%'";
    }
    $sql .= " ORDER BY title ASC";
    $result = $conn->query($sql);
    $books = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
    return $books;
}

function getBookById($book_id) {
    global $conn;
    $sql = "SELECT * FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function updateBook($book_id, $title, $author, $isbn, $quantity) {
    global $conn;
    $title = $conn->real_escape_string($title);
    $author = $conn->real_escape_string($author);
    $isbn = $conn->real_escape_string($isbn);
    $quantity = (int)$quantity;

    $current_book = getBookById($book_id);
    if (!$current_book) {
        return false;
    }

    $issued_count_sql = "SELECT COUNT(*) AS issued_count FROM issued_books WHERE book_id = ? AND (status = 'Issued' OR status = 'Renewed' OR status = 'Overdue')";
    $stmt_count = $conn->prepare($issued_count_sql);
    $stmt_count->bind_param("i", $book_id);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $issued_count = $result_count->fetch_assoc()['issued_count'];

    if ($quantity < $issued_count) {
        return false;
    }

    $available_quantity = $quantity - $issued_count;
    $sql = "UPDATE books SET title = ?, author = ?, isbn = ?, quantity = ?, available_quantity = ? WHERE book_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiii", $title, $author, $isbn, $quantity, $available_quantity, $book_id);
    return $stmt->execute();
}

function deleteBook($book_id) {
    global $conn;
    $issued_count_sql = "SELECT COUNT(*) AS issued_count FROM issued_books WHERE book_id = ? AND (status = 'Issued' OR status = 'Renewed' OR status = 'Overdue')";
    $stmt_check = $conn->prepare($issued_count_sql);
    $stmt_check->bind_param("i", $book_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $issued_count = $result_check->fetch_assoc()['issued_count'];

    if ($issued_count > 0) {
        return false;
    }

    $sql = "DELETE FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    return $stmt->execute();
}

function addMember($name, $address, $phone, $email) {
    global $conn;
    $name = $conn->real_escape_string($name);
    $address = $conn->real_escape_string($address);
    $phone = $conn->real_escape_string($phone);
    $email = $conn->real_escape_string($email);

    $sql = "INSERT INTO members (name, address, phone, email) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $address, $phone, $email);
    return $stmt->execute();
}

function getAllMembers($search_term = '') {
    global $conn;
    $search_term = $conn->real_escape_string($search_term);
    $sql = "SELECT * FROM members";
    if (!empty($search_term)) {
        $sql .= " WHERE name LIKE '%$search_term%' OR phone LIKE '%$search_term%' OR email LIKE '%$search_term%'";
    }
    $sql .= " ORDER BY name ASC";
    $result = $conn->query($sql);
    $members = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }
    }
    return $members;
}

function getMemberById($member_id) {
    global $conn;
    $sql = "SELECT * FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function updateMember($member_id, $name, $address, $phone, $email) {
    global $conn;
    $name = $conn->real_escape_string($name);
    $address = $conn->real_escape_string($address);
    $phone = $conn->real_escape_string($phone);
    $email = $conn->real_escape_string($email);

    $sql = "UPDATE members SET name = ?, address = ?, phone = ?, email = ? WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $address, $phone, $email, $member_id);
    return $stmt->execute();
}

function deleteMember($member_id) {
    global $conn;
    $issued_count_sql = "SELECT COUNT(*) AS issued_count FROM issued_books WHERE member_id = ? AND (status = 'Issued' OR status = 'Renewed' OR status = 'Overdue')";
    $stmt_check = $conn->prepare($issued_count_sql);
    $stmt_check->bind_param("i", $member_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $issued_count = $result_check->fetch_assoc()['issued_count'];

    if ($issued_count > 0) {
        return false;
    }

    $sql = "DELETE FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $member_id);
    return $stmt->execute();
}

function issueBook($book_id, $member_id, $issue_date, $due_date) {
    global $conn;

    $book = getBookById($book_id);
    if (!$book || $book['available_quantity'] <= 0) {
        return false;
    }

    $member = getMemberById($member_id);
    if (!$member) {
        return false;
    }

    $sql = "INSERT INTO issued_books (book_id, member_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, 'Issued')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $book_id, $member_id, $issue_date, $due_date);

    if ($stmt->execute()) {
        $update_book_sql = "UPDATE books SET available_quantity = available_quantity - 1 WHERE book_id = ?";
        $stmt_update = $conn->prepare($update_book_sql);
        $stmt_update->bind_param("i", $book_id);
        $stmt_update->execute();
        return true;
    }
    return false;
}

function calculateFine($due_date, $return_date) {
    if (strtotime($return_date) > strtotime($due_date)) {
        $diff = abs(strtotime($return_date) - strtotime($due_date));
        $days_overdue = floor($diff / (60 * 60 * 24));
        return $days_overdue * FINE_PER_DAY;
    }
    return 0.00;
}

function returnBook($issue_id) {
    global $conn;

    $sql = "SELECT book_id, due_date FROM issued_books WHERE issue_id = ?";
    $stmt_select = $conn->prepare($sql);
    $stmt_select->bind_param("i", $issue_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $issued_book = $result->fetch_assoc();

    if (!$issued_book) {
        return false;
    }

    $book_id = $issued_book['book_id'];
    $due_date = $issued_book['due_date'];
    $return_date = date('Y-m-d');
    $fine_amount = calculateFine($due_date, $return_date);

    $sql_update = "UPDATE issued_books SET return_date = ?, status = 'Returned', fine_amount = ? WHERE issue_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sdi", $return_date, $fine_amount, $issue_id);

    if ($stmt->execute()) {
        $update_book_sql = "UPDATE books SET available_quantity = available_quantity + 1 WHERE book_id = ?";
        $stmt_update = $conn->prepare($update_book_sql);
        $stmt_update->bind_param("i", $book_id);
        $stmt_update->execute();
        return true;
    }
    return false;
}

function renewBook($issue_id, $new_due_date) {
    global $conn;

    $sql = "SELECT * FROM issued_books WHERE issue_id = ?";
    $stmt_select = $conn->prepare($sql);
    $stmt_select->bind_param("i", $issue_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $issue_record = $result->fetch_assoc();

    if (!$issue_record || ($issue_record['status'] != 'Issued' && $issue_record['status'] != 'Overdue' && $issue_record['status'] != 'Renewed')) {
        return false;
    }

    if (strtotime($new_due_date) <= strtotime($issue_record['due_date'])) {
        return false;
    }

    $sql = "UPDATE issued_books SET due_date = ?, status = 'Renewed' WHERE issue_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_due_date, $issue_id);
    return $stmt->execute();
}

function getAllIssuedBooks($search_term = '') {
    global $conn;
    $search_term = $conn->real_escape_string($search_term);
    $sql = "SELECT ib.*, b.title AS book_title, b.author AS book_author, m.name AS member_name
            FROM issued_books ib
            JOIN books b ON ib.book_id = b.book_id
            JOIN members m ON ib.member_id = m.member_id";
    if (!empty($search_term)) {
        $sql .= " WHERE b.title LIKE '%$search_term%' OR m.name LIKE '%$search_term%' OR ib.status LIKE '%$search_term%'";
    }
    $sql .= " ORDER BY ib.issue_date DESC";
    $result = $conn->query($sql);
    $issued_books = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $issued_books[] = $row;
        }
    }
    return $issued_books;
}

function getIssuedBooksByMemberId($member_id) {
    global $conn;
    $sql = "SELECT ib.*, b.title AS book_title, b.author AS book_author
            FROM issued_books ib
            JOIN books b ON ib.book_id = b.book_id
            WHERE ib.member_id = ?
            ORDER BY ib.issue_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member_issued_books = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $member_issued_books[] = $row;
        }
    }
    return $member_issued_books;
}

function updateOverdueStatus() {
    global $conn;
    $sql = "UPDATE issued_books SET status = 'Overdue' WHERE status = 'Issued' AND due_date < CURDATE()";
    $conn->query($sql);
}

updateOverdueStatus();
?>
