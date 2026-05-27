<?php

function getNextWeeklyOccurrence(string $originalStartTime): string
{
    $originalTimestamp = strtotime($originalStartTime);

    if ($originalTimestamp === false) {
        return $originalStartTime;
    }

    $targetWeekday = (int) date('w', $originalTimestamp);
    $targetTime = date('H:i:s', $originalTimestamp);

    $today = new DateTime('today');
    $now = new DateTime();

    $todayWeekday = (int) $today->format('w');
    $daysUntil = ($targetWeekday - $todayWeekday + 7) % 7;

    $nextDate = clone $today;
    $nextDate->modify("+{$daysUntil} days");

    $candidate = new DateTime($nextDate->format('Y-m-d') . ' ' . $targetTime);

    if ($candidate < $now) {
        $candidate->modify('+7 days');
    }

    return $candidate->format('Y-m-d\TH:i:s');
}

function applyNextWeeklyOccurrences(array $classes): array
{
    foreach ($classes as &$class) {
        $class['original_start_time'] = $class['start_time'];
        $class['start_time'] = getNextWeeklyOccurrence($class['start_time']);
    }

    unset($class);

    usort($classes, function ($a, $b) {
        return strtotime($a['start_time']) <=> strtotime($b['start_time']);
    });

    return $classes;
}