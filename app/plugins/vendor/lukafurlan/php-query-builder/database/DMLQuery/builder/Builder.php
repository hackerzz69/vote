<?php
namespace lukafurlan\database\DMLQuery\builder;

use Fox\QueryType;
use lukafurlan\database\DMLQuery\DMLQuery;
use PDO;

/**
 * @author Luka Furlan <Luka.furlan9@gmail.com>
 * @copyright 2018 Luka Furlan
 */

abstract class Builder {

    protected $query = "";
    protected $bind  = [];

    /** @var PDO */
    protected $connection;

    public function __construct($queryStart, $connection) {
        $this->query .= $queryStart;
        $this->connection = $connection;
    }

    public function build() {
        return new DMLQuery("(" . $this->query . ")", $this->bind);
    }

    public function execute() {
        $stmt = $this->connection->prepare($this->query);
        return $stmt->execute($this->bind);
    }

}