<?php

use Envms\FluentPDO\Query;

class Model
{
    protected static ?PDO $pdo = null;
    protected static ?Query $fluent = null;

    public static function getDb(): Query
    {
        if (!self::$fluent) {
            if (!self::$pdo) {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=utf8mb4',
                    MYSQL_HOST,
                    MYSQL_DATABASE
                );

                self::$pdo = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            }

            self::$fluent = new Query(self::$pdo);
        }

        return self::$fluent;
    }
}