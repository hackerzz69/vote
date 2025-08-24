<?php

use Fox\QueryType;

class VoteLinks extends Model {

    public static function addLink($title, $url, $site_id) {
        return self::getDb()
            ->insertInto("vote_links", ["title", "url", "site_id", "active"])
            ->values([
                'title'   => $title,
                'url'     => $url,
                'site_id' => $site_id,
                'active'  => 1
            ])
            ->execute();
    }

    public static function editLink($id, $title, $link, $siteId) {
        return self::getDb()
            ->update("vote_links")
            ->set([
                'title'   => $title,
                'url'     => $link,
                'site_id' => $siteId
            ])
            ->where("id", $id)
            ->execute();
    }

    public static function setActive($id, $active) {
        return self::getDb()
            ->update("vote_links")
            ->set(['active' => $active])
            ->where("id", $id)
            ->execute();
    }

    public static function deleteLink($id) {
        return self::getDb()
            ->deleteFrom("vote_links")
            ->where("id", $id)
            ->execute();
    }

    /**
     * Returns all voting links
     * @return array
     */
    public static function getLinks(): array {
        return self::getDb()
            ->from("vote_links")
            ->fetchAll(); // Changed from execute() to fetchAll()
    }

    /**
     * Returns all active voting links
     * @return array
     */
    public static function getVoteLinks(): array {
        return self::getDb()
            ->from("vote_links")
            ->where("active", 1)
            ->fetchAll(); // Changed from execute() to fetchAll()
    }

    /**
     * Returns a single vote link based on the id column
     * @param int $id
     * @return array|null
     */
    public static function getLink(int $id): ?array {
        $link = self::getDb()
            ->from("vote_links")
            ->where("id", $id)
            ->fetch(); // Replaces getSingle(true)->execute()

        return $link ?: null;
    }
}