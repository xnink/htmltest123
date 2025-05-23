<?php
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cookie = $_POST["cookie"];
 
    // 获取用户信息
    $userUrl = "https://pan.quark.cn/account/info";
    $userInfo = json_decode(file_get_contents($userUrl, false, stream_context_create([
        'http' => [
            'header' => "Cookie: $cookie\r\n"
        ]
    ])), true)["data"];
 
    if (!$userInfo["nickname"]) {
        echo "登录失败，cookie错误。\n";
        exit();
    }
 
    echo "hello, {$userInfo['nickname']}! 登录成功。\n";
 
    // 查看当前签到状态
    $stateUrl = "https://drive-m.quark.cn/1/clouddrive/capacity/growth/info?pr=ucpro&fr=pc&uc_param_str=";
    $response = json_decode(file_get_contents($stateUrl, false, stream_context_create([
        'http' => [
            'header' => "Cookie: $cookie\r\n"
        ]
    ])), true);
    $sign = $response["data"]["cap_sign"];
 
    if ($sign["sign_daily"]) {
        $number = $sign["sign_daily_reward"] / (1024 * 1024);
        $progress = bcdiv($sign["sign_progress"], $sign["sign_target"], 4) * 100;
        echo "今日已签到获取{$number}MB，进度{$progress}%\n";
        exit();
    }
 
    // 执行签到
    $signUrl = "https://drive-m.quark.cn/1/clouddrive/capacity/growth/sign?pr=ucpro&fr=pc&uc_param_str=";
    $params = [
        "sign_cyclic" => true
    ];
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($params)
        ]
    ];
    $dataResponse = json_decode(file_get_contents($signUrl, false, stream_context_create($options)), true);
 
    if (isset($dataResponse["error_code"])) {
        echo "签到失败，请检查cookie是否正常或过期，错误代码: {$dataResponse['error_code']}\n";
        exit(); }
 
    $mb = $dataResponse["data"]["sign_daily_reward"] / 2048;
    echo json_encode($dataResponse) . "\n";
    echo "签到成功,获取到{$mb}MB!\n";
}
?>