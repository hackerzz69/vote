<?php

use Rammewerk\Router\Router;

class PageRouter extends Router {

    private static $instance;

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new PageRouter(null); // ✅ Pass null to match expected Closure
        }
        return self::$instance;
    }

    private $controller;
    private $method;
    private $params;

    public $route_paths = [];

public function initRoutes() {
    $this->add('', fn() => $this->setRoute('index', 'index'));
    $this->add('callback', fn() => $this->setRoute('index', 'callback'));

    $this->add('vote/([0-9]+)', function($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') throw new Exception('Method Not Allowed');
        return $this->setRoute('index', 'vote', ['id' => $id]);
    });

    $this->add('buttons', function() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Method Not Allowed');
        return $this->setRoute('index', 'buttons');
    });

    $this->add('admin', fn() => $this->setRoute('admin', 'index'));
    $this->add('admin/api', fn() => $this->setRoute('api', 'index'));
    $this->add('api/users', fn() => $this->setRoute('api', 'users'));

    $this->add('api/users/([A-Za-z0-9 ]+)', fn($username) => $this->setRoute('api', 'users', ['username' => $username]));
    $this->add('api/users/([A-Za-z0-9 ]+)/votes', fn($username) => $this->setRoute('api', 'votes', ['username' => $username]));

    $this->add('admin/login', fn() => $this->setRoute('login', 'index'));
    $this->add('admin/login/authenticate', fn() => $this->setRoute('login', 'authenticate'));
    $this->add('admin/mfa', fn() => $this->setRoute('admin', 'mfa'));

    $this->add('admin/links', function() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') throw new Exception('Method Not Allowed');
        return $this->setRoute('links', 'index');
    });

    $this->add('admin/links/add', fn() => $this->setRoute('links', 'add'));
    $this->add('admin/links/edit/([0-9]+)', fn($id) => $this->setRoute('links', 'edit', ['id' => $id]));
    $this->add('admin/links/delete/([0-9]+)', fn($id) => $this->setRoute('links', 'delete', ['id' => $id]));

    $this->add('admin/links/toggle/([0-9]+)', function($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') throw new Exception('Method Not Allowed');
        return $this->setRoute('links', 'toggle', ['id' => $id]);
    });

    $this->add('admin/voters', fn() => $this->setRoute('admin', 'voters'));
    $this->add('admin/votes', fn() => $this->setRoute('admin', 'votes'));
    $this->add('admin/users', fn() => $this->setRoute('users', 'index'));
    $this->add('admin/users/add', fn() => $this->setRoute('users', 'add'));
    $this->add('admin/users/edit/([0-9]+)', fn($id) => $this->setRoute('users', 'edit', ['id' => $id]));
    $this->add('admin/users/delete/([0-9]+)', fn($id) => $this->setRoute('users', 'delete', ['id' => $id]));
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
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    public function getUrl() {
        $baseUrl = 'http' . ($this->isSecure() ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
        return $baseUrl . web_root;
    }
}