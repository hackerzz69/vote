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
        return self::getDb()
            ->insertInto("users", ['username', 'password', 'created', 'scopes'])
            ->values([
                $username,
                password_hash($password, PASSWORD_BCRYPT),
                time(),
                $scopes
            ])
            ->execute();
    }

    public static function updateLogin($id, $ip_address) {
        return self::getDb()
            ->update("users")
            ->set([
                'last_ip' => $ip_address,
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
            'scopes' => $scopes
        ];

        if ($password !== null) {
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        return self::getDb()
            ->update("users")
            ->set($data)
            ->where("id", $id)
            ->execute();
    }
}