<?php

/**
 * Render success and error alert messages from query parameters.
 *
 * Pages that use a message map (associative array) should pass it as $messageMap.
 * Pages that display the raw query parameter (with underscores replaced by spaces)
 * should call this function without a message map.
 *
 * @param array|null $messageMap Optional associative array mapping error/success
 *                               codes to human-readable messages.
 * @param string     $alertClass CSS class prefix for alert containers.
 *                               Defaults to 'schedule-alert' (used on most pages).
 */
function render_alerts(?array $messageMap = null, string $alertClass = 'schedule-alert'): void
{
    $successCode = $_GET['success'] ?? null;
    $errorCode = $_GET['error'] ?? null;

    if ($successCode !== null) {
        $message = resolve_message($successCode, $messageMap);

        if ($message !== null) {
            echo '<div class="' . htmlspecialchars($alertClass, ENT_QUOTES, 'UTF-8')
                . ' success-alert">'
                . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
                . '</div>';
        }
    }

    if ($errorCode !== null) {
        $message = resolve_message($errorCode, $messageMap);

        if ($message !== null) {
            echo '<div class="' . htmlspecialchars($alertClass, ENT_QUOTES, 'UTF-8')
                . ' error-alert">'
                . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
                . '</div>';
        }
    }
}

/**
 * Resolve a message code into a human-readable string.
 *
 * If a message map is provided, the code is looked up in it.
 * Otherwise the code is converted by replacing underscores with spaces
 * and capitalizing the first letter.
 *
 * @param string     $code       The raw error/success code.
 * @param array|null $messageMap Optional lookup table.
 * @return string|null The resolved message, or null if the code is not in the map.
 */
function resolve_message(string $code, ?array $messageMap): ?string
{
    if ($messageMap !== null) {
        return $messageMap[$code] ?? null;
    }

    return ucfirst(str_replace('_', ' ', $code));
}
