<?php
session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/avatar.php';

header('Content-Type: application/json');

$db = getDatabaseConnection();

$memberId = null;

if (isset($_SESSION['user_id'])) {
    $stmtMember = $db->prepare("
        SELECT id
        FROM members
        WHERE user_id = ?
    ");
    $stmtMember->execute([$_SESSION['user_id']]);
    $member = $stmtMember->fetch();

    if ($member) {
        $memberId = $member['id'];
    }
}

$type = $_GET['type'] ?? 'all';
$day = $_GET['day'] ?? 'all';
$trainer = $_GET['trainer'] ?? 'all';

$sql = "
SELECT
    classes.*,
    users.name AS trainer_name,
    users.photo AS trainer_photo,

    COUNT(
        DISTINCT CASE
            WHEN enrollments.status = 'enrolled'
            THEN enrollments.id
        END
    ) AS enrolled_count,

    MAX(
        CASE
            WHEN enrollments.member_id = :member_id
            THEN enrollments.status
        END
    ) AS user_booking_status,

    (
        SELECT COUNT(*) + 1
        FROM enrollments AS e2
        WHERE e2.class_id = classes.id
          AND e2.status = 'waitlist'
          AND e2.id < (
              SELECT id
              FROM enrollments AS e3
              WHERE e3.class_id = classes.id
                AND e3.member_id = :member_id_sub
                AND e3.status = 'waitlist'
              LIMIT 1
          )
    ) AS waitlist_position,

    ROUND(AVG(reviews.rating), 1) AS average_rating,
    COUNT(DISTINCT reviews.id) AS review_count

FROM classes

LEFT JOIN trainers
    ON classes.trainer_id = trainers.id

LEFT JOIN users
    ON trainers.user_id = users.id

LEFT JOIN enrollments
    ON classes.id = enrollments.class_id

LEFT JOIN reviews
    ON classes.id = reviews.class_id

WHERE 1=1
";

$params = [
    ':member_id' => $memberId,
    ':member_id_sub' => $memberId
];

if ($type !== 'all') {
    $validTypes = ['yoga', 'hiit', 'strength', 'cardio', 'other'];

    if (in_array($type, $validTypes, true)) {
        $sql .= " AND classes.type = :type";
        $params[':type'] = $type;
    }
}

if ($trainer !== 'all') {
    $trainerId = filter_var($trainer, FILTER_VALIDATE_INT);

    if ($trainerId) {
        $sql .= " AND classes.trainer_id = :trainer_id";
        $params[':trainer_id'] = $trainerId;
    }
}

if ($day !== 'all') {
    $dayMap = [
        'sunday' => '0',
        'monday' => '1',
        'tuesday' => '2',
        'wednesday' => '3',
        'thursday' => '4',
        'friday' => '5',
        'saturday' => '6'
    ];

    $sql .= " AND strftime('%w', classes.start_time) = :day";
    $params[':day'] = $dayMap[$day] ?? '-1';
}

$sql .= "
GROUP BY classes.id
ORDER BY classes.start_time
";

$stmt = $db->prepare($sql);
$stmt->execute($params);

$classes = $stmt->fetchAll();

foreach ($classes as &$class) {
    $class['trainer_avatar'] = getUserAvatar(
        $class['trainer_name'] ?? 'Trainer',
        $class['trainer_photo'] ?? null,
        96
    );
}

echo json_encode($classes);