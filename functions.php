<?php
require_once 'vendor/autoload.php';
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
function ActiveVoucher($ev_number, $ev_code){
    global $connect;
    $Payer_Account = select("PaySetting", "ValuePay", "NamePay", 'perfectmoney_Payer_Account',"select")['ValuePay'];
    $AccountID = select("PaySetting", "ValuePay", "NamePay", 'perfectmoney_AccountID',"select")['ValuePay'];
    $PassPhrase = select("PaySetting", "ValuePay", "NamePay", 'perfectmoney_PassPhrase',"select")['ValuePay'];
    
    if (empty($ev_number) || empty($ev_code) || empty($AccountID) || empty($PassPhrase)) {
        error_log("ActiveVoucher: Missing required parameters");
        return false;
    }
    
    $opts = array(
        'socket' => array(
            'bindto' => 'ip',
        )
    );

    $context = stream_context_create($opts);
    
    try {
        $voucher = file_get_contents("https://perfectmoney.com/acct/ev_activate.asp?AccountID=" . urlencode($AccountID) . "&PassPhrase=" . urlencode($PassPhrase) . "&Payee_Account=" . urlencode($Payer_Account) . "&ev_number=" . urlencode($ev_number) . "&ev_code=" . urlencode($ev_code));
        return $voucher;
    } catch (Exception $e) {
        error_log("ActiveVoucher error: " . $e->getMessage());
        return false;
    }
}
function update($table, $field, $newValue, $whereField = null, $whereValue = null) {
    global $pdo;
    
    $allowedTables = ['user', 'invoice', 'setting', 'admin', 'marzban_panel', 'product', 'Payment_report', 'DiscountSell', 'textbot'];
    if (!in_array($table, $allowedTables)) {
        error_log("Invalid table name: $table");
        return false;
    }

    try {
        if ($whereField !== null) {
            $stmt = $pdo->prepare("UPDATE `$table` SET `$field` = ? WHERE `$whereField` = ?");
            $result = $stmt->execute([$newValue, $whereValue]);
        } else {
            $stmt = $pdo->prepare("UPDATE `$table` SET `$field` = ?");
            $result = $stmt->execute([$newValue]);
        }
        return $result;
    } catch (PDOException $e) {
        error_log("Update query failed: " . $e->getMessage());
        return false;
    }
}
function step($step, $from_id){
    global $pdo;
    
    if (empty($step) || !is_numeric($from_id)) {
        error_log("Invalid step parameters");
        return false;
    }
    
    try {
        $stmt = $pdo->prepare('UPDATE user SET step = ? WHERE id = ?');
        return $stmt->execute([$step, $from_id]);
    } catch (PDOException $e) {
        error_log("Step update failed: " . $e->getMessage());
        return false;
    }
}
function select($table, $field, $whereField = null, $whereValue = null, $type = "select") {
    global $pdo;
    
    $allowedTables = ['user', 'invoice', 'setting', 'admin', 'marzban_panel', 'product', 'Payment_report', 'DiscountSell', 'textbot', 'channels', 'help', 'Discount', 'affiliates', 'PaySetting', 'Giftcodeconsumed'];
    if (!in_array($table, $allowedTables)) {
        error_log("Invalid table name: $table");
        return false;
    }

    $query = "SELECT $field FROM `$table`";

    if ($whereField !== null) {
        $query .= " WHERE `$whereField` = :whereValue";
    }

    try {
        $stmt = $pdo->prepare($query);

        if ($whereField !== null) {
            $stmt->bindParam(':whereValue', $whereValue, PDO::PARAM_STR);
        }

        $stmt->execute();

        switch ($type) {
            case "count":
                return $stmt->rowCount();
            case "FETCH_COLUMN":
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            case "fetchAll":
                return $stmt->fetchAll();
            default:
                return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log("Select query failed: " . $e->getMessage());
        return false;
    }
}

function generateUUID() {
    try {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        return $uuid;
    } catch (Exception $e) {
        error_log("UUID generation failed: " . $e->getMessage());
        return false;
    }
}
function tronratee(){
    try {
        $tronrate = [];
        $requeststron = @file_get_contents('https://api.nobitex.ir/v2/orderbook/TRXIRT');
        $requestsusd = @file_get_contents('https://api.nobitex.ir/v2/orderbook/USDTIRT');
        
        if ($requeststron === false || $requestsusd === false) {
            error_log("Failed to fetch exchange rates");
            return false;
        }
        
        $requeststron = json_decode($requeststron, true);
        $requestsusd = json_decode($requestsusd, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error in tronratee");
            return false;
        }
        
        $tronrate['result']['USD'] = $requestsusd['lastTradePrice'] * 0.1;
        $tronrate['result']['TRX'] = $requeststron['lastTradePrice'] * 0.1;
        return $tronrate;
    } catch (Exception $e) {
        error_log("tronratee error: " . $e->getMessage());
        return false;
    }
}
function nowPayments($payment, $price_amount, $order_id, $order_description){
    $apinowpayments = select("PaySetting", "ValuePay", "NamePay", 'apinowpayment',"select");
    
    if (!$apinowpayments || empty($apinowpayments['ValuePay'])) {
        error_log("NowPayments API key not configured");
        return false;
    }
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.nowpayments.io/v1/' . $payment,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_ENCODING => '',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => array(
            'x-api-key:' . $apinowpayments['ValuePay'],
            'Content-Type: application/json'
        ),
    ));
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
        'price_amount' => $price_amount,
        'price_currency' => 'usd',
        'pay_currency' => 'trx',
        'order_id' => $order_id,
        'order_description' => $order_description,
    ]));

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($response === false || $httpCode !== 200) {
        error_log("NowPayments API request failed");
        return false;
    }
    
    return json_decode($response);
}
function StatusPayment($paymentid){
    $apinowpayments = select("PaySetting", "ValuePay", "NamePay", 'apinowpayment',"select");
    
    if (!$apinowpayments || empty($apinowpayments['ValuePay'])) {
        error_log("NowPayments API key not configured");
        return false;
    }
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.nowpayments.io/v1/payment/' . $paymentid,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTPHEADER => array(
            'x-api-key:' . $apinowpayments['ValuePay']
        ),
    ));
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($response === false || $httpCode !== 200) {
        error_log("StatusPayment API request failed");
        return false;
    }
    
    return json_decode($response, true);
}
function formatBytes($bytes, $precision = 2): string
{
    global $textbotlang;
    
    if ($bytes <= 0) return '0 B';
    
    $base = log($bytes, 1024);
    $power = $bytes > 0 ? floor($base) : 0;
    $suffixes = [$textbotlang['users']['format']['byte'] ?? 'B',
                 $textbotlang['users']['format']['kilobyte'] ?? 'KB',
                 $textbotlang['users']['format']['MBbyte'] ?? 'MB', 
                 $textbotlang['users']['format']['GBbyte'] ?? 'GB',
                 $textbotlang['users']['format']['TBbyte'] ?? 'TB'];
    return round(pow(1024, $base - $power), $precision) . ' ' . $suffixes[$power];
}
#---------------------[ ]--------------------------#
function generateUsername($from_id, $Metode, $username, $randomString, $text)
{
    global $pdo, $textbotlang;
    $setting = select("setting", "*");
    
    if (!is_numeric($from_id)) {
        error_log("Invalid from_id in generateUsername");
        return false;
    }
    
    if($Metode == $textbotlang['users']['customidAndRandom']){
        return $from_id."_".$randomString;
    }
    elseif($Metode == $textbotlang['users']['customusernameandorder']){
        return $username."_".$randomString;
    }
    elseif($Metode == $textbotlang['users']['customusernameorder']){
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoice WHERE id_user = ?");
            $stmt->execute([$from_id]);
            $countInvoice = $stmt->fetchColumn() + 1;
            return $username."_".$countInvoice;
        } catch (PDOException $e) {
            error_log("generateUsername query failed: " . $e->getMessage());
            return $username."_".rand(1, 999);
        }
    }
    elseif($Metode == $textbotlang['users']['customusername']) {
        return sanitizeUserName($text);
    }
    elseif($Metode == $textbotlang['users']['customtextandrandom']) {
        return $setting['namecustome']."_".$randomString;
    }
    
    return false;
}

function outputlunk($text){
    if (empty($text) || !filter_var($text, FILTER_VALIDATE_URL)) {
        error_log("Invalid URL in outputlunk: $text");
        return "";
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $text);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    
    $response = curl_exec($ch);
    
    if($response === false) {
        $error = curl_error($ch);
        error_log("cURL error in outputlunk: $error");
        curl_close($ch);
        return "";
    }
    
    curl_close($ch);
    return $response;
}
function DirectPayment($order_id){
    global $pdo,$ManagePanel,$textbotlang,$keyboard,$from_id,$message_id,$callback_query_id;
    
    // Validate order_id
    if (empty($order_id) || !is_numeric($order_id)) {
        error_log("Invalid order_id in DirectPayment: $order_id");
        return false;
    }
    
    try {
        $setting = select("setting", "*");
        $admin_ids = select("admin", "id_admin",null,null,"FETCH_COLUMN");
        $Payment_report = select("Payment_report", "*", "id_order", $order_id,"select");
        
        if (!$Payment_report) {
            error_log("Payment report not found for order: $order_id");
            return false;
        }
        
        $format_price_cart = number_format($Payment_report['price']);
        $Balance_id = select("user", "*", "id", $Payment_report['id_user'],"select");
        $steppay = explode("|", $Payment_report['invoice']);
        
        if ($steppay[0] == "getconfigafterpay") {
            $stmt = $pdo->prepare("SELECT * FROM invoice WHERE username = ? AND Status = 'unpaid' LIMIT 1");
            $stmt->execute([$steppay[1]]);
            $get_invoice = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$get_invoice) {
                error_log("Invoice not found for username: " . $steppay[1]);
                return false;
            }
            
            $stmt = $pdo->prepare("SELECT * FROM product WHERE name_product = ? AND (Location = ? OR Location = '/all')");
            $stmt->execute([$get_invoice['name_product'], $get_invoice['Service_location']]);
            $info_product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $username_ac = $get_invoice['username'];
            $randomString = bin2hex(random_bytes(2));
            $marzban_list_get = select("marzban_panel", "*", "name_panel", $get_invoice['Service_location'],"select");
            
            $date = strtotime("+" . $get_invoice['Service_time'] . "days");
            if(intval($get_invoice['Service_time']) == 0){
                $timestamp = 0;
            }else{
                $timestamp = strtotime(date("Y-m-d H:i:s", $date));
            }
            
            $datac = array(
                'expire' => $timestamp,
                'data_limit' => $get_invoice['Volume'] * pow(1024, 3),
            );
            
            $dataoutput = $ManagePanel->createUser($marzban_list_get['name_panel'],$username_ac,$datac);

            if ($dataoutput['username'] == null) {
                $dataoutput['msg'] = json_encode($dataoutput['msg']);
                sendmessage($Balance_id['id'], $textbotlang['users']['sell']['ErrorConfig'], $keyboard, 'HTML');
                $texterros = sprintf($textbotlang['users']['buy']['errorInCreate'],$dataoutput['msg'],$Balance_id['id'],$Balance_id['username']);
                foreach ($admin_ids as $admin) {
                    sendmessage($admin, $texterros, null, 'HTML');
                    step('home', $admin);
                }
                return false;
            }
            
            $output_config_link = "";
            $config = "";
            $Shoppinginfo = [
                'inline_keyboard' => [
                    [
                        ['text' => $textbotlang['users']['help']['btninlinebuy'], 'callback_data' => "helpbtn"],
                    ]
                ]
            ];
            if ($marzban_list_get['sublink'] == "onsublink") {
                $output_config_link = $dataoutput['subscription_url'];
            }
            if ($marzban_list_get['configManual'] == "onconfig") {
                if(isset($dataoutput['configs']) and count($dataoutput['configs']) !=0){
                    foreach ($dataoutput['configs'] as $configs) {
                        $config .= "\n" . $configs;
                        $configqr .= $configs;
                    }
                }else{
                    $config .= "";
                    $configqr .= "";
                }
            }
            $Shoppinginfo = json_encode($Shoppinginfo);
            if($marzban_list_get['type'] == "wgdashboard"){
                $textcreatuser = sprintf($textbotlang['users']['buy']['createservicewgbuy'],$dataoutput['username'],$get_invoice['name_product'],$marzban_list_get['name_panel'],$get_invoice['Service_time'],$get_invoice['Volume']);
            }else{
                $textcreatuser = sprintf($textbotlang['users']['buy']['createservice'],$dataoutput['username'],$get_invoice['name_product'],$marzban_list_get['name_panel'],$get_invoice['Service_time'],$get_invoice['Volume'],$config,$output_config_link);
            }
            if ($marzban_list_get['configManual'] == "onconfig") {
                if (count($dataoutput['configs']) == 1) {
                    $urlimage = "{$get_invoice['id_user']}$randomString.png";
                    $writer = new PngWriter();
                    $qrCode = QrCode::create($configqr)
                        ->setEncoding(new Encoding('UTF-8'))
                        ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
                        ->setSize(400)
                        ->setMargin(0)
                        ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);
                    $result = $writer->write($qrCode, null, null);
                    $result->saveToFile($urlimage);
                    telegram('sendphoto', [
                        'chat_id' => $get_invoice['id_user'],
                        'photo' => new CURLFile($urlimage),
                        'reply_markup' => $Shoppinginfo,
                        'caption' => $textcreatuser,
                        'parse_mode' => "HTML",
                    ]);
                    unlink($urlimage);
                } else {
                    sendmessage($get_invoice['id_user'], $textcreatuser, $Shoppinginfo, 'HTML');
                }
            }
            elseif ($marzban_list_get['sublink'] == "onsublink") {
                $urlimage = "{$get_invoice['id_user']}$randomString.png";
                $writer = new PngWriter();
                $qrCode = QrCode::create($output_config_link)
                    ->setEncoding(new Encoding('UTF-8'))
                    ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
                    ->setSize(400)
                    ->setMargin(0)
                    ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);
                $result = $writer->write($qrCode, null, null);
                $result->saveToFile($urlimage);
                telegram('sendphoto', [
                    'chat_id' => $get_invoice['id_user'],
                    'photo' => new CURLFile($urlimage),
                    'reply_markup' => $Shoppinginfo,
                    'caption' => $textcreatuser,
                    'parse_mode' => "HTML",
                ]);
                if($marzban_list_get['type'] == "wgdashboard"){
                    $urlimage = "{$marzban_list_get['inboundid']}_{$dataoutput['username']}.conf";
                    file_put_contents($urlimage,$output_config_link);
                    sendDocument($get_invoice['id_user'], $urlimage,$textbotlang['users']['buy']['configwg']);
                    unlink($urlimage);
                }
                unlink($urlimage);
            }
            $partsdic = explode("_", $Balance_id['Processing_value_four']);
            if ($partsdic[0] == "dis") {
                $SellDiscountlimit = select("DiscountSell", "*", "codeDiscount", $partsdic[1],"select");
                $value = intval($SellDiscountlimit['usedDiscount']) + 1;
                update("DiscountSell","usedDiscount",$value, "codeDiscount",$partsdic[1]);
                $stmt = $pdo->prepare("INSERT INTO Giftcodeconsumed (id_user,code) VALUES (:id_user,:code)");
                $stmt->bindParam(':id_user', $Balance_id['id']);
                $stmt->bindParam(':code', $partsdic[1]);
                $stmt->execute();
                $result = ($SellDiscountlimit['price'] / 100) * $get_invoice['price_product'];
                $pricediscount = $get_invoice['price_product'] - $result;
                $text_report = sprintf($textbotlang['users']['Report']['discountused'],$Balance_id['username'],$Balance_id['id'],$partsdic[1]);
                if (strlen($setting['Channel_Report']) > 0) {
                    telegram('sendmessage',[
                        'chat_id' => $setting['Channel_Report'],
                        'text' => $text_report,
                    ]);
                }
            }else{
                $pricediscount = null;
            }
            $affiliatescommission = select("affiliates", "*", null, null,"select");
            if ($affiliatescommission['status_commission'] == "oncommission" &&($Balance_id['affiliates'] !== null || $Balance_id['affiliates'] != 0)) {
                if($pricediscount == null){
                    $result = ($get_invoice['price_product'] * $affiliatescommission['affiliatespercentage']) / 100;
                }else{
                    $result = ($pricediscount * $affiliatescommission['affiliatespercentage']) / 100;
                }
                $user_Balance = select("user", "*", "id", $Balance_id['affiliates'],"select");
                if(isset($user_Balance)){
                    $Balance_prim = $user_Balance['Balance'] + $result;
                    update("user","Balance",$Balance_prim, "id",$Balance_id['affiliates']);
                    $result = number_format($result);
                    $textadd =sprintf($textbotlang['users']['affiliates']['porsantuser'],$result);
                    sendmessage($Balance_id['affiliates'], $textadd, null, 'HTML');
                }
            }
            $Balance_prims = $Balance_id['Balance'] - $get_invoice['price_product'];
            if($Balance_prims <= 0) $Balance_prims = 0;
            update("user","Balance",$Balance_prims, "id",$Balance_id['id']);
            $Balance_id['Balance'] = select("user", "Balance", "id", $get_invoice['id_user'],"select")['Balance'];
            $balanceformatsell = number_format($Balance_id['Balance'], 0);
            $text_report = sprintf($textbotlang['users']['Report']['reportbuyafterpay'] ,$get_invoice['username'],$get_invoice['price_product'],$get_invoice['Volume'],$get_invoice['id_user'],$Balance_id['number'],$get_invoice['Service_location'],$balanceformatsell,$randomString,$Balance_id['username']);
            if (strlen($setting['Channel_Report']) > 0) {
                telegram('sendmessage',[
                    'chat_id' => $setting['Channel_Report'],
                    'text' => $text_report,
                    'parse_mode' => "HTML"
                ]);
            }
            update("invoice","status","active","username",$get_invoice['username']);
            if($Payment_report['Payment_Method'] == "cart to cart"){
                update("invoice","Status","active","id_invoice",$get_invoice['id_invoice']);
                telegram('answerCallbackQuery', array(
                        'callback_query_id' => $callback_query_id,
                        'text' => $textbotlang['users']['moeny']['acceptedcart'],
                        'show_alert' => true,
                        'cache_time' => 5,
                    )
                );
            }
            return true;
        } else {
            $Balance_confrim = intval($Balance_id['Balance']) + intval($Payment_report['price']);
            update("user","Balance",$Balance_confrim, "id",$Payment_report['id_user']);
            update("Payment_report","payment_Status","paid","id_order",$Payment_report['id_order']);
            
            return true;
        }
    } catch (Exception $e) {
        error_log("DirectPayment error: " . $e->getMessage());
        return false;
    }
}
function savedata($type,$namefiled,$valuefiled){
    global $from_id;
    
    if (!is_numeric($from_id) || empty($type) || empty($namefiled)) {
        error_log("Invalid parameters in savedata");
        return false;
    }
    
    try {
        if($type == "clear"){
            $datauser = [];
            $datauser[$namefiled] = $valuefiled;
            $data = json_encode($datauser);
            return update("user","Processing_value",$data,"id",$from_id);
        }elseif($type == "save"){
            $userdata = select("user","*","id",$from_id,"select");
            if (!$userdata) {
                error_log("User not found in savedata: $from_id");
                return false;
            }
            
            $dataperevieos = json_decode($userdata['Processing_value'],true);
            if (!is_array($dataperevieos)) {
                $dataperevieos = [];
            }
            
            $dataperevieos[$namefiled] = $valuefiled;
            return update("user","Processing_value",json_encode($dataperevieos),"id",$from_id);
        }
    } catch (Exception $e) {
        error_log("savedata error: " . $e->getMessage());
        return false;
    }
    
    return false;
}
function sanitizeUserName($userName) {
    if (empty($userName)) {
        return '';
    }
    
    // More comprehensive list of forbidden characters
    $forbiddenCharacters = [
        "'", "\"", "<", ">", "--", "#", ";", "\\", "%", "(", ")",
        "{", "}", "[", "]", "|", "^", "~", "`", "!", "@", "$",
        "&", "*", "+", "=", "?", "/", ":", ",", "."
    ];

    foreach ($forbiddenCharacters as $char) {
        $userName = str_replace($char, "", $userName);
    }

    // Remove any non-printable characters
    $userName = preg_replace('/[^\x20-\x7E]/u', '', $userName);
    
    // Trim whitespace and limit length
    $userName = trim($userName);
    $userName = substr($userName, 0, 32);

    return $userName;
}
function checktelegramip(){
    // Updated Telegram IP ranges
    $telegram_ip_ranges = [
        ['lower' => '149.154.160.0', 'upper' => '149.154.175.255'],
        ['lower' => '91.108.4.0',    'upper' => '91.108.7.255'],
        ['lower' => '91.108.8.0',    'upper' => '91.108.63.255'],
        ['lower' => '149.154.164.0', 'upper' => '149.154.167.255'],
        ['lower' => '149.154.168.0', 'upper' => '149.154.175.255'],
        ['lower' => '91.108.16.0',   'upper' => '91.108.31.255']
    ];
    
    if (!isset($_SERVER['REMOTE_ADDR'])) {
        return false;
    }
    
    $ip_dec = (float) sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
    $ok = false;
    
    foreach ($telegram_ip_ranges as $telegram_ip_range) {
        if (!$ok) {
            $lower_dec = (float) sprintf("%u", ip2long($telegram_ip_range['lower']));
            $upper_dec = (float) sprintf("%u", ip2long($telegram_ip_range['upper']));
            if ($ip_dec >= $lower_dec && $ip_dec <= $upper_dec) {
                $ok = true;
            }
        }
    }
    
    return $ok;
}
function generateAuthStr($length = 10) {
    if ($length < 1 || $length > 128) {
        $length = 10;
    }
    
    try {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        return substr(str_shuffle(str_repeat($characters, ceil($length / strlen($characters)))), 0, $length);
    } catch (Exception $e) {
        error_log("generateAuthStr error: " . $e->getMessage());
        return false;
    }
}
function channel($id_channel){
    global $from_id,$APIKEY;
    
    if (empty($id_channel) || empty($APIKEY) || !is_numeric($from_id)) {
        error_log("Invalid parameters in channel function");
        return [];
    }
    
    $channel_link = array();
    
    try {
        $response = telegram('getChatMember',[
            "chat_id" => "@$id_channel",
            "user_id" => $from_id,
        ]);
        
        if($response['ok']){
            if(!in_array($response['result']['status'], ['member', 'creator', 'administrator'])){
                $channel_link[] = $id_channel;
            }
        } else {
            error_log("Telegram API error in channel function: " . json_encode($response));
        }
    } catch (Exception $e) {
        error_log("channel function error: " . $e->getMessage());
    }
    
    return $channel_link;
}
function addFieldToTable($tableName, $fieldName, $defaultValue = null, $datatype = "VARCHAR(500)") {
    global $pdo;
    
    // Validate table and field names
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName) || 
        !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $fieldName)) {
        error_log("Invalid table or field name");
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_name = :tableName");
        $stmt->bindParam(':tableName', $tableName);
        $stmt->execute();
        $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tableExists['count'] == 0) return false;
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$pdo->query("SELECT DATABASE()")->fetchColumn(), $tableName, $fieldName]);
        $filedExists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($filedExists['count'] != 0) return false;
        
        $query = "ALTER TABLE `$tableName` ADD `$fieldName` $datatype";
        $statement = $pdo->prepare($query);
        $statement->execute();
        
        if($defaultValue != null){
            $stmt = $pdo->prepare("UPDATE `$tableName` SET `$fieldName` = ?");
            $stmt->execute([$defaultValue]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("addFieldToTable error: " . $e->getMessage());
        return false;
    }
}

function publickey(){
    try {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://phpizmir.com/mirza/createpublickey.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ));
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($response === false || $httpCode !== 200) {
            error_log("publickey API request failed");
            return false;
        }
        
        return $response;
    } catch (Exception $e) {
        error_log("publickey error: " . $e->getMessage());
        return false;
    }
}

// Improved addBackButtonToKeyboard function
function addBackButtonToKeyboard($keyboard_json) {
    global $textbotlang;
    
    if (empty($keyboard_json)) {
        return $keyboard_json;
    }
    
    try {
        // Decode the keyboard JSON
        $keyboard = json_decode($keyboard_json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error in addBackButtonToKeyboard");
            return $keyboard_json;
        }
        
        // Check if it's an inline keyboard
        if (isset($keyboard['inline_keyboard']) && is_array($keyboard['inline_keyboard'])) {
            // Add back button as the last row if text is available
            $backText = $textbotlang['users']['back_button'] ?? '🔙 بازگشت';
            $keyboard['inline_keyboard'][] = [
                ['text' => $backText, 'callback_data' => 'back_to_menu']
            ];
        }
        
        // Return the modified keyboard
        return json_encode($keyboard);
    } catch (Exception $e) {
        error_log("addBackButtonToKeyboard error: " . $e->getMessage());
        return $keyboard_json;
    }
}

// Additional security function to validate API keys
function validateApiKey($key) {
    if (empty($key) || strlen($key) < 10) {
        return false;
    }
    
    // Basic bot token format validation
    if (preg_match('/^[0-9]{8,10}:[a-zA-Z0-9_-]{35}$/', $key)) {
        return true;
    }
    
    return false;
}

// Function to log security events
function logSecurityEvent($event, $details = '') {
    $logEntry = date('Y-m-d H:i:s') . " - SECURITY: $event";
    if (!empty($details)) {
        $logEntry .= " - Details: $details";
    }
    $logEntry .= " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
    
    error_log($logEntry);
}