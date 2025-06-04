<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;

class UsersController extends ControllerBase {    

    public function beforeExecuteRoute()
    {
        // Executed before every found action
        $rawBody = $this->request->getJsonRawBody(true);
        if($rawBody){
            foreach ($rawBody as $key => $value) {
                $_POST[$key] = $value;
            }
        }
    }

    public function indexAction()
    {
        //echo json_encode(array("status"=>"success","message"=>"Hello World!"));
    }

    public function registerAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $user = new Users();
        $generalService = new GeneralService();
        $emailerService = new EmailerService();
        $smsService = new SMSService();

        /** Validation Block **/
        try {
        //Username
        $data['username'] = $this->request->getPost('username');
        if (!is_string($data['username'])) {
            $errors['username'] = 'Username expected. Can consist of words or numbers';
        } else {
            //$data['username'] = $generalService->getSafeURL($data['username']);
            //list($data['firstname'], $data['lastname']) = explode(' ', $data['username']); //First & Last name
        }
        
        //Firstname
        $data['firstname'] = $this->request->getPost('firstname');
        // if (!is_string($data['firstname'])) {
        //     $errors['firstname'] = 'Firstname expected';
        // }

        //Lastname
        $data['lastname'] = $this->request->getPost('lastname');
        // if (!is_string($data['lastname'])) {
        //     $errors['lastname'] = 'Lastname expected';
        // }

        //Email
        $data['email'] = $this->request->getPost('email');
        if (!is_string($data['email'])) {
            $errors['email'] = 'Email expected';
        }

        //Phone
        $data['phone'] = $this->request->getPost('phone');
        if (!preg_match('/^[0-9_-]{3,16}$/', $data['phone'])) {
            $errors['phone'] = 'Phone expected';
        }

        //Password
        $data['password'] = $this->request->getPost('password');
        if (!is_string($data['password'])) {
            $errors['password'] = 'Password expected.';
        }

        //Password Confirm
        $data['passwordtwo'] = "";
        $data['passwordtwo'] = $this->request->getPost('passwordtwo');
        if (!is_string($data['passwordtwo']) || $data['password']!=$data['passwordtwo']) {
            $errors['passwordtwo'] = 'Both passwords does not match';
        }

        //Hash password
        $data['passwordhash'] = $this->security->hash($data['password']);

        //Address
        $data['address'] = $this->request->getPost('address');

        //City
        $data['city'] = $this->request->getPost('city');

        //State
        $data['state'] = $this->request->getPost('state');

        //Country
        $data['country'] = $this->request->getPost('country');

        //Postal zip code
        $data['postalzipcode'] = $this->request->getPost('postalzipcode');

        //Profile photo
        $data['photo'] = "files/avatar.png";
        if($this->request->hasFiles() == true){ 
            $extIMG = array(
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
            );
            foreach( $this->request->getUploadedFiles() as $file)
            {              
                // is it a valid extension?
                if ( in_array($file->getType() , $extIMG) && $file->getError() == 0 )
                {
                    $Name          = preg_replace("/[^A-Za-z0-9.]/", '-', $file->getName() );
                    $NewFileName=date("Ymdhis", strtotime("now")).rand(100,1000); //Random file new name
                    $FileExtension = $file->getExtension();
                    $FileName      = "files/" . $NewFileName . "." . $FileExtension;
                    if(!$file->moveTo($FileName)) { // move file to needed path";
                        return json_encode(array("status"=>"failed","message"=>"Error uploading photo"));
                    } else {
                        $data['photo'] = "$FileName";
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        }

        //Referrer code
        $data['referrercode'] = $this->request->getPost('referrercode');

        //Referral code
        $data['referralcode'] = rand ( 10000 , 99999 ); //Referral code

        //Verification code
        $data['verificationcode'] = $generalService->generate_code('9'); //Verification code

        //Verification method
        $data['verificationmethod'] = $this->request->getPost('verificationmethod');        
        if (!is_string($data['verificationmethod'])) {
            $data['verificationmethod'] = 'email';
        }

        //Account type
        $data['type'] = "player";

        //Date of Birth
        $data['dob'] = $this->request->getPost('dob');
        if (!is_string($data['dob'])) {
            $errors['dob'] = 'Date of birth required';
        }
        //Date of Birth Control //date('d-m-Y',strtotime($data['dob']));
        $birthday = strtotime($data['dob']);
        $ageLimit=18;
        if(time() - $birthday < $ageLimit * 31536000)  {
            $errors['dob'] = 'This site requires +18yrs';
        } 

        //Get current date and time
        $data['date'] = date("d-m-Y");
        $data['time'] = date('h:i A');

        // Check if User already exist in the Database
        $existResult=$user->checkUserExist($data);

        //Error form handling check and Submit Data
        if($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } elseif ($existResult >= 1) {
        //Already exist
            return json_encode(array("status"=>"failed","message"=>"Duplicate entry. Username, email or phone already exist."));
        } else {
        // Store to Database Model and check for errors
            $result=$user->addUser($data);
            if ($result) {
                //Success
                $emailerService->sendWelcomeEmail($data['username'], $data['email'], $data['verificationcode'], $data['password']);
                $smsService->sendWelcomeSMS($data['username'], $data['phone'], $data['verificationcode']);
                //Proceed to Login The New User
                $userData=$user->singleUser($data['username']); //Get User data from the Database
                $userid=$userData->id;
                $username=$userData->username;
                $useremail=$userData->email;
                $userimage=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
                $accounttype=$userData->type;
                $logintoken=$generalService->generate_code('15'); //Auth login token
                $logindevice=$userData->logindevice;
                $lastlogindate=$userData->lastlogindate;
                $newdate=date("d-m-Y");
                $user->updateUserLoginToken($data['username'], $logintoken, $newdate); //Update User DB with generated token and last login date
                echo json_encode(array("status"=>"success","message"=>"Registration successful. Please check your email to proceed!"));
            } else {
                //Failed
                echo json_encode(array("status"=>"failed","message"=>"Error occurred on database. Please try again"));
            }            
        }
        } catch(Exception $e) {
            return json_encode(array("status"=>"failed","message"=>$e->getMessage()));
        }
    }

    public function loginAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $user = new Users();
        $generalService = new GeneralService();

        /** Validation Block **/
        //Username
        $data['username'] = $this->request->getPost('username');
        if (!is_string($data['username'])) {
            $errors['username'] = 'Username expected.';
        }

        //Password
        $data['password'] = $this->request->getPost('password');
        if (!is_string($data['password'])) {
            $errors['password'] = 'Password expected.';
        }

        // Check if User already exist in the Database
        $existResult=$user->checkUserDataExist($data['username']);

        // Check if User is verified via the email verification link sent in the Database
        /**$emailVerifiedResult=$user->checkUserEmailVerified($data['username']);
        if ($emailVerifiedResult != 1) {
            return json_encode(array("status"=>"failed","message"=>"Account not verified. Please do check your email for verification link"));
        }**/

        // Check if User is verified via the phone verification code sent in the Database
        //$existResult=$user->checkUserPhoneVerified($data['username']);

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 
        
        //Already exist
        if ($existResult >= 1) {
            // Get User data from the Database
            $userData=$user->singleUser($data['username']);
            $userPasswordHash=$userData->password;
            //Compare password with hashed password
            if ($this->security->checkHash($data['password'], $userPasswordHash)) {
                // The password is valid
                $userid=$userData->id;
                $username=$userData->username;
                $useremail=$userData->email;
                $userimage=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
                $accounttype=$userData->type;
                $logindevice=$userData->logindevice;
                $lastlogindate=$userData->lastlogindate;
                $logintoken=$userData->logintoken;
                if(!$logintoken){
                    $logintoken=$generalService->generate_code('15'); //Auth login token
                }
                $newdate=date("d-m-Y");
                $user->updateUserLoginToken($data['username'], $logintoken, $newdate); //Update User DB with generated token and last login date
                echo json_encode(array("status"=>"success","message"=>"login successful","userid"=>"$userid","username"=>"$username","useremail"=>"$useremail","userimage"=>"$userimage","accounttype"=>"$accounttype","logintoken"=>"$logintoken","logindevice"=>"$logindevice"));
            } else {
                //Invalid login attempt
                echo json_encode(array("status"=>"failed","message"=>"Invalid login credentials"));
            }
        } else {
            //Invalid login attempt
            echo json_encode(array("status"=>"failed","message"=>"Invalid login attempt"));
        }
    }

    public function forgotpasswordAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $user = new Users();
        $emailerService = new EmailerService();
        $generalService = new GeneralService();

        /** Validation Block **/
        //Email
        $data['email'] = $this->request->getPost('email');
        if (!is_string($data['email'])) {
            $errors['email'] = 'Email expected';
        }

        //Reset code
        $data['resetcode'] = $this->request->getPost('resetcode');

        //Today's date
        $newdate=date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        //Reset password if reset code is available
        if ($data['resetcode'] && is_string($data['resetcode'])) {
            //Reset pass
            $validResetCode=$user->verifyUserPasswordResetCode($data['email'], $data['resetcode'], $newdate); //Verify User provided password reset code and today's expiry date
            if ($validResetCode >= 1) {
                //User reset code is valid
                $newpassword=$generalService->generate_code('6'); //New temp password
                $newpasswordhash=$this->security->hash($newpassword);
                $user->resetUserPassword($data['email'], $newpasswordhash); //Clear User password reset code and update with new password
                $emailerService->sendNewPasswordResetEmail($data['email'], $newpassword);
                echo json_encode(array("status"=>"success","message"=>"New password sent to your email"));
            } else {
                //User reset code not valid
                echo json_encode(array("status"=>"failed","message"=>"Provided reset code not valid or expired"));
            }
        } else {
            //check if user exist and send pass recovery code to user
            $existResult=$user->checkUserDataExist($data['email']);
            if ($existResult >= 1) {
                $passwordresetcode=rand(1000,10000); //Reset code
                $user->updateUserPasswordResetCode($data['email'], $passwordresetcode, $newdate); //Update User DB with generated password reset code and today's expiry date
                //User reset code sent
                $emailerService->sendPasswordRecoveryEmail($data['email'], $passwordresetcode);
                echo json_encode(array("status"=>"success","message"=>"Password reset code sent to your email"));
            } else {
                //User does not exist
                echo json_encode(array("status"=>"failed","message"=>"User does not exist"));
            }
        }
    }

    public function changeresetpasswordAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $user = new Users();
        $emailerService = new EmailerService();

        /** Validation Block **/
        //Email
        $data['email'] = $this->request->getPost('email');
        if (!is_string($data['email'])) {
            $errors['email'] = 'Email expected';
        }

        //Reset code
        $data['resetcode'] = $this->request->getPost('resetcode');

        //Today's date
        $newdate=date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        //Reset password if reset code is available
        if ($data['resetcode'] && is_string($data['resetcode'])) {
            //Reset Pass
            //Password
            $data['password'] = $this->request->getPost('password');
            if (!is_string($data['password'])) {
                $errors['password'] = 'Password expected.';
            }

            //Password Confirm
            $data['passwordtwo'] = $this->request->getPost('passwordtwo');
            if (!is_string($data['passwordtwo']) || $data['password']!=$data['passwordtwo']) {
                $errors['passwordtwo'] = 'Both passwords does not match';
            }

            //Hash password
            $data['passwordhash'] = $this->security->hash($data['password']);

            $validResetCode=$user->verifyUserPasswordResetCode($data['email'], $data['resetcode'], $newdate); //Verify User provided password reset code and today's expiry date
            if ($validResetCode >= 1) {
                //User reset code is valid
                $newpasswordhash=$data['passwordhash']; //New password
                $user->resetUserPassword($data['email'], $newpasswordhash); //Clear User password reset code and update with new password
                $emailerService->sendNewPasswordResetEmail($data['email'], $data['password']);
                echo json_encode(array("status"=>"success","message"=>"Password changed"));
            } else {
                //User reset code not valid
                echo json_encode(array("status"=>"failed","message"=>"Provided reset code not valid or expired"));
            }
        } else {
            //check if user exist and send pass recovery code to user
            $existResult=$user->checkUserDataExist($data['email']);
            if ($existResult >= 1) {
                $passwordresetcode=rand(1000,10000); //Reset code
                $user->updateUserPasswordResetCode($data['email'], $passwordresetcode, $newdate); //Update User DB with generated password reset code and today's expiry date
                //User reset code sent
                $emailerService->sendPasswordResetLinkEmail($data['email'], $passwordresetcode);
                echo json_encode(array("status"=>"success","message"=>"Password reset link sent to your email"));
            } else {
                //User does not exist
                echo json_encode(array("status"=>"failed","message"=>"User does not exist"));
            }
        }
    }

    public function verifyaccountAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $user = new Users();
        $emailerService = new EmailerService();

        /** Validation Block **/
        //UserID
        $data['userid'] = $this->request->getPost('userid');
        if (!is_string($data['userid'])) {
            $errors['userid'] = 'UserID expected';
        }

        //Verification Code
        $data['verificationcode'] = $this->request->getPost('verificationcode');
        if (!is_string($data['verificationcode'])) {
            $errors['verificationcode'] = 'Verification code expected';
        }

        //Error form handling check and Submit Data
        if ($errors) {
            echo json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        //check if user exist and send pass recovery code to user
        $existResult=$user->checkUserDataExist($data['userid']);
        if ($existResult >= 1) {
            $verifyResult=$user->verifyUserAccount($data['userid'], $data['verificationcode']); //Verify User account if code is valid
            if ($verifyResult >= 1) {
                echo json_encode(array("status"=>"success","message"=>"Account verified and activated successfully"));
            } else {
                echo json_encode(array("status"=>"failed","message"=>"Account activation code invalid!"));
            }
        } else {
            //User does not exist
            echo json_encode(array("status"=>"failed","message"=>"User does not exist"));
        }
    
    }

    public function accountdetailsAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();
        $games = new Games();
        $gamebets = new Gamebets();
        $transactions = new Transactions();
        $notifications = new Notifications();
        $itemList = array();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 
        
        //Passed the User Auth Check, Proceed with the Business Logic
        $userData=$user->singleUser($this->request->getPost('userid')); // Get User data from the Database
        $itemList['id']=$userData->id;
        $itemList['userid']=$userData->id;
        $itemList['username']=$userData->username;
        $itemList['firstname']=$userData->firstname;
        $itemList['lastname']=$userData->lastname;
        $itemList['userimage']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
        $itemList['useremail']=$userData->email;
        $itemList['userphone']=$userData->phone;
        $itemList['accounttype']=$userData->type;
        $itemList['address']=$userData->address;
        $itemList['city']=$userData->city;
        $itemList['state']=$userData->state;
        $itemList['country']=$userData->country;
        $itemList['postalzipcode']=$userData->postalzipcode;
        $itemList['bankname']=$userData->bankname;
        $itemList['bankaccountname']=$userData->bankaccountname;
        $itemList['bankaccountnumber']=$userData->bankaccountnumber;
        $itemList['bankcode']=$userData->bankcode;
        $transactionTotalResultData=$gamebets->sumBetPendingAmountByUser($this->request->getPost('userid')); // Get User Total Orders Sum data from the Database
        $totalpending_amount=$transactionTotalResultData->totalsum;
        $itemList['walletbalance']=$userData->walletbalance-$totalpending_amount; //Remove pending bet amount from wallet balance to get Available Balance
        $itemList['referralcode']=$userData->referralcode;
        $itemList['referrercode']=$userData->referrercode;
        $itemList['lastlogindate']=$userData->lastlogindate;
        $itemList['totalusersreferred']=$user->sumReferredUsers($itemList['referralcode']); // Get User Total Referrals Sum data from the Database
        $transactionTotalResultData=$transactions->sumUserTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
        $itemList['totaltransactions']=$transactionTotalResultData->totalsum;
        $transactionTotalResultData=$transactions->sumUserWithdrawalTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
        $itemList['totalpayout']=$transactionTotalResultData->totalsum;
        $transactionTotalResultData=$transactions->sumUserDepositTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
        $itemList['totaldeposits']=$transactionTotalResultData->totalsum;
        $transactionTotalResultData=$transactions->sumUserWinTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
        $itemList['totalwinnings']=$transactionTotalResultData->totalsum;
        $transactionTotalResultData=$transactions->sumUserLossTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
        $itemList['totalloss']=$transactionTotalResultData->totalsum;
        $itemList['notifications']=$notifications->sumNotificationsByUser($itemList['userid']);
        echo json_encode($itemList);  
    }

    public function accountupdateAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();
        $data = [];

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        if($this->request->getPost('firstname')){ //Firstname change check
            $data['firstname'] = $this->request->getPost('firstname');
            if (is_string($data['firstname'])) {
                $user->updateUserAccount($this->request->getPost('userid'), $this->request->getPost('firstname'), "firstname"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Firstname must be a string"));
            }
        }

        if($this->request->getPost('lastname')){ //Lastname change check
            $data['lastname'] = $this->request->getPost('lastname');
            if (is_string($data['lastname'])) {
                $user->updateUserAccount($this->request->getPost('userid'), $this->request->getPost('lastname'), "lastname"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Lastname must be a string"));
            }
        }

        if($this->request->getPost('phone')){ //Phone change check
            $data['phone'] = $this->request->getPost('phone');
            if (preg_match('/^[0-9_-]{3,16}$/', $data['phone'])) {
                $user->updateUserAccount($this->request->getPost('userid'), $this->request->getPost('phone'), "phone"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Phone must be numbers only"));
            }
        }

        if($this->request->getPost('address')){ //Address change check
            $data['address'] = $this->request->getPost('address');
            if (is_string($data['address'])) {
                $user->updateUserAccount($this->request->getPost('userid'), $this->request->getPost('address'), "address"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Address must be a string"));
            }
        }

        if($this->request->getPost('city')){ //City change check
            $data['city'] = $this->request->getPost('city');
            if (is_string($data['city'])) {
                $user->updateUserAccount($this->request->getPost('userid'), $this->request->getPost('city'), "city"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"City must be a string"));
            }
        }

        if($this->request->getPost('state')){ //State change check
            $data['state'] = $this->request->getPost('state');
            if (is_string($data['state'])) {
                $user->updateUserAccount($this->request->getPost('userid'), $this->request->getPost('state'), "state"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"State must be a string"));
            }
        }

        if($this->request->getPost('country')){ //Country change check
            $data['country'] = $this->request->getPost('country');
            if (is_string($data['country'])) {
                $user->updateUserAccount($this->request->getPost('userid'), $this->request->getPost('country'), "country"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Country must be a string"));
            }
        }

        if($this->request->getPost('postalzipcode')){ //Postalzipcode change check
            $data['postalzipcode'] = $this->request->getPost('postalzipcode');
            if (is_string($data['postalzipcode'])) {
                $user->updateUserAccount($this->request->getPost('userid'), $this->request->getPost('postalzipcode'), "postalzipcode"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Postal/Zip code must be a string"));
            }
        }
        
        if($this->request->getPost('password')){ //Password change check
            $data['password'] = $this->request->getPost('password');
            if (is_string($data['password'])) {
                $data['passwordhash'] = $this->security->hash($data['password']); //Hash password
                $user->updateUserAccount($this->request->getPost('userid'), $data['passwordhash'], "password"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Password expected. Must consist of words, numbers or symbols"));
            }
        }

        if($this->request->getPost('bankname')){ //Bank name change check
            $data['bankname'] = $this->request->getPost('bankname');
            if (is_string($data['bankname'])) {
                $user->updateUserAccount($this->request->getPost('userid'), $this->request->getPost('bankname'), "bankname"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Bank name must be a string"));
            }
        }

        if($this->request->getPost('bankaccountname')){ //Bank account name change check
            $data['bankaccountname'] = $this->request->getPost('bankaccountname');
            if (is_string($data['bankaccountname'])) {
                $user->updateUserAccount($this->request->getPost('userid'), $this->request->getPost('bankaccountname'), "bankaccountname"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Bank account name must be a string"));
            }
        }

        if($this->request->getPost('bankaccountnumber')){ //Bank account number change check
            $data['bankaccountnumber'] = $this->request->getPost('bankaccountnumber');
            if (is_string($data['bankaccountnumber'])) {
                $user->updateUserAccount($this->request->getPost('userid'), $this->request->getPost('bankaccountnumber'), "bankaccountnumber"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Bank account number must be a string"));
            }
        }

        if($this->request->getPost('bankswiftsortcode')){ //Bank swift/sort code change check
            $data['bankswiftsortcode'] = $this->request->getPost('bankswiftsortcode');
            if (is_string($data['bankswiftsortcode'])) {
                $user->updateUserAccount($this->request->getPost('userid'), $this->request->getPost('bankswiftsortcode'), "bankswiftsortcode"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Bank swift/sort code must be a string"));
            }
        }

        if($this->request->getPost('bankcode')){ //Bank paystack code change check
            $data['bankcode'] = $this->request->getPost('bankcode');
            if (is_string($data['bankcode'])) {
                $user->updateUserAccount($this->request->getPost('userid'), $this->request->getPost('bankcode'), "bankcode"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Bank code must be a string"));
            }

            try {
                //Update User PayStack TRANSFER RECIPIENT account
                $user_bankaccountnumber=$this->request->getPost('bankaccountnumber');
                $user_bankaccountname=$this->request->getPost('bankaccountname');
                $user_bankname=$this->request->getPost('bankname');
                $user_bankcode=$this->request->getPost('bankcode');
                $userData=$user->singleUser($this->request->getPost('userid')); // Get User data from the Database
                $user_name=$userData->username;
                $user_firstname=$userData->firstname;
                $user_lastname=$userData->lastname;
                $user_fullname="$user_firstname $user_lastname";
                $user_email=$userData->email;

                //Verify account name against provided bank account name for security
                if(empty($user_firstname) || empty($user_lastname)) { 
                    return json_encode(array("status"=>"failed","message"=>"Your profile name hasn't be updated yet. You need to update your account first"));
                }
                if(strpos($user_bankaccountname, $user_firstname) == false && strpos($user_bankaccountname, $user_lastname) == false) { 
                    return json_encode(array("status"=>"failed","message"=>"Your profile name and your bank account name doesn't match"));
                }

                //Start cURL
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.paystack.co/transferrecipient",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>'{"type": "nuban", "description": "User account", "name": "' .$user_bankaccountname. '", "account_number": "' .$user_bankaccountnumber. '", "bank_code": "' .$user_bankcode. '", "currency": "NGN"}',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer sk_live_604b3fed49de98c2e64360105aeca50e434c28ee'
                ),
                ));
                $response_output = curl_exec($curl);
                curl_close($curl);
                //End cURL

                if ($response_output) {
                    $result = json_decode($response_output, true);
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Error connecting to your bank server"));
                }

                if ( array_key_exists('data', $result) ) {
                    $user_recipient_code = $result['data']['recipient_code'];
                    $user->updateUserAccount($this->request->getPost('userid'), $user_recipient_code, "paystackrecipientcode"); //Update User account
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Invalid bank details. Please check your details again"));
                }
            } catch(Exception $e) {
                return json_encode(array("status"=>"failed","message"=>$e->getMessage()));
            } 
        }

        if($this->request->hasFiles() == true){ //Photo change check
            $extIMG = array(
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
            );
            foreach( $this->request->getUploadedFiles() as $file)
            {              
                // is it a valid extension?
                if ( in_array($file->getType() , $extIMG) && $file->getError() == 0 )
                {
                    $Name          = preg_replace("/[^A-Za-z0-9.]/", '-', $file->getName() );
                    $NewFileName=date("Ymdhis", strtotime("now")).rand(100,1000); //Random file new name
                    $FileExtension = $file->getExtension();
                    $FileName      = "files/" . $NewFileName . "." . $FileExtension;
                    if(!$file->moveTo($FileName)) { // move file to needed path";
                        return json_encode(array("status"=>"failed","message"=>"Error uploading photo"));
                    } else {
                        $user->updateUserAccount($this->request->getPost('userid'), $FileName, "photo"); //Update User account details changes
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        }
        
        echo json_encode(array("status"=>"success","message"=>"Account updated successfully"));
    }

    public function dashboarddataAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();
        $transactions = new Transactions();
        $games = new Games();
        $gamebets = new Gamebets();
        $itemList = array();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        try {
            $userData=$user->singleUser($this->request->getPost('userid')); // Get User data from the Database
            $itemList['id']=$userData->id;
            $itemList['userid']=$userData->id;
            $itemList['username']=$userData->username;
            $itemList['useremail']=$userData->email;
            $itemList['userphone']=$userData->phone;
            $itemList['accounttype']=$userData->type;
            $itemList['referralcode']=$userData->referralcode;
            //Transaction Records
            $itemList['totalusersreferred']=$user->sumReferredUsers($itemList['referralcode']); // Get User Total Referrals Sum data from the Database
            $transactionTotalResultData=$transactions->sumUserTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
            $itemList['totaltransactions']=$transactionTotalResultData->totalsum;
            $transactionTotalResultData=$transactions->sumUserWithdrawalTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
            $itemList['totalpayout']=$transactionTotalResultData->totalsum;
            $transactionTotalResultData=$transactions->sumUserDepositTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
            $itemList['totaldeposits']=$transactionTotalResultData->totalsum;
            $transactionTotalResultData=$transactions->sumUserWinTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
            $itemList['totalwinnings']=$transactionTotalResultData->totalsum;
            $transactionTotalResultData=$transactions->sumUserLossTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
            $itemList['totalloss']=$transactionTotalResultData->totalsum;
            
            //Graph Reports 
            //Set Dates 
            $currentmonth = date("m-Y"); //current month
            $currentmonthLABEL = date("M"); //current month
            $lastmonthone= date('m-Y', strtotime("-1 month")); //1 month ago
            $lastmonthoneLABEL = date("M", strtotime("-1 month")); //1 month ago
            $lastmonthtwo= date('m-Y', strtotime("-2 month")); //2 month ago
            $lastmonthtwoLABEL = date("M", strtotime("-2 month")); //2 month ago
            $lastmonththree= date('m-Y', strtotime("-3 month")); //3 month ago
            $lastmonththreeLABEL = date("M", strtotime("-3 month")); //3 month ago
            $lastmonthfour= date('m-Y', strtotime("-4 month")); //4 month ago
            $lastmonthfourLABEL = date("M", strtotime("-4 month")); //4 month ago
            $lastmonthfive= date('m-Y', strtotime("-5 month")); //5 month ago
            $lastmonthfiveLABEL = date("M", strtotime("-5 month")); //5 month ago

            //Game Bets Counts
            $currentmonth_RecordCOUNT=$gamebets->betsCOUNTByUser($currentmonth,$this->request->getPost('userid')); // Get data for graph chart
            $lastmonthone_RecordCOUNT=$gamebets->betsCOUNTByUser($lastmonthone,$this->request->getPost('userid')); // Get data for graph chart
            $lastmonthtwo_RecordCOUNT=$gamebets->betsCOUNTByUser($lastmonthtwo,$this->request->getPost('userid')); // Get data for graph chart
            $lastmonththree_RecordCOUNT=$gamebets->betsCOUNTByUser($lastmonththree,$this->request->getPost('userid')); // Get data for graph chart
            $lastmonthfour_RecordCOUNT=$gamebets->betsCOUNTByUser($lastmonthfour,$this->request->getPost('userid')); // Get data for graph chart
            $lastmonthfive_RecordCOUNT=$gamebets->betsCOUNTByUser($lastmonthfive,$this->request->getPost('userid')); // Get data for graph chart
            $GameBetsCounts_GraphData = array
            (
            array("$currentmonthLABEL","$currentmonth_RecordCOUNT"),
            array("$lastmonthoneLABEL","$lastmonthone_RecordCOUNT"),
            array("$lastmonthtwoLABEL","$lastmonthtwo_RecordCOUNT"),
            array("$lastmonththreeLABEL","$lastmonththree_RecordCOUNT"),
            array("$lastmonthfourLABEL","$lastmonthfour_RecordCOUNT"),
            array("$lastmonthfiveLABEL","$lastmonthfive_RecordCOUNT")
            );

            //Transaction Statistics Counts
            $paid_TransactionsCOUNT=$transactions->transactionsPaymentStatisticsCOUNTByCustomer("paid",$this->request->getPost('userid')); // Get data for pie-chart graph
            $unpaid_TransactionsCOUNT=$transactions->transactionsPaymentStatisticsCOUNTByCustomer("unpaid",$this->request->getPost('userid')); // Get data for pie-chart graph
            $refunded_TransactionsCOUNT=$transactions->transactionsPaymentStatisticsCOUNTByCustomer("refunded",$this->request->getPost('userid')); // Get data for pie-chart graph
            $win_RecordCOUNT=$gamebets->betsStatisticsCOUNTByUser("win",$this->request->getPost('userid')); // Get data for pie-chart graph
            $loss_RecordCOUNT=$gamebets->betsStatisticsCOUNTByUser("loss",$this->request->getPost('userid')); // Get data for pie-chart graph
            $draw_RecordCOUNT=$gamebets->betsStatisticsCOUNTByUser("draw",$this->request->getPost('userid')); // Get data for pie-chart graph
            $transactionsPaymentsStatisticsData = array
            (
            array("Paid","$paid_TransactionsCOUNT"),
            array("Unpaid","$unpaid_TransactionsCOUNT"),
            array("Refunded","$refunded_TransactionsCOUNT")
            );
            $transactionsBetsStatisticsData = array
            (
            array("Win","$win_RecordCOUNT"),
            array("Loss","$loss_RecordCOUNT"),
            array("Draw","$draw_RecordCOUNT")
            );

            //Graph Data
            $itemList['gamesgraphdata']=$GameBetsCounts_GraphData; //Graph Data
            $itemList['betsstatisticsdata']=$transactionsBetsStatisticsData; //Graph Data
            $itemList['paymentstatisticsdata']=$transactionsPaymentsStatisticsData; //Graph Data
            echo json_encode($itemList);  
        } catch(Exception $e) {
            return json_encode(array("status"=>"failed","message"=>$e->getMessage()));
        } 
    }

    public function allAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$user->allUsers(); // Get User data from the Database
        foreach($resultData as $userData) {
            $itemList['userid']=$userData->id;
            $itemList['username']=$userData->username;
            $itemList['firstname']=$userData->firstname;
            $itemList['lastname']=$userData->lastname;
            $itemList['userimage']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
            $itemList['useremail']=$userData->email;
            $itemList['userphone']=$userData->phone;
            $itemList['accounttype']=$userData->type;
            $itemList['address']=$userData->address;
            $itemList['city']=$userData->city;
            $itemList['state']=$userData->state;
            $itemList['country']=$userData->country;
            $itemList['postalzipcode']=$userData->postalzipcode;
            $itemList['walletbalance']=$userData->walletbalance;
            $itemList['referralcode']=$userData->referralcode;
            $itemList['referrercode']=$userData->referrercode;
            $itemList['lastlogindate']=$userData->lastlogindate;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);     
    }

    public function searchAllAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$user->searchUser($this->request->getPost('query')); // Get User data from the Database
        foreach($resultData as $userData) {
            $itemList['userid']=$userData->id;
            $itemList['username']=$userData->username;
            $itemList['firstname']=$userData->firstname;
            $itemList['lastname']=$userData->lastname;
            $itemList['userimage']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
            $itemList['useremail']=$userData->email;
            $itemList['userphone']=$userData->phone;
            $itemList['accounttype']=$userData->type;
            $itemList['walletbalance']=$userData->walletbalance;
            $itemList['address']=$userData->address;
            $itemList['city']=$userData->city;
            $itemList['state']=$userData->state;
            $itemList['country']=$userData->country;
            $itemList['referralcode']=$userData->referralcode;
            $itemList['referrercode']=$userData->referrercode;
            $itemList['lastlogindate']=$userData->lastlogindate;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists); 
    }

    public function notificationsAction($type=all)
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();
        $notifications = new Notifications();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        if($type=="all"){
            $resultData=$notifications->allEntriesByUser($this->request->getPost('userid')); // Get data from the Database
        } else {
            $resultData=$notifications->allEntriesTypeByUser($this->request->getPost('userid'),$type); // Get User data from the Database
        }
        foreach($resultData as $userData) {
            $itemList['userid']=$userData->id;
            $itemList['username']=$userData->username;
            $itemList['title']=$userData->title;
            $itemList['type']=$userData->type;
            $itemList['details']=$userData->details;
            $itemList['fromid']=$userData->fromid;
            $itemList['from']=$userData->from;
            $itemList['actionid']=$userData->actionid;
            $itemList['actionsubid']=$userData->actionsubid;
            $itemList['status']=$userData->status;
            $itemList['time']=$userData->time;
            $itemList['date']=$userData->date;
            $itemLists[] = $itemList;
            $notifications->updateEntryReadByUser($this->request->getPost('userid')); //Update details changes
        }
        echo json_encode($itemLists); 
    }

    public function addnotificationAction($type=all)
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();
        $notifications = new Notifications();
        $errors = [];
        $data = [];

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        //User data
        $data['userid'] = $this->request->getPost('userid');
        $data['username'] = $this->request->getPost('username');

        //title
        $data['title'] = $this->request->getPost('title');
        if (!is_string($data['title'])) {
            $errors['title'] = 'Title expected';
        }

        //details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Details expected';
        }

        //type
        $data['details'] = $type;

        //status
        $data['status'] = "unread";

        //from
        $data['fromid'] = "";
        $data['from'] = "";

        //Get current date and time
        $data['time'] = date("h:i:sa");
        $data['date'] = date("d-m-Y");

        //Add to Database
        $notifications->addEntry($data);
        echo json_encode(array("status"=>"success","message"=>"Emergency added successful"));
    }

    public function updatenotificationsAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();
        $notifications = new Notifications();
        $errors = [];
        $data = [];

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $notifications->updateEntryReadByUser($this->request->getPost('userid')); //Update details changes
        echo json_encode(array("status"=>"success","message"=>"Notifications updated successfully"));
    }

    public function referrersAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$user->allReferrers(); // Get User data from the Database
        foreach($resultData as $userData) {
            $itemList['userid']=$userData->id;
            $itemList['username']=$userData->username;
            $itemList['firstname']=$userData->firstname;
            $itemList['lastname']=$userData->lastname;
            $itemList['userimage']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
            $itemList['useremail']=$userData->email;
            $itemList['userphone']=$userData->phone;
            $itemList['accounttype']=$userData->type;
            $itemList['address']=$userData->address;
            $itemList['city']=$userData->city;
            $itemList['state']=$userData->state;
            $itemList['country']=$userData->country;
            $itemList['postalzipcode']=$userData->postalzipcode;
            $itemList['deliveryoption']=$userData->deliveryoption;
            $itemList['paymentoption']=$userData->paymentoption;
            $itemList['referralcode']=$userData->referralcode;
            $itemList['referrercode']=$userData->referrercode;
            $itemList['lastlogindate']=$userData->lastlogindate;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);     
    }

    public function referralsAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();
        $transactions = new Transactions();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $userData=$user->singleUser($this->request->getPost('userid')); // Get User data from the Database
        $referralcode=$userData->referralcode;
        $referrercode=$userData->referrercode;
        $resultData=$user->referredUsers($referralcode); // Get User data from the Database
        foreach($resultData as $userData) {
            $itemList['type']="Player";
            $itemList['userid']=$userData->id;
            $itemList['username']=$userData->username;
            $itemList['firstname']=$userData->firstname;
            $itemList['lastname']=$userData->lastname;
            $itemList['userimage']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
            $itemList['useremail']=$userData->email;
            $itemList['userphone']=$userData->phone;
            $itemList['accounttype']=$userData->type;
            $itemList['address']=$userData->address;
            $itemList['city']=$userData->city;
            $itemList['state']=$userData->state;
            $itemList['country']=$userData->country;
            $itemList['postalzipcode']=$userData->postalzipcode;
            $itemList['details']=$userData->details;
            $itemList['referralcode']=$userData->referralcode;
            $itemList['referrercode']=$userData->referrercode;
            $itemList['lastlogindate']=$userData->lastlogindate;
            $transactionTotalResultData=$transactions->sumUserWithdrawalTransactions($itemList['userid']);
            $itemList['totalpayout']=$transactionTotalResultData->totalsum;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists); 
    }

    public function leaderboardAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();
        $games = new Games();
        $gamebets = new Gamebets();
        $transactions = new Transactions();

        //Auth Check
        /**$authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } **/

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$user->allTopUsers(); // Get User data from the Database
        foreach($resultData as $userData) {
            $itemList['type']="Player";
            $itemList['userid']=$userData->id;
            $itemList['username']=$userData->username;
            $itemList['firstname']=$userData->firstname;
            $itemList['lastname']=$userData->lastname;
            $itemList['userimage']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
            $itemList['useremail']=$userData->email;
            $itemList['userphone']=$userData->phone;
            $itemList['accounttype']=$userData->type;
            $itemList['address']=$userData->address;
            $itemList['city']=$userData->city;
            $itemList['state']=$userData->state;
            $itemList['country']=$userData->country;
            $itemList['postalzipcode']=$userData->postalzipcode;
            $itemList['details']=$userData->details;
            $itemList['ranking']=$userData->ranking;
            $itemList['userpoints']=$userData->points;
            $itemList['referralcode']=$userData->referralcode;
            $itemList['referrercode']=$userData->referrercode;
            $itemList['lastlogindate']=$userData->lastlogindate;            
            $itemList['userwins']=$gamebets->sumBetWinsByUser($itemList['userid']); // Get User Total Referrals Sum data from the Database
            $itemList['userloss']=$gamebets->sumBetLossByUser($itemList['userid']); // Get User Total Referrals Sum data from the Database
            $itemList['userdraws']=$gamebets->sumBetDrawsByUser($itemList['userid']); // Get User Total Referrals Sum data from the Database
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists); 
    }

    public function addreviewAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $emailerService = new EmailerService();
        $errors = [];
        $data = [];
        $reviews = new Reviews(); 

        /** Validation Block **/
        //playerid
        $data['playerid'] = $this->request->getPost('playerid');
        if (!is_string($data['playerid'])) {
            $errors['playerid'] = 'User ID expected';
        }
        
        //reviewer name
        $data['name'] = $this->request->getPost('name');
        if (!is_string($data['name'])) {
            $errors['name'] = 'Name expected';
        }

        //reviewer email
        $data['email'] = $this->request->getPost('email');
        if (!is_string($data['email'])) {
            $errors['email'] = 'Email expected';
        }

        //reviewer phone
        $data['phone'] = $this->request->getPost('phone');

        //status
        $data['status'] = "pending";

        //reviewer rating
        $data['rating'] = $this->request->getPost('rating');
        if (!is_string($data['rating'])) {
            $errors['rating'] = 'Rating expected';
        } 
        
        //reviewer details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Details expected';
        }       
        
        //reviewer photo
        $data['photo'] = "files/avatar.png";
        if($this->request->hasFiles() == true){ 
            $extIMG = array(
                'image/jpeg',
                'image/png',
                'image/gif',
            );
            foreach( $this->request->getUploadedFiles() as $file)
            {              
                // is it a valid extension?
                if ( in_array($file->getType() , $extIMG) && $file->getError() == 0 )
                {
                    $Name          = preg_replace("/[^A-Za-z0-9.]/", '-', $file->getName() );
                    $NewFileName=date("Ymdhis", strtotime("now")).rand(100,1000); //Random file new name
                    $FileExtension = $file->getExtension();
                    $FileName      = "files/" . $NewFileName . "." . $FileExtension;
                    if(!$file->moveTo($FileName)) { // move file to needed path";
                        return json_encode(array("status"=>"failed","message"=>"Error uploading photo"));
                    } else {
                        $data['photo'] = "$FileName";
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        } 

        //Get current date and time
        $data['date'] = date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        // Store to Database Model and check for errors
        $reviews->addReview($data);
        //$emailerService->sendAdminNewProductReviewAlertEmail($data['productid'], $data['name'], $data['email'], $data['details']);
        echo json_encode(array("status"=>"success","message"=>"Player review and rating added successfully"));
    }

    public function reviewsAction($playerid)
    {
        /** Init Block **/
        $reviews = new Reviews(); 

        /** Logic Block **/
        $itemLists = array();
        $itemList = array();
        $resultData=$reviews->allReviewsByPlayer($playerid); // Get product reviews data from the Database
        foreach($resultData as $productData) {
            $itemList['id']=$productData->id;
            $itemList['playerid']=$productData->playerid;
            $itemList['name']=$productData->name;
            $itemList['email']=$productData->email;
            $itemList['phone']=$productData->phone;
            $itemList['photo']=$this->getDi()->getShared('siteurl').'/'.$productData->photo;
            $itemList['rating']=$productData->rating;
            $itemList['status']=$productData->status;
            $itemList['details']=$productData->details;
            $itemList['date']=$productData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);
    }

    public function profileAction($userid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();
        $games = new Games();
        $gamebets = new Gamebets();
        $transactions = new Transactions();
        $itemList = array();

        //Auth Check
        /**$authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } **/
        
        //Passed the User Auth Check, Proceed with the Business Logic
        $userData=$user->singleUser($userid); // Get User data from the Database
        $itemList['id']=$userData->id;
        $itemList['userid']=$userData->id;
        $itemList['username']=$userData->username;
        $itemList['firstname']=$userData->firstname;
        $itemList['lastname']=$userData->lastname;
        $itemList['userimage']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
        $itemList['useremail']=$userData->email;
        $itemList['userphone']=$userData->phone;
        $itemList['accounttype']=$userData->type;
        $itemList['address']=$userData->address;
        $itemList['city']=$userData->city;
        $itemList['state']=$userData->state;
        $itemList['country']=$userData->country;
        $itemList['postalzipcode']=$userData->postalzipcode;
        // $itemList['walletbalance']=$userData->walletbalance;
        $itemList['referralcode']=$userData->referralcode;
        $itemList['referrercode']=$userData->referrercode;
        $itemList['lastlogindate']=$userData->lastlogindate;
        $itemList['details']=$userData->details;
        $itemList['date']=$userData->date;
        $itemList['userpoints']=$userData->ranking;
        $itemList['userwins']=$gamebets->sumBetWinsByUser($itemList['userid']); // Get User Total Referrals Sum data from the Database
        $itemList['userloss']=$gamebets->sumBetLossByUser($itemList['userid']); // Get User Total Referrals Sum data from the Database
        $itemList['userdraws']=$gamebets->sumBetDrawsByUser($itemList['userid']); // Get User Total Referrals Sum data from the Database
        $itemList['totalusersreferred']=$user->sumReferredUsers($itemList['referralcode']); // Get User Total Referrals Sum data from the Database
        $transactionTotalResultData=$transactions->sumUserTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
        $itemList['totaltransactions']=$transactionTotalResultData->totalsum;
        $transactionTotalResultData=$transactions->sumUserWithdrawalTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
        $itemList['totalpayout']=$transactionTotalResultData->totalsum;
        $transactionTotalResultData=$transactions->sumUserDepositTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
        $itemList['totaldeposits']=$transactionTotalResultData->totalsum;
        $transactionTotalResultData=$transactions->sumUserWinTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
        $itemList['totalwinnings']=$transactionTotalResultData->totalsum;
        $transactionTotalResultData=$transactions->sumUserLossTransactions($itemList['userid']); // Get User Total Orders Sum data from the Database
        $itemList['totalloss']=$transactionTotalResultData->totalsum;
        echo json_encode($itemList);  
    }

    public function removeaccountAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $user->removeUser($this->request->getPost('customerid')); //Delete User account
        echo json_encode(array("status"=>"success","message"=>"Account deleted successfully"));
    }

}
?>
