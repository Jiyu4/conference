<?php
/**
 * IRECSTEM 2026 - Participant Dashboard (JSON Database)
 */

require_once 'config.php';
requireLogin();

$user = getCurrentUser();
$message = '';
$message_type = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $institution = sanitize($_POST['institution'] ?? '');
    $country = sanitize($_POST['country'] ?? '');
    $dietary = sanitize($_POST['dietary'] ?? '');
    $participation_type = sanitize($_POST['participation_type'] ?? 'in-person');

    $db = users();
    $update_data = [
        'full_name' => $full_name,
        'institution' => $institution,
        'country' => $country,
        'dietary' => $dietary,
        'participation_type' => $participation_type
    ];

    if ($db->update($_SESSION['user_id'], $update_data)) {
        $message = 'Profile updated successfully!';
        $message_type = 'success';
        $user = getCurrentUser(); // Refresh user data
    } else {
        $message = 'Failed to update profile.';
        $message_type = 'error';
    }
}

// Handle paper upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_paper') {
    $title = sanitize($_POST['paper_title'] ?? '');
    $abstract = sanitize($_POST['paper_abstract'] ?? '');
    $keywords = sanitize($_POST['paper_keywords'] ?? '');
    $track = sanitize($_POST['paper_track'] ?? '');

    $file_path = '';
    if (isset($_FILES['paper_file']) && $_FILES['paper_file']['error'] === 0) {
        $allowed = ['pdf', 'doc', 'docx'];
        $ext = strtolower(pathinfo($_FILES['paper_file']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $filename = $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $file_path = 'uploads/' . $filename;
            move_uploaded_file($_FILES['paper_file']['tmp_name'], UPLOAD_DIR . $filename);
        }
    }

    $paper = [
        'user_id' => $_SESSION['user_id'],
        'title' => $title,
        'abstract' => $abstract,
        'keywords' => $keywords,
        'track' => $track,
        'file_path' => $file_path,
        'status' => 'pending'
    ];

    $db_papers = papers();
    if ($db_papers->insert($paper)) {
        $message = 'Paper submitted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Failed to submit paper.';
        $message_type = 'error';
    }
}

// Get user's papers
$papers_db = papers();
$papers = $papers_db->findAll(['user_id' => $_SESSION['user_id']]);

// Get certificate status
$cert_db = certificates();
$certificate = $cert_db->findBy('user_id', $_SESSION['user_id']);

// Check if conference has passed for certificate availability
$conference_date = new DateTime('2026-09-17');
$today = new DateTime();
$certificates_available = $today >= $conference_date;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | IRECSTEM 2026</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        .dashboard-container {
            padding-top: 100px;
            min-height: 100vh;
            background: var(--gray-100);
        }
        .dashboard-header {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-dark) 100%);
            padding: 40px 0;
            color: white;
        }
        .dashboard-header h1 {
            color: white;
            margin-bottom: 5px;
        }
        .dashboard-header p {
            color: rgba(255,255,255,0.8);
        }
        .dashboard-content {
            padding: 40px 0;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        .dashboard-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: 30px;
            box-shadow: var(--shadow);
        }
        .dashboard-card h3 {
            color: var(--navy);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--gray-200);
        }
        .dashboard-card h3 i {
            color: var(--primary);
            margin-right: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: var(--dark);
            font-weight: 500;
            margin-bottom: 8px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 0.95rem;
            transition: var(--transition);
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .alert {
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
        }
        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .paper-item {
            padding: 20px;
            background: var(--gray-100);
            border-radius: var(--radius);
            margin-bottom: 15px;
        }
        .paper-item:last-child {
            margin-bottom: 0;
        }
        .paper-item h4 {
            color: var(--navy);
            margin-bottom: 5px;
        }
        .paper-item .meta {
            font-size: 0.85rem;
            color: var(--gray-500);
            margin-bottom: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }
        .status-reviewing {
            background: #dbeafe;
            color: #2563eb;
        }
        .status-accepted {
            background: #dcfce7;
            color: #16a34a;
        }
        .status-rejected {
            background: #fee2e2;
            color: #dc2626;
        }
        .user-info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-200);
        }
        .user-info-item:last-child {
            border-bottom: none;
        }
        .user-info-item span:first-child {
            color: var(--gray-500);
        }
        .user-info-item span:last-child {
            color: var(--dark);
            font-weight: 500;
        }
        .certificate-box {
            text-align: center;
            padding: 30px;
        }
        .certificate-box i {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
        .certificate-box.disabled {
            opacity: 0.5;
        }
        .certificate-box.disabled i {
            color: var(--gray-400);
        }
        .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .nav-user {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--dark);
        }
        .nav-user i {
            color: var(--primary);
        }
        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="index.html">
                    <div class="logo-placeholder conference-logo">
                        <i class="fas fa-globe-americas"></i>
                        <span>IRECSTEM</span>
                    </div>
                </a>
            </div>
            <div class="nav-right">
                <div class="nav-user">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
                <a href="logout.php" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.9rem;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="container">
                <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                <p>Manage your conference registration and submissions</p>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="container">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="dashboard-grid">
                    <!-- Sidebar -->
                    <div>
                        <!-- User Info Card -->
                        <div class="dashboard-card" style="margin-bottom: 30px;">
                            <h3><i class="fas fa-user"></i> My Profile</h3>
                            <div class="user-info-item">
                                <span>Email</span>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="user-info-item">
                                <span>Institution</span>
                                <span><?php echo htmlspecialchars($user['institution'] ?: 'Not set'); ?></span>
                            </div>
                            <div class="user-info-item">
                                <span>Country</span>
                                <span><?php echo htmlspecialchars($user['country'] ?: 'Not set'); ?></span>
                            </div>
                            <div class="user-info-item">
                                <span>Participation</span>
                                <span><?php echo ucfirst($user['participation_type']); ?></span>
                            </div>
                            <div class="user-info-item">
                                <span>Status</span>
                                <span class="status-badge status-<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span>
                            </div>
                        </div>

                        <!-- Certificate Card -->
                        <div class="dashboard-card">
                            <h3><i class="fas fa-certificate"></i> Certificate</h3>
                            <div class="certificate-box <?php echo $certificates_available ? '' : 'disabled'; ?>">
                                <?php if ($certificates_available && $certificate): ?>
                                    <i class="fas fa-certificate"></i>
                                    <h4>Certificate Available!</h4>
                                    <p style="color: var(--gray-500); margin: 10px 0;"><?php echo $certificate['certificate_number']; ?></p>
                                    <a href="#" class="btn btn-primary" onclick="alert('Certificate download coming soon!'); return false;">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                <?php elseif ($certificates_available): ?>
                                    <i class="fas fa-certificate"></i>
                                    <h4>Certificate Processing</h4>
                                    <p style="color: var(--gray-500); margin: 10px 0;">Your certificate is being prepared.</p>
                                <?php else: ?>
                                    <i class="fas fa-clock"></i>
                                    <h4>Coming Soon</h4>
                                    <p style="color: var(--gray-500); margin: 10px 0;">Certificates will be available after the conference (Sept 17, 2026).</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div>
                        <!-- Edit Profile Card -->
                        <div class="dashboard-card" style="margin-bottom: 30px;">
                            <h3><i class="fas fa-edit"></i> Edit Profile</h3>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_profile">
                                <div class="form-group">
                                    <label for="full_name">Full Name</label>
                                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="participation_type">Participation Type</label>
                                    <select id="participation_type" name="participation_type">
                                        <option value="in-person" <?php echo $user['participation_type'] === 'in-person' ? 'selected' : ''; ?>>In-Person</option>
                                        <option value="virtual" <?php echo $user['participation_type'] === 'virtual' ? 'selected' : ''; ?>>Virtual</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="institution">Institution/Organization</label>
                                    <input type="text" id="institution" name="institution" value="<?php echo htmlspecialchars($user['institution'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="dietary">Dietary Requirements</label>
                                    <input type="text" id="dietary" name="dietary" value="<?php echo htmlspecialchars($user['dietary'] ?? ''); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>

                        <!-- Upload Paper Card -->
                        <div class="dashboard-card">
                            <h3><i class="fas fa-file-upload"></i> Submit Research Paper</h3>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="upload_paper">
                                <div class="form-group">
                                    <label for="paper_title">Paper Title *</label>
                                    <input type="text" id="paper_title" name="paper_title" required placeholder="Enter paper title">
                                </div>
                                <div class="form-group">
                                    <label for="paper_track">Conference Track *</label>
                                    <select id="paper_track" name="paper_track" required>
                                        <option value="">Select a track</option>
                                        <option value="science">Science</option>
                                        <option value="technology">Technology</option>
                                        <option value="education">Education</option>
                                        <option value="management">Management</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="paper_abstract">Abstract (200-300 words) *</label>
                                    <textarea id="paper_abstract" name="paper_abstract" required placeholder="Enter paper abstract"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="paper_keywords">Keywords (comma separated)</label>
                                    <input type="text" id="paper_keywords" name="paper_keywords" placeholder="e.g., AI, Education, Machine Learning">
                                </div>
                                <div class="form-group">
                                    <label for="paper_file">Upload Paper (PDF/DOC) *</label>
                                    <input type="file" id="paper_file" name="paper_file" accept=".pdf,.doc,.docx" required style="padding: 10px; border: 2px dashed var(--gray-300); border-radius: var(--radius); width: 100%;">
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Submit Paper</button>
                            </form>
                        </div>

                        <!-- My Papers Card -->
                        <?php if (count($papers) > 0): ?>
                        <div class="dashboard-card" style="margin-top: 30px;">
                            <h3><i class="fas fa-file-alt"></i> My Submissions</h3>
                            <?php foreach ($papers as $paper): ?>
                                <div class="paper-item">
                                    <h4><?php echo htmlspecialchars($paper['title']); ?></h4>
                                    <div class="meta">
                                        <strong>Track:</strong> <?php echo ucfirst($paper['track']); ?> |
                                        <strong>Submitted:</strong> <?php echo date('M d, Y', strtotime($paper['created_at'])); ?>
                                    </div>
                                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 10px;">
                                        <?php echo htmlspecialchars(substr($paper['abstract'], 0, 150)); ?>...
                                    </p>
                                    <span class="status-badge status-<?php echo $paper['status']; ?>">
                                        <?php echo ucfirst($paper['status']); ?>
                                    </span>
                                    <?php if (!empty($paper['file_path'])): ?>
                                        <a href="<?php echo $paper['file_path']; ?>" class="btn btn-outline" style="padding: 5px 12px; font-size: 0.8rem; margin-left: 10px;">
                                            <i class="fas fa-download"></i> View
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
