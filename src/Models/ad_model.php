<?php
function get_all_ads() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM ads ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
