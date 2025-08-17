<?php


class Users extends Model {

    public static function getUsers() {
        return self::getDb()->select()
            ->columns(['*'])
            ->from("users")
            ->execute();
    }

    public static function getUser($username) {
        return self::getDb()->select(true)
            ->columns(['*'])
            ->from("users")
            ->where(['username = :user'])
            ->bind(['user' => $username])
            ->execute();
    }

    public static function getUserById($id) {
        return self::getDb()->select(true)
            ->columns(['*'])
            ->from("users")
            ->where(['id = :id'])
            ->bind(['id' => $id])
            ->execute();
    }

    public static function deleteUser($id) {
        return self::getDb()->delete()
            ->from("users")
            ->where(["id = :id"])
            ->bind([':id' => $id])
            ->execute();
    }

    public static function createUser($username, $password, $scopes) {
        return self::getDb()->insert()
            ->into("users")
            ->columns(['username', 'password', 'created', 'scopes'])
            ->values([
                [$username, password_hash($password, PASSWORD_BCRYPT), time(), $scopes]
            ])
            ->execute();
    }

    public static function updateLogin($id, $ip_address) {
        return self::getDb()->update()->table("users")
            ->columns([
                'last_ip' => ":last_ip",
                'last_login' => ':last_login'
            ])
            ->where([
                'id = :id'
            ])->bind([
                ':id'  => $id,
                ':last_ip'  => $ip_address,
                'last_login' => time()
            ])->execute();
    }

    public static function updateMfa($id, $secret) {
        return self::getDb()->update()->table("users")
            ->columns(['mfa_secret' => ':mfa_secret'])
            ->where([
                'id = :id'
            ])->bind([
                ':id' => $id,
                ':mfa_secret' => $secret
            ])->execute();
    }


    public static function updateUser($id, $username, $password, $scopes) {
        if ($password == null) {
            return self::getDb()->update()->table("users")
                ->columns([
                    'username' => ":username",
                    'scopes'   => ':scopes'
                ])
                ->where([
                    'id = :id'
                ])->bind([
                    ':id'       => $id,
                    ':username' => $username,
                    ':scopes'   => $scopes
                ])->execute();
        }

        return self::getDb()->update()->table("users")
            ->columns([
                'username' => ":username",
                'password' => ":password",
                'scopes'   => ':scopes'
            ])
            ->where([
                'id = :id'
            ])->bind([
                ':id'       => $id,
                ':username' => $username,
                ":password" => password_hash($password, PASSWORD_BCRYPT),
                ':scopes'   => $scopes
            ])->execute();
    }


}