<?php
use Fox\Cookies;
use Fox\Request;
use Router\Route;
use Router\Router;

class Security {

    private static $instance;

    /**
     * @param $controller
     * @return Security
     */
    public static function getInstance($controller) {
        if (!self::$instance) {
            self::$instance = new Security($controller);
        }
        return self::$instance;
    }

    private $is_root;
    private $user;
    private $controller;
    private $cookies;
    private $router;
    private $request;

    /**
     * Security constructor.
     * @param Controller $controller
     */
    public function __construct(Controller $controller) {
        $this->controller = $controller;
        $this->cookies    = $controller->getCookies();
        $this->router     = $controller->getRouter();
        $this->request    = $controller->getRequest();
    }

    /**
     * Verifies if a user has access to certain pages.
     * @return bool
     */
    public function checkAccess() {
        $user_key   = $this->cookies->get("user_key");
        $root_key   = $this->cookies->get("access_key");

        $controller = $this->router->getController();
        $action     = $this->router->getMethod();

        $omit = [
            'index' => ['index', 'buttons', 'vote', 'callback'],
            'api'   => ['users', 'votes'],
        ];

        if (in_array($controller, array_keys($omit))) {
            if (in_array($action, $omit[$controller])) {
                return true;
            }
        }

        if (!$user_key && !$root_key && $controller != 'login') {
            $this->controller->redirect("admin/login");
            exit;
        }

        if ($this->request->hasQuery("signout")) {
            $this->logout();
            exit;
        }

        $this->is_root = $root_key != null;
        $is_user = $user_key != null;

        if ($is_user) {
            $session = UsersSessions::getSession($user_key);

            if (!$session) {
                $this->logout();
                exit;
            }

            $ip_address = $this->request->getAddress();

            if ($ip_address != $session['ip_address']) {
                $this->logout();
                exit;
            }

            $user = Users::getUserById($session['user_id']);

            if (!$user) {
                $this->logout();
                exit;
            }

            $scopes = json_decode($user['scopes'], true);

            if (in_array($controller, array_keys($scopes))) {
                $actions = $scopes[$controller];

                if (!in_array($action, $actions)) {
                    return false;
                }
            }

            $this->user = $user;
            $this->controller->set("username", $user['username']);
        } else if ($this->is_root) {
            $hash = $this->cookies->get("access_key");

            if (!password_verify(admin_password, $hash) || disable_root) {
                $this->logout();
                exit;
            }

            $hashed = password_hash(admin_password, PASSWORD_BCRYPT);
            $this->user = ['username' => admin_username];
            $this->cookies->set("access_key", $hashed, 86400);
        }

        if ($this->user) {
            if ($controller == "login") {
                $this->request->redirect("admin");
                exit;
            }

            $this->controller->set("username", $this->user['username']);
            $this->controller->set("action", $action);
            $this->controller->set("controller", $controller);
            return true;
        } else {
            if ($controller == "login") {
                return true;
            }
        }

        return false;
    }

    public function logout() {
        if ($this->cookies->has("user_key")) {
            $user_key = $this->cookies->get('user_key');
            $this->cookies->delete("user_key");

            UsersSessions::deleteSession($user_key);
        }

        $this->cookies->delete("access_key");
        $this->request->redirect("admin/login");
    }

    public function getUser() {
        return $this->user;
    }

    public function isRoot() {
        return $this->is_root;
    }
}