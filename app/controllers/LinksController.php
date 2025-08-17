<?php


use Fox\CSRF;

class LinksController extends Controller {

    public function index() {
        $this->set("links", VoteLinks::getLinks());
    }

    public function toggle($id = null) {
        $link = VoteLinks::getLink($id);

        if (!$link) {
            $this->setView("errors/show404");
            return;
        }

        VoteLinks::setActive($link['id'], $link['active'] == 1 ? 0 : 1);
        $this->redirect("admin/links");
        exit;
    }

    public function edit($id = null) {
        $link = VoteLinks::getLink($id);

        if (!$link) {
            $this->setView("errors/show404");
            return;
        }

        if ($this->request->isPost() && CSRF::post()) {
            $title   = $this->request->getPost("title");
            $url     = $this->request->getPost("url");
            $site_id = $this->request->getPost("site_id");

            if (strlen($title) < 3 || strlen($title) > 20) {
                $this->set("error", "Title must be between 3 and 20 characters.");
            } else if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                $this->set("error", "Url is not valid.");
            } else {
                $edited = VoteLinks::editLink($link['id'], $title, $url, $site_id);

                if ($edited) {
                    $this->redirect("admin/links/edit/".$link['id']);
                    exit;
                }

            }
        }

        $this->set("link", $link);
        $this->set("csrf_token", CSRF::token());
    }

    public function add() {
        if ($this->request->isPost() && CSRF::post()) {
            $title   = $this->request->getPost("title");
            $url     = $this->request->getPost("url");
            $site_id = $this->request->getPost("site_id");

            if (strlen($title) < 3 || strlen($title) > 20) {
                $this->set("error", "Title must be between 3 and 20 characters.");
            } else if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                $this->set("error", "Url is not valid.");
            } else {
                $added = VoteLinks::addLink($title, $url, $site_id);

                if ($added) {
                    $this->redirect("admin/links");
                    exit;
                }

                $this->set("error", "Failed to add vote link. Error Unknown.");
            }
        }

        $this->set("csrf_token", CSRF::token());
    }

    public function delete($id = null) {
        $link = VoteLinks::getLink($id);

        if (!$link) {
            $this->setView("errors/show404");
            return;
        }

        if ($this->request->isPost() && CSRF::post()) {
            $delete = VoteLinks::deleteLink($id);

            if (!$delete) {
                $this->set("error", "Failed to delete link.");
            } else {
                $this->redirect("admin/links");
                exit;
            }
        }

        $this->set("link", $link);
        $this->set("csrf_token", CSRF::token());
    }


}