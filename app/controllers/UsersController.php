<?php
use Fox\CSRF;

class UsersController extends Controller {

    public function index() {
        $this->set("users", Users::getUsers());
        $this->set("csrf_token", CSRF::token());
    }

    public function delete($id = null) {
        $user = Users::getUserById($id);

        if (!$user) {
            $this->setView("errors/show404");
            return;
        }

        if ($user['super_admin']) {
            $this->setView("errors/show401");
            return;
        }

        if ($this->request->isPost() && CSRF::post()) {
            $deleted = Users::deleteUser($user['id']);

            if ($deleted) {
                $this->redirect("admin/users");
                exit;
            }

            $this->set("error", "Failed to delete user.");
        }

        $this->set("user", $user);
        $this->set("csrf_token", CSRF::token());
    }

    public function edit($id = null) {
        $user = Users::getUserById($id);

        if (!$user) {
            $this->setView("errors/show404");
            return;
        }

        if (!$this->security->getUser()['super_admin'] && $user['super_admin']) {
            $this->setView("errors/show401");
            return;
        }

        if ($this->request->isPost() && CSRF::post() ) {
            $username  = $this->request->getPost("username", 'string');
            $password  = $this->request->getPost("password");
            $scopes    = $this->request->getPost("access_list");

            if ($password != null && (strlen($password) < 3 || strlen($password) > 25)) {
                $this->set("error", "Password must be between 6 and 25 characters.");
            } else {
                $updated = Users::updateUser($user['id'], $username, $password, $scopes);

                if ($updated) {
                    $user = Users::getUserById($id);
                    $this->set("success", "User Updated!");
                } else {
                    $this->set("error", "Failed to create user.");
                }
            }
        }

        $this->set("user", $user);
        $this->set("user_scopes", json_decode($user['scopes'], true));
        $this->set("scopes", $this->getScopes());
        $this->set("csrf_token", CSRF::token());
    }

    public function add() {
        if ($this->request->isPost() && CSRF::post()) {
            $username  = $this->request->getPost("username", 'string');
            $password  = $this->request->getPost("password");
            $scopes    = $this->request->getPost("access_list");

            $user = Users::getUser($username);

            if ($user) {
                $this->set("error", "Username already exists.");
            } else if (strlen($password) < 3 || strlen($password) > 25) {
                $this->set("error", "Password must be ebtween 6 and 25 characters.");
            } else {
                $created = Users::createUser($username, $password, $scopes);

                if ($created) {
                    $this->set("success", "User created!");
                } else {
                    $this->set("error", "Failed to create user.");
                }
            }
        }

        $this->set("scopes", $this->getScopes());
        $this->set("csrf_token", CSRF::token());
    }

    private function getScopes() {
        $scopes = [];

        foreach ($this->router->routes as $route) {
            $scope = call_user_func($route->callback, []);
            if ($scope[0] == "index" || $scope[0] == 'login' || $scope[0] == "api")
                continue;
            $scopes[$scope[0]][] = $scope[1];
        }

        return $scopes;
    }

}