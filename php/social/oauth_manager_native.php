<?php  

include_once "googleApi.php";
include_once "facebookApi.php";
include_once "oauth_general_api.php";

$agent = $_GET['agent'];
$oauth_token = $_GET['oauth_token'];
$ctk_b64 = $_GET['Orbweb-CTK'];


// 檢查 ctk
$general_api = new OAUTH_GENERAL_API;
$r = $general_api->verify_ctk($ctk_b64);
if ( $r===false ) {
    return $app->response->setJsonContent([
        'error' => "Auth Error"
    ]);
}
else{
    $company_id = $r['id'];
    $company_name = $r['name'];

    $fb_app_ver = $r['fb_app_ver'];
    $fb_client_id = $r['fb_client_id'];
    $fb_client_secret = $r['fb_client_secret'];
    $google_client_id = $r['google_client_id'];
}

// 檢查授權, 取得資料
if ($agent == 'google') {

    $google_api = new GOOGLE_API;

    // 驗證 token
    // if ( !$google_api->verify($oauth_token) ) {
    //     return $app->response->setJsonContent([
    //         'error' => "OAuth token fail"
    //     ]);
    // }

    // 取得用戶資訊
    $r = $google_api->v3_userinfo($oauth_token);
    if ($r === false) {
        return $app->response->setJsonContent([
            'error' => "OAuth token fail"
        ]);
    }
    else{
        $email = $r['email'];
        $username = $r['username'];
        $pic = $r['picture'];
    }

}
elseif ($agent == 'facebook') {

    $facebook_api = new FACEBOOK_API;

    // 驗證 token
    if ( !$facebook_api->verify($oauth_token, $fb_client_id, $fb_client_secret) ) {
        return $app->response->setJsonContent([
            'error' => "OAuth token fail"
        ]);
    }
    
    // 取得用戶資訊
    $r = $facebook_api->userinfo($oauth_token, $fb_app_ver);
    if ($r === false) {
        return $app->response->setJsonContent([
            'error' => "Get userinfo fail"
        ]);
    }
    else{
        $email = $r['email'];
        $username = $r['username'];
        $pic = $r['picture'];
    }
}
else{
    return $app->response->setJsonContent([
        'error' => "Agent fail"
    ]);
}

$user_data = $general_api->check_user_exist($company_id, $email);
if ( $user_data === false ){
    // 註冊
    $status = "created";
    $session_token = $general_api->user_register($company_id, $username, $pic, $email);
}
else{
    // 登入
    $status = "updated";
    $session_token = $general_api->user_login($user_data, $pic);
}

return $app->response->setJsonContent([
    'status' => $status,
    'session_token' => $session_token
]);

?>