<?php
// src/lib/flash_messages.php

function set_flash_message(string $type, string $message): void {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
}

function get_flash_messages(): array {
    if (isset($_SESSION['flash_messages'])) {
        $messages = $_SESSION['flash_messages'];
        unset($_SESSION['flash_messages']);
        return $messages;
    }
    return [];
}

function display_flash_messages(): void {
    $messages = get_flash_messages();
    if (!empty($messages)) {
        echo '<div class="flash-messages-container">';
        foreach ($messages as $msg) {
            $typeClass = htmlspecialchars($msg['type']) . '-message';
            echo '<p class="message ' . $typeClass . '">' . $msg['message'] . '</p>'; // Message non échappé pour permettre le HTML
        }
        echo '</div>';
    }
}
?>