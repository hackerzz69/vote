<?php

use Fox\CSRF;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\Algorithm;

class LoginController extends Controller
{
    public function index()
    {
        if ($this->cookies->has("authenticate")) {
            //error_log("[Login] Redirecting to MFA step — cookie found.");
            $this->redirect("admin/login/authenticate");
            exit();
        }

        if ($this->request->isPost() && CSRF::post()) {
            $username = $this->request->getPost("username", "string");
            $password = $this->request->getPost("password");

            //error_log("[Login] Attempting login for username: {$username}");

            if ($username !== admin_username || disable_root) {
                $user = Users::getUser($username);

                if (!$user) {
                    //error_log("[Login] User not found: {$username}");
                    $this->set("error", "Invalid username or password.");
                } elseif (!isset($user["password"])) {
                    //error_log(
                    //    "[Login] Password field missing for user: {$username}"
                    //);
                    $this->set("error", "Invalid username or password.");
                } elseif (!password_verify($password, $user["password"])) {
                    //error_log(
                    //    "[Login] Password mismatch for user: {$username}"
                    //);
                    $this->set("error", "Invalid username or password.");
                } else {
                    //error_log("[Login] User authenticated: {$username}");

                    if (!empty($user["mfa_secret"])) {
                        //error_log("[Login] MFA required for user: {$username}");
                        $this->cookies->set("authenticate", $user["id"], 300);
                        $this->redirect("admin/login/authenticate");
                        exit();
                    }

                    $this->createSession($user);
                    //error_log("[Login] Session created for user: {$username}");
                    $this->redirect("admin");
                    exit();
                }
            } else {
                //error_log("[Login] Root login attempt");

                if ($password !== admin_password) {
                    //error_log("[Login] Root password mismatch");
                    $this->set("error", "Invalid username or password. 1");
                } else {
                    //error_log("[Login] Root authenticated");
                    $hashed = password_hash(admin_password, PASSWORD_BCRYPT);
                    $this->cookies->set("access_key", $hashed);
                    $this->redirect("admin");
                    exit();
                }
            }
        }

        $this->set("csrf_token", CSRF::token());
    }

    public function authenticate()
    {
        if (!$this->cookies->has("authenticate")) {
            //error_log("[MFA] No authentication cookie found — redirecting.");
            $this->redirect("admin/login");
            exit();
        }

        if ($this->request->isPost() && CSRF::post()) {
            $user_id = $this->cookies->get("authenticate", "int");
            $user = Users::getUserById($user_id);

            if (!$user) {
                //error_log("[MFA] Invalid user ID: {$user_id}");
                $this->set("error", "Invalid user id.");
            } else {
                $code = $this->request->getPost("code", "int");
                $mfa_secret = $user["mfa_secret"];

                $tfa = new TwoFactorAuth(
                    qrcodeprovider: new BaconQrCodeProvider(),
                    issuer: site_title,
                    digits: 6,
                    period: 30,
                    algorithm: Algorithm::Sha1
                );
                $verified = $tfa->verifyCode($mfa_secret, $code);

                if ($verified) {
                    //error_log("[MFA] Code verified for user ID: {$user_id}");
                    $this->createSession($user);
                    $this->cookies->delete("authenticate");
                    $this->redirect("admin");
                    exit();
                }

                //error_log("[MFA] Invalid code for user ID: {$user_id}");
                $this->set("error", "Invalid Code.");
            }
        }

        $this->set("csrf_token", CSRF::token());
    }

    public function createSession($user)
    {
        $random_key = Functions::generateString(25);
        $expires = time() + 86400;
        $ip_address = $this->request->getAddress();

        UsersSessions::registerSession(
            $user["id"],
            $random_key,
            $ip_address,
            $expires
        );
        Users::updateLogin($user["id"], $ip_address);

        $this->cookies->set("user_key", $random_key);
        //error_log("[Session] Registered session for user ID: {$user["id"]}");
    }
}
