<?php
namespace lukafurlan\database\connector;
use PDO;

/**
 * @author Luka Furlan <Luka.furlan9@gmail.com>
 * @copyright 2018 Luka Furlan
 */

class MySQLConnector extends Connector {

    public function connect() {
        $this->connection = new PDO("mysql:host=".MYSQL_HOST.";dbname=".MYSQL_DATABASE,
            MYSQL_USERNAME,
            MYSQL_PASSWORD, [
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ]);

        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

}