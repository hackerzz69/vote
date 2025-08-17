<?php


class UsersSessions extends Model {

    public static function getSession($key) {
        return self::getDb()->select(true)
            ->columns(['*'])
            ->from("users_sessions")
            ->where(['access_key = :key', 'expires > :time'])
            ->bind([':key' => $key, ':time' => time()])
            ->execute();
    }

    public static function registerSession($user_id, $key, $ip_address, $expires) {
        return self::getDb()->insert()
            ->into("users_sessions")
            ->columns(['user_id', 'access_key', 'ip_address', 'expires'])
            ->values([
                [$user_id, $key, $ip_address, $expires]
            ])
            ->execute();
    }

    public static function updateSession($key, $time) {
        return self::getDb()->update()->table("users_sessions")
            ->columns([
                'expires' => ":time"
            ])
            ->where([
                'access_key = :access_key'
            ])->bind([
                ':expires'     => $time,
                ':access_key'  => $key
            ])->execute();
    }

    public static function deleteSession($key) {
        return self::getDb()->delete()->from("users_sessions")
            ->where([
                'access_key = :access_key'
            ])->bind([
                ':access_key'  => $key
            ])->execute();
    }

}