<?php
session_start();
require_once __DIR__ . '/includes/dbaccess.php';
require_once __DIR__ . '/util/profile_functions.php';
require_once __DIR__ . '/util/user_persistence_functions.php';
require_once __DIR__ . '/util/utils.php';
// If user is not logged in, redirect to login page.
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$isEditMode = isset($_GET['action']) && $_GET['action'] === 'edit';

// Initialize with session data
$currentUser = $_SESSION['user'];
$result = ['success' => false, 'errors' => [], 'data' => $currentUser];
$errors = [];

if ($isPost) {
    // Validate the submitted data
    $result = validate_profile_input($_POST);
    $errors = $result['errors'];

    if ($result['success']) {
        $db_obj = new mysqli($host, $user, $pass, $db);
        if ($db_obj->connect_error) {
            $errors[] = "DB error: " . $db_obj->connect_error;
        } else {
            $saveResult = update_user_profile(
                $db_obj,
                (int)$currentUser['id'],
                $result['data']['fullname'],
                $result['data']['email']
            );

            if ($saveResult['success']) {
                // Profilbild speichern, falls eine Datei hochgeladen wurde
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                    $pathUploadRoot = __DIR__ . '/user_uploads';
                    $userFolder     = $pathUploadRoot . '/' . $currentUser['id'] . '/profilepicture';
                    if (!is_dir($userFolder)) {
                        mkdir($userFolder, 0755, true);
                    }
                    $targetPath = $userFolder . '/' . basename($_FILES['profile_picture']['name']);
                    move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath);
                }

                $_SESSION['user']['fullname'] = $result['data']['fullname'];
                $_SESSION['user']['email'] = $result['data']['email'];

                $db_obj->close();
                header('Location: /profile.php?success=1');
                exit;
            }

            $errors[] = $saveResult['error'];
            $db_obj->close();
        }
    }

    $result['errors'] = $errors;
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>
<body>

<?php include __DIR__ .'/includes/_navbar.php'; ?>

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

                    <form method="post" enctype="multipart/form-data" action="/profile.php">
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
                        
                        <div class="mb-3">
                            <label for="profile_picture" class=form-label>Profile Picture</>
                            <input
                                id="profile_picture"
                                name="profile_picture"
                                type="file"
                                class="form-control"
                                <?= !$isEditMode ? 'disabled' : '' ?>
                            />
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <?php if ($isEditMode): ?>
                                <a href="/profile.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            <?php else: ?>
                                <a href="/profile.php?action=edit" class="btn btn-primary">Edit Profile</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
