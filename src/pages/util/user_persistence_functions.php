<?php

/**
 * Update a user's profile data (fullname + email).
 */
function update_user_profile(mysqli $db, int $userId, string $fullname, string $email): array
{
    $checkSql = "SELECT id FROM users WHERE email = ? AND id <> ?";
    $checkStmt = $db->prepare($checkSql);
    if (!$checkStmt) {
        return ['success' => false, 'error' => 'DB-Fehler: ' . $db->error];
    }

    $checkStmt->bind_param("si", $email, $userId);
    if (!$checkStmt->execute()) {
        $error = $checkStmt->error ?: $db->error;
        $checkStmt->close();
        return ['success' => false, 'error' => 'DB-Fehler: ' . $error];
    }

    $existing = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();
    if ($existing) {
        return ['success' => false, 'error' => 'Diese E-Mail-Adresse wird bereits verwendet.'];
    }

    $sql = "UPDATE users SET fullname = ?, email = ? WHERE id = ?";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'error' => 'DB-Fehler: ' . $db->error];
    }

    $stmt->bind_param("ssi", $fullname, $email, $userId);
    if (!$stmt->execute()) {
        $error = $stmt->error ?: $db->error;
        $stmt->close();
        return ['success' => false, 'error' => 'DB-Fehler: ' . $error];
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
        return ['success' => false, 'error' => 'DB-Fehler: ' . $db->error];
    }

    $stmt->bind_param("si", $passwordHash, $userId);
    if (!$stmt->execute()) {
        $error = $stmt->error ?: $db->error;
        $stmt->close();
        return ['success' => false, 'error' => 'DB-Fehler: ' . $error];
    }

    $stmt->close();
    return ['success' => true, 'error' => ''];
}

/**
 * Update a user's role (admin|user).
 */
function update_user_role(mysqli $db, int $userId, string $role): array
{
    $role = strtolower($role);
    if (!in_array($role, ['admin', 'user'], true)) {
        return ['success' => false, 'error' => 'Ungueltige Rolle.'];
    }

    $sql = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'error' => 'DB-Fehler: ' . $db->error];
    }

    $stmt->bind_param("si", $role, $userId);
    if (!$stmt->execute()) {
        $error = $stmt->error ?: $db->error;
        $stmt->close();
        return ['success' => false, 'error' => 'DB-Fehler: ' . $error];
    }

    $stmt->close();
    return ['success' => true, 'error' => ''];
}

/**
 * Delete a user account (assets removed via FK cascade).
 */
function delete_user_account(mysqli $db, int $userId): array
{
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'error' => 'DB-Fehler: ' . $db->error];
    }

    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        $error = $stmt->error ?: $db->error;
        $stmt->close();
        return ['success' => false, 'error' => 'DB-Fehler: ' . $error];
    }

    $stmt->close();
    return ['success' => true, 'error' => ''];
}
?>
