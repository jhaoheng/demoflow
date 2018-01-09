<?php  

$agent = $_GET['agent'];
$ctk_b64 = $_GET['Orbweb-CTK'];
$ctk_api_token = base64_decode($ctk_b64);

$parameters = [
    "api_token" => $ctk_api_token
];
$phql = "SELECT * FROM customers WHERE api_token=:api_token";
$mysql = new Mysql_Manager;
$r = $mysql->fetchOne($phql, $parameters);

if ($r === false) {
    return;
}
else{
    $fb_client_id = $r['fb_client_id'];
    $google_client_id = $r['google_client_id'];
}

if ($agent == 'facebook') {
    $fb_app_id = $fb_client_id;
    $redirect_uri = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST']."/v1.2/social/fb_oauth";
    $scope="email";
    $ctk = $ctk_b64;
    facebook($fb_app_id, $redirect_uri, $scope, $ctk);
}
else if($agent == 'google'){

    $client_id = $google_client_id;
    $redirect_uri = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST']."/v1.2/social/google_oauth";
    $scope = "email+https://www.googleapis.com/auth/plus.me+profile";
    $ctk = $ctk_b64;
    google($client_id, $redirect_uri, $scope, $ctk);
}

function facebook($fb_app_id, $redirect_uri, $scope, $ctk){
    $url = "https://www.facebook.com/v2.10/dialog/oauth?client_id=$fb_app_id&response_type=token&redirect_uri=$redirect_uri&state=$ctk&scope=$scope";

    header('Location: '.$url);
}


function google($client_id, $redirect_uri, $scope, $ctk){
    $url = "https://accounts.google.com/o/oauth2/v2/auth?response_type=token&client_id=$client_id&redirect_uri=$redirect_uri&scope=$scope&state=$ctk";

    header('Location: '.$url);
}

?>