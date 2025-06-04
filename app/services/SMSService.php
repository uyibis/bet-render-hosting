<?php

class SMSService {

    public function sendSMS($userphone, $message)
    {
        $url = "http://www.smslive247.com/http/index.aspx?cmd=sendquickmsg&owneremail=olatunjiogunkomaya@yahoo.com&subacct=MYLAGOSAPP&subacctpwd=kokopikokoma344&message=$message&sender=BoardMan&sendto=$userphone&msgtype=0";
        //Start cURL
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => "$url",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            //CURLOPT_POSTFIELDS =>'{"From": "+12016902575", "To": "' .$userphone. '", "Body": "' .$message. '"}',
            //CURLOPT_HTTPHEADER => array(
            //    'AC492550fc0e68c72b516407355a6701af:4ef2a2d07e14b6f2317ed953edc0e54d',
            //),
            ));
            $response_output = curl_exec($curl);
            curl_close($curl);
        //End cURL
        return true;
    }
    
    public function sendWelcomeSMS($username, $userphone, $verificationcode)
    {
        $message = "Hello $username, thanks for registering on our platform and here is your verification code: $verificationcode";
        $response=$this->sendSMS($userphone, $message);
        return $response;
    }

    public function sendNewPasswordResetSMS($userphone, $userpassword)
    {
        $message = "Hello, you requested a password reset action on your account and here is your new password: $userpassword";
        $response=$this->sendSMS($userphone, $message);
        return $response;
    }

    public function sendPasswordRecoverySMS($userphone, $userpasswordresetcode)
    {
        $message = "Hello, you requested a password reset action on your account and here is your password reset code: $userpasswordresetcode";
        $response=$this->sendSMS($userphone, $message);
        return $response;
    }

    public function sendOrderNotificationCustomerSMS($userphone, $orderid)
    {
        $message = "Hello, you have a new transaction on our platform. Your transaction ID is: $orderid";
        $response=$this->sendSMS($userphone, $message);
        return $response;
    }
}
?>