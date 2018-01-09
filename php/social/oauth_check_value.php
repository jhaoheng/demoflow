<?php  

/*
此頁面用於 debug 當 client 端(web/mobile) 透過
- facebook
    - https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow
- google
    - 
*/

/*
使用方法 : 建立一個 client 端，在 redirect_uri 中，設定此 api，在 browser 會顯示出
 */

use Phalcon\Http\Request;
$request = new Request();

echo "==Method==<br>";
var_dump($request->getMethod());
echo "<br><br>";

echo "==Headers==<br>";
var_dump($request->getHeaders());
echo "<br><br>";

echo "==Raw / Body==<br>";
var_dump($request->getRawBody());
echo "<br><br>";

echo "==ContentType==<br>";
var_dump($request->getContentType());
echo "<br><br>";

echo "==Scheme==<br>";
var_dump($request->getScheme());
echo "<br><br>";

echo "==URI==<br>";
var_dump($request->getURI());
echo "<br><br>";

echo "==Fragment / Hashtag==<br>";
$fragment = "<script>document.write(window.location.hash);</script>";
echo $fragment;
echo "<br><br>";

?>