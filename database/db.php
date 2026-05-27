<?php

function getDatabaseConnection(): PDO
{
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/database.db');

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $db->exec('PRAGMA foreign_keys = ON');

        return $db;
    } catch (PDOException $exception) {
        error_log($exception->getMessage());

        http_response_code(500);
        exit('Database connection error.');
    }
}