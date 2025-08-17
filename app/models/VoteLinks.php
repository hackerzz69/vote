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
                ':id'      => $id,
                ':active'  => $active,
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
        return self::getDb()->select()
            ->columns([
                "*",
                '(SELECT COUNT(*) FROM votes WHERE votes.site_id = vote_links.id) AS votes'
            ])
            ->from("vote_links")
            ->execute();
    }

    /**
     * Returns all voting links
     * @return array
     */
	public static function getVoteLinks() {
        return self::getDb()->select()
            ->columns(["*"])
            ->from("vote_links")
            ->where(["active = 1"])
            ->execute();
    }

    /**
     * Returns a single vote link based on the id column
     * @param $id
     * @return mixed
     */
    public static function getLink($id) {
        return self::getDb()->select()
            ->columns(["*"])
            ->from("vote_links")
            ->where(["id = :id"])
            ->bind([':id' => $id])
            ->getSingle(true)
            ->execute();
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