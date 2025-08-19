<?php
declare(strict_types=1);

$logfile = __DIR__ . '/install_log.txt';
logMessage("Script started");

$errors = [];

if (!empty($_POST)) {
    logMessage("\n--- Install attempt at " . date('Y-m-d H:i:s') . " ---");
    logMessage("POST: " . print_r($_POST, true));

    $host   = $_POST['MYSQL_HOST']     ?? '';
    $user   = $_POST['MYSQL_USERNAME'] ?? '';
    $pass   = $_POST['MYSQL_PASSWORD'] ?? '';
    $dbname = $_POST['MYSQL_DATABASE'] ?? '';

    logMessage("Attempting DB connection with:\nHost: $host\nUser: $user\nPass: $pass\nDB: $dbname");

    $conn = new mysqli($host, $user, $pass, $dbname);
    logMessage("DB connection attempted.");

    if ($conn->connect_error) {
        logMessage("DB connection error: {$conn->connect_error}");
        $errors[] = $conn->connect_error;
    } else {
        logMessage("DB connection successful. Proceeding to constants.php update...");

        try {
            $constantsPath = realpath(__DIR__ . '/../app/constants.php');
            logMessage("Reading constants.php for marker replacement...");

            $expected = [
                'MYSQL_HOST',
                'MYSQL_USERNAME',
                'MYSQL_PASSWORD',
                'MYSQL_DATABASE',
                'web_root',
                'site_title',
            ];

            $content = file_get_contents($constantsPath);

            $newBlock = "// --- INSTALLER GENERATED CONSTANTS START ---\n";
            foreach ($expected as $key) {
                if (isset($_POST[$key])) {
                    $val = addslashes($_POST[$key]);
                    $newBlock .= "if (!defined('$key')) define('$key', '$val');\n";
                }
            }
            $newBlock .= "// --- INSTALLER GENERATED CONSTANTS END ---";

            $pattern = '/\/\/ --- INSTALLER GENERATED CONSTANTS START ---.*?\/\/ --- INSTALLER GENERATED CONSTANTS END ---/s';
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $newBlock, $content);
            } else {
                logMessage("Installer markers not found. Appending block.");
                $content .= "\n\n" . $newBlock;
            }

            file_put_contents($constantsPath, $content);
            logMessage("constants.php updated successfully.");

            // Run SQL import
            $sql = file_get_contents('../database.sql');
            if (!$conn->multi_query($sql)) {
                $errors[] = $conn->error;
                logMessage("DB error: {$conn->error}");
            } else {
                logMessage("DB import successful");

                if (verifyConstants($constantsPath, $expected)) {
                    if ($_POST['auto_delete_install'] ?? '' === '1') {
                        deleteInstallFolder(__DIR__);
                        logMessage("Install folder deleted.");
                    }
                } else {
                    logMessage("Not all required constants found. Install folder not deleted.");
                    header("Location: index.php?error=constants_missing");
                }
            }
        } catch (Exception $e) {
            logMessage("Exception: " . $e->getMessage());
            echo $e;
        }
    }
}

// 🔧 Helper Functions
function logMessage(string $message): void {
    global $logfile;
    file_put_contents($logfile, $message . "\n", FILE_APPEND);
}

function verifyConstants(string $filePath, array $required): bool {
    $content = file_get_contents($filePath);
    foreach ($required as $const) {
        if (strpos($content, $const) === false) {
            logMessage("Missing constant: $const");
            return false;
        }
    }
    return true;
}

function deleteInstallFolder(string $dir): void {
    $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
    }
    rmdir($dir);
}