<?php
class ApiController extends Controller {

    public function index() {
        $this->set("base_url", $this->router->getUrl().'api');
        $this->set("root", web_root.'api');
    }

    public function users($username = null) {
        if (!$this->request->isQuery()) {
            return ['error' => 'Invalid request Type.'];
        }
        
        $username = $this->filter($username);
        $limit    = $this->request->hasPost("limit") ? 
                $this->request->getPost("limit", "int") : 100;
        
        if ($username == null) {
            $users   = Votes::getUsers();
            $userArr = [];

            foreach ($users as $user) {
                if (count($userArr) == $limit)
                    break;

                $username = $user['username'];
    
                if (in_array($username, array_keys($userArr))) {
                    if ($userArr[$username]['last_vote'] < $user['started_on']) {
                        $userArr[$username]['last_vote']   = $user['started_on'];
                        $userArr[$username]['ip_address']  = $user['ip_address'];
                        $userArr[$username]['total_votes'] +=1;
    
                        if ($user['voted_on'] == -1) {
                            $userArr[$username]['pending']++;
                        } else {
                            $userArr[$username]['completed']++;
                        }
                    }
                } else {
                    $userArr[$username] = [
                        'username'    => $user['username'],
                        'ip_address'  => $user['ip_address'],
                        'last_vote'   => $user['started_on'],
                        'total_votes' => 1,
                        'pending'     => $user['voted_on'] == -1 ? 1 : 0,
                        'completed'   => $user['voted_on'] == -1 ? 0 : 1
                    ];
                }
            }

            $values = array_values($userArr);

            if (empty($values)) {
                return ['error' => 'No results'];
            }

            return $values;
        }

        $user = Votes::getUser($username);

        if (!$user) {
            return ['error' => 'No data found for '.$username];
        }

        return $user;
    }

    public function votes($username) {
        $votes = Votes::getPendingVotes($username);
        Votes::claimVotes($username);
        return $votes;
    }

    public function beforeExecute() {
        parent::beforeExecute();

        if ($this->getActionName() != "index") {
            $this->disableView(true);
        }
    }


}