<?php

use Fox\Cookies;
use Fox\Request;

class Security
{
    private static ?Security $instance = null;

    /**
     * @param Controller $controller
     * @return Security
     */
    public static function getInstance(Controller $controller): self
    {
        if (!self::$instance) {
            self::$instance = new Security($controller);
        }
        return self::$instance;
    }

    private bool $is_root = false;
    private ?array $user = null;
    private Controller $controller;
    private Cookies $cookies;
    private PageRouter $router;
    private Request $request;

    /**
     * Security constructor.
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->cookies    = $controller->getCookies();
        $this->router     = $controller->getRouter(); // ✅ PageRouter instance
        $this->request    = $controller->getRequest();
    }

    /**
     * Verifies if a user has access to certain pages.
     * @return bool
     */
    public function checkAccess(): bool
    {
        $user_key = $this->cookies->get("user_key");
        $root_key = $this->cookies->get("access_key");

        $controller = $this->router->getController();
        $action     = $this->router->getMethod();

        $omit = [
            'index' => ['index', 'buttons', 'vote', 'callback'],
            'api'   => ['users', 'votes'],
        ];

        if (isset($omit[$controller]) && in_array($action, $omit[$controller])) {
            return true;
        }

        if (!$user_key && !$root_key && $controller !== 'login') {
            $this->controller->redirect("admin/login");
            exit;
        }

        if ($this->request->hasQuery("signout")) {
            $this->logout();
            exit;
        }

        $this->is_root = $root_key !== null;
        $is_user = $user_key !== null;

        if ($is_user) {
            $session = UsersSessions::getSession($user_key);

            if (!$session || $this->request->getAddress() !== $session['ip_address']) {
                $this->logout();
                exit;
            }

            $user = Users::getUserById($session['user_id']);

            if (!$user) {
                $this->logout();
                exit;
            }

            $scopes = json_decode($user['scopes'] ?? '{}', true);

            if (isset($scopes[$controller]) && !in_array($action, $scopes[$controller])) {
                return false;
            }

            $this->user = $user;
            $this->controller->set("username", $user['username']);
        } elseif ($this->is_root) {
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
            if ($controller === "login") {
                $this->request->redirect("admin");
                exit;
            }

            $this->controller->set("username", $this->user['username']);
            $this->controller->set("action", $action);
            $this->controller->set("controller", $controller);
            return true;
        }

        return $controller === "login";
    }

    public function logout(): void
    {
        if ($this->cookies->has("user_key")) {
            $user_key = $this->cookies->get("user_key");
            $this->cookies->delete("user_key");
            UsersSessions::deleteSession($user_key);
        }

        $this->cookies->delete("access_key");
        $this->request->redirect("admin/login");
    }

    public function getUser(): ?array
    {
        return $this->user;
    }

    public function isRoot(): bool
    {
        return $this->is_root;
    }
}