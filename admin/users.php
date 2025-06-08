<?php
$page_title = "Kelola Pengguna";
$current_admin_page = 'users';
include_once '../partials/header.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: ../auth/login.php?error=Unauthorized");
    exit();
}
?>

<h2>Daftar Pengguna Sistem</h2>

<?php
if (isset($_GET['message'])) {
    echo '<p class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</p>';
}
if (isset($_GET['error'])) {
    echo '<p class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</p>';
}

$result = $conn->query("SELECT user_id, username, email, fullname, npm, role, birth_date FROM users ORDER BY role, username");
?>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Nama Lengkap</th>
            <th>NPM</th>
            <th>Role</th>
            <th>Tgl Lahir</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($user = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $user['user_id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                <td><?php echo htmlspecialchars($user['npm']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td><?php echo htmlspecialchars(date('d M Y', strtotime($user['birth_date']))); ?></td>
                <td>
                    <div class="action-buttons">
                        <a href="edit_user.php?user_id=<?php echo $user['user_id']; ?>" class="btn-small btn-edit">Edit</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8">Tidak ada data pengguna.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
include_once '../partials/footer.php';
?>