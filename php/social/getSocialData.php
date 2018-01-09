<?php  

include_once "oauth_general_api.php";

$oauth_general_api = new OAUTH_GENERAL_API;

$ctk_b64 = $_GET['Orbweb-CTK'];
$r = $oauth_general_api->verify_ctk($ctk_b64);

if ($r === false) {
    return $app->response->setJsonContent([
        "error" => "auth fail"
    ]);
}

return $app->response->setJsonContent([
    'name' => $r["name"],
    'facebook' => [
        'fb_app_ver' => $r["fb_app_ver"],
        'fb_client_id' => $r["fb_client_id"],
        'fb_client_secret' => $r["fb_client_secret"],
    ],
    'google' => [
        'google_client_id' => $r["google_client_id"]
    ]
]);

?>