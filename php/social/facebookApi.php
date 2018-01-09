<?php  

/**
* 
*/
class FACEBOOK_API
{

    public $funcDetailRes;
    
    function __construct()
    {
        # code...
    }

    public function getFacebookAppAdminToken($fb_client_id, $fb_client_secret){
        $client_id = $fb_client_id;
        $client_secret = $fb_client_secret;
        $url = "https://graph.facebook.com/oauth/access_token?client_id=$client_id&client_secret=$client_secret&grant_type=client_credentials";
        $r = $this->curl_r_json($url);
        if (array_key_exists('error', $r)) {
            return false;
        }
        return $r;
    }

    public function verify($oauth_token, $fb_client_id, $fb_client_secret){

        $r = $this->getFacebookAppAdminToken($fb_client_id, $fb_client_secret);
        if ($r === false) {
            return false;
        }
        else{
            $app_token = $r['access_token'];
        }


        $url = "https://graph.facebook.com/debug_token?input_token=$oauth_token&access_token=$app_token";
        $r = $this->curl_r_json($url);
        if (array_key_exists('error', $r['data'])) {
            return false;
        }
        else{
            $fb_user_id = $r['data']['user_id'];
        }
        return $r;
    }

    public function userinfo($oauth_token, $fb_app_ver){
        $fields = "email,name,id,picture.width(320).height(320)";
        $url = "https://graph.facebook.com/$fb_app_ver/me?access_token=$oauth_token&fields=$fields";
        $r = $this->curl_r_json($url);

        if (array_key_exists('error', $r)) {
            return false;
        }
        else{
            $email = $r['email'];
            $user_fbid = $r['id'];
            $pic_url = $r['picture']['data']['url'];
            $pic = base64_encode(file_get_contents($pic_url));
            $username = $r['name'];
        }

        $userinfo['email'] = $email;
        $userinfo['username'] = $username;
        $userinfo['picture'] = $pic;
        return $userinfo;
    }

    public function curl_r_json($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        // var_dump($result);
        return $result;
    }
}

?>