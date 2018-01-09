<?php 

#1 
$oauth_token = empty( !$_GET['access_token'] ) ? $_GET['access_token'] : '';
$ctk_b64 = empty( !$_GET['ctk'] ) ? $_GET['ctk'] : '';

if ( empty($oauth_token) || empty($ctk_b64) ) {  
?>

    <script type="text/javascript">
    // 傳入的 ex : protocol://hostname[:port]/path/[parameters]?[query]#fragment
    var protocol = window.location.protocol;
    var hostname = window.location.hostname;
    var pathname = window.location.pathname; 
    baseUrl = protocol+'//'+hostname+pathname;

    fragment = window.location.hash.substr(1);
    if (fragment) {
        
        state = fragment.substr(fragment.indexOf('state='))
                      .split('&')[0]
                      .split('=')[1];
        access_token = fragment.substr(fragment.indexOf('access_token='))
                      .split('&')[0]
                      .split('=')[1];
        location.href = baseUrl+"?ctk=" + state + '&access_token=' + access_token;
    } 
    else 
        exit();
    </script>

<?php 
}
if (empty($oauth_token)) {
    return;
}

// #2 驗證 CTK;
$ctk_api_token = base64_decode($ctk_b64);
$parameters = [
    "api_token" => $ctk_api_token
];
$phql = "SELECT * FROM customers WHERE api_token=:api_token";
$mysql = new Mysql_Manager;
$r = $mysql->fetchOne($phql, $parameters);
if ($r === false) {
    return $app->response->setJsonContent([
        'error' => "Auth Error"
    ]);
}
else{
    $c_id = $r['id'];
    $company_name = $r['name'];
}

// #3 google api 取得 user info
$url = "https://www.googleapis.com/oauth2/v2/userinfo?alt=json&access_token=$oauth_token";
$result = curl_r_json($url);
if (array_key_exists("error", $result)) {
    return $app->response->setJsonContent([
        'error' => "OAuth token fail"
    ]);
}
else{
    $email = $result['email'];
    $username = $result['name'];
    $picture = $result['picture']."?sz=320";
    $pic = base64_encode(file_get_contents($picture));
}

// 找尋是否在 database 中有這個 email
$parameters = [
    "email" => $email,
    "customer_id" => $c_id
];

$phql = "SELECT * FROM users WHERE email = :email AND customer_id=:customer_id";
$mysql = new Mysql_Manager;
$result = $mysql->fetchOne($phql, $parameters);

// 狀況有 ... 用戶
// - 沒註冊過
//     - 寫入資料庫
// - 有註冊 但無啟用 / 帳號已刪除 無論是何種狀態...全部都進行更新就好...若 token 已有，則無須更新

if ($result === false) {
    // echo "用戶註冊";
    $session_token = user_register($c_id, $username, $pic, $email);
}
else{
    // echo "用戶登入";
    $session_token = user_login($result, $pic);
}

$app->response->setStatusCode(200);
$app->response->setHeader("Content-Type", "application/json");
$app->response->setJsonContent([
    'session_token' => $session_token
]);
$app->response->send();
return;

function curl_r_json($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result, true);
    // var_dump($result);
    return $result;
}

function user_login($user_data, $pic){

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
    $mysql = new Mysql_Manager;
    $mysql->execute($phql, $parameters);

    return createUsersSession($user_data['id']);

}

function user_register($c_id, $username, $pic, $email){

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
    $mysql = new Mysql_Manager;
    $r = $mysql->execute($phql, $parameters);
    $user_id = $r->getInsertId();

    return createUsersSession($user_id);
}


function createUsersSession($u_id){

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
    $phql = "INSERT INTO $KEYS VALUES $VALUES";
    $mysql = new Mysql_Manager;
    $mysql->execute($phql, $parameters);
    return $session_token
}

?>