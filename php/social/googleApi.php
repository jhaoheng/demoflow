<?php  

/**
* 
*/
class GOOGLE_API
{
    
    public $funcDetailRes;

    function __construct()
    {
        # code...
    }

    // 取得 token 相關資訊 用不到
    // url : https://www.googleapis.com/oauth2/v1/tokeninfo
    // query : ?access_token={token}&token_type=Bearer
    public function v1_verify($oauth_token){
        $url = "https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=$oauth_token&token_type=Bearer";
        $r = $this->curl_r_json($url);
        // var_dump($r);
        $this->funcDetailRes = $r;

        if (array_key_exists("error", $r)) {
            return false;
        }
        return true;
    }

    // 取得 userinfo
    // url : https://www.googleapis.com/oauth2/v2/userinfo
    // query : ?alt=json&access_token={token}
    public function v2_userinfo($oauth_token){
        $url = "https://www.googleapis.com/oauth2/v2/userinfo?alt=json&access_token=$oauth_token";
        $r = $this->curl_r_json($url);
        // var_dump($r);
        $this->funcDetailRes = $r;

        if (array_key_exists("error", $r)) {
            return false;
        }
        else{
            $email = $r['email'];
            $username = $r['name'];
            $picture = $r['picture']."?sz=320";
            $pic = base64_encode(file_get_contents($picture));
        }
        $userinfo['email'] = $email;
        $userinfo['username'] = $username;
        $userinfo['picture'] = $pic;
        return $userinfo;
    }

    public function v3_userinfo($oauth_token){
        $url = "https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=".$oauth_token;
        $r = $this->curl_r_json($url);
        
        if (array_key_exists("error_description", $r)) {
            return false;
        }
        else{
            $email = $r['email'];
            $username = $r['given_name'];
            $picture = $r['picture']."?sz=320";
            $pic = base64_encode(file_get_contents($picture));
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