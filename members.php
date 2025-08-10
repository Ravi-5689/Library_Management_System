<?php
require_once 'functions.php';

$message = '';
$error = '';

$member_search_term = isset($_GET['member_search']) ? $_GET['member_search'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_member'])) {
        $name = $_POST['name'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        if (addMember($name, $address, $phone, $email)) {
            $message = "Member '$name' added successfully!";
        } else {
            $error = "Failed to add member. Phone or Email might already exist.";
        }
    } elseif (isset($_POST['update_member'])) {
        $member_id = $_POST['member_id'];
        $name = $_POST['name'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        if (updateMember($member_id, $name, $address, $phone, $email)) {
            $message = "Member '$name' updated successfully!";
        } else {
            $error = "Failed to update member. Phone or Email might already exist.";
        }
    } elseif (isset($_POST['delete_member'])) {
        $member_id = $_POST['member_id'];
        if (deleteMember($member_id)) {
            $message = "Member deleted successfully!";
        } else {
            $error = "Failed to delete member. Ensure no books are currently issued to this member.";
        }
    }
}

$members = getAllMembers($member_search_term);

$edit_member = null;
if (isset($_GET['edit_member_id'])) {
    $edit_member_id = $_GET['edit_member_id'];
    $edit_member = getMemberById($edit_member_id);
}

$selected_member_history = null;
$member_issued_books_history = [];
if (isset($_GET['view_history_member_id'])) {
    $view_history_member_id = $_GET['view_history_member_id'];
    $selected_member_history = getMemberById($view_history_member_id);
    if ($selected_member_history) {
        $member_issued_books_history = getIssuedBooksByMemberId($view_history_member_id);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members - Library Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 p-4">
    <div class="container">
        <h1 class="text-4xl font-extrabold text-gray-800 text-center mb-8">
            👥 Member Management
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
                <?php echo $edit_member ? 'Edit Member' : 'Add New Member'; ?>
            </h2>
            <form action="members.php" method="POST" class="space-y-4">
                <?php if ($edit_member): ?>
                    <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($edit_member['member_id']); ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="name" class="block text-sm font-medium text-gray-700">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_member['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="address" class="block text-sm font-medium text-gray-700">Address:</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($edit_member['address'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($edit_member['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($edit_member['email'] ?? ''); ?>">
                </div>
                <button type="submit" name="<?php echo $edit_member ? 'update_member' : 'add_member'; ?>" class="btn-success">
                    <?php echo $edit_member ? 'Update Member' : 'Add Member'; ?>
                </button>
                <?php if ($edit_member): ?>
                    <a href="members.php" class="btn-primary ml-2 inline-block">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="section-card">
            <h2 class="text-3xl font-bold text-gray-700 mb-6">Registered Members</h2>
            <form action="members.php" method="GET" class="mb-6 flex space-x-2">
                <input type="text" name="member_search" placeholder="Search members by name, phone, or email..." value="<?php echo htmlspecialchars($member_search_term); ?>" class="flex-grow">
                <button type="submit" class="btn-primary">Search</button>
                <?php if (!empty($member_search_term)): ?>
                    <a href="members.php" class="btn-warning">Clear Search</a>
                <?php endif; ?>
            </form>

            <div class="overflow-x-auto">
                <table class="table-auto w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-6 py-3">ID</th>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Address</th>
                            <th class="px-6 py-3">Phone</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($members)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No members found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($member['member_id']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($member['name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($member['address']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($member['phone']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($member['email']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <a href="members.php?edit_member_id=<?php echo htmlspecialchars($member['member_id']); ?>" class="btn-warning text-xs">Edit</a>
                                        <form action="members.php" method="POST" class="inline-block" onsubmit="return confirmDelete('Are you sure you want to delete this member?');">
                                            <input type="hidden" name="delete_member" value="1">
                                            <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($member['member_id']); ?>">
                                            <button type="submit" class="btn-danger text-xs">Delete</button>
                                        </form>
                                        <a href="members.php?view_history_member_id=<?php echo htmlspecialchars($member['member_id']); ?>" class="btn-primary text-xs ml-1">View History</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($selected_member_history): ?>
            <div class="section-card">
                <h2 class="text-3xl font-bold text-gray-700 mb-6">Issued Books History for <?php echo htmlspecialchars($selected_member_history['name']); ?></h2>
                <a href="members.php" class="btn-primary mb-4 inline-block">Back to All Members</a>
                <div class="overflow-x-auto mt-4">
                    <table class="table-auto w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="px-6 py-3">Issue ID</th>
                                <th class="px-6 py-3">Book Title</th>
                                <th class="px-6 py-3">Author</th>
                                <th class="px-6 py-3">Issue Date</th>
                                <th class="px-6 py-3">Due Date</th>
                                <th class="px-6 py-3">Return Date</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Fine</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($member_issued_books_history)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">No books found for this member.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($member_issued_books_history as $issue): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($issue['issue_id']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($issue['book_title']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($issue['book_author']); ?></td>
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
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <script src="scripts.js"></script>
</body>
</html>
