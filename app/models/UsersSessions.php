<?php

class UsersSessions extends Model {

    public static function getSession($key) {

        $now = time();

        $query = self::getDb()
            ->from("users_sessions")
            ->where([
                "access_key = ?" => $key,
                "expires_at >= ?" => $now
            ]);


        return $query->fetch();
    }

    public static function registerSession($user_id, $key, $ip_address, $expires) {


        try {
            $query = self::getDb()
                ->insertInto("users_sessions")
                ->values([
                    "user_id"    => $user_id,
                    "access_key" => $key,
                    "ip_address" => $ip_address,
                    "expires_at" => $expires
                ]);



            $result = $query->execute();

            return $result;
        } catch (PDOException $e) {

            return false;
        }
    }

    public static function updateSession($key, $time) {


        try {
            $query = self::getDb()
                ->update("users_sessions")
                ->set(["expires_at" => $time])
                ->where(["access_key = ?" => $key]);



            return $query->execute();
        } catch (PDOException $e) {

            return false;
        }
    }

    public static function deleteSession($key) {


        try {
            $query = self::getDb()
                ->deleteFrom("users_sessions")
                ->where(["access_key = ?" => $key]);



            return $query->execute();
        } catch (PDOException $e) {

            return false;
        }
    }
}