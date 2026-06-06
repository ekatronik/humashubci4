<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.ipify.org");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
$ip = curl_exec($ch);

if (curl_errno($ch)) {
    echo "cURL Error: " . htmlspecialchars(curl_error($ch));
} else {
    echo "<h3>OUTGOING SERVER IP: " . htmlspecialchars($ip) . "</h3>";
    echo "<p>Copy this IP address and whitelist it in your Cloudflare WAF rule instead of the cPanel shared IP.</p>";
}
curl_close($ch);
?>
