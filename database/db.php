<?php
function getDatabaseConnection() {
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $db;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>