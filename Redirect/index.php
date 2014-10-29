<?php  session_start();

//if($_SESSION['nick']=="您好" || $_SESSION['nick']=="")
//{
        //header("location:"."http://container.api.taobao.com/container?appkey=12638776");
//}
//error_reporting(0);
?>
<?php
$num_iid = $_REQUEST['num_iid'];
$shop_id = $_REQUEST['id'];
$top_appkey = $_GET['top_appkey'];
$top_parameters = $_GET['top_parameters'];
$top_session = $_GET['top_session'];
$top_sign = $_GET['top_sign'];
$groupup = $_REQUEST['groupup'];
$addFav= $_REQUEST['addFav'];
$Uurl= $_REQUEST['Uurl'];
$Uurl = htmlspecialchars($Uurl);
$ucenter = $_REQUEST['ucenter'];
$num_iid = htmlspecialchars($num_iid);
$shop_id = htmlspecialchars($shop_id);
$top_appkey = htmlspecialchars($top_appkey);
$top_parameters = htmlspecialchars($top_parameters);
$top_session = htmlspecialchars($top_session);
$top_sign = htmlspecialchars($top_sign);
$cs_type = $_GET['cs_type'];
$code = $_REQUEST['code'];
$error = $_REQUEST['error'];
$error_description = $_REQUEST['error_description'];
$state = $_REQUEST['state'];
$host = '121.196.142.10';
if (strpos($state, '51zwd') !== false) {
    $host = 'yjsc2.51zwd.com';
} else if (strpos($state, 'login') !== false) {
    $host = 'ecmall.51zwd.com';
}

if($error){
        if($error_description){
                if(strpos($error_description, 'purchase') !== false)
                {
                         header("Content-type:text/html;charset=gbk");
                         $content  = '<script>';
                         $content .= 'alert("请按确认键后进行产品的订购，然后再进行相关操作！");';
                         $content .= 'window.location = "http://fuwu.taobao.com/ser/detail.htm?spm=0.0.0.0.EvCI8O&service_code=FW_GOODS-1856100";';
                         $content .= '</script>';
                 exit($content);
                }
                if (strpos($error_description, 'larger') !== false) {
                         header("Content-type:text/html;charset=gbk");
                         $content  = '<script>';
                         $content .= 'alert("同时在线人数过多，请重试一次！");';
                         $content .= 'window.location = "http://'.$host.'/taobao-upload-multi-store/index.php?g=Taobao&m=Index&a=free";';
                         $content .= '</script>';
                         exit($content);
                }
                exit($error_description);
        }
}

$sess = htmlspecialchars($_GET['sess']);
/*
 require_once('./php-log/'. "BaeLog.class.php");
 $secret = array("user"=>"6DeUk68G8Qqgi5Y1n1U8Qr01","passwd"=>"jtI1PCeckg7UzqfNgM9Wf7bHdsQbGtYu" );
 $log = BaeLog::getInstance($secret);
 $log->setLogLevel(16);
 $log->Debug("Code is ".$code);
 */

if ($state == 'android') {
    $url = 'https://oauth.taobao.com/token';
    $params = array(
        'client_id' => '21357243',
        'client_secret' => '244fd3a0b1f554046a282dd9b673b386',
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => 'http://'.$host.'/index.php');
    foreach($params as $key=>$value) { $params_string .= $key.'='.$value.'&'; }
    rtrim($params_string, '&');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($params));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    curl_close($ch);
    $dataObject = json_decode($data);
    header("location: com.zwd51://#access_token=".$dataObject->access_token);
    exit;
}

//只能带一个参数state，需要自己处理就好了
if (strpos($state,"wap")!== false) {

           //获得client_id
     $wapParamArr = explode("_",$state);
     //$wapParamCnt = count($wapParamArr);
     $client_id = $wapParamArr[1];
     $num_iid = $wapParamArr[2];


          //获取$appsecret
         include ('conn.php');
         $sql = "SELECT  * from ecm_taoapi_self  where appkey=".$client_id;
                 $result = mysql_query($sql);
                 if ($myrow = mysql_fetch_array($result))
                 {
                     $appsecret=$myrow["appscret"];
                 }

                // echo "client_id:".$client_id."<br>";
                //  echo "appsecret:".$appsecret."<br>";
                // exit;

                 mysql_close($link);

    $url = 'https://oauth.taobao.com/token';
    $params = array(
        'client_id' => $client_id ,
        'client_secret' => $appsecret,
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => 'http://'.$host.'/index.php');
    foreach($params as $key=>$value) { $params_string .= $key.'='.$value.'&'; }
    rtrim($params_string, '&');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($params));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    curl_close($ch);
    $dataObject = json_decode($data);
    header("location: http://3g.51zwd.com/goodsup.php?num_iid=".$num_iid."&access_token=".$dataObject->access_token);
    exit;
}


if($code=="" && $Uurl=="" && $num_iid=="" && $cs_type=="")
{
         $errinfo = "errordes:".$error_description."\n request:".$_SERVER["QUERY_STRING"]."\n"."post:".$GLOBALS['HTTP_RAW_POST_DATA']."\n" ;
         writeLogToFile($errinfo);
         echo "抱歉，系统正在维护中，请从搜款页面上传！";
         header("location:"."http://www.51zwd.com/index.php?app=category2&api=1");
         exit;

}

if ($code && strpos($state, 'login') !== false) {
    header("location:"."http://".$host."/index.php?app=member&act=taobaoAuthBack&code=".$code);
    exit;
}

if($code){
         header("location:"."http://".$host."/taobao-upload-multi-store/index.php?g=Taobao&m=Index&a=authBack&code=".$code."&state=".$state);
        // $log->Debug("Location is "."http://yjsc.51zwd.com/taobao-upload/index.php?g=Taobao&m=Index&a=authBack&code=".$code);
   exit;
}

if($cs_type){
        if($cs_type =='cs' ){
                 header("location:"."http://112.124.54.224:8080/duapp/CsInfoOfferServlet"."?top_appkey=".$top_appkey."&top_session=".$top_session);
                exit;
        }else if($cs_type == 'bs')
        {
                 header("location:"."http://112.124.54.224:8080/duapp/getInfoServlet"."?top_appkey=".$top_appkey."&top_session=".$top_session);
                exit;
        }
}



//$secret = '7761c59001f88ff0c285a279252b2608'; // 别忘了改成你自己的

if($sess)
{
        echo "sessionkey:".$top_session."<br>";
        exit;
}
if($shop_id)
{
         $_SESSION['appkey']='12638776';
         $secret ='214b0c61488e24f24a73558b0333d143';
         $_SESSION['appscret']=$secret;
}
else
{

}
 //echo "secret".$secret."<br>";
 //exit;

//echo "appkey:".$top_appkey."<br>";
//echo "top_parameters:".$top_parameters."<br>";
//echo "top_session:".$top_session."<br>";
//echo "secret:".$secret."<br>";
$md5 = md5( $top_appkey . $top_parameters . $top_session . $secret, true );
$sign = base64_encode( $md5 );
//echo "top sign    :".$top_sign;
//echo "sign        :".$sign;
if ( $sign != $top_sign ) {
//echo "signature invalid.";
//exit();
}

$parameters = array();
parse_str( base64_decode( $top_parameters ), $parameters );




$now = time();
$ts = $parameters['ts'] / 1000;
if ( $ts > ( $now + 60 * 10 ) || $now > ( $ts + 60 * 30 ) ) {
        header("location:"."http://www.51zwd.com");
//echo "request out of date.";
exit();
}

//echo "welcome {$parameters['visitor_nick']}.";
        $_SESSION['topsession'] = $_REQUEST['top_session'];
        $_SESSION['nick'] = $parameters['visitor_nick'];

        //exit;

if($shop_id)
{
        $url = "upshop".$shop_id.".html";
        //echo "urlddd:".$url;
        header("location:".$url);
        exit;
}
else if($num_iid)
{

  $nick = urlencode($_SESSION['nick']);

        $url = "http://yjsc.51zwd.com/editgoods8.php?num_iid=".$num_iid."&sesskey=".$_SESSION['topsession']."&appkey=".$top_appkey."&nick=".$nick;

//  echo $url;
 // exit;
        //echo $url;
        header("location:".$url);
}
else if($addFav)
{


  $nick = urlencode($_SESSION['nick']);
  $Uurl= urldecode($Uurl);
  $Uurl=$Uurl."&nick=".$nick;
  //echo "Uurl2 :". $Uurl;
  //exit;
  if($Uurl=="")
  {

  }
  //exit;

        header("location:".$Uurl);
}
else if($groupup)
{
        $sessionid = session_id();
  $nick = urlencode($_SESSION['nick']);
        $url = "http://yjsc.51wangpi.com/themes/mall/default/progroup.php?sesskey=".$_SESSION['topsession']."&sess=".$sessionid."&nick=".$nick."&appkey=".$top_appkey;
        echo $url;
        echo "修复中！";
        //echo "sess".$sess."<br>";
        //echo "nick".$_SESSION['nick']."<br>";
        header("location:".$url);
        exit;
}
else if($ucenter)
{
        $sessionid = session_id();
        $nick = urlencode($_SESSION['nick']);
  //$url = "http://yjsc.51wangpi.com/themes/mall/default/ucenter.php?sess=".$sessionid."&nick=".$nick."&appkey=".$top_appkey;
  $url = "http://yjsc.51zwd.com/ucenter.php?sess=".$sessionid."&nick=".$nick."&appkey=".$top_appkey;
        echo $url;
        echo "修复中！";
        //echo "sess".$sess."<br>";
        //echo "nick".$_SESSION['nick']."<br>";
        header("location:".$url);
        exit;
}

else
{
        header("location:"."http://www.51zwd.com");
}
        //$ref = $_REQUEST['ref'];
        //echo "ref".$ref;
       // header("location:".$ref);

//echo "sessionkey:".$top_session;


function writeLogToFile($errinfo)
{
                //写入文件

                // w表示以写入的方式打开文件，如果文件不存在，系统会自动建立
                $file_pointer = fopen("oauth_err.txt","a+");
                fwrite($file_pointer,$errinfo);
                fclose($file_pointer);

}






 ?>
