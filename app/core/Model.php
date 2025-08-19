<?php

use Envms\FluentPDO\Query;

class Model {

    protected static ?PDO $pdo = null;
    protected static ?Query $fluent = null;

    public static function getDb(): Query {
        if (!self::$fluent) {
            if (!self::$pdo) {
                self::$pdo = new PDO(
                    'mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
                    MYSQL_USERNAME,
                    MYSQL_PASSWORD
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            self::$fluent = new Query(self::$pdo);
        }
        return self::$fluent;
    }
}