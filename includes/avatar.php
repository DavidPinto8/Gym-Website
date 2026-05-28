<?php

function getUserAvatar(string $name, ?string $photo = null, int $size = 200): string
{
    $safeSize = max(32, min($size, 512));

    if ($photo !== null && trim($photo) !== '') {
        $photo = trim($photo);

        if (str_starts_with($photo, 'assets/')) {
            $photo = substr($photo, strlen('assets/'));
        }

        $filename = basename($photo);

        if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._-]*\.(jpg|jpeg|png)$/i', $filename)) {
            return 'assets/' . $filename;
        }
    }

    return 'https://ui-avatars.com/api/?name='
        . rawurlencode($name)
        . '&background=1a1a1a'
        . '&color=d4af37'
        . '&size=' . $safeSize
        . '&bold=true';
}