<?php

define('BASE_URL', 'http://localhost:9000/');
define('PROJECT_ROOT', dirname(__DIR__));

require_once PROJECT_ROOT . '/database/db.php';

$testsRun = 0;
$testsPassed = 0;

function assertTest(string $name, bool $condition): void
{
    global $testsRun, $testsPassed;

    $testsRun++;

    if ($condition) {
        $testsPassed++;
        echo "[PASS] {$name}\n";
        return;
    }

    echo "[FAIL] {$name}\n";
}

function getLocation(array $response): string
{
    foreach ($response['headers'] as $header) {
        if (stripos($header, 'Location:') === 0) {
            return trim(substr($header, strlen('Location:')));
        }
    }

    return '';
}

function redirectContains(array $response, string $expected): bool
{
    return str_contains(urldecode(getLocation($response)), $expected);
}

function getCsrfToken(string $html): ?string
{
    if (
        preg_match(
            '/name=["\']csrf_token["\'][^>]*value=["\']([^"\']+)["\']/i',
            $html,
            $matches
        )
    ) {
        return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }

    return null;
}

function getUserIdByUsername(string $username): ?int
{
    $db = getDatabaseConnection();

    $stmt = $db->prepare("
        SELECT id
        FROM users
        WHERE username = ?
    ");

    $stmt->execute([$username]);

    $userId = $stmt->fetchColumn();

    return $userId === false ? null : (int) $userId;
}

function getStoredPasswordByUsername(string $username): ?string
{
    $db = getDatabaseConnection();

    $stmt = $db->prepare("
        SELECT password
        FROM users
        WHERE username = ?
    ");

    $stmt->execute([$username]);

    $password = $stmt->fetchColumn();

    return $password === false ? null : $password;
}

function cleanupReportTestData(string $marker): void
{
    $db = getDatabaseConnection();

    $stmt = $db->prepare("
        DELETE FROM notifications
        WHERE message LIKE ?
    ");

    $stmt->execute(['%' . $marker . '%']);

    $stmt = $db->prepare("
        DELETE FROM reports
        WHERE subject LIKE ?
    ");

    $stmt->execute(['%' . $marker . '%']);
}

function cleanupNotificationTestData(string $marker): void
{
    $db = getDatabaseConnection();

    $stmt = $db->prepare("
        DELETE FROM notifications
        WHERE message = ?
    ");

    $stmt->execute([$marker]);
}

function loginAs(
    TestClient $client,
    string $username,
    string $password,
    string $expectedRedirect
): bool {
    $loginPage = $client->get('login.php');
    $csrfToken = getCsrfToken($loginPage['body']);

    if ($csrfToken === null) {
        return false;
    }

    $response = $client->post('actions/action_login.php', [
        'csrf_token' => $csrfToken,
        'username' => $username,
        'password' => $password
    ]);

    return redirectContains($response, $expectedRedirect);
}

class TestClient
{
    private array $cookies = [];

    public function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    public function post(string $path, array $data = []): array
    {
        return $this->request('POST', $path, $data);
    }

    private function request(string $method, string $path, array $data = []): array
    {
        $headers = [];

        if (!empty($this->cookies)) {
            $cookieParts = [];

            foreach ($this->cookies as $name => $value) {
                $cookieParts[] = $name . '=' . $value;
            }

            $headers[] = 'Cookie: ' . implode('; ', $cookieParts);
        }

        if ($method === 'POST') {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }

        $options = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
                'follow_location' => 0,
                'max_redirects' => 0
            ]
        ];

        if ($method === 'POST') {
            $options['http']['content'] = http_build_query($data);
        }

        $context = stream_context_create($options);
        $body = @file_get_contents(BASE_URL . $path, false, $context);

        $responseHeaders = $http_response_header ?? [];

        foreach ($responseHeaders as $header) {
            if (preg_match('/^Set-Cookie:\s*([^=;]+)=([^;]*)/i', $header, $matches)) {
                $this->cookies[$matches[1]] = $matches[2];
            }
        }

        $status = 0;

        if (
            isset($responseHeaders[0]) &&
            preg_match('/\s(\d{3})\s/', $responseHeaders[0], $matches)
        ) {
            $status = (int) $matches[1];
        }

        return [
            'status' => $status,
            'headers' => $responseHeaders,
            'body' => $body === false ? '' : $body
        ];
    }
}

echo "=== Flow Fitness Security and Functional Tests ===\n\n";

echo "[Module: Password Storage and Authentication]\n";

$storedPassword = getStoredPasswordByUsername('jbrunson_11');

assertTest(
    'Seeded member password is hashed and verified with password_verify',
    $storedPassword !== null &&
    $storedPassword !== 'password' &&
    password_verify('password', $storedPassword)
);

$memberClient = new TestClient();

assertTest(
    'Valid member credentials create an authenticated session',
    loginAs($memberClient, 'jbrunson_11', 'password', 'profile.php')
);

$wrongPasswordClient = new TestClient();
$loginPage = $wrongPasswordClient->get('login.php');
$loginCsrf = getCsrfToken($loginPage['body']);

$wrongPasswordResponse = $wrongPasswordClient->post('actions/action_login.php', [
    'csrf_token' => $loginCsrf ?? '',
    'username' => 'jbrunson_11',
    'password' => 'wrong-password'
]);

assertTest(
    'Incorrect password is rejected',
    redirectContains($wrongPasswordResponse, 'error=invalid')
);

echo "\n[Module: SQL Injection]\n";

$injectionClient = new TestClient();
$loginPage = $injectionClient->get('login.php');
$loginCsrf = getCsrfToken($loginPage['body']);

$injectionResponse = $injectionClient->post('actions/action_login.php', [
    'csrf_token' => $loginCsrf ?? '',
    'username' => "' OR 1 = 1 GROUP BY username HAVING username='admin';--",
    'password' => 'anything'
]);

assertTest(
    'SQL Injection payload does not bypass login',
    redirectContains($injectionResponse, 'error=invalid')
);

echo "\n[Module: CSRF Protection]\n";

$csrfLoginClient = new TestClient();

$loginWithoutCsrf = $csrfLoginClient->post('actions/action_login.php', [
    'username' => 'jbrunson_11',
    'password' => 'password'
]);

assertTest(
    'Login rejects POST requests without CSRF token',
    redirectContains($loginWithoutCsrf, 'Invalid CSRF security token.')
);

$memberProfile = $memberClient->get('profile.php');
$memberCsrf = getCsrfToken($memberProfile['body']);

$bookingWithoutCsrf = $memberClient->post('actions/action_book_class.php', [
    'class_id' => 1
]);

assertTest(
    'Booking rejects authenticated request without CSRF token',
    redirectContains($bookingWithoutCsrf, 'Invalid CSRF security token.')
);

echo "\n[Module: Authorization]\n";

$anonymousClient = new TestClient();
$anonymousProfile = $anonymousClient->get('profile.php');

assertTest(
    'Anonymous visitor cannot access member profile',
    redirectContains($anonymousProfile, 'login.php')
);

$memberAdminAction = $memberClient->post('actions/action_update_equipment.php', [
    'csrf_token' => $memberCsrf ?? '',
    'equipment_id' => 1,
    'available_units' => 1
]);

assertTest(
    'Member cannot execute administrator equipment updates',
    redirectContains($memberAdminAction, 'login.php')
);

echo "\n[Module: Session and Logout]\n";

$getLogoutClient = new TestClient();
$getLogoutAuthenticated = loginAs($getLogoutClient, 'jbrunson_11', 'password', 'profile.php');

$getLogoutResponse = $getLogoutClient->get('actions/action_logout.php');
$profileAfterGetLogout = $getLogoutClient->get('profile.php');

assertTest(
    'GET request does not log out an authenticated member',
    $getLogoutAuthenticated &&
    $getLogoutResponse['status'] === 302 &&
    $profileAfterGetLogout['status'] === 200
);

$invalidLogoutClient = new TestClient();
$invalidLogoutAuthenticated = loginAs($invalidLogoutClient, 'jbrunson_11', 'password', 'profile.php');

$invalidLogoutResponse = $invalidLogoutClient->post('actions/action_logout.php', [
    'csrf_token' => 'invalid-token'
]);

$profileAfterInvalidLogout = $invalidLogoutClient->get('profile.php');

assertTest(
    'Logout with invalid CSRF token preserves the authenticated session',
    $invalidLogoutAuthenticated &&
    redirectContains($invalidLogoutResponse, 'Invalid security token.') &&
    $profileAfterInvalidLogout['status'] === 200
);

$validLogoutClient = new TestClient();
$validLogoutAuthenticated = loginAs($validLogoutClient, 'jbrunson_11', 'password', 'profile.php');

$profileBeforeLogout = $validLogoutClient->get('profile.php');
$logoutCsrf = getCsrfToken($profileBeforeLogout['body']);

$validLogoutResponse = $validLogoutClient->post('actions/action_logout.php', [
    'csrf_token' => $logoutCsrf ?? ''
]);

$profileAfterValidLogout = $validLogoutClient->get('profile.php');

assertTest(
    'Logout with POST and valid CSRF token destroys the session',
    $validLogoutAuthenticated &&
    redirectContains($validLogoutResponse, 'index.php') &&
    redirectContains($profileAfterValidLogout, 'login.php')
);

echo "\n[Module: Avatar Upload API]\n";

$avatarGetClient = new TestClient();
$avatarGetResponse = $avatarGetClient->get('api/upload_avatar.php');
$avatarGetJson = json_decode($avatarGetResponse['body'], true);

assertTest(
    'Avatar endpoint rejects non-POST requests',
    $avatarGetResponse['status'] === 405 &&
    is_array($avatarGetJson) &&
    ($avatarGetJson['success'] ?? null) === false
);

$anonymousAvatarClient = new TestClient();
$anonymousAvatarResponse = $anonymousAvatarClient->post('api/upload_avatar.php');
$anonymousAvatarJson = json_decode($anonymousAvatarResponse['body'], true);

assertTest(
    'Avatar endpoint rejects anonymous POST requests',
    $anonymousAvatarResponse['status'] === 401 &&
    is_array($anonymousAvatarJson) &&
    ($anonymousAvatarJson['success'] ?? null) === false
);

$avatarCsrfClient = new TestClient();
$avatarLoggedIn = loginAs($avatarCsrfClient, 'jbrunson_11', 'password', 'profile.php');

$avatarWithoutCsrf = $avatarCsrfClient->post('api/upload_avatar.php');
$avatarWithoutCsrfJson = json_decode($avatarWithoutCsrf['body'], true);

assertTest(
    'Avatar endpoint rejects authenticated upload without CSRF token',
    $avatarLoggedIn &&
    $avatarWithoutCsrf['status'] === 403 &&
    is_array($avatarWithoutCsrfJson) &&
    ($avatarWithoutCsrfJson['success'] ?? null) === false
);

$avatarValidationClient = new TestClient();
$avatarValidationLoggedIn = loginAs($avatarValidationClient, 'jbrunson_11', 'password', 'profile.php');
$avatarProfilePage = $avatarValidationClient->get('profile.php');
$avatarCsrf = getCsrfToken($avatarProfilePage['body']);

$avatarWithoutFile = $avatarValidationClient->post('api/upload_avatar.php', [
    'csrf_token' => $avatarCsrf ?? ''
]);

$avatarWithoutFileJson = json_decode($avatarWithoutFile['body'], true);

assertTest(
    'Avatar endpoint rejects request without uploaded file data',
    $avatarValidationLoggedIn &&
    $avatarWithoutFile['status'] === 400 &&
    is_array($avatarWithoutFileJson) &&
    ($avatarWithoutFileJson['success'] ?? null) === false
);

echo "\n[Module: Stored XSS]\n";

$xssClient = new TestClient();
$xssLoggedIn = loginAs($xssClient, 'jbrunson_11', 'password', 'profile.php');

$xssProfilePage = $xssClient->get('profile.php');
$xssCsrf = getCsrfToken($xssProfilePage['body']);

$xssMarker = 'XSS_TEST_' . bin2hex(random_bytes(5));
$xssPayload = '<script>alert(1)</script>';
$xssSubject = $xssMarker . ' ' . $xssPayload;

$xssProtected = false;

if ($xssLoggedIn && $xssCsrf !== null) {
    try {
        $xssClient->post('actions/action_submit_report.php', [
            'csrf_token' => $xssCsrf,
            'category' => 'other',
            'subject' => $xssSubject,
            'description' => $xssPayload
        ]);

        $reportsPage = $xssClient->get('reports.php');

        $xssProtected =
            str_contains($reportsPage['body'], $xssMarker) &&
            str_contains($reportsPage['body'], '&lt;script&gt;alert(1)&lt;/script&gt;') &&
            !str_contains($reportsPage['body'], '<script>alert(1)</script>');
    } finally {
        cleanupReportTestData($xssMarker);
    }
}

assertTest(
    'Stored report payload is encoded before being output as HTML',
    $xssProtected
);

echo "\n[Module: Safe Navigation Links]\n";

$notificationClient = new TestClient();
$notificationLoggedIn = loginAs($notificationClient, 'jbrunson_11', 'password', 'profile.php');

$notificationMarker = 'UNSAFE_LINK_TEST_' . bin2hex(random_bytes(5));
$unsafeLinkProtected = false;

if ($notificationLoggedIn) {
    $userId = getUserIdByUsername('jbrunson_11');

    if ($userId !== null) {
        try {
            $db = getDatabaseConnection();

            $stmt = $db->prepare("
                INSERT INTO notifications (
                    user_id,
                    message,
                    link
                )
                VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $userId,
                $notificationMarker,
                'javascript:alert(1)'
            ]);

            $notificationsPage = $notificationClient->get('notifications.php');

            $unsafeLinkProtected =
                str_contains($notificationsPage['body'], $notificationMarker) &&
                !str_contains($notificationsPage['body'], 'href="javascript:alert(1)"') &&
                !str_contains($notificationsPage['body'], "href='javascript:alert(1)'");
        } finally {
            cleanupNotificationTestData($notificationMarker);
        }
    }
}

assertTest(
    'Notification links do not render javascript URLs',
    $unsafeLinkProtected
);

echo "\n[Module: Database Configuration]\n";

$db = getDatabaseConnection();
$foreignKeysEnabled = (int) $db->query('PRAGMA foreign_keys')->fetchColumn();

assertTest(
    'Database connection enables SQLite foreign key enforcement',
    $foreignKeysEnabled === 1
);

echo "\n[Module: Static Form Security]\n";

$phpFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(PROJECT_ROOT, FilesystemIterator::SKIP_DOTS)
);

$unsafeLogoutLinks = [];

foreach ($phpFiles as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    if (str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR)) {
        continue;
    }

    $contents = file_get_contents($file->getPathname());

    if (preg_match('/<a[^>]+href=["\'][^"\']*action_logout\.php/i', $contents)) {
        $unsafeLogoutLinks[] = $file->getPathname();
    }
}

assertTest(
    'No page exposes logout through an unsafe GET link',
    count($unsafeLogoutLinks) === 0
);

$trainerDashboard = file_get_contents(PROJECT_ROOT . '/trainer_dashboard.php');

assertTest(
    'Trainer profile form submits the common csrf_token field',
    str_contains($trainerDashboard, 'name="csrf_token"') &&
    !str_contains($trainerDashboard, 'name="csrf"')
);

echo "\n=== Test Suite Summary ===\n";
echo "Executed: {$testsRun} | Passed: {$testsPassed} / {$testsRun}\n";

if ($testsPassed === $testsRun) {
    echo "All automated checks passed.\n";
    exit(0);
}

echo "One or more checks failed.\n";
exit(1);