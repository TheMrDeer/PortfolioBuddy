<?php

/**
 * Update a user's profile data (fullname + email).
 */
function update_user_profile(mysqli $db, int $userId, string $fullname, string $email): array
{
    $checkSql = "SELECT id FROM users WHERE email = ? AND id <> ?";
    $checkStmt = $db->prepare($checkSql);
    if (!$checkStmt) {
        return ['success' => false, 'error' => 'DB error: ' . $db->error];
    }

    $checkStmt->bind_param("si", $email, $userId);
    if (!$checkStmt->execute()) {
        $error = $checkStmt->error ?: $db->error;
        $checkStmt->close();
        return ['success' => false, 'error' => 'DB error: ' . $error];
    }

    $existing = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();
    if ($existing) {
        return ['success' => false, 'error' => 'This email address is already in use.'];
    }

    $sql = "UPDATE users SET fullname = ?, email = ? WHERE id = ?";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'error' => 'DB error: ' . $db->error];
    }

    $stmt->bind_param("ssi", $fullname, $email, $userId);
    if (!$stmt->execute()) {
        $error = $stmt->error ?: $db->error;
        $stmt->close();
        return ['success' => false, 'error' => 'DB error: ' . $error];
    }

    $stmt->close();
    return ['success' => true, 'error' => ''];
}

/**
 * Update a user's password hash.
 */
function update_user_password(mysqli $db, int $userId, string $passwordHash): array
{
    $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'error' => 'DB error: ' . $db->error];
    }

    $stmt->bind_param("si", $passwordHash, $userId);
    if (!$stmt->execute()) {
        $error = $stmt->error ?: $db->error;
        $stmt->close();
        return ['success' => false, 'error' => 'DB error: ' . $error];
    }

    $stmt->close();
    return ['success' => true, 'error' => ''];
}
?>
