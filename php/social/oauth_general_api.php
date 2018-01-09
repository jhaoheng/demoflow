<?php  

include_once TOOL_PATH."/database/mysql_manager.php";

/**
* 
*/
class OAUTH_GENERAL_API
{
    
    public $mysql;

    function __construct()
    {
        $this->mysql = new Mysql_Manager;
    }

    public function verify_ctk($ctk_b64){

        $mysql = $this->mysql;

        $ctk_api_token = base64_decode($ctk_b64);
        $parameters = [
            "api_token" => $ctk_api_token
        ];
        $phql = "SELECT * FROM customers WHERE api_token=:api_token";
        $r = $mysql->fetchOne($phql, $parameters);
        if ($r === false) {
            return false;
        }
        else{
            $data['id'] = $r['id'];
            $data['name'] = $r['name'];

            $data['fb_app_ver'] = $r['fb_app_ver'];
            $data['fb_client_id'] = $r['fb_client_id'];
            $data['fb_client_secret'] = $r['fb_client_secret'];
            $data['google_client_id'] = $r['google_client_id'];
        }
        return $data;
    }

    public function check_user_exist($company_id, $email){
        $mysql = $this->mysql;
        $parameters = [
            "email" => $email,
            "customer_id" => $company_id
        ];

        $phql = "SELECT * FROM users WHERE email = :email AND customer_id=:customer_id";
        $result = $mysql->fetchOne($phql, $parameters);

        if ($result === false) {
            return false;
        }
        else{
            return $result;
        }
    }

    public function user_login($user_data, $pic){

        $mysql = $this->mysql;

        // 圖片
        if ($user_data['pic']==null && !empty($pic)) {
            $user_data['pic'] = $pic;
        }

        // others
        $user_data['activation_code'] = null;
        $user_data['is_verified'] = 1;
        $user_data['deleted_at'] = null;
        $user_data['updated_at'] = (new DTime)->getNowDate();

        // update
        $parameters = [
            "id" => $user_data['id'],
            "pic" => $user_data['pic'],
            "activation_code" => $user_data['activation_code'],
            "is_verified" => $user_data['is_verified'],
            "deleted_at" => $user_data['deleted_at'],
            "updated_at" => $user_data['updated_at']
        ];
        $SET_VALUES = "pic=:pic, activation_code=:activation_code, is_verified=:is_verified, deleted_at=:deleted_at, updated_at=:updated_at";
        $phql = "UPDATE users SET $SET_VALUES WHERE id=:id";
        $mysql->execute($phql, $parameters);

        return $this->createUsersSession($user_data['id']);

    }

    public function user_register($c_id, $username, $pic, $email){

        $mysql = $this->mysql;

        $dTime = new DTime;
        $date = $dTime->getNowDate();

        $parameters = [
            "customer_id" => $c_id,
            "username" => $username,
            "pic" => $pic,
            "email" => $email,
            "is_verified" => 1,
            "created_at" => $date,
            "updated_at" => $date
        ];

        $KEYS = "(customer_id, username, pic, email, is_verified, created_at, updated_at)";
        $VALUES = "(:customer_id, :username, :pic, :email, :is_verified, :created_at, :updated_at)";
        $phql = "INSERT INTO users $KEYS VALUES $VALUES";

        $r = $mysql->execute($phql, $parameters);
        $user_id = $r->getInsertId();

        return $this->createUsersSession($user_id);
    }

    public function createUsersSession($u_id){
        $mysql = $this->mysql;
        // session_token
        $s_token = new SESSION_TOKEN;
        $session_token = $s_token->create();

        // 建立 token
        $parameters = [
            "u_id" => $u_id,
            "session_token" => $session_token,
            "created_from" => "facebook",
            "created_at" => date('Y-m-d H:i:s')
        ];
        $KEYS = "(`u_id`, `session_token`, `created_from`, `created_at`)";
        $VALUES = "(:u_id, :session_token, :created_from, :created_at)";
        $phql = "INSERT INTO $KEYS VALUES $VALUES";
        $mysql->execute($phql, $parameters);
        return $session_token;
    }
}

?>