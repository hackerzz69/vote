<?php

use Router\Router;

class PageRouter extends Router {

    private static $instance;

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new PageRouter(web_root);
        }
        return self::$instance;
    }

    private $controller;
    private $method;
    private $params;

    public $route_paths = [];

    public function initRoutes() {

        $this->all('', function() {
            return $this->setRoute('index', 'index');
        });

        $this->all('callback', function() {
            return $this->setRoute('index', 'callback');
        });

        $this->get('vote/([0-9]+)', function($id) {
            return $this->setRoute('index', 'vote', ['id' => $id]);
        });

        $this->post('buttons', function() {
            return $this->setRoute('index', 'buttons');
        });

        $this->all('admin', function() {
            return $this->setRoute('admin', 'index');
        });

        $this->all('admin/api', function() {
            return $this->setRoute('api', 'index');
        });

        $this->all('api/users', function() {
            return $this->setRoute('api', 'users');
        });

        $this->all('api/users/([A-Za-z0-9 ]+)', function($username) {
            return $this->setRoute('api', 'users', ['username' => $username]);
        });

        $this->all('api/users/([A-Za-z0-9 ]+)/votes', function($username) {
            return $this->setRoute('api', 'votes', ['username' => $username]);
        });

        $this->all('admin/login', function() {
            return $this->setRoute('login', 'index');
        });
        
        $this->all('admin/login/authenticate', function() {
            return $this->setRoute('login', 'authenticate');
        });

        $this->all('admin/mfa', function() {
            return $this->setRoute('admin', 'mfa');
        });

        $this->get('admin/links', function() {
            return $this->setRoute('links', 'index');
        });

        $this->all('admin/links/add', function() {
            return $this->setRoute('links', 'add');
        });

        $this->all('admin/links/edit/([0-9]+)', function($id)  {
            return $this->setRoute('links', 'edit', ['id' => $id]);
        });

        $this->all('admin/links/delete/([0-9]+)', function($id)  {
            return $this->setRoute('links', 'delete', ['id' => $id]);
        });

        $this->get('admin/links/toggle/([0-9]+)', function($id) {
            return $this->setRoute('links', 'toggle', ['id' => $id]);
        });

        $this->all('admin/voters', function() {
            return $this->setRoute('admin', 'voters');
        });

        $this->all('admin/votes', function() {
            return $this->setRoute('admin', 'votes');
        });

        $this->all('admin/users', function() {
            return $this->setRoute('users', 'index');
        });

        $this->all('admin/users/add', function() {
            return $this->setRoute('users', 'add');
        });

        $this->all('admin/users/edit/([0-9]+)', function($id) {
            return $this->setRoute('users', 'edit',  ['id' => $id]);
        });

        $this->all('admin/users/delete/([0-9]+)', function($id) {
            return $this->setRoute('users', 'delete', ['id' => $id]);
        });
    }

    public function setRoute($controller, $method, $params = []) {
        $this->controller = $controller;
        $this->method = $method;
        $this->params = $params;

        return [$controller, $method, $params];
    }

    public function getController($formatted = false) {
        return $formatted ? ucfirst($this->controller).'Controller' : $this->controller;
    }

    public function getViewPath() {
        return $this->getController().'/'.$this->getMethod();
    }

    public function getMethod() {
        return $this->method;
    }

    public function getParams() {
        return $this->params;
    }

    public function isSecure() {
        return
          (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }

    public function getUrl() {
        $baseUrl =  'http'.($this->isSecure() ? 's' : '').'://' . $_SERVER['HTTP_HOST'];
        return $baseUrl.web_root;
    }
}