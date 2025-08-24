<?php
use Fox\CSRF;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\Algorithm;

class AdminController extends Controller
{
    public function index()
    {
        $totals = Votes::getTotals();
        $days = 14;

        $this->set("total_votes", $totals["total"]);
        $this->set("unique_users", $totals["u_total"]);
        $this->set("claimed", $totals["claimed"]);
        $this->set("pending", $totals["pending"]);
        $this->set("days", $days);
        $this->set(
            "lastNDays",
            implode(",", Functions::getLastNDays($days, "d"))
        );
        $this->set(
            "vote_data",
            json_encode(array_values(Votes::getVoteData($days)))
        );

        $this->set("callback_url", $this->router->getUrl() . "callback");
    }

    public function mfa()
    {
        $user = $this->security->getUser();
        $user_id = $user["id"];
        $username = $user["username"];

        if ($user["mfa_secret"]) {
            if ($this->request->hasQuery("remove")) {
                if (Users::updateMfa($user_id, null)) {
                    $this->redirect("admin/mfa");
                    exit();
                }
            }
        } else {
            try {
                $qrProvider = new BaconQrCodeProvider();
                $tfa = new TwoFactorAuth(
                    new BaconQrCodeProvider(),
                    site_title,
                    6,
                    30,
                    Algorithm::Sha1
                );

                if ($this->request->isPost()) {
                    $code = $this->request->getPost("code");
                    $secret = $this->request->getPost("secret");

                    if ($tfa->verifyCode($secret, $code)) {
                        if (Users::updateMfa($user_id, $secret)) {
                            $this->redirect("admin/mfa");
                            exit();
                        }
                        $this->set("error", "Failed to update user.");
                    } else {
                        $this->set("error", "Code failed.");
                    }
                }

                $secret = $tfa->createSecret();
                $qrcode = $tfa->getQRCodeImageAsDataUri($username, $secret);

                $this->set("auth_secret", $secret);
                $this->set("qr_code", $qrcode);
            } catch (Exception $e) {
                $this->set("error", $e->getMessage());
            }
        }

        // ✅ Refresh user after potential update
        $user = Users::getUserById($user_id);
        $this->set("user", $user);
    }

    public function voters()
    {
        if ($this->request->isPost() && CSRF::post()) {
            $users = Votes::getUsers();
            $userArr = [];

            // groups users and counts votes, and uses most recent ip. Faster and less complex than a query.
            foreach ($users as $user) {
                if (in_array($user["username"], array_keys($userArr))) {
                    $in_arr = $userArr[$user["username"]];
                    if ($in_arr[3] < $user["started_on"]) {
                        $userArr[$user["username"]][2] = $user["ip_address"];
                        $userArr[$user["username"]][1] += $user["total"];
                    }
                } else {
                    $user["started_on"] = date(
                        "d.m.Y g:i A",
                        $user["started_on"]
                    );
                    $userArr[$user["username"]] = array_values($user);
                }
            }

            return ["data" => array_values($userArr)];
        }

        $this->set("csrf_token", CSRF::token());
        return true;
    }

    public function votes()
    {
        $votes_arr = [];

        if ($this->request->isPost() && CSRF::post()) {
            $votes = Votes::getVotes();

            foreach ($votes as $vote) {
                $voted_class = $vote["voted_on"] != -1 ? "success" : "danger";
                $claim_class = $vote["claimed"] == 1 ? "success" : "danger";

                $votes_arr[] = [
                    $vote["username"],
                    $vote["ip_address"],
                    $vote["site_id"],
                    date("d.m.Y g:i A", $vote["started_on"]),
                    '<span class="text-' .
                    $voted_class .
                    '">' .
                    ($vote["voted_on"] == -1 ? "No" : "Yes") .
                    "</span>",
                    '<span class="text-' .
                    $claim_class .
                    '">' .
                    ($vote["claimed"] != 1 ? "No" : "Yes") .
                    "</span>",
                ];
            }
            return ["data" => $votes_arr];
        }

        $this->set("csrf_token", CSRF::token());
    }

    public function beforeExecute()
    {
        parent::beforeExecute();

        $req_bearer = ["votes", "voters"];

        if (
            $this->request->isPost() &&
            in_array($this->getActionName(), $req_bearer)
        ) {
            $this->disableView(true);
        }
    }
}