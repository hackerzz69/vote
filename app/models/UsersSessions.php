<?php

class UsersSessions extends Model {

    public static function getSession($key) {
        error_log("[Session] Fetching session for key: " . var_export($key, true));

        $now = time();

        $query = self::getDb()
            ->from("users_sessions")
            ->where([
                "access_key = ?" => $key,
                "expires_at >= ?" => $now
            ]);

        error_log("[SQL] " . $query->getQuery());
        error_log("[SQL Params] " . var_export($query->getParameters(), true));

        return $query->fetch();
    }

    public static function registerSession($user_id, $key, $ip_address, $expires) {
        error_log("[Session] user_id: " . var_export($user_id, true));
        error_log("[Session] key: " . var_export($key, true));
        error_log("[Session] ip_address: " . var_export($ip_address, true));
        error_log("[Session] expires_at: " . var_export($expires, true));

        try {
            $query = self::getDb()
                ->insertInto("users_sessions")
                ->values([
                    "user_id"    => $user_id,
                    "access_key" => $key,
                    "ip_address" => $ip_address,
                    "expires_at" => $expires
                ]);

            error_log("[SQL] " . $query->getQuery());
            error_log("[SQL Params] " . var_export($query->getParameters(), true));

            $result = $query->execute();
            error_log("[Session] Registered session for user ID: " . $user_id);
            return $result;
        } catch (PDOException $e) {
            error_log("[Session Error] " . $e->getMessage());
            return false;
        }
    }

    public static function updateSession($key, $time) {
        error_log("[Session] Updating session for key: " . var_export($key, true) . " with new expiry: " . var_export($time, true));

        try {
            $query = self::getDb()
                ->update("users_sessions")
                ->set(["expires_at" => $time])
                ->where(["access_key = ?" => $key]);

            error_log("[SQL] " . $query->getQuery());
            error_log("[SQL Params] " . var_export($query->getParameters(), true));

            return $query->execute();
        } catch (PDOException $e) {
            error_log("[Session Error] " . $e->getMessage());
            return false;
        }
    }

    public static function deleteSession($key) {
        error_log("[Session] Deleting session for key: " . var_export($key, true));

        try {
            $query = self::getDb()
                ->deleteFrom("users_sessions")
                ->where(["access_key = ?" => $key]);

            error_log("[SQL] " . $query->getQuery());
            error_log("[SQL Params] " . var_export($query->getParameters(), true));

            return $query->execute();
        } catch (PDOException $e) {
            error_log("[Session Error] " . $e->getMessage());
            return false;
        }
    }
}