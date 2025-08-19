<?php

class Votes extends Model {

    public static function getVoteData($days) {
        $start = strtotime("-$days days");
        $end = time();

        $votes = self::getDb()
            ->from("votes")
            ->where([
                "voted_on != ?" => -1,
                "voted_on > ?" => $start,
                "voted_on < ?" => $end
            ])
            ->fetchAll();

        $labels = Functions::getLastNDays($days, 'n j');
        $data = array_fill_keys($labels, 0);

        foreach ($votes as $vote) {
            $day = date("n j", $vote['voted_on']);
            if (isset($data[$day])) {
                $data[$day]++;
            }
        }

        return $data;
    }

    public static function getTotals() {
        return self::getDb()
            ->from("votes")
            ->select([
                "COUNT(DISTINCT username) AS u_total",
                "COUNT(*) AS total",
                "(SELECT COUNT(*) FROM votes WHERE claimed = 1) AS claimed",
                "(SELECT COUNT(*) FROM votes WHERE claimed = 0) AS pending"
            ])
            ->fetch();
    }

    public static function getUniqueUsers() {
        return self::getDb()
            ->from("votes")
            ->select(["COUNT(DISTINCT username) AS total"])
            ->fetch();
    }

    public static function getVotes() {
        return self::getDb()
            ->from("votes")
            ->orderBy("voted_on DESC")
            ->fetchAll();
    }

    public static function getActiveVote($username) {
        return self::getDb()
            ->from("votes")
            ->where([
                "voted_on = ?" => -1,
                "username = ?" => $username,
                "(? - voted_on) < ?" => [time(), 43200]
            ])
            ->fetch();
    }

    public static function getLatestVote($username, $site_id) {
        return self::getDb()
            ->from("votes")
            ->where([
                "voted_on != ?" => -1,
                "username = ?" => $username,
                "(? - voted_on) < ?" => [time(), 43200],
                "site_id = ?" => $site_id
            ])
            ->orderBy("voted_on DESC")
            ->fetch();
    }

    public static function getLastVote($username) {
        return self::getDb()
            ->from("votes")
            ->where([
                "voted_on != ?" => -1,
                "username = ?" => $username
            ])
            ->orderBy("voted_on DESC")
            ->fetch();
    }

    public static function getVote($key) {
        return self::getDb()
            ->from("votes")
            ->where([
                "voted_on = ?" => -1,
                "vote_key = ?" => $key
            ])
            ->orderBy("voted_on DESC")
            ->fetch();
    }

    public static function createVote($name, $ip, $vkey, $siteId) {
        return self::getDb()
            ->insertInto("votes")
            ->values([
                "username"    => $name,
                "ip_address"  => $ip,
                "vote_key"    => $vkey,
                "site_id"     => $siteId,
                "started_on"  => time()
            ])
            ->execute();
    }

    public static function getUsers() {
        return self::getDb()
            ->from("votes")
            ->select([
                "votes.username",
                "COUNT(*) AS total",
                "votes.ip_address",
                "votes.started_on",
                "votes.voted_on"
            ])
            ->groupBy("votes.username, votes.ip_address, votes.started_on, votes.voted_on")
            ->orderBy("total DESC, username ASC")
            ->fetchAll();
    }

    public static function getPendingVotes($username) {
        return self::getDb()
            ->from("votes")
            ->where([
                "username = ?" => $username,
                "voted_on != ?" => -1,
                "claimed = ?" => 0
            ])
            ->fetchAll();
    }

    public static function claimVotes($username) {
        return self::getDb()
            ->update("votes")
            ->set(["claimed" => 1])
            ->where([
                "username = ?" => $username,
                "voted_on != ?" => -1,
                "claimed = ?" => 0
            ])
            ->execute();
    }

    public static function getUser($username) {
        return self::getDb()
            ->from("votes")
            ->select([
                "votes.username",
                "COUNT(*) AS total",
                "votes.ip_address",
                "votes.started_on AS last_vote",
                "(SELECT COUNT(*) FROM votes WHERE username = votes.username) AS total",
                "(SELECT COUNT(*) FROM votes WHERE username = votes.username AND claimed = 0) AS pending",
                "(SELECT COUNT(*) FROM votes WHERE username = votes.username AND claimed = 1) AS completed"
            ])
            ->where(["username = ?" => $username])
            ->groupBy("votes.username, votes.ip_address, votes.started_on")
            ->orderBy("votes.started_on DESC, username ASC")
            ->fetch();
    }

    public static function completeVote($id, $time) {
        return self::getDb()
            ->update("votes")
            ->set(["voted_on" => $time])
            ->where(["id = ?" => $id])
            ->execute();
    }
}