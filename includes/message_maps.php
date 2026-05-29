<?php

/**
 * Centralized success and error message dictionaries.
 *
 * Each page that needs human-readable messages should reference the maps
 * defined here instead of defining its own local arrays. This keeps all
 * user-facing copy in one place for easy maintenance and consistency.
 */

$MESSAGE_MAPS = [

    // ─── Login ────────────────────────────────────────────────────
    'login' => [
        'errors' => [
            'invalid'      => 'Invalid username or password.',
            'login_failed' => 'Login failed. Please try again.',
        ],
    ],

    // ─── Registration ─────────────────────────────────────────────
    'register' => [
        'errors' => [
            'user_exists'      => 'Username or Email already taken.',
            'db_fail'          => 'Registration failed. Please try again.',
            'empty_fields'     => 'All fields are required.',
            'invalid_email'    => 'Please provide a valid email address.',
            'short_password'   => 'Password must be at least 6 characters long.',
            'invalid_plan'     => 'Please select a valid membership plan.',
            'invalid_payment'  => 'Please provide a valid payment method.',
        ],
    ],

    // ─── Profile ──────────────────────────────────────────────────
    'profile' => [
        'successes' => [
            'profile_updated'  => 'Profile updated successfully.',
            'password_updated' => 'Password updated successfully.',
            'plan_updated'     => 'Membership plan updated successfully.',
        ],
        'errors' => [
            'empty_fields'                  => 'Please fill in all required fields.',
            'username_taken'                => 'That username is already taken.',
            'update_failed'                 => 'Something went wrong while updating your profile.',
            'invalid_plan'                  => 'Please select a valid membership plan.',
            'password_mismatch'             => 'The new passwords do not match.',
            'short_password'                => 'Password must be at least 6 characters long.',
            'invalid_current_password'      => 'Current password is incorrect.',
            'Invalid CSRF security token.'  => 'Invalid security token.',
        ],
    ],

    // ─── Billing ──────────────────────────────────────────────────
    'billing' => [
        'successes' => [
            'payment_updated' => 'Payment method updated successfully.',
        ],
        'errors' => [
            'invalid_input'          => 'Please select a valid payment method and provide 4 digits.',
            'update_failed'          => 'Could not update payment method.',
            'invalid_csrf'           => 'Invalid security token.',
            'missing_payment_method' => 'Please add a payment method before changing your plan.',
        ],
    ],
];

/**
 * Get a combined (successes + errors) message map for a given page.
 *
 * @param string $page Key in the $MESSAGE_MAPS array.
 * @return array Associative array mapping codes to messages.
 */
function get_message_map(string $page): array
{
    global $MESSAGE_MAPS;

    $entry = $MESSAGE_MAPS[$page] ?? [];

    return array_merge(
        $entry['successes'] ?? [],
        $entry['errors'] ?? []
    );
}

/**
 * Get only the error message map for a given page.
 *
 * @param string $page Key in the $MESSAGE_MAPS array.
 * @return array Associative array mapping error codes to messages.
 */
function get_error_map(string $page): array
{
    global $MESSAGE_MAPS;

    return $MESSAGE_MAPS[$page]['errors'] ?? [];
}

/**
 * Get only the success message map for a given page.
 *
 * @param string $page Key in the $MESSAGE_MAPS array.
 * @return array Associative array mapping success codes to messages.
 */
function get_success_map(string $page): array
{
    global $MESSAGE_MAPS;

    return $MESSAGE_MAPS[$page]['successes'] ?? [];
}
