<?php
// Test registration endpoint

$cookieJar = __DIR__ . '/storage/cookies.txt';
if (file_exists($cookieJar)) {
    unlink($cookieJar);
}

// 1) GET /register to grab CSRF token
$ch = curl_init('http://127.0.0.1:8000/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$body = curl_exec($ch);
curl_close($ch);
preg_match('/name="_token" value="([^"]+)"/', $body, $m);
$formToken = $m[1] ?? '';
echo "Form token: " . substr($formToken, 0, 10) . PHP_EOL;

// 2) POST /register with phone+name+password
$phone = '8881160407';  // Use a different number than the user's failed attempt
$ch = curl_init('http://127.0.0.1:8000/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    '_token' => $formToken,
    'name' => 'Harsh Pathak',
    'phone_number' => $phone,
    'role' => 'customer',
    'password' => 'password',
    'password_confirmation' => 'password',
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$redirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);
echo "Register: $code -> $redirect" . PHP_EOL;
echo "Body: " . substr($body, 0, 200) . PHP_EOL;

// 3) Follow redirect
$ch = curl_init('http://127.0.0.1:8000' . ($redirect ?: '/dashboard'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);
echo "Follow: $code -> $url" . PHP_EOL;
echo "Final body length: " . strlen($body) . PHP_EOL;

// 4) Verify the user was created
$user = \App\Models\User::where('phone_number', $phone)->first();
if ($user) {
    echo "User created: id={$user->id}, name={$user->name}, phone={$user->phone_number}, email=" . ($user->email ?? 'null') . PHP_EOL;
} else {
    echo "User NOT found!" . PHP_EOL;
}

// 5) Clean up - delete the user
if ($user) {
    $user->delete();
    echo "User deleted for cleanup" . PHP_EOL;
}
