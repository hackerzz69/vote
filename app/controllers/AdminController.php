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
        $username = strtolower($user["username"]);

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

        // Group users case-insensitively, preserve original casing for display
        foreach ($users as $user) {
            $key = strtolower($user["username"]); // normalized key
            $displayName = $user["username"];     // original casing

            if (isset($userArr[$key])) {
                // Add to vote count
                $userArr[$key][1] += $user["total"];

                // Update IP and timestamp if newer
                if (strtotime($userArr[$key][3]) < $user["started_on"]) {
                    $userArr[$key][2] = $user["ip_address"];
                    $userArr[$key][3] = date("m/d/Y g:i A", $user["started_on"]);
                    $userArr[$key][0] = $displayName; // update display name to latest casing
                }
            } else {
                // Initialize row: [Name, Votes, IP, Last Vote]
                $userArr[$key] = [
                    $displayName,
                    $user["total"],
                    $user["ip_address"],
                    date("m/d/Y g:i A", $user["started_on"])
                ];
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