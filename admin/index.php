<?php
/**
 * IRECSTEM 2026 - Admin Panel (JSON Database)
 */

require_once '../config.php';
requireAdmin();

$message = '';
$message_type = '';

// Get stats
$db_users = users();
$db_papers = papers();

$all_users = $db_users->all();
$all_papers = $db_papers->all();

$total_users = count(array_filter($all_users, fn($u) => !($u['is_admin'] ?? false));
$total_papers = count($all_papers);
$pending_papers = count(array_filter($all_papers, fn($p) => $p['status'] === 'pending'));
$in_person = count(array_filter($all_users, fn($u) => ($u['participation_type'] ?? '') === 'in-person' && !($u['is_admin'] ?? false)));
$virtual = count(array_filter($all_users, fn($u) => ($u['participation_type'] ?? '') === 'virtual' && !($u['is_admin'] ?? false)));

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($_GET['action'] === 'approve_user') {
        $db_users->update($id, ['status' => 'approved']);
        $message = 'User approved successfully!';
        $message_type = 'success';
    } elseif ($_GET['action'] === 'reject_user') {
        $db_users->update($id, ['status' => 'rejected']);
        $message = 'User rejected!';
        $message_type = 'success';
    } elseif ($_GET['action'] === 'approve_paper') {
        $db_papers->update($id, ['status' => 'accepted']);
        $message = 'Paper accepted!';
        $message_type = 'success';
    } elseif ($_GET['action'] === 'reject_paper') {
        $db_papers->update($id, ['status' => 'rejected']);
        $message = 'Paper rejected!';
        $message_type = 'success';
    } elseif ($_GET['action'] === 'issue_certificate') {
        $user = $db_users->findById($id);
        if ($user) {
            $cert = [
                'user_id' => $id,
                'certificate_number' => generateCertificateNumber(),
                'issue_date' => date('Y-m-d'),
                'status' => 'issued'
            ];
            $cert_db = certificates();
            $cert_db->insert($cert);
            $message = 'Certificate issued!';
            $message_type = 'success';
        }
    }
}

// Refresh data after actions
$all_users = $db_users->all();
$all_papers = $db_papers->all();

// Get recent items
$recent_users = array_slice(array_filter($all_users, fn($u) => !($u['is_admin'] ?? false)), 0, 10);
$recent_papers = array_slice($all_papers, 0, 10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | IRECSTEM 2026</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { background: var(--gray-100); }
        .admin-container { padding-top: 100px; padding-bottom: 40px; }
        .admin-header {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-dark) 100%);
            padding: 30px 0;
            color: white;
            margin-bottom: 30px;
        }
        .admin-header h1 { color: white; margin-bottom: 5px; }
        .admin-nav {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .admin-nav a {
            padding: 8px 16px;
            background: rgba(255,255,255,0.1);
            color: white;
            border-radius: var(--radius);
            font-size: 0.9rem;
        }
        .admin-nav a:hover, .admin-nav a.active {
            background: var(--primary);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            text-align: center;
        }
        .stat-card i {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        .stat-card h3 {
            font-size: 2rem;
            color: var(--navy);
            margin-bottom: 5px;
        }
        .stat-card p {
            color: var(--gray-500);
            font-size: 0.9rem;
        }
        .data-section {
            background: white;
            border-radius: var(--radius-xl);
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        .data-section h2 {
            color: var(--navy);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--gray-200);
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }
        .data-table th {
            background: var(--gray-100);
            color: var(--dark);
            font-weight: 600;
        }
        .data-table tr:hover {
            background: var(--gray-100);
        }
        .action-btn {
            padding: 5px 10px;
            border-radius: var(--radius);
            font-size: 0.8rem;
            margin-right: 5px;
            text-decoration: none;
        }
        .btn-approve {
            background: #dcfce7;
            color: #16a34a;
        }
        .btn-reject {
            background: #fee2e2;
            color: #dc2626;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-approved { background: #dcfce7; color: #16a34a; }
        .status-rejected { background: #fee2e2; color: #dc2626; }
        .status-accepted { background: #dcfce7; color: #16a34a; }
        .status-reviewing { background: #dbeafe; color: #2563eb; }
        .alert {
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
        }
        .alert-success { background: #dcfce7; color: #16a34a; }
        .nav-brand { margin-right: auto; }
        .admin-nav-top {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        @media (max-width: 768px) {
            .data-table { font-size: 0.85rem; }
            .data-table th, .data-table td { padding: 8px 10px; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="../index.html">
                    <div class="logo-placeholder conference-logo">
                        <i class="fas fa-globe-americas"></i>
                        <span>IRECSTEM</span>
                    </div>
                </a>
            </div>
            <div class="admin-nav-top">
                <a href="../dashboard.php" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.9rem;">
                    <i class="fas fa-user"></i> Participant View
                </a>
                <a href="../logout.php" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.9rem;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <div class="container">
            <div class="admin-header">
                <div class="container">
                    <h1><i class="fas fa-cog"></i> Admin Panel</h1>
                    <p>Manage registrations, papers, and certificates</p>
                    <div class="admin-nav">
                        <a href="#" class="active">Dashboard</a>
                        <a href="users.php">All Users</a>
                        <a href="papers.php">All Papers</a>
                        <a href="certificates.php">Certificates</a>
                    </div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="container">
                    <div class="alert alert-success"><?php echo $message; ?></div>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Participants</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-file-alt"></i>
                        <h3><?php echo $total_papers; ?></h3>
                        <p>Papers Submitted</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-clock"></i>
                        <h3><?php echo $pending_papers; ?></h3>
                        <p>Pending Review</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-building"></i>
                        <h3><?php echo $in_person; ?></h3>
                        <p>In-Person</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-laptop"></i>
                        <h3><?php echo $virtual; ?></h3>
                        <p>Virtual</p>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="container">
                <div class="data-section">
                    <h2><i class="fas fa-users" style="color: var(--primary); margin-right: 10px;"></i>Recent Registrations</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Institution</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_users as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['institution'] ?: '-'); ?></td>
                                    <td><?php echo ucfirst($row['participation_type'] ?? 'in-person'); ?></td>
                                    <td><span class="status-badge status-<?php echo $row['status'] ?? 'approved'; ?>"><?php echo ucfirst($row['status'] ?? 'approved'); ?></span></td>
                                    <td>
                                        <?php if (($row['status'] ?? 'approved') === 'pending'): ?>
                                            <a href="?action=approve_user&id=<?php echo $row['id']; ?>" class="action-btn btn-approve">Approve</a>
                                            <a href="?action=reject_user&id=<?php echo $row['id']; ?>" class="action-btn btn-reject">Reject</a>
                                        <?php else: ?>
                                            <a href="users.php" style="color: var(--primary); font-size: 0.85rem;">View</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($recent_users) === 0): ?>
                                <tr><td colspan="6" style="text-align: center; color: var(--gray-500);">No registrations yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Papers -->
            <div class="container">
                <div class="data-section">
                    <h2><i class="fas fa-file-alt" style="color: var(--primary); margin-right: 10px;"></i>Recent Paper Submissions</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Track</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_papers as $row):
                                $paper_user = $db_users->findById($row['user_id']);
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($row['title'], 0, 50)); ?>...</td>
                                    <td><?php echo htmlspecialchars($paper_user['full_name'] ?? 'Unknown'); ?></td>
                                    <td><?php echo ucfirst($row['track']); ?></td>
                                    <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                    <td>
                                        <?php if ($row['status'] === 'pending' || $row['status'] === 'reviewing'): ?>
                                            <a href="?action=approve_paper&id=<?php echo $row['id']; ?>" class="action-btn btn-approve">Accept</a>
                                            <a href="?action=reject_paper&id=<?php echo $row['id']; ?>" class="action-btn btn-reject">Reject</a>
                                        <?php else: ?>
                                            <a href="papers.php" style="color: var(--primary); font-size: 0.85rem;">View</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($recent_papers) === 0): ?>
                                <tr><td colspan="5" style="text-align: center; color: var(--gray-500);">No papers submitted yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
