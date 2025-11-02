<?php
session_start();
require_once __DIR__ . '/profile_functions.php';

// If user is not logged in, redirect to login page.
if (!isset($_SESSION['user'])) {
    header('Location: /PortfolioBuddy/login.php');
    exit;
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$isEditMode = isset($_GET['action']) && $_GET['action'] === 'edit';

// Initialize with session data
$user = $_SESSION['user'];
$result = ['success' => false, 'errors' => [], 'data' => $user];

if ($isPost) {
    // Validate the submitted data
    $result = validate_profile_input($_POST);

    if ($result['success']) {
        // On successful validation, update the session data.
        // In a real application, you would update the database here.
        $_SESSION['user']['fullname'] = $result['data']['fullname'];
        $_SESSION['user']['email'] = $result['data']['email'];

        // Redirect to the profile page in view mode to show the changes
        header('Location: /PortfolioBuddy/profile.php?success=1');
        exit;
    } else {
        // If validation fails, stay in edit mode to show errors.
        $isEditMode = true;
    }
}

// Prefill data for the form fields, escaping for security.
$prefillFullname = htmlspecialchars($result['data']['fullname'] ?? '', ENT_QUOTES, 'UTF-8');
$prefillEmail = htmlspecialchars($result['data']['email'] ?? '', ENT_QUOTES, 'UTF-8');
$errors = $result['errors'];

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>My Profile — PortfolioBuddy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include '_navbar.php'; // Using a shared navbar for consistency ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 mb-4">My Profile</h1>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">Profile updated successfully!</div>
                    <?php endif; ?>

                    <?php if ($isPost && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/PortfolioBuddy/profile.php">
                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input
                                id="fullname"
                                name="fullname"
                                type="text"
                                class="form-control"
                                value="<?= $prefillFullname ?>"
                                <?= !$isEditMode ? 'readonly' : '' ?>
                            />
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                class="form-control"
                                value="<?= $prefillEmail ?>"
                                <?= !$isEditMode ? 'readonly' : '' ?>
                            />
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <?php if ($isEditMode): ?>
                                <a href="/PortfolioBuddy/profile.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            <?php else: ?>
                                <a href="/PortfolioBuddy/profile.php?action=edit" class="btn btn-primary">Edit Profile</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
