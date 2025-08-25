<?php

use Fox\QueryType;

class VoteLinks extends Model {

    public static function editLink($id, $title, $link, $siteId) {
        return self::getDb()->update()->table("vote_links")
            ->columns([
                'title'   => ":title",
                'url'     => ":url",
                'site_id' => ':site_id'
            ])
            ->where([
                'id = :id'
            ])->bind([
                ':id'      => $id,
                ':title'   => $title,
                ':url'     => $link,
                ':site_id' => $siteId
            ])->execute();
    }

    public static function setActive($id, $active) {
        return self::getDb()->update()->table("vote_links")
            ->columns([
                'active' => ":active",
            ])
            ->where([
                'id = :id'
            ])->bind([
                ':id'     => $id,
                ':active' => $active,
            ])->execute();
    }

    public static function deleteLink($id) {
        return self::getDb()->delete()
            ->from("vote_links")
            ->where(["id = :id"])
            ->bind([":id" => $id])
            ->execute();
    }

    /**
     * Returns all voting links
     * @return array
     */
    public static function getLinks() {
        $query = self::getDb()->from("vote_links")
            ->select("*, (SELECT COUNT(*) FROM votes WHERE votes.site_id = vote_links.id) AS votes");

        error_log("[DEBUG SQL] " . $query->getQuery());

        return $query->execute()->fetchAll();
    }

    /**
     * Returns all active voting links
     * @return array
     */
    public static function getVoteLinks() {
        $query = self::getDb()->from("vote_links")
            ->where(["active = 1"]);

        error_log("[DEBUG SQL] " . $query->getQuery());

        return $query->execute()->fetchAll();
    }

    /**
     * Returns a single vote link based on the id column
     * @param int $id
     * @return array|null
     */
    public static function getLink($id) {
        try {
            $query = self::getDb()->from("vote_links")
                ->where(["id" => $id]);

            error_log("[DEBUG SQL] " . $query->getQuery());

            $stmt = $query->execute(["id" => $id]);
            return $stmt ? $stmt->fetch() : null;
        } catch (\PDOException $e) {
            error_log("[PDO ERROR] " . $e->getMessage());
            return null;
        }
    }

    public static function addLink($title, $url, $site_id) {
        return self::getDb()->insert()
            ->into("vote_links")
            ->columns(["title", "url", "site_id", "active"])
            ->values([
                [ $title, $url, $site_id, 1 ]
            ])->execute();
    }
}