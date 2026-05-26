<?php

function getUserAvatar($name, $photo = null, $size = 200) {
    if (!empty($photo)) {
        $safePhoto = htmlspecialchars($photo);

        if (str_starts_with($safePhoto, 'assets/')) {
            return $safePhoto;
        }

        return 'assets/' . $safePhoto;
    }

    return 'https://ui-avatars.com/api/?name=' .
        urlencode($name) .
        '&background=1a1a1a' .
        '&color=d4af37' .
        '&size=' . intval($size) .
        '&bold=true';
}