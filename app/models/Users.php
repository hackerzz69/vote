<?php

class Users extends Model {

    public static function getUsers() {
        return self::getDb()
            ->from("users")
            ->fetchAll();
    }

    public static function getUser($username) {
        return self::getDb()
            ->from("users")
            ->where("username", $username)
            ->fetch();
    }

    public static function getUserById($id) {
        return self::getDb()
            ->from("users")
            ->where("id", $id)
            ->fetch();
    }

    public static function deleteUser($id) {
        return self::getDb()
            ->deleteFrom("users")
            ->where("id", $id)
            ->execute();
    }

    public static function createUser($username, $password, $scopes) {
        if (empty($username) || empty($password) || empty($scopes)) {
            error_log('User creation failed: Empty value detected! ' . print_r([$username, $password, $scopes], true), 3, __DIR__ . '/../../error.log');
            return false;
        }

        // Ensure scopes is a string (JSON encoded if array)
        if (is_array($scopes)) {
            $scopes = json_encode($scopes);
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $created = time();

        $logData = [
            'sql' => 'INSERT INTO users (username, password, created, scopes) VALUES (?, ?, ?, ?)',
            'values' => [
                'username' => $username,
                'password' => $hashed_password,
                'created'  => $created,
                'scopes'   => $scopes
            ]
        ];
        error_log('User creation SQL: ' . print_r($logData, true), 3, __DIR__ . '/../../error.log');

        return self::getDb()
            ->insertInto("users", ['username', 'password', 'created', 'scopes'])
            ->values([
                'username' => $username,
                'password' => $hashed_password,
                'created'  => $created,
                'scopes'   => $scopes
            ])
            ->execute();
    }

    public static function updateLogin($id, $ip_address) {
        return self::getDb()
            ->update("users")
            ->set([
                'last_ip'    => $ip_address,
                'last_login' => time()
            ])
            ->where("id", $id)
            ->execute();
    }

    public static function updateMfa($id, $secret) {
        return self::getDb()
            ->update("users")
            ->set(['mfa_secret' => $secret])
            ->where("id", $id)
            ->execute();
    }

    public static function updateUser($id, $username, $password, $scopes) {
        $data = [
            'username' => $username,
            'scopes'   => is_array($scopes) ? json_encode($scopes) : $scopes
        ];

        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        return self::getDb()
            ->update("users")
            ->set($data)
            ->where("id", $id)
            ->execute();
    }
}