<?php
/*
Name    :   Simple CSRF protection class for Core-PHP (Non-Framework).
By      :   Banujan Balendrakumar | https://github.com/banujan6
License :   Free & Open
Thanks  :   http://itman.in - getRealIpAddr();
*/

namespace Fox;

class CSRF {

    private static function startSession() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private static function debug($message) {
        if (defined('CSRF_DEBUG') && CSRF_DEBUG) {
            error_log("[CSRF] $message");
        }
    }

    private static function randomToken() {
        $keySet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for ($i = 0; $i < 5; $i++) {
            $keySet = str_shuffle($keySet);
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $clientIp  = self::getRealIpAddr();
        $entropy   = $keySet . $userAgent . $clientIp . microtime(true);

        $hashedToken = base64_encode(password_hash($entropy, PASSWORD_BCRYPT));

        self::setToken($hashedToken);
        self::debug("Generated token: $hashedToken");

        return $hashedToken;
    }

    private static function getRealIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    private static function setToken($token) {
        self::startSession();

        $tokenList = json_decode($_SESSION['X-CSRF-TOKEN-LIST'] ?? '[]', true);
        if (!is_array($tokenList)) {
            $tokenList = [];
        }

        $tokenList[] = $token;
        $_SESSION['X-CSRF-TOKEN-LIST'] = json_encode($tokenList);
    }

    private static function checkToken($token) {
        self::startSession();

        $tokenList = json_decode($_SESSION['X-CSRF-TOKEN-LIST'] ?? '[]', true);
        if (!is_array($tokenList)) {
            return false;
        }

        if (in_array($token, $tokenList)) {
            self::removeToken($token);
            return true;
        }

        return false;
    }

    private static function removeToken($token) {
        self::startSession();

        $tokenList = json_decode($_SESSION['X-CSRF-TOKEN-LIST'] ?? '[]', true);
        if (!is_array($tokenList)) {
            return;
        }

        $index = array_search($token, $tokenList);
        if ($index !== false) {
            unset($tokenList[$index]);
            $_SESSION['X-CSRF-TOKEN-LIST'] = json_encode(array_values($tokenList));
        }
    }

    private static function authToken($arrData) {
        if (empty($arrData)) {
            return false;
        }

        if ($arrData["method"] !== $_SERVER["REQUEST_METHOD"] && $arrData["method"] !== "ALL") {
            return true;
        }

        self::startSession();

        $token = $arrData["token"] ?? null;
        if (!$token) {
            return false;
        }

        return self::checkToken($token);
    }

    public static function token() {
        return self::randomToken();
    }

    public static function get() {
        return self::authToken([
            "method" => "GET",
            "token"  => $_GET['_token'] ?? null
        ]);
    }

    public static function post() {
        return self::authToken([
            "method" => "POST",
            "token"  => $_POST['_token'] ?? null
        ]);
    }

    public static function all() {
        $token = $_POST['_token'] ?? $_GET['_token'] ?? null;

        return self::authToken([
            "method" => "ALL",
            "token"  => $token
        ]);
    }

    public static function flushToken() {
        self::startSession();
        $_SESSION['X-CSRF-TOKEN-LIST'] = json_encode([]);
    }
}