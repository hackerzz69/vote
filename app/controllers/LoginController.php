<?php


use Fox\CSRF;

class LoginController extends Controller {

    public function index() {
        if ($this->cookies->has("authenticate")) {
            $this->redirect("admin/login/authenticate");
            exit;
        }

        if ($this->request->isPost() && CSRF::post()) {
            $username = $this->request->getPost("username", 'string');
            $password = $this->request->getPost("password");

            if ($username != admin_username || disable_root) {
                $user = Users::getUser($username);

                if (!$user || !password_verify($password, $user['password'])) {
                    $this->set("error", "Invalid username or password.");
                } else {
                    if ($user['mfa_secret']) {
                        $this->cookies->set("authenticate", $user['id'], 300);
                        $this->redirect("admin/login/authenticate");
                        exit;
                    }
                    
                    $this->createSession($user);
                    $this->redirect("admin");
                    exit;
                }
            } else {
                if ($password != admin_password) {
                    $this->set("error", "Invalid username or password. 1");
                } else {
                    $hashed = password_hash(admin_password, PASSWORD_BCRYPT);
                    $this->cookies->set("access_key", $hashed);
                    $this->redirect("admin");
                    exit;
                }
            }
        }

        $this->set("csrf_token", CSRF::token());
    }

    public function authenticate() {
        if (!$this->cookies->has("authenticate")) {
            $this->redirect("admin/login");
            exit;
        }

        if ($this->request->isPost() && CSRF::post()) {
            $user_id  = $this->cookies->get("authenticate", "int");
            $user     = Users::getUserById($user_id);

            if (!$user) {
                $this->set("error", "Invalid user id.");
            } else {
                $code       = $this->request->getPost("code", "int");
                $mfa_secret = $user['mfa_secret'];

                $tfa        = new RobThree\Auth\TwoFactorAuth(site_title);
                $verified   = $tfa->verifyCode($mfa_secret, $code);
                
                if ($verified) {
                    $this->createSession($user);
                    $this->cookies->delete("authenticate");
                    $this->redirect("admin");
                    exit;
                }

                $this->set("error", "Invalid Code.");
            }
        }

        $this->set("csrf_token", CSRF::token());
    }

    public function createSession($user) {
        $random_key = Functions::generateString(25);
        $expires    = time() + 86400;
        $ip_address = $this->request->getAddress();

        UsersSessions::registerSession($user['id'], $random_key, $ip_address, $expires);
        Users::updateLogin($user['id'], $ip_address);

        $this->cookies->set("user_key", $random_key);
    }
}