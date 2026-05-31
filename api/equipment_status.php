<?php

require_once __DIR__ . '/../database/db.php';

header('Content-Type: application/json');

$db = getDatabaseConnection();

$type = $_GET['type'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "
    SELECT
        id,
        name,
        type,
        location,
        total_units,
        available_units
    FROM equipment
    WHERE 1 = 1
";

$params = [];

if ($type !== 'all') {
    $sql .= " AND type = ?";
    $params[] = $type;
}

if ($search !== '') {
    $sql .= " AND (name LIKE ? OR location LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$sql .= " ORDER BY type, name";

$stmt = $db->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll());