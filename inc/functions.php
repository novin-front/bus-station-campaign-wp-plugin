<?php

function wp_emigration_create_database_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'table_campaign_data';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        $sql = "CREATE TABLE `wp_table_campaign_data` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `full_name` varchar(40) COLLATE utf8_persian_ci NOT NULL,
            `mobile` varchar(11) COLLATE utf8_persian_ci NOT NULL,
            `user_u_id` varchar(50) COLLATE utf8_persian_ci NOT NULL,
            `referral_id` varchar(50) COLLATE utf8_persian_ci NOT NULL,
            `operator_code` int(11) DEFAULT NULL,
            `bolton_api_response` varchar(600) COLLATE utf8_persian_ci DEFAULT NULL,
            `bolton_activated_status` varchar(500) COLLATE utf8_persian_ci DEFAULT NULL,
            `transaction_status` varchar(500) COLLATE utf8_persian_ci DEFAULT NULL,
            `tracking_code` varchar(25) COLLATE utf8_persian_ci DEFAULT NULL,
            `sms_response_json_one` varchar(500) COLLATE utf8_persian_ci DEFAULT NULL,
            `sms_response_json_two` varchar(500) COLLATE utf8_persian_ci DEFAULT NULL,
            `fam_api_result` varchar(600) COLLATE utf8_persian_ci NOT NULL,
            `create_at` datetime NOT NULL DEFAULT current_timestamp()
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;";
    }
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
function wp_referring_users_create_database_table()
{
    global $wpdb;
    $table_name = 'referring_users';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        $sql = "CREATE TABLE `referring_users` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `referring_user_id` varchar(50) COLLATE utf8_persian_ci NOT NULL,
            `referring_user_count` int(100) NOT NULL,
            `referring_peoples` longtext COLLATE utf8_persian_ci NOT NULL,
            `create_at` varchar(30) COLLATE utf8_persian_ci NOT NULL DEFAULT current_timestamp()
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;";
    }
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
function wp_emigration_delete_database_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'table_campaign_data';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
    delete_option("wp_emigration_db_version");
}
function wp_emigration_set_default_plugin_config()
{
    $current_settings = get_option("wp_emigration_settings");
    if (!$current_settings) {
        $default_configs = [
            "active_campaign" => true,
            "role" => "admin"
        ];
        update_option("wp_emigration_settings", $default_configs);
    }
}

add_action('rest_api_init', function () {
    header("Access-Control-Allow-Origin: *");
});
add_action('rest_api_init', function () {
    add_action('rest_pre_serve_request', function () {
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Wpml-Language', true);
        header("Access-Control-Allow-Origin: *");
    });
}, 15);


add_action('rest_api_init', 'wp_emigration_register_new_routes');
add_action('rest_api_init', 'wp_emigration_get_info_register_new_routes');


function wp_emigration_register_new_routes()
{
    register_rest_route('campaign/v1', 'save-data/', array(
        'methods' => 'post',
        'callback' => 'save_campaign_info'
    ));
}
function wp_emigration_get_info_register_new_routes()
{
    register_rest_route('campaign-validation/v1', 'get-info/', array(
        'methods' => 'post',
        'callback' => 'get_info_user_data'
    ));
}

function get_info_user_data(WP_REST_Request $request)
{

    $parameters = $request->get_params();
    $data = wp_validate_user_get_info($parameters);
    $response = new WP_REST_Response($data, 200);
    return $response;
}
function wp_validate_user_get_info($parameters)
{
    $validate_fildes = [];
    $user_name = $parameters["userName"];
    $password = $parameters["password"];
    $result = array(
        "success" => false,
        "validate_error" => [],
        'message'  => '',
        'status'       => 200
    );
    foreach ($parameters as $key => $value) {
        $validate = wp_validate_data($value, $key);
        if ($validate) {
            array_push($validate_fildes, $validate);
        }
    }
    if (count($validate_fildes)) {
        $result = array(
            "success" => false,
            "validate_error" => $validate_fildes,
            'message'  => 'خطا در اعتبار سنجی اطلاعات',
            'status'       => 200
        );
        return $result;
    }
    $user = wp_authenticate_username_password(null, $user_name, $password);
    if (is_wp_error($user)) {
        $result = array(
            "success" => false,
            "validate_error" => [],
            'message'  => 'کاربری با این مشخصات یافت نشد',
            'status'       => 200
        );
        return $result;
    }
    $userLogden =  wp_signon([
        'user_login'    => $user_name,
        'user_password' => $password,
        'remember'      => false,
    ]);
    if (is_wp_error($userLogden)) {
        $result = array(
            "success" => false,
            "validate_error" => [],
            'message'  => 'در هنگام انجام عملیات لاگین خطایی رخ داده است لطفا دوباره سعی نمایید',
            'status'       => 200
        );
        wp_send_json($result);
    } else {
        $result = array(
            "success" => true,
            "validate_error" => [],
            'message'  => 'با موفقیت وارد شدید',
            'user_data' => get_all_emigration_campaingn(),
        );
        wp_send_json($result);
    }
}

function get_all_emigration_campaingn()
{
    global $wpdb;
    $data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}table_campaign_data");
    // return $data;
    return change_user_information($data);
}

function change_user_information($user_data){
    $new_user_data =[];
    for ($i = 0; $i < count($user_data);$i++) {
        array_push($new_user_data,create_new_user_data($user_data[$i]));
    }
    return $new_user_data;
}
function create_new_user_data ($data_array){
    return array(
        'fullName' => $data_array-> full_name,
        'mobile' => $data_array -> mobile ,
        'operatorName' => get_operator_name_by_code($data_array->operator_code),
        'boltonActivatedStatus' => $data_array->bolton_activated_status,
        'transactionStatus' => $data_array -> transaction_status ,
        'trackingCode' => $data_array -> tracking_code ,
        'smsStatusOne' => get_sms_status($data_array -> sms_response_json_one ),
        'smsMessageIdsOne' => get_sms_message_id($data_array-> sms_response_json_one),
        'smsStatusTwo' => get_sms_status($data_array->sms_response_json_two),
        'smsMessageIdsTwo' => get_sms_message_id($data_array -> sms_response_json_two),
        'createAt' => $data_array -> create_at,
    );
}

function save_campaign_info(WP_REST_Request $request)
{

    $parameters = $request->get_params();
    $data = wp_validate_emigration_params($parameters);
    $response = new WP_REST_Response($data, 200);
    return $response;
}


function wp_validate_emigration_params($parameters)
{
    $validate_fildes = [];
    $result = array(
        "success" => false,
        "validate_error" => [],
        'message'  => '',
    );
    foreach ($parameters as $key => $value) {
        $validate = wp_validate_data($value, $key);
        if ($validate) {
            array_push($validate_fildes, $validate);
        }
    }
    if (count($validate_fildes)) {
        $result = array(
            "success" => false,
            "validate_error" => $validate_fildes,
            'message'  => 'خطا در اعتبار سنجی اطلاعات',
            "ffff" => preg_match('^(\+98|0)?9\d{9}$/g', $parameters["mobile"]) && (strlen($parameters["mobile"]) > 11 || strlen($parameters["mobile"]) < 10),
        );
        return $result;
    }
    $user_checked = check_has_user($parameters["mobile"]);
    $referral_user_checked = check_has_referring_user_id($parameters["referralCode"]);
    $user_has_access = false;
    if ((count($user_checked) > 0)) {
        $user_has_access = true;
    }else{
        $user_has_access = false;
    }
    $get_all_user_register = get_user_by_mobile($parameters["mobile"]);
    if(count($referral_user_checked) > 0){
        if(intval($referral_user_checked["referring_user_count"])=== 0  && (count($get_all_user_register) > 1) ){
            $user_has_access = true;
        }else{
            $user_has_access = false;
        }
    }
    if($user_has_access) {
        $result = array(
            "success" => false,
            "validate_error" => [],
            'message'  => 'شماره شما قبلا در سیستم ثبت شده است',
            "mobile" => preg_match('^(\+98|0)?9\d{9}$/g', $parameters["mobile"]),
        );
        return $result;
    }
    $mobile = $parameters["mobile"];
    $fam_api_result = check_is_installed_fam($parameters["simType"],$parameters["mobile"]);
    $fam_api_result = "";
    $save_data = save_information($fam_api_result,$parameters);
    $now = new DateTime();
    
    if ($save_data) {
        $result = array(
            "success" => true,
            "validate_error" => $validate_fildes,
            'message'  => 'اطلاعات شما با موفقیت ثبت شد',
            // "famResultCode" => $fam_api_result -> code,
            // "famResultMessage" => $fam_api_result -> message,
            "shortUrl" =>generate_user_invitation_link($mobile),
        );
        return $result;
    } 
    else {
        $result = array(
            "success" => false,
            "validate_error" => $validate_fildes,
            'message'  => 'خطا در ذخیره اطلاعات',
            "status" => $save_data,
            "user_checked" => $user_checked,
            "famApiCall" => $fam_api_result,
        );
        return $result;
    }
}
function check_is_installed_fam($sim_type,$user_phone_number){
    $url = "https://famepay.ir:6966/api/v1.0/preregistrationUser";
    $username="maat";
    $password = "daefddd8e8d20f00395ce1f34a39ff9c6fbd2bf140897425594d607b94b814ef";
    $now = new DateTime();
    $time = $now->getTimestamp();
    $data_hash = $user_phone_number .'|'. $username .'|'. $password .'|';
    $checksum =  hash('sha256', $data_hash, false);
    $basic_authentication = base64_encode($username .":". $password .":". $time);
    $response = wp_remote_post(
        $url,
        array(
            'method'=> 'POST',
            'headers'=> array(
            'basicAuthentication' => $basic_authentication,
            'checksum' => $checksum,
            'Content-Type'=> 'application/json',
            ),
            'body' =>  json_encode( array(
                'username'=> $username,
                'password'=> $password,
                'phoneNumber'=> $user_phone_number,
                'time' => $now->getTimestamp(),
                'packageType' => intval($sim_type),
            ) ),
        )
    );

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return "errors : $error_message";
    } else {
        $body = wp_remote_retrieve_body($response);
        return json_decode($body);
    }
}

function validate_mobile($mobile)
{
    if (!(is_null($mobile)) || !(empty($mobile))) {
        if (strlen($mobile) > 9 && strlen($mobile) <= 11) {
            return preg_match('/^[0][9][0-9]{9,9}$/', $mobile);
        } else {
            return false;
        }
    } else {
        return false;
    }
}
function wp_validate_data($data, $data_type)
{
    switch (true) {
        case $data_type === "userName":
            if (is_null($data) || empty($data)) {
                return array(
                    "message" => "مقدار نام کاربری نمی توان خالی باشد",
                    "type" => $data_type
                );
            } elseif (preg_match('/[a-zA-Z0-9]+[\w-\.]{3,30}/g', $data)) {
                return array(
                    "message" => "فرمت نام کاربری وارد شده اشتباه است",
                    "type" => $data_type
                );
            }
            break;
        case $data_type === "email":
            if (is_null($data) || empty($data)) {
                return array(
                    "message" => "مقدار ایمیل نمی توان خالی باشد",
                    "type" => $data_type
                );
            } elseif (!is_email($data)) {
                return array(
                    "message" => "ایمیل وارد شده اشتباه است",
                    "type" => $data_type
                );
            }
            break;
        case $data_type === "mobile":
            if (is_null($data) || empty($data)) {
                return array(
                    "message" => "مقدار موبایل نمی توان خالی باشد",
                    "type" => $data_type
                );
            }
            if (validate_mobile($data)) {
                return null;
            } else {
                return array(
                    "message" =>  "فرمت شماره موبایل وارد شده اشتباه است",
                    "type" => $data_type
                );
            }
            break;
        case $data_type === "fullName":
            if (is_null($data) || empty($data)) {
                return array(
                    "message" => "مقدار وارد شده برای نام و نام خانوادگی نمی توان خالی باشد",
                    "type" => $data_type
                );
            } elseif (strlen($data) <= 2 || strlen($data) > 40) {
                return array(
                    "message" =>  "تعداد کارکتر نام و نام خانوادگی در محدوده مجاز نیست ",
                    "type" => $data_type
                );
            }
            break;
        case $data_type === "emigrationType":
            if (is_null($data) || empty($data)) {
                return array(
                    "message" => "مقدار وارد شده برای نوع مهاجرت نمی توان خالی باشد",
                    "type" => $data_type
                );
            } elseif (strlen($data) <= 2 || strlen($data) > 40) {
                return array(
                    "message" =>  "تعداد کارکتر نوع مهاجرت در محدوده مجاز نیست ",
                    "type" => $data_type
                );
            }
            break;
        case $data_type === "password":
            if (is_null($data) || empty($data)) {
                return array(
                    "message" =>  "مقدار کلمه عبور نمی توان خالی باشد",
                    "type" => $data_type
                );
            } elseif (preg_match('/^(?=.*\d)(?=.*[a-zA-Z]).{5,}$/gm', $data)) {
                return array(
                    "message" =>  "فرمت کلمه عبور وارد شده اشتباه است",
                    "type" => $data_type
                );
            }
            break;
        default:
            return null;
            break;
    }
}

function check_has_user($mobile)
{
    // if ($mobile == "09192018492" || $mobile == "09124474386" || $mobile == "09364931098" || $mobile == "09331520952" || $mobile == "09212882953") {
    //     return [];
    // }
    global $wpdb;
    $user = $wpdb->get_results("SELECT mobile FROM {$wpdb->prefix}table_campaign_data WHERE mobile = {$mobile}  LIMIT 1");
    return $user;
}
function check_has_user_by_referring_user_id($referral_id)
{
    global $wpdb;
    $user = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}table_campaign_data WHERE user_u_id = {$referral_id}  LIMIT 1");
    return $user;
}
function get_user_by_mobile($mobile)
{
    global $wpdb;
    $user = $wpdb->get_results("SELECT mobile FROM {$wpdb->prefix}table_campaign_data WHERE mobile = {$mobile} ");
    return $user;
}
function check_has_referring_user_id($referral_id)
{
    if($referral_id === ""){
        return [];
    }
    global $wpdb;
    $referral_user = $wpdb->get_results("SELECT * FROM `referring_users` WHERE `referring_user_id`='{$referral_id}'  LIMIT 1");
    return $referral_user;
}
function update_user_referring_by_referral_id($referral_id,$refrall_count,$referring_peoples,$new_referral_peoples){
    global $wpdb;
    $refrall_count = ($refrall_count + 1);
    $arry_referring_peoples_save =[];
    $arry_referring_peoples_save =explode(',', $referring_peoples);
    array_push($arry_referring_peoples_save,$new_referral_peoples);
    $string_referring_peoples_save =implode(",", $arry_referring_peoples_save);
    $wpdb->update('referring_users', array('referring_user_count'=>$refrall_count, 'referring_peoples'=>$string_referring_peoples_save), array('referring_user_id'=>$referral_id));
    
}
function insert_user_referring_table($mobile){
    global $wpdb;
    $wpdb->insert(
        'referring_users',
        array(
            'referring_user_id' => crc32($mobile),
            'referring_user_count' => 0,
            'referring_peoples' => "",
        ),
    );
}

function save_information($fam_api_result,$parameters)
{
    $name = $parameters["fullName"];
    $mobile = $parameters["mobile"];
    $user_invitation_link = generate_user_invitation_link($mobile);
    $sms_one_message = "$name کاربر گرامی فام؛\r\n
    برای دریافت بسته اینترنت رایگان خود اپلیکیشن را از لینک زیر دریافت نمایید :
    \r\n
    https://trc.metrix.ir/soyjep/";
    $sms_two_message = "همراه گرامی فام؛ \r\n
    بسته اینترنت رایگان شما فعال گردید. \r\n
    بانکداری جلوتر از زمان \r\n
    با افتتاح حساب غیر حضوری در فام در قرعه کشی جوایز میلیونی فام شرکت کنید \r\n
    در ضمن دوستان خود را از لینک زیر دعوت کنید تا شانس بیشتری داشته باشید:\r\n" . $user_invitation_link ;
    
    $operator_code = which_operator($mobile);
    
    if($fam_api_result -> code == "101" || $fam_api_result -> code == "103" && false){
        $sms_two_response = send_sms($parameters["mobile"],$sms_two_message,0);
        $bolton_api_response = active_bolton($operator_code, $mobile);
        $bolton_activated_status = get_bolton_status($operator_code, $bolton_api_response);
        $transaction_status = get_error_by_transaction_code($bolton_api_response['resultCode'],$bolton_api_response);
    }else{
        $sms_one_response = send_sms($parameters["mobile"],$sms_one_message,0);
        $sms_two_response = send_sms($parameters["mobile"],$sms_two_message,600);
    }

    admin_status_notification($bolton_api_response['resultCode'], $transaction_status);
    $referaral_code = $parameters["referralCode"];
    $checked_refrall_user = check_has_referring_user_id( $referaral_code);
    if(count($checked_refrall_user) > 0) {
        $refrall_count = intval($checked_refrall_user[0] -> referring_user_count);
        $referring_peoples = $checked_refrall_user[0] -> referring_peoples;
        update_user_referring_by_referral_id($referaral_code, $refrall_count,$referring_peoples,crc32($parameters["mobile"]));
        if($refrall_count < 2){
            $get_old_user_by_rerall_id = check_has_user_by_referring_user_id($parameters["referralCode"]);
            $name_user_three = $get_old_user_by_rerall_id[0] -> full_name;
            $sms_three_message = "$name_user_three عزیز \r\n
            تبریک ! همین الان می توانی جایزه بسته اینترنتی 500 مگابایت دعوت از دوستان خود را از طریق لینک زیر دریافت کنید :
            \r\n
            https://trc.metrix.ir/hp78i4/";
            $sms_two_response = send_sms($get_old_user_by_rerall_id[0] -> mobile,$sms_three_message,0);
        }
    }
    $checked_has_refrall_user_in_db = check_has_referring_user_id(crc32($mobile));
    if(count($checked_has_refrall_user_in_db) === 0) {
        insert_user_referring_table($mobile);
    }
    global $wpdb;
    return $wpdb->insert(
        $wpdb->prefix . 'table_campaign_data',
        array(
            'full_name' => sanitize_text_field($parameters["fullName"]),
            'mobile' => sanitize_text_field($parameters["mobile"]),
            'user_u_id' => crc32($parameters["mobile"]),
            'operator_code' => $operator_code,
            'bolton_api_response' => json_encode($bolton_api_response),
            'bolton_activated_status' => $bolton_activated_status,
            'transaction_status' => $transaction_status,
            'tracking_code' => $bolton_api_response['tracking_code'],
            'sms_response_json_one' => $sms_one_response,
            'sms_response_json_two' => $sms_two_response,
            'referral_id' => $parameters["referralCode"],
            'fam_api_result' => json_encode($fam_api_result),
            
        ),
    );
}


function send_sms($mobile, $sms_message, $delay)
{
    $url = "http://api.ghasedaksms.com/v3/sms/send/simple";
    $now = new DateTime();
    $senddate =  $now->getTimestamp() + $delay;
    $response = wp_remote_post(
        $url,
        array(
            'method'      => 'POST',
            'headers'     => array(
                'apikey' => 'kevWUrPjmvebhnaZ2vXUWcrLcWtE4F09AkG4kupyikE'
            ),
            'body'  => array(
                'message' => $sms_message,
                'sender' => '500027504092',
                'receptor' =>  $mobile,
                'senddate' => $senddate,

            ),
        )
    );

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return "Something went wrong: $error_message";
    } else {
        $body = wp_remote_retrieve_body($response);
        return $body;
    }
}
function get_sms_status($sms_data){
    if($sms_data !== null){
        $sms_decode = json_decode($sms_data);
        if($sms_decode -> result  == 'success'){
            return 'با موفقیت ارسال شد';
        }else if( $sms_decode -> result == "error") {
            return 'خطا در ارسال پیام';
        }else{
            return 'وضعیت نا مشخص';
        }
    }
    return "پاسخی یافت نشد";
}
function get_sms_message_id($sms_data){
    if($sms_data !== null){
        $sms_decode = json_decode($sms_data);
        return $sms_decode-> messageids;
    }
    return "کدی یافت نشد";
}
function admin_status_notification($status_code, $status_message)
{
    switch (true) {
        case $status_code == '20006':
            $sms_message = "در فرآیند کمپین مشکلی رخ داده است لطفا بررسی نمایید\n کد خطا : \n متن خطا :\n $status_message";
            send_sms('09192018492', $sms_message, 0);
            send_sms('09124474386',$sms_message,0);
            break;
        case $status_code == '20008':
            $sms_message = "در فرآیند کمپین مشکلی رخ داده است لطفا بررسی نمایید \n کد خطا : \n $status_code \n متن خطا :\n $status_message";
            send_sms('09192018492', $sms_message, 0);
            send_sms('09124474386', $sms_message, 0);
            break;
        case $status_code != '200' && $status_code != 'success':
            $sms_message = "در فعال سازی بسته خطایی رخ داده است \n کد خطا : \n $status_code \n متن خطا : \n $status_message";
            send_sms('09192018492', $sms_message, 0);
            send_sms('09124474386', $sms_message, 0);
            break;
        default:
            break;
    }
}
function get_error_by_transaction_code($code, $response)
{
    switch (true) {
        case $code == "120":
            return "نام کاربری یا رمز عبور اشتباه است";
        case $code == "130":
            return "حساب مسدود شدهاست";
        case $code == "140":
            return "کد پذیرنده اشتباه است";
        case $code == "150":
            return "خطای داخلی رخ دادهاست";
        case $code == "160":
            return "مشکل داخلی برای ایرانسل رخ دادهاست";
        case $code == "170":
            return "شماره تلفن وارد شده اشتباه است";
        case $code == "175":
            return "شماره فاکتور اشتباه است.";
        case $code == "115":
            return "مبلغ وارد شده کمتر از حداقل مبلغ مجاز است";
        case $code == "117":
            return "مبلغ وارد شده بیشتر از حداکثر مبلغ مجاز است";
        case $code == "116":
            return "شماره همراه مورد نظر غیر فعال است/ ثبت نشدهاست";
        case $code == "118":
            return "مبلغ شارژ شگفت انگیز می تواند 100000،50000 ،200000 باشد.";
        case $code == "400":
            return "بسته مورد نظر یافت نشد، مشخصات نامعتبر می باشد.";
        case $code == "20006":
            return "حساب کاربری مسدود می باشد.";
        case $code == "20008":
            return "موجودی حساب کافی نمی باشد.";
        case $code == "200":
            return "پاسخ موفقیتآمیز";
        case $code == "400":
            return "پاسخ همراه با خطا";
        default:
            if (array_key_exists("responseMessage", $response)) {
                return $response['responseMessage'];
            } else {
                return "خطایی غیر قابل پیش بینی ، در لیست خطا ها یافت نشد";
            }
    }
}
function get_bolton_status($operator_code, $bolton_api_response)
{
    if ($operator_code === 1) {
        $response = $bolton_api_response['trnState'] == '200' ? "Activated" : "NotActivated";
        return $response;
    } else if ($operator_code === 2) {
        $response = $bolton_api_response['trnState'] == '200' ? "Activated" : "NotActivated";
        return $response;
    } else if ($operator_code === 3) {
        $response = $bolton_api_response['trnState'] == '200' ? "Activated" : "NotActivated";
        return $response;
    } else {
        return $response = "The type of operator is not known";
    }
}

function which_operator($mobile)
{
    $prefix = substr($mobile, 0, 4);
    $hamrahe_aval = array('0910', '0911', '0912', '0913', '0914', '0915', '0916', '0917', '0918', '0919', '0990', '0991', '0992', '0993', '0994');
    $irancell = array('0930', '0933', '0935', '0936', '0937', '0938', '0939', '0901', '0902', '0903', '0904', '0905', '0941');
    $rightel = array('0920', '0921', '0922');
    $operator_code = 0;
    foreach ($hamrahe_aval as $value) {
        if ($value === $prefix) {
            $operator_code = 1;
        }
    }
    foreach ($irancell as $value) {
        if ($value === $prefix) {
            $operator_code = 2;
        }
    }
    foreach ($rightel as $value) {
        if ($value === $prefix) {
            $operator_code = 3;
        }
    }
    return $operator_code;
}
function get_operator_name_by_code($operator_code){
    switch (true) {
        case $operator_code == '1':
           return "همراه اول";
        case $operator_code == '2':
            return "ایرانسل";
        case $operator_code == '3':
            return "رایتل";
        default:
            return "نوع اپراتور مشخص نیست";
    }
}
function active_bolton($operator_code, $mobile)
{
    $response = "";
    if ($operator_code === 1) {
        $response = buy_hamrahe_aval_package($mobile);
        return $response;
    } else if ($operator_code === 2) {
        $response = checkAbilityToIrancellTransactions($mobile);
        return $response;
    } else if ($operator_code === 3) {
        $response = buy_rightel_package($mobile);
        return $response;
    } else {
        return $response = "The type of operator is not known";
    }
}

function buy_hamrahe_aval_package($mobile)
{
    $url = "https://mci.irpointcenter.com/mci/buyBundle/";
    $tracking_code= rand() . "";
    $response = wp_remote_post(
        $url,
        array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'method'      => 'POST',
            'data_format' => 'body',
            'body'  => json_encode(array(
                'username' => PANEL_USER_NAME,
                'password' => PANEL_PASSWORD,
                'phoneToCharge' =>  $mobile,
                'bundleId' => 1001030,
                'invoiceNumber' => $tracking_code,

            )),
        )
    );

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return "Something went wrong: $error_message";
    } else {
        $body = wp_remote_retrieve_body($response);
        $decode = json_decode(stripslashes($body), true);
        $decode["tracking_code"] = $tracking_code;
        return $decode;
    }
}

function checkAbilityToIrancellTransactions($mobile){
    global $amount , $profile_type , $response;
    $profile_type ='63344';
    $amount=49050;
    $response =  check_available_irancell_package($mobile,$profile_type,$amount);

    if($response['resultCode'] == '121'){
        $profile_type ='61624';
        $amount=49050;
        $GLOBALS['response'] =  buy_irancell_package($mobile,$profile_type,$amount);
    }else if($response['resultCode'] == '187'){
        $profile_type ='63344';
        $amount=49050;
        $GLOBALS['response'] =  buy_irancell_package($mobile,$profile_type,$amount);
    }else{
        $GLOBALS['response'] = buy_irancell_package($mobile,$profile_type,$amount);
    }


    return $response;


}

function check_available_irancell_package($mobile,$profile_type,$amount)
{
    $url = "https://topup.irpointcenter.com/irancell/checkOrderBeforeBuy";
    $tracking_code= rand() . "";
    $response = wp_remote_post(
        $url,
        array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'method'      => 'POST',
            // 'data_format' => 'body',
            'body'  => json_encode (array(
                'user' => PANEL_USER_NAME,
                'password' => PANEL_PASSWORD,
                'mSISDN' =>  $mobile,
                'profileType' => $profile_type , //'61624',
                'amount' => $amount, //49050 //مبلغ خرید شارژ/ هزینه بسته

            )),
        )
    );

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return "Something went wrong: $error_message";
    } else {
        $body = wp_remote_retrieve_body($response);
        $decode = json_decode(stripslashes($body), true);
        $decode["tracking_code"] = $tracking_code;
        return $decode;
    }
}
function buy_irancell_package($mobile,$bundle_id,$amount)
{
    $url = "https://multitopup.irpointcenter.com/topup/bundle/buy";
    $tracking_code= rand() . "";
    $response = wp_remote_post(
        $url,
        array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'method'      => 'POST',
            // 'data_format' => 'body',
            'body'  => json_encode (array(
                'username' => PANEL_USER_NAME,
                'password' => PANEL_PASSWORD,
                'cellNumber' =>  $mobile,
                'bundleId' => $bundle_id , //'61624',
                'amount' => $amount, //49050 //مبلغ خرید شارژ/ هزینه بسته
                'operatorType' => 0,
                'requestId' => $tracking_code,
                'reserveNumber' => $tracking_code . $mobile,
                'deviceType' => "Internet"
            )),
        )
    );

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return "Something went wrong: $error_message";
    } else {
        $body = wp_remote_retrieve_body($response);
        $decode = json_decode(stripslashes($body), true);
        $decode["trnState"] = $decode['code'];
        $decode["resultCode"] = $decode['code'];
        $decode["tracking_code"] = $tracking_code;
        return $decode;
    }
}



function buy_rightel_package($mobile)
{
    $url = "https://rightel.irpointcenter.com/rightel/buyBundle/";
    $package_list = get_rightel_package();
    $request_id = $package_list['requestId'];
    $tracking_code= rand() . "";
    $response = wp_remote_post(
        $url,
        array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'method'      => 'POST',
            'data_format' => 'body',
            'body'  => json_encode(
                array(
                    'user' => PANEL_USER_NAME,
                    'password' => PANEL_PASSWORD,
                    'mSISDN' =>  $mobile,
                    'chargeChannel' => '1',
                    'invoiceNumber' =>$tracking_code, //شماره فاکتور
                    'requestId' => $request_id, //شناسه درخواست
                    'bundleId' => '98112170', //شماره بسته انتخابی 
                )
            ),
        )
    );

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return "Something went wrong: $error_message";
    } else {
        $body = wp_remote_retrieve_body($response);
        $decode = json_decode(stripslashes($body), true);
        $decode["tracking_code"] = $tracking_code;
        return $decode;
    }
}



function get_rightel_package()
{
    $url = "https://rightel.irpointcenter.com/rightel/bundleList";

    $response = wp_remote_post(
        $url,
        array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'method'      => 'POST',
            'data_format' => 'body',
            'body'  => json_encode(array(
                'username' => PANEL_USER_NAME,
                'password' => PANEL_PASSWORD,
            )),
        )
    );

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return "Something went wrong: $error_message";
    } else {
        $body = wp_remote_retrieve_body($response);
        $decode = json_decode(stripslashes($body), true);
        return $decode;
    }
}

function generate_user_invitation_link($mobile){
    $user_hash = crc32($mobile);
    return 'https://fampayment.com/bus_station?referralCode=' . $user_hash;
}
function short_url($link,$title){
    $url = "https://yun.ir/api/v1/urls";
    $data = json_encode([
        "title" => $title,
        "url" => $link,
    ]);
    $response = wp_remote_post(
        $url,
        array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8','X-API-Key' => "1615:4xo5mrib7sg8ow48ggk4sco40s0wgoc",),
            'method'      => 'POST',
            'data_format' => 'body',
            'body'  => $data,
        )
    );

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return "Something went wrong: $error_message";
    } else {
        $body = wp_remote_retrieve_body($response);
        $decode = json_decode(stripslashes($body), true);
        return $decode;
    }
}