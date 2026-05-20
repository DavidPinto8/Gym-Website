<?php

function getUserAvatar($name, $photo = null, $size = 200) {

    if (!empty($photo)) {
        return htmlspecialchars($photo);
    }

    return 'https://ui-avatars.com/api/?name=' .
        urlencode($name) .
        '&background=1a1a1a' .
        '&color=d4af37' .
        '&size=' . intval($size) .
        '&bold=true';
}