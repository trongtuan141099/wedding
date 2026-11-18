<?php
/* Temporary, standalone hosting diagnostic. No phpinfo, secrets or guest data.
 * Intentionally uses older PHP syntax so it can explain an unsupported runtime.
 * Remove this file from the server once troubleshooting is complete.
 */
if (function_exists('ini_set')) {
    @ini_set('display_errors', '0');
    @ini_set('log_errors', '1');
}
$report = array('WEDDING HOSTING CHECK 1.0', '');
$finished = false;
function wh_line($name, $value) {
    global $report;
    $report[] = $name . ': ' . $value;
}
function wh_failure($error) {
    $text = $error->getMessage();
    $reason = 'See cPanel Errors for the full server-side error.';
    if (preg_match('/undefined function ([a-zA-Z0-9_\\\\]+)\(/i', $text, $match)) {
        $reason = 'Required PHP function unavailable: ' . $match[1];
    } elseif (stripos($text, 'Permission denied') !== false) {
        $reason = 'Filesystem permission/ownership prevents application access.';
    } elseif (get_class($error) === 'JsonException') {
        $reason = 'The saved state is not valid JSON. Preserve it and inspect a backup.';
    } elseif (stripos($text, 'session') !== false) {
        $reason = 'PHP session initialization failed. Check session functions and data/sessions permissions.';
    } elseif (strpos($text, 'Storage lock failed') !== false) {
        $reason = 'Cannot open/lock the application storage file.';
    }
    wh_line('APP_STARTUP', 'FAIL');
    wh_line('ERROR_TYPE', get_class($error));
    wh_line('ERROR_LOCATION', basename($error->getFile()) . ':' . $error->getLine());
    wh_line('ERROR_REASON', $reason);
}
if (function_exists('register_shutdown_function')) {
    register_shutdown_function(function () {
        global $finished, $report;
        if ($finished) return;
        $last = error_get_last();
        if ($last) {
            $report[] = 'APP_STARTUP: FATAL BEFORE COMPLETION';
            $report[] = 'ERROR_LOCATION: ' . basename($last['file']) . ':' . $last['line'];
        }
        if (!headers_sent()) header('Content-Type: text/plain; charset=utf-8');
        echo implode("\n", $report) . "\n";
    });
}
wh_line('PHP_VERSION', PHP_VERSION);
wh_line('PHP_HANDLER', PHP_SAPI);
$supported = version_compare(PHP_VERSION, '8.0.0', '>=');
wh_line('PHP_REQUIREMENT', $supported ? 'PASS (8.0+; select 8.2+ on production)' : 'FAIL - select PHP 8.2+ for this domain in MultiPHP Manager');
$required = array('getenv', 'session_start', 'session_save_path', 'session_set_cookie_params',
    'session_status', 'session_name', 'session_destroy',
    'random_bytes', 'hash_equals', 'flock', 'fopen', 'file_get_contents', 'json_decode',
    'json_encode', 'ini_set', 'set_error_handler', 'restore_error_handler');
$missing = array();
foreach ($required as $name) if (!function_exists($name)) $missing[] = $name;
wh_line('REQUIRED_FUNCTIONS', $missing ? 'FAIL - ' . implode(', ', $missing) : 'PASS');
wh_line('FILEINFO_UPLOADS', class_exists('finfo') ? 'PASS' : 'MISSING - needed for image/music uploads');
$data = function_exists('getenv') ? getenv('WEDDING_DATA_DIR') : false;
if (!$data) $data = __DIR__ . '/data';
wh_line('DATA_DIRECTORY', is_dir($data) ? 'EXISTS' : 'MISSING - application will try to create it');
wh_line('DATA_WRITABLE', is_dir($data) ? (is_writable($data) ? 'PASS' : 'FAIL') : 'NOT CHECKED');
wh_line('UPLOADS_WRITABLE', is_dir(__DIR__ . '/uploads') && is_writable(__DIR__ . '/uploads') ? 'PASS' : 'FAIL');
$stateFile = $data . '/state.php';
if (is_file($stateFile)) {
    if (is_readable($stateFile)) {
        $raw = file_get_contents($stateFile);
        $prefix = "<?php exit; ?>\n";
        if (strncmp($raw, $prefix, strlen($prefix)) === 0 && function_exists('json_decode')) {
            $decoded = json_decode(substr($raw, strlen($prefix)), true);
            wh_line('STATE_JSON', json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['config']) ? 'PASS' : 'FAIL - invalid JSON or missing config');
            unset($decoded, $raw);
        } else wh_line('STATE_JSON', 'FAIL - unexpected file prefix or JSON unavailable');
    } else wh_line('STATE_JSON', 'FAIL - cannot read state.php');
} else wh_line('STATE_JSON', 'NO SAVED STATE - application defaults will be used');
if ($supported && !$missing) {
    // A separate, temporary session never reuses the administrator session.
    session_name('WeddingHostingCheck');
    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) return false;
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
    try {
        require __DIR__ . '/bootstrap.php';
        $snapshot = state();
        unset($snapshot);
        wh_line('APP_STARTUP', 'PASS - bootstrap, sessions, file locking and state read work');
    } catch (Exception $error) {
        wh_failure($error);
    } catch (Throwable $error) {
        wh_failure($error);
    }
    restore_error_handler();
    if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
} else wh_line('APP_STARTUP', 'SKIPPED - resolve the PHP version/functions reported above first');
$report[] = '';
$report[] = 'Share this report for troubleshooting. No passwords or guest records are printed.';
$report[] = 'Remove hosting-check.php after the issue is resolved.';
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex, nofollow');
$finished = true;
echo implode("\n", $report) . "\n";
