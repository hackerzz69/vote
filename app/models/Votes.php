<?php

use lukafurlan\database\connector\MySQLConnector;

class Votes extends Model {

    /**
     * @param $days integer
     * @return array
     */
    public static function getVoteData($days) {
        $start  = strtotime(date('Y-m-d H:i:s')." - $days DAYS");
        $end    = strtotime(date('Y-m-d H:i:s'));

        $votes = self::getDb()->select()
            ->columns(['*'])
            ->from("votes")
            ->where([
                'voted_on != -1',
                'voted_on > :start',
                'voted_on < :end'
            ])->bind([
                ':start' => $start,
                ':end' => $end
            ])->execute();

        $days = Functions::getLastNDays($days, 'n j');
        $data = array_fill_keys($days, 0);

        $total = 0;

        foreach ($votes as $vote) {
            $day = date("n j", $vote['voted_on']);

            if (!isset($data[$day])) {
                continue;
            }

            $data[$day] += 1;
            $total += 1;
        }

        return $data;
    }

    /**
     * @return array|bool|mixed
     */
    public static function getTotals() {
        $result = self::getDb()->select(true)
            ->columns([
                "COUNT(DISTINCT username) AS u_total",
                "COUNT(*) AS total",
                "(SELECT COUNT(*) FROM votes v WHERE v.claimed = 1) AS claimed",
                "(SELECT COUNT(*) FROM votes v WHERE v.claimed = 0) AS pending",
            ])
            ->from("votes")
            ->execute();
        return $result;
    }

    /**
     * @return array|bool|mixed
     */
    public static function getUniqueUsers() {
        $result = self::getDb()->select(true)
            ->columns(["COUNT(DISTINCT username) AS total"])
            ->from("votes")
            ->execute();
        return $result;
    }

    /**
     * @return array|bool|mixed
     */
    public static function getVotes() {
        $result = self::getDb()->select()
            ->columns(["*"])
            ->from("votes")
            ->order('voted_on DESC')
            ->execute();
        return $result;
    }

    /**
     * @param $username
     * @return array|bool|mixed
     */
    public static function getActiveVote($username) {
        $result = self::getDb()->select(true)
            ->columns(["*"])
            ->from("votes")
            ->where([
                "voted_on = -1",
                "username = :username",
                ":time - voted_on < 43200"
            ])
            ->bind([
                ":username" => "$username",
                ':time' => time()
            ])
            ->execute();
        return $result;
    }

    /**
     * @param $username
     * @param $site_id
     * @return array|bool|mixed
     */
    public static function getLatestVote($username, $site_id) {
        $result = self::getDb()->select(true)
            ->columns([
                "*"
            ])
            ->from("votes")
            ->where([
                "voted_on != -1",
                "username = :username",
                ":time - voted_on < 43200",
                "site_id = :sid"
            ])
            ->bind([
                ":username" => "$username",
                ":time" => time(),
                ":sid" => $site_id
            ])
            ->order('voted_on DESC')
            ->execute();

        return $result;
    }

    /**
     * @param $username
     * @return array|bool|mixed
     */
    public static function getLastVote($username) {
        $result = self::getDb()->select(true)
            ->columns([
                "*"
            ])
            ->from("votes")
            ->where([
                "voted_on != -1",
                "username = :username"
            ])
            ->bind([
                ":username" => $username
            ])
            ->order('voted_on DESC')
            ->execute();
        return $result;
    }

    /**
     * @param $key
     * @return array|bool|mixed
     */
    public static function getVote($key) {
        $result = self::getDb()->select(true)
            ->columns([
                "*"
            ])
            ->from("votes")
            ->where([
                "voted_on = -1",
                "vote_key = :key"
            ])
            ->bind([
                ":key" => $key
            ])
            ->order('voted_on DESC')
            ->execute();
        return $result;
    }

    /**
     * @param $name
     * @param $ip
     * @param $vkey
     * @param $siteId
     */
    public static function createVote($name, $ip, $vkey, $siteId) {
        self::getDb()->insert()
            ->into("votes")
            ->columns(["username", "ip_address", "vote_key", "site_id", "started_on"])
            ->values([
                [ $name, $ip, $vkey, $siteId, time() ]
            ])->execute();
    }

    public static function getUsers() {
        $result = self::getDb()->select()
            ->columns([
                'votes.username',
                'COUNT(*) AS total',
                'votes.ip_address',
                'votes.started_on',
                'votes.voted_on'
            ])
            ->from("votes")
            ->group("votes.username, votes.ip_address, votes.started_on, votes.voted_on")
            ->order("total DESC, username ASC")
            ->execute();
        return $result;
    }

    public static function getPendingVotes($username) {
        $result = self::getDb()->select()
            ->columns(['*'])
            ->from("votes")
            ->where(['username = :username AND voted_on != -1 AND claimed = 0'])
            ->bind([
                ':username' => $username
            ])->execute();
        return $result;
    }

    /**
     * @param $username
     * @return bool
     */
    public static function claimVotes($username) {
        return self::getDb()->update()->table("votes")
            ->columns([
                'claimed' => ':claimed',
            ])
            ->where([
                'username = :username',
                'voted_on != -1',
                'claimed = 0'
            ])->bind([
                ':username'  => $username,
                ':claimed'  => 1,
            ])->execute();
    }

    public static function getUser($username) {
        $result = self::getDb()->select(true)
            ->columns([
                'votes.username',
                'COUNT(*) AS total',
                'votes.ip_address',
                'votes.started_on AS last_vote',
                '(SELECT COUNT(*) FROM votes v WHERE v.username = votes.username) AS total',

                '(SELECT COUNT(*) FROM votes v WHERE v.username = votes.username AND claimed = 0) AS pending',
                '(SELECT COUNT(*) FROM votes v WHERE v.username = votes.username AND claimed = 1) AS completed',
            ])
            ->from("votes")
            ->where(['username = :username'])
            ->group("votes.username, votes.ip_address, votes.started_on")
            ->order("votes.started_on DESC, username ASC")
            ->bind([
                ':username' => $username
            ])
            ->execute();
        return $result;
    }

    /**
     * @param $id
     * @param $time
     * @return bool
     */
    public static function completeVote($id, $time) {
        return self::getDb()->update()->table("votes")
            ->columns([
                'voted_on' => ':voted_on',
            ])
            ->where([
                'id = :id',
            ])->bind([
                ':id'    => $id,
                ':voted_on'  => $time,
            ])->execute();
    }


}