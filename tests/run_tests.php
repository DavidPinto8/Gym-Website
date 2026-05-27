<?php

define('BASE_URL', 'http://localhost:9000/');
define('PROJECT_ROOT', dirname(__DIR__));

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

        if (isset($responseHeaders[0]) && preg_match('/\s(\d{3})\s/', $responseHeaders[0], $matches)) {
            $status = (int) $matches[1];
        }

        return [
            'status' => $status,
            'headers' => $responseHeaders,
            'body' => $body === false ? '' : $body
        ];
    }
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

function loginAsMember(TestClient $client): bool
{
    $loginPage = $client->get('login.php');
    $csrfToken = getCsrfToken($loginPage['body']);

    if ($csrfToken === null) {
        return false;
    }

    $response = $client->post('actions/action_login.php', [
        'csrf_token' => $csrfToken,
        'username' => 'jbrunson_11',
        'password' => 'password'
    ]);

    return str_contains(getLocation($response), 'profile.php');
}

function getDatabase(): PDO
{
    $db = new PDO('sqlite:' . PROJECT_ROOT . '/database/database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec('PRAGMA foreign_keys = ON');

    return $db;
}

function cleanupXssTestData(string $marker): void
{
    $db = getDatabase();

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

echo "=== Flow Fitness Security and Sanity Tests ===\n\n";

echo "[Module: Password Storage and Login]\n";

$memberClient = new TestClient();

assertTest(
    'Login accepts seeded password verified against password hash',
    loginAsMember($memberClient)
);

$wrongPasswordClient = new TestClient();
$loginPage = $wrongPasswordClient->get('login.php');
$csrfToken = getCsrfToken($loginPage['body']);

$wrongPasswordResponse = $wrongPasswordClient->post('actions/action_login.php', [
    'csrf_token' => $csrfToken ?? '',
    'username' => 'jbrunson_11',
    'password' => 'wrong-password'
]);

assertTest(
    'Login rejects an incorrect password',
    str_contains(getLocation($wrongPasswordResponse), 'error=invalid')
);

echo "\n[Module: SQL Injection]\n";

$injectionClient = new TestClient();
$loginPage = $injectionClient->get('login.php');
$csrfToken = getCsrfToken($loginPage['body']);

$injectionResponse = $injectionClient->post('actions/action_login.php', [
    'csrf_token' => $csrfToken ?? '',
    'username' => "' OR 1 = 1 GROUP BY username HAVING username='admin';--",
    'password' => ''
]);

assertTest(
    'SQL Injection payload does not bypass login',
    str_contains(getLocation($injectionResponse), 'error=invalid')
);

echo "\n[Module: CSRF]\n";

$profilePage = $memberClient->get('profile.php');
$memberCsrfToken = getCsrfToken($profilePage['body']);

$bookingWithoutToken = $memberClient->post('actions/action_book_class.php', [
    'class_id' => 1
]);

assertTest(
    'Booking rejects authenticated request without CSRF token',
    str_contains(urldecode(getLocation($bookingWithoutToken)), 'Invalid CSRF security token.')
);

$getLogoutResponse = $memberClient->get('actions/action_logout.php');
$profileAfterGetLogout = $memberClient->get('profile.php');

assertTest(
    'GET request does not log the member out',
    $getLogoutResponse['status'] === 302 &&
    $profileAfterGetLogout['status'] === 200
);

$logoutResponse = $memberClient->post('actions/action_logout.php', [
    'csrf_token' => $memberCsrfToken ?? ''
]);

$profileAfterPostLogout = $memberClient->get('profile.php');

assertTest(
    'POST logout with CSRF token destroys the authenticated session',
    str_contains(getLocation($logoutResponse), 'index.php') &&
    str_contains(getLocation($profileAfterPostLogout), 'login.php')
);

echo "\n[Module: Avatar Upload API]\n";

$anonymousClient = new TestClient();
$avatarResponse = $anonymousClient->post('api/upload_avatar.php');
$avatarJson = json_decode($avatarResponse['body'], true);

assertTest(
    'Avatar API rejects anonymous requests',
    $avatarResponse['status'] === 401 &&
    is_array($avatarJson) &&
    ($avatarJson['success'] ?? null) === false
);

echo "\n[Module: Stored XSS]\n";

$xssClient = new TestClient();
$xssLoginSucceeded = loginAsMember($xssClient);
$xssProfilePage = $xssClient->get('profile.php');
$xssCsrfToken = getCsrfToken($xssProfilePage['body']);

$marker = 'XSS_TEST_' . bin2hex(random_bytes(5));
$xssPayload = '<script>alert(1)</script>';
$xssSubject = $marker . ' ' . $xssPayload;

$xssProtected = false;

if ($xssLoginSucceeded && $xssCsrfToken !== null) {
    try {
        $xssClient->post('actions/action_submit_report.php', [
            'csrf_token' => $xssCsrfToken,
            'category' => 'other',
            'subject' => $xssSubject,
            'description' => $xssPayload
        ]);

        $reportsPage = $xssClient->get('reports.php');

        $xssProtected =
            str_contains($reportsPage['body'], $marker) &&
            str_contains($reportsPage['body'], '&lt;script&gt;alert(1)&lt;/script&gt;') &&
            !str_contains($reportsPage['body'], '<script>alert(1)</script>');
    } finally {
        cleanupXssTestData($marker);
    }
}

assertTest(
    'Stored report input is encoded before being output as HTML',
    $xssProtected
);

echo "\n[Module: Static CSRF Checks]\n";

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
    'No PHP page exposes logout through a GET link',
    count($unsafeLogoutLinks) === 0
);

$trainerDashboard = file_get_contents(PROJECT_ROOT . '/trainer_dashboard.php');

assertTest(
    'Trainer profile form submits csrf_token using the common field name',
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