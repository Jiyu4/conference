<?php
/**
 * IRECSTEM 2026 - Authentication Handler
 * Login, Register, Logout (JSON Database)
 */

require_once 'config.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        // Handle Registration
        $full_name = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $institution = sanitize($_POST['institution'] ?? '');
        $country = sanitize($_POST['country'] ?? '');
        $dietary = sanitize($_POST['dietary'] ?? '');
        $participation_type = sanitize($_POST['participation_type'] ?? 'in-person');

        // Validation
        if (empty($full_name) || empty($email) || empty($password)) {
            $message = 'Please fill in all required fields.';
            $message_type = 'error';
        } elseif ($password !== $confirm_password) {
            $message = 'Passwords do not match.';
            $message_type = 'error';
        } elseif (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters.';
            $message_type = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $message_type = 'error';
        } else {
            $db = users();

            // Check if email exists
            if ($db->exists('email', $email)) {
                $message = 'An account with this email already exists.';
                $message_type = 'error';
            } else {
                // Hash password and create user
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $user = [
                    'full_name' => $full_name,
                    'email' => $email,
                    'password' => $password_hash,
                    'institution' => $institution,
                    'country' => $country,
                    'dietary' => $dietary,
                    'participation_type' => $participation_type,
                    'status' => 'approved',
                    'is_admin' => 0
                ];

                $user_id = $db->insert($user);

                if ($user_id) {
                    // Auto login after registration
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['is_admin'] = 0;

                    header('Location: dashboard.php');
                    exit;
                } else {
                    $message = 'Registration failed. Please try again.';
                    $message_type = 'error';
                }
            }
        }
    } elseif ($action === 'login') {
        // Handle Login
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $message = 'Please enter both email and password.';
            $message_type = 'error';
        } else {
            $db = users();
            $user = $db->findBy('email', $email);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'] ?? 0;

                if ($user['is_admin'] ?? 0) {
                    header('Location: admin/');
                } else {
                    header('Location: dashboard.php');
                }
                exit;
            } else {
                $message = 'Invalid email or password.';
                $message_type = 'error';
            }
        }
    }
}

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_GET['register']) ? 'Register' : 'Login'; ?> | IRECSTEM 2026</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 20px 60px;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-dark) 100%);
        }
        .auth-card {
            background: white;
            padding: 50px;
            border-radius: var(--radius-xl);
            width: 100%;
            max-width: 450px;
            box-shadow: var(--shadow-xl);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-header .logo {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
        .auth-header h1 {
            color: var(--navy);
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        .auth-header p {
            color: var(--gray-500);
        }
        .auth-form .form-group {
            margin-bottom: 20px;
        }
        .auth-form label {
            display: block;
            color: var(--dark);
            font-weight: 500;
            margin-bottom: 8px;
        }
        .auth-form input,
        .auth-form select {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
        }
        .auth-form input:focus,
        .auth-form select:focus {
            outline: none;
            border-color: var(--primary);
        }
        .auth-form .btn {
            width: 100%;
            margin-top: 10px;
        }
        .auth-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid var(--gray-200);
        }
        .auth-footer a {
            color: var(--primary);
            font-weight: 600;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
        }
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        @media (max-width: 480px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .auth-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo"><i class="fas fa-globe-americas"></i></div>
                <h1><?php echo isset($_GET['register']) ? 'Create Account' : 'Welcome Back'; ?></h1>
                <p>IRECSTEM 2026 <?php echo isset($_GET['register']) ? 'Registration' : 'Login'; ?></p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['register'])): ?>
            <!-- Registration Form -->
            <form class="auth-form" method="POST" action="">
                <input type="hidden" name="action" value="register">

                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required placeholder="Min 6 characters">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm password">
                    </div>
                </div>

                <div class="form-group">
                    <label for="participation_type">Participation Type</label>
                    <select id="participation_type" name="participation_type">
                        <option value="in-person">In-Person (Venue)</option>
                        <option value="virtual">Virtual (Online)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="institution">Institution/Organization</label>
                    <input type="text" id="institution" name="institution" placeholder="Enter your institution">
                </div>

                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" placeholder="Enter your country">
                </div>

                <div class="form-group">
                    <label for="dietary">Dietary Requirements</label>
                    <input type="text" id="dietary" name="dietary" placeholder="e.g., Vegetarian, None">
                </div>

                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="auth.php">Login here</a>
            </div>

            <?php else: ?>
            <!-- Login Form -->
            <form class="auth-form" method="POST" action="">
                <input type="hidden" name="action" value="login">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="auth.php?register=1">Register here</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
