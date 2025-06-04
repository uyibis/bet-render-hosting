<?php

use Phalcon\Mvc\Controller;

class AdminController extends \Phalcon\Mvc\Controller {

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
        $admin = new Admin();
        $generalService = new GeneralService();
        $emailerService = new EmailerService();

        /** Validation Block **/
        //Username
        $data['username'] = $this->request->getPost('username');
        if (!is_string($data['username'])) {
            $errors['username'] = 'Username expected. Can consist of words or numbers';
        }
        $data['username'] = $generalService->getSafeURL($data['username']);
        
        //Firstname
        $data['firstname'] = $this->request->getPost('firstname');
        if (!is_string($data['firstname'])) {
            $errors['firstname'] = 'Firstname expected';
        }

        //Lastname
        $data['lastname'] = $this->request->getPost('lastname');
        if (!is_string($data['lastname'])) {
            $errors['lastname'] = 'Lastname expected';
        }

        //Email
        $data['email'] = $this->request->getPost('email');
        if (!is_string($data['email'])) {
            $errors['email'] = 'Email expected';
        }

        //Phone
        $data['phone'] = $this->request->getPost('phone');
        if (!is_string($data['phone'])) {
            $errors['phone'] = 'Phone number expected';
        }

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

        //Profile photo
        $data['photo'] = "files/avatar.png";

        //Get current date and time
        $data['date'] = date("d-m-Y");
        $data['time'] = date('h:i A');

        // Check if User already exist in the Database
        $existResult=$admin->checkAdminExist($data);

        //Error form handling check and Submit Data
        if ($errors) {
            echo json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } elseif ($existResult >= 1) {
        //Already exist
            echo json_encode(array("status"=>"failed","message"=>"Duplicate entry. Username, email or phone already exist."));
        } else {
        // Store to Database Model and check for errors
            $result=$admin->addAdmin($data);
            if ($result) {
                //Success
                //$emailerService->sendWelcomeEmail($data['username'], $data['email'], $data['verificationcode']);
                echo json_encode(array("status"=>"success","message"=>"Thanks you for signing up!","result"=>$result));
            } else {
                //Failed
                echo json_encode(array("status"=>"failed","message"=>"Error occurred on database. Please try again"));
            }            
        }
    }

    public function loginAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $admin = new Admin();
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
        $existResult=$admin->checkAdminDataExist($data['username']);

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 
        
        //Already exist
        if ($existResult >= 1) {
            // Get User data from the Database
            $userData=$admin->singleAdmin($data['username']);
            $userPasswordHash=$userData->password;
            //Compare password with hashed password
            if ($this->security->checkHash($data['password'], $userPasswordHash)) {
                // The password is valid
                $userid=$userData->id;
                $username=$userData->username;
                $useremail=$userData->email;
                $userimage=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
                $accounttype=$userData->role;
                $logintoken=$generalService->generate_code('25');//Auth login token
                $logindevice=$userData->logindevice;
                $lastlogindate=$userData->lastlogindate;
                $newdate=date("d-m-Y");
                $admin->updateAdminLoginToken($data['username'], $logintoken, $newdate); //Update User DB with generated token and last login date
                echo json_encode(array("status"=>"success","message"=>"login successful","userid"=>"$userid","username"=>"$username","useremail"=>"$useremail","userimage"=>"$userimage","accounttype"=>"$accounttype","logintoken"=>"$logintoken","logindevice"=>"$logindevice"));
            } else {
                //Invalid login attempt
                return json_encode(array("status"=>"failed","message"=>"Invalid login credentials"));
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
        $admin = new Admin();
        $emailerService = new EmailerService();
        $generalService = new GeneralService();

        /** Validation Block **/
        //Email
        $data['email'] = $this->request->getPost('email');
        if (!is_string($data['email']) || !preg_match('/^[A-z0-9_-]{3,16}$/', $data['email'])) {
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
            $validResetCode=$admin->verifyAdminPasswordResetCode($data['email'], $data['resetcode'], $newdate); //Verify User provided password reset code and today's expiry date
            if ($validResetCode >= 1) {
                //User reset code is valid
                $newpassword=$generalService->generate_code('6'); //New temp password
                $newpasswordhash=$this->security->hash($newpassword);
                $admin->resetAdminPassword($data['email'], $newpasswordhash); //Clear User password reset code and update with new password
                $emailerService->sendNewPasswordResetEmail($data['email'], $newpassword);
                echo json_encode(array("status"=>"success","message"=>"New password sent to your email"));
            } else {
                //User reset code not valid
                echo json_encode(array("status"=>"failed","message"=>"Provided reset code not valid or expired"));
            }
        } else {
            //check if user exist and send pass recovery code to user
            $existResult=$admin->checkAdminDataExist($data['email']);
            if ($existResult >= 1) {
                $passwordresetcode=$generalService->generate_code('15'); //Reset code
                $admin->updateAdminPasswordResetCode($data['email'], $passwordresetcode, $newdate); //Update User DB with generated password reset code and today's expiry date
                //User reset code sent
                $emailerService->sendPasswordRecoveryEmail($data['email'], $passwordresetcode);
                echo json_encode(array("status"=>"success","message"=>"Password reset code sent to your email"));
            } else {
                //User does not exist
                echo json_encode(array("status"=>"failed","message"=>"User does not exist"));
            }
        }

    }

    public function accountdetailsAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $userData=$admin->singleAdmin($this->request->getPost('userid')); // Get User data from the Database
        $userid=$userData->id;
        $username=$userData->username;
        $firstname=$userData->firstname;
        $lastname=$userData->lastname;
        $userimage=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
        $useremail=$userData->email;
        $userphone=$userData->phone;
        $accounttype=$userData->type;
        $address=$userData->address;
        $city=$userData->city;
        $state=$userData->state;
        $country=$userData->country;
        $postalzipcode=$userData->postalzipcode;
        $lastlogindate=$userData->lastlogindate;
        echo json_encode(array("status"=>"success","username"=>"$username","useremail"=>"$useremail","userimage"=>"$userimage","accounttype"=>"$accounttype","userphone"=>"$userphone","firstname"=>"$firstname","lastname"=>"$lastname","address"=>"$address","city"=>"$city","state"=>"$state","country"=>"$country","postalzipcode"=>"$postalzipcode","lastlogindate"=>"$lastlogindate"));
    }

    public function accountupdateAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();
        $data = [];

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        if($this->request->getPost('firstname')){ //Firstname change check
            $data['firstname'] = $this->request->getPost('firstname');
            if (is_string($data['firstname'])) {
                $admin->updateAdminAccount($this->request->getPost('userid'), $this->request->getPost('firstname'), "firstname"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Firstname must be a string"));
            }
        }

        if($this->request->getPost('lastname')){ //Lastname change check
            $data['lastname'] = $this->request->getPost('lastname');
            if (is_string($data['lastname'])) {
                $admin->updateAdminAccount($this->request->getPost('userid'), $this->request->getPost('lastname'), "lastname"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Lastname must be a string"));
            }
        }

        if($this->request->getPost('phone')){ //Phone change check
            $data['phone'] = $this->request->getPost('phone');
            if (preg_match('/^[0-9_-]{3,16}$/', $data['phone'])) {
                $admin->updateAdminAccount($this->request->getPost('userid'), $this->request->getPost('phone'), "phone"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Phone must be numbers only"));
            }
        }

        if($this->request->getPost('address')){ //Address change check
            $data['address'] = $this->request->getPost('address');
            if (is_string($data['address'])) {
                $admin->updateAdminAccount($this->request->getPost('userid'), $this->request->getPost('address'), "address"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Address must be a string"));
            }
        }

        if($this->request->getPost('city')){ //City change check
            $data['city'] = $this->request->getPost('city');
            if (is_string($data['city'])) {
                $admin->updateAdminAccount($this->request->getPost('userid'), $this->request->getPost('city'), "city"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"City must be a string"));
            }
        }

        if($this->request->getPost('state')){ //State change check
            $data['state'] = $this->request->getPost('state');
            if (is_string($data['state'])) {
                $admin->updateAdminAccount($this->request->getPost('userid'), $this->request->getPost('state'), "state"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"State must be a string"));
            }
        }

        if($this->request->getPost('country')){ //Country change check
            $data['country'] = $this->request->getPost('country');
            if (is_string($data['country'])) {
                $admin->updateAdminAccount($this->request->getPost('userid'), $this->request->getPost('country'), "country"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Country must be a string"));
            }
        }

        if($this->request->getPost('postalzipcode')){ //Postalzipcode change check
            $data['postalzipcode'] = $this->request->getPost('postalzipcode');
            if (is_string($data['postalzipcode'])) {
                $admin->updateAdminAccount($this->request->getPost('userid'), $this->request->getPost('postalzipcode'), "postalzipcode"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Postal/Zip code must be a string"));
            }
        }
        
        if($this->request->getPost('password')){ //Password change check
            $data['password'] = $this->request->getPost('password');
            if (is_string($data['password'])) {
                $data['passwordhash'] = $this->security->hash($data['password']); //Hash password
                $admin->updateAdminAccount($this->request->getPost('userid'), $data['passwordhash'], "password"); //Update User account details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Password expected. Must consist of words, numbers or symbols"));
            }
        }

        if($this->request->hasFiles() == true){ //Photo change check
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
                        $admin->updateAdminAccount($this->request->getPost('userid'), $FileName, "photo"); //Update User account details changes
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        }
        
        echo json_encode(array("status"=>"success","message"=>"Account updated successfully"));
    }

    public function allAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$admin->allAdmin(); // Get User data from the Database
        foreach($resultData as $userData) {
            $itemList['userid']=$userData->id;
            $itemList['username']=$userData->username;
            $itemList['firstname']=$userData->firstname;
            $itemList['lastname']=$userData->lastname;
            $itemList['userimage']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
            $itemList['useremail']=$userData->email;
            $itemList['userphone']=$userData->phone;
            $itemList['address']=$userData->address;
            $itemList['city']=$userData->city;
            $itemList['state']=$userData->state;
            $itemList['country']=$userData->country;
            $itemList['lastlogindate']=$userData->lastlogindate;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);     
    }

    public function searchAllAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$admin->searchAdmin($this->request->getPost('query')); // Get User data from the Database
        foreach($resultData as $userData) {
            $itemList['userid']=$userData->id;
            $itemList['username']=$userData->username;
            $itemList['firstname']=$userData->firstname;
            $itemList['lastname']=$userData->lastname;
            $itemList['userimage']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
            $itemList['useremail']=$userData->email;
            $itemList['userphone']=$userData->phone;
            $itemList['address']=$userData->address;
            $itemList['city']=$userData->city;
            $itemList['state']=$userData->state;
            $itemList['country']=$userData->country;
            $itemList['lastlogindate']=$userData->lastlogindate;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);     
    }

    public function dashboarddataAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();
        $users = new Users();
        $transactions = new Transactions();
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();
        //$supporttickets = new Supporttickets();
        $itemList = array();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemList['totalusers']=$users->sumUsers(); // Get User Total users Sum data from the Database
        $itemList['totalgames']=$games->sumEntries(); // Get User Total merchants Sum data from the Database
        $itemList['totalgamebets']=$gamebets->allBetsCount(); // Get User Total products Sum data from the Database
        $itemList['totalorders']=$transactions->sumOrders(); // Get User Total orders Sum data from the Database
        $itemList['totalsales']=$transactions->sumSales(); // Get User Total sales Sum data from the Database
        $itemList['totaladmins']=$admin->sumAdmins(); // Get User Total admins Sum data from the Database
        //$itemList['totaltickets'] = $supporttickets->adminTicketsCount(); //Total Support Tickets

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

        //User SignUps
        $currentmonth_UsersSignupCOUNT=$users->usersSignupCOUNT($currentmonth); // Get data for graph chart
        $lastmonthone_UsersSignupCOUNT=$users->usersSignupCOUNT($lastmonthone); // Get data for graph chart
        $lastmonthtwo_UsersSignupCOUNT=$users->usersSignupCOUNT($lastmonthtwo); // Get data for graph chart
        $lastmonththree_UsersSignupCOUNT=$users->usersSignupCOUNT($lastmonththree); // Get data for graph chart
        $lastmonthfour_UsersSignupCOUNT=$users->usersSignupCOUNT($lastmonthfour); // Get data for graph chart
        $lastmonthfive_UsersSignupCOUNT=$users->usersSignupCOUNT($lastmonthfive); // Get data for graph chart
        $usersSignup_GraphData = array
        (
        array("$currentmonthLABEL","$currentmonth_UsersSignupCOUNT"),
        array("$lastmonthoneLABEL","$lastmonthone_UsersSignupCOUNT"),
        array("$lastmonthtwoLABEL","$lastmonthtwo_UsersSignupCOUNT"),
        array("$lastmonththreeLABEL","$lastmonththree_UsersSignupCOUNT"),
        array("$lastmonthfourLABEL","$lastmonthfour_UsersSignupCOUNT"),
        array("$lastmonthfiveLABEL","$lastmonthfive_UsersSignupCOUNT")
        );

        //Games Posts
        $currentmonth_ProductsSignupCOUNT=$games->EntriesCOUNT($currentmonth); // Get data for graph chart
        $lastmonthone_ProductsSignupCOUNT=$games->EntriesCOUNT($lastmonthone); // Get data for graph chart
        $lastmonthtwo_ProductsSignupCOUNT=$games->EntriesCOUNT($lastmonthtwo); // Get data for graph chart
        $lastmonththree_ProductsSignupCOUNT=$games->EntriesCOUNT($lastmonththree); // Get data for graph chart
        $lastmonthfour_ProductsSignupCOUNT=$games->EntriesCOUNT($lastmonthfour); // Get data for graph chart
        $lastmonthfive_ProductsSignupCOUNT=$games->EntriesCOUNT($lastmonthfive); // Get data for graph chart
        $gamesSignup_GraphData = array
        (
        array("$currentmonthLABEL","$currentmonth_ProductsSignupCOUNT"),
        array("$lastmonthoneLABEL","$lastmonthone_ProductsSignupCOUNT"),
        array("$lastmonthtwoLABEL","$lastmonthtwo_ProductsSignupCOUNT"),
        array("$lastmonththreeLABEL","$lastmonththree_ProductsSignupCOUNT"),
        array("$lastmonthfourLABEL","$lastmonthfour_ProductsSignupCOUNT"),
        array("$lastmonthfiveLABEL","$lastmonthfive_ProductsSignupCOUNT")
        );

        //GameBets Counts
        $currentmonth_PostsCOUNT=$gamebets->betsCOUNT($currentmonth); // Get data for graph chart
        $lastmonthone_PostsCOUNT=$gamebets->betsCOUNT($lastmonthone); // Get data for graph chart
        $lastmonthtwo_PostsCOUNT=$gamebets->betsCOUNT($lastmonthtwo); // Get data for graph chart
        $lastmonththree_PostsCOUNT=$gamebets->betsCOUNT($lastmonththree); // Get data for graph chart
        $lastmonthfour_PostsCOUNT=$gamebets->betsCOUNT($lastmonthfour); // Get data for graph chart
        $lastmonthfive_PostsCOUNT=$gamebets->betsCOUNT($lastmonthfive); // Get data for graph chart
        $gamebetsCounts_GraphData = array
        (
        array("$currentmonthLABEL","$currentmonth_PostsCOUNT"),
        array("$lastmonthoneLABEL","$lastmonthone_PostsCOUNT"),
        array("$lastmonthtwoLABEL","$lastmonthtwo_PostsCOUNT"),
        array("$lastmonththreeLABEL","$lastmonththree_PostsCOUNT"),
        array("$lastmonthfourLABEL","$lastmonthfour_PostsCOUNT"),
        array("$lastmonthfiveLABEL","$lastmonthfive_PostsCOUNT")
        );

        //Transaction Counts
        $currentmonth_TransactionsCOUNT=$transactions->transactionsCOUNT($currentmonth); // Get data for graph chart
        $lastmonthone_TransactionsCOUNT=$transactions->transactionsCOUNT($lastmonthone); // Get data for graph chart
        $lastmonthtwo_TransactionsCOUNT=$transactions->transactionsCOUNT($lastmonthtwo); // Get data for graph chart
        $lastmonththree_TransactionsCOUNT=$transactions->transactionsCOUNT($lastmonththree); // Get data for graph chart
        $lastmonthfour_TransactionsCOUNT=$transactions->transactionsCOUNT($lastmonthfour); // Get data for graph chart
        $lastmonthfive_TransactionsCOUNT=$transactions->transactionsCOUNT($lastmonthfive); // Get data for graph chart
        $transactionsCounts_GraphData = array
        (
        array("$currentmonthLABEL","$currentmonth_TransactionsCOUNT"),
        array("$lastmonthoneLABEL","$lastmonthone_TransactionsCOUNT"),
        array("$lastmonthtwoLABEL","$lastmonthtwo_TransactionsCOUNT"),
        array("$lastmonththreeLABEL","$lastmonththree_TransactionsCOUNT"),
        array("$lastmonthfourLABEL","$lastmonthfour_TransactionsCOUNT"),
        array("$lastmonthfiveLABEL","$lastmonthfive_TransactionsCOUNT")
        );

        //Transaction Statistics Counts
        $paid_TransactionsCOUNT=$transactions->transactionsPaymentStatisticsCount("paid"); // Get data for pie-chart graph
        $unpaid_TransactionsCOUNT=$transactions->transactionsPaymentStatisticsCount("unpaid"); // Get data for pie-chart graph
        $refunded_TransactionsCOUNT=$transactions->transactionsPaymentStatisticsCount("refunded"); // Get data for pie-chart graph
        $win_RecordCOUNT=$gamebets->betsStatisticsCOUNT("win"); // Get data for pie-chart graph
        $loss_RecordCOUNT=$gamebets->betsStatisticsCOUNT("loss"); // Get data for pie-chart graph
        $draw_RecordCOUNT=$gamebets->betsStatisticsCOUNT("draw"); // Get data for pie-chart graph
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
        $itemList['usersgraphdata']=$usersSignup_GraphData; //Graph Data
        $itemList['gamesgraphdata']=$gamesSignup_GraphData; //Graph Data
        $itemList['gamebetsgraphdata']=$gamebetsCounts_GraphData; //Graph Data
        $itemList['transactionsgraphdata']=$transactionsCounts_GraphData; //Graph Data
        $itemList['betsstatisticsdata']=$transactionsBetsStatisticsData; //Statistics Data
        $itemList['paymentstatisticsdata']=$transactionsPaymentsStatisticsData; //Statistics Data
        echo json_encode($itemList);  
    }

    public function productsbymerchantAction($merchantid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $products = new Products();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$products->allProductsByMerchant($merchantid); // Get products data from the Database
        foreach($resultData as $productData) {
            $itemList['id']=$productData->id;
            $itemList['productid']=$productData->id;
            $itemList['name']=$productData->title;
            $itemList['photo']=$this->getDi()->getShared('siteurl').'/'.$productData->photo;
            $itemList['price']=$productData->price;
            $itemList['oldprice']=$productData->oldprice;
            $itemList['stock']=$productData->stock;
            $itemList['sold']=$productData->sold;
            $itemList['details']=$productData->details;
            $itemList['categoryid']=$productData->categoryid;
            $itemList['category']=$productData->category;
            $itemList['subcategoryid']=$productData->subcategoryid;
            $itemList['subcategory']=$productData->subcategory;
            $itemList['clearancesales']=$productData->clearancesales;
            $itemList['salespromo']=$productData->salespromo;
            $itemList['safeurl']=$productData->safeurl;
            $itemList['status']=$productData->status;
            $itemList['date']=$productData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);     
    }
    
    public function transactionsbymerchantAction($userid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $merchants = new Merchants();
        $merchantsales = new Merchantsales();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$merchantsales->allTransactions($userid); // Get transactions data from the Database
        foreach($resultData as $transactionsData) {
            $itemList['orderid']=$transactionsData->orderid;
            $itemList['productid']=$transactionsData->productid;
            $itemList['product']=$transactionsData->product;
            $itemList['quantity']=$transactionsData->quantity;
            $itemList['amount']=$transactionsData->price;
            $itemList['details']=$transactionsData->details;
            $itemList['paymentstatus']=$transactionsData->paymentstatus;
            $itemList['deliverystatus']=$transactionsData->deliverystatus;
            $itemList['paymentmethod']=$transactionsData->paymentmethod;
            $itemList['deliverymethod']=$transactionsData->deliverymethod;
            $itemList['userid']=$transactionsData->userid;
            $itemList['username']=$transactionsData->username;
            $itemList['useraddress']=$transactionsData->useraddress;
            $itemList['usercity']=$transactionsData->usercity;
            $itemList['userstate']=$transactionsData->userstate;
            $itemList['userzipcode']=$transactionsData->userzipcode;
            $itemList['usercountry']=$transactionsData->usercountry;
            $itemList['userphone']=$transactionsData->userphone;
            $itemList['useremail']=$transactionsData->useremail;
            $itemList['usercomment']=$transactionsData->usercomment;
            $itemList['date']=$transactionsData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);    
    }
    
    public function transactionsbycustomerAction($userid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $transactions = new Transactions();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$transactions->transactionsByCustomer($userid); // Get user transactions data from the Database
        foreach($resultData as $transactionsData) {
            $itemList['orderid']=$transactionsData->id;
            $itemList['details']=$transactionsData->details;
            $itemList['amount']=$transactionsData->amount;
            $itemList['paymentstatus']=$transactionsData->paymentstatus;
            $itemList['deliverystatus']=$transactionsData->deliverystatus;
            $itemList['paymentmethod']=$transactionsData->paymentmethod;
            $itemList['deliverymethod']=$transactionsData->deliverymethod;
            $itemList['userid']=$transactionsData->userid;
            $itemList['username']=$transactionsData->username;
            $itemList['useraddress']=$transactionsData->useraddress;
            $itemList['usercity']=$transactionsData->usercity;
            $itemList['userstate']=$transactionsData->userstate;
            $itemList['userzipcode']=$transactionsData->userzipcode;
            $itemList['usercountry']=$transactionsData->usercountry;
            $itemList['userphone']=$transactionsData->userphone;
            $itemList['useremail']=$transactionsData->useremail;
            $itemList['usercomment']=$transactionsData->usercomment;
            $itemList['date']=$transactionsData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);       
    }

    public function transactionsbyshipperAction($userid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $transactions = new Transactions();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$transactions->transactionsByShipper($userid); // Get user transactions data from the Database
        foreach($resultData as $transactionsData) {
            $itemList['orderid']=$transactionsData->orderid;
            $itemList['details']=$transactionsData->details;
            $itemList['amount']=$transactionsData->amount;
            $itemList['paymentstatus']=$transactionsData->paymentstatus;
            $itemList['deliverystatus']=$transactionsData->deliverystatus;
            $itemList['paymentmethod']=$transactionsData->paymentmethod;
            $itemList['deliverymethod']=$transactionsData->deliverymethod;
            $itemList['userid']=$transactionsData->userid;
            $itemList['username']=$transactionsData->username;
            $itemList['useraddress']=$transactionsData->useraddress;
            $itemList['usercity']=$transactionsData->usercity;
            $itemList['userstate']=$transactionsData->userstate;
            $itemList['userzipcode']=$transactionsData->userzipcode;
            $itemList['usercountry']=$transactionsData->usercountry;
            $itemList['userphone']=$transactionsData->userphone;
            $itemList['useremail']=$transactionsData->useremail;
            $itemList['usercomment']=$transactionsData->usercomment;
            $itemList['date']=$transactionsData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);       
    }

    public function userreferralsAction($userid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $user = new Users();
        $transactions = new Transactions();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $userData=$user->singleUser($userid); // Get User data from the Database
        $referralcode=$userData->referralcode;
        $referrercode=$userData->referrercode;
        $resultData=$user->referredUsers($referralcode); // Get User data from the Database
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
            $transactionTotalResultData=$transactions->sumCustomerTransactions($userData->id); // Get User Total Orders Sum data from the Database
            $itemList['totalorders']=$transactionTotalResultData->totalsum;
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
            $resultData=$notifications->allEntries(); // Get data from the Database
        } elseif($type=="admin"){
            $resultData=$notifications->allEntriesByUser($this->request->getPost('userid')); // Get data from the Database
        } else {
            $resultData=$notifications->allEntriesType($type); // Get User data from the Database
        }
        foreach($resultData as $userData) {
            $itemList['userid']=$userData->id;
            $itemList['username']=$userData->username;
            $itemList['title']=$userData->title;
            $itemList['details']=$userData->details;
            $itemList['fromid']=$userData->fromid;
            $itemList['from']=$userData->from;
            $itemList['type']=$userData->type;
            $itemList['status']=$userData->status;
            $itemList['time']=$userData->time;
            $itemList['date']=$userData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists); 
    }

    public function locationstrackingAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $transactions = new Transactions();

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$transactions->allEntries(); // Get data from the Database
        foreach($resultData as $entryData) {
            $itemList['id']=$entryData->id;
            $itemList['latitude']=$entryData->latitude;
            $itemList['longitude']=$entryData->longitude;
            $itemList['date']=$entryData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);     
    }

    public function addcategoryAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $categories = new Categories();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        
        //Category name
        $data['leagueid'] = $this->request->getPost('leagueid');
        $data['category'] = $this->request->getPost('category');
        if (!is_string($data['category'])) {
            $errors['category'] = 'Category title expected';
        }
        $data['safeurl'] = $generalService->getSafeURL($data['category']);

        //Category type
        $data['type'] = $this->request->getPost('type');
        if (!is_string($data['type'])) {
            $errors['type'] = 'Category type expected';
        }

        //Category details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Category details expected';
        }

        //Category photo
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
                        $data['image'] = "$FileName";
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
        $categories->addCategory($data);
        echo json_encode(array("status"=>"success","message"=>"Category added successfully"));
    }

    public function editcategoryAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $categories = new Categories();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        
        //Category name
        if($this->request->getPost('category')){ //category change check
            $data['category'] = $this->request->getPost('category');
            if (is_string($data['category'])) {
                $categories->updateCategory($this->request->getPost('categoryid'), $this->request->getPost('category'), "category"); //Update details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"category must be a string"));
            }
        }

        //Category details
        if($this->request->getPost('details')){ //details change check
            $data['details'] = $this->request->getPost('details');
            if (is_string($data['details'])) {
                $categories->updateCategory($this->request->getPost('categoryid'), $this->request->getPost('details'), "details"); //Update details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"category details must be a string"));
            }
        }

        //Category photo
        if($this->request->hasFiles() == true){ //Photo change check
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
                        $categories->updateCategory($this->request->getPost('categoryid'), $FileName, "image"); //Update photo changes
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        }

        echo json_encode(array("status"=>"success","message"=>"Details updated successfully"));
    }
    
    public function categoriesAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $authService = new AuthService();
        $admin = new Admin();
        $categories = new Categories();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$categories->allCategories(); // Get data from the Database
        foreach($resultData as $categoriesData) {
            $itemList['id']=$categoriesData->id;
            $itemList['categoryid']=$categoriesData->id;
            $itemList['type']=$categoriesData->type;
            $itemList['category']=$categoriesData->category;
            $itemList['image']=$categoriesData->image;
            $itemList['details']=$categoriesData->details;
            $itemList['safeurl']=$categoriesData->safeurl;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);  
    }

    public function addsubcategoryAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $categories = new Categories();
        $subcategories = new Subcategories();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        
        //Category name
        $data['category'] = $this->request->getPost('category');
        if (!is_string($data['category'])) {
            $errors['category'] = 'Category expected';
        }
        //Get League ID from Category
        $data['leagueid'] = $categories->getCategoryLeagueID($data['category']); //Get League ID from Category
        
        //Subcategory name
        $data['subcategory'] = $this->request->getPost('subcategory');
        if (!is_string($data['subcategory'])) {
            $errors['subcategory'] = 'Subcategory title expected';
        }
        $data['safeurl'] = $generalService->getSafeURL($data['subcategory']);

        //Subcategory details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Subcategory details expected';
        }

        //Subcategory type
        $data['type'] = "";

        //Subcategory photo
        $data['image'] = "files/subcategory-icon.png";
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
                        $data['image'] = "$FileName";
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
        $subcategories->addSubcategory($data);
        echo json_encode(array("status"=>"success","message"=>"Subcategory added successfully"));
    }

    public function editsubcategoryAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $subcategories = new Subcategories();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        
        //Subcategory name
        if($this->request->getPost('subcategory')){ //subcategory change check
            $data['subcategory'] = $this->request->getPost('subcategory');
            if (is_string($data['subcategory'])) {
                $subcategories->updateSubCategory($this->request->getPost('subcategoryid'), $this->request->getPost('subcategory'), "subcategory"); //Update details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"category must be a string"));
            }
        }

        //Subcategory details
        if($this->request->getPost('details')){ //details change check
            $data['details'] = $this->request->getPost('details');
            if (is_string($data['details'])) {
                $subcategories->updateSubCategory($this->request->getPost('subcategoryid'), $this->request->getPost('details'), "details"); //Update details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"subcategory details must be a string"));
            }
        }

        //Category photo
        if($this->request->hasFiles() == true){ //Photo change check
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
                        $subcategories->updateSubCategory($this->request->getPost('subcategoryid'), $FileName, "image"); //Update photo changes
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        }

        echo json_encode(array("status"=>"success","message"=>"Details updated successfully"));
    }
    
    public function subcategoriesAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $admin = new Admin();
        $authService = new AuthService();
        $generalService = new GeneralService();
        $subcategories = new Subcategories();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$subcategories->allSubcategories(); // Get data from the Database
        foreach($resultData as $subcategoriesData) {
            $itemList['id']=$subcategoriesData->id;
            $itemList['subcategoryid']=$subcategoriesData->id;
            $itemList['categoryid']=$subcategoriesData->categoryid;
            $itemList['category']=$generalService->getCategoryName($subcategoriesData->categoryid);
            $itemList['subcategory']=$subcategoriesData->subcategory;
            $itemList['type']=$subcategoriesData->type;
            $itemList['image']=$subcategoriesData->image;
            $itemList['details']=$subcategoriesData->details;
            $itemList['safeurl']=$subcategoriesData->safeurl;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);  
    }

    public function removecategoryAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $categories = new Categories();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $categories->removeCategory($this->request->getPost('categoryid')); //Delete
        echo json_encode(array("status"=>"success","message"=>"Category deleted successfully"));
    }

    public function removesubcategoryAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $subcategories = new Subcategories();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $subcategories->removeSubcategory($this->request->getPost('subcategoryid')); //Delete
        echo json_encode(array("status"=>"success","message"=>"Subcategory deleted successfully"));
    }

    public function removeratingAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $Productreviews = new Productreviews(); 

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $Productreviews->removeReview($this->request->getPost('ratingid')); //Delete
        echo json_encode(array("status"=>"success","message"=>"Rating review deleted successfully"));
    }

    public function addreviewAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $Productreviews = new Productreviews(); 

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //productid
        $data['productid'] = $this->request->getPost('productid');
        if (!is_string($data['productid'])) {
            $errors['productid'] = 'Product ID expected';
        }
        
        //reviewer name
        $data['name'] = "Admin";

        //reviewer email
        $data['email'] = "";

        //reviewer phone
        $data['phone'] = "";

        //status
        $data['status'] = "approved";

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

        //Get current date and time
        $data['date'] = date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        // Store to Database Model and check for errors
        $Productreviews->addReview($data);
        echo json_encode(array("status"=>"success","message"=>"Product review and rating added successfully"));
    }

    public function updatereviewAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $Productreviews = new Productreviews(); 

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //review ratingid
        $data['ratingid'] = $this->request->getPost('ratingid');
        if (!is_string($data['ratingid'])) {
            $errors['ratingid'] = 'Product rating ID expected';
        }

        //review status
        $data['status'] = $this->request->getPost('status');
        if (!is_string($data['status'])) {
            $errors['status'] = 'Status expected';
        } 

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        // Store to Database Model and check for errors
        $Productreviews->updateRatingReview($this->request->getPost('ratingid'), $this->request->getPost('status'), "status"); 
        echo json_encode(array("status"=>"success","message"=>"Product review and rating updated successfully"));
    }

    public function reviewsAction($productid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $Productreviews = new Productreviews(); 

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Logic Block **/
        $itemLists = array();
        $itemList = array();
        $resultData=$Productreviews->allReviewsByAdmin($productid); // Get product reviews data from the Database
        foreach($resultData as $productData) {
            $itemList['id']=$productData->id;
            $itemList['productid']=$productData->productid;
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

    public function bannersAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $admin = new Admin();
        $authService = new AuthService();
        $banners = new Banners();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$banners->getBannersItems(); // Get data from the Database
        foreach($resultData as $bannersData) {
            $itemList['id']=$bannersData->id;
            $itemList['image']=$this->getDi()->getShared('siteurl').'/'.$bannersData->image;
            $itemList['link']=$bannersData->link;
            $itemList['position']=$bannersData->position;
            $itemList['author']=$bannersData->author;
            $itemList['date']=$bannersData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);  
    }

    public function addbannerAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $banners = new Banners();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        
        //Link name
        $data['link'] = $this->request->getPost('link');
        if (!is_string($data['link'])) {
            $errors['link'] = 'Link expected';
        }
        
        //Position name
        $data['position'] = $this->request->getPost('position');
        if (!is_string($data['position'])) {
            $errors['position'] = 'Position title expected';
        }

        //Details name
        $data['details'] = $this->request->getPost('details');

        //Banner photo
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
                        $data['image'] = "$FileName";
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
        $banners->addNewBanner($data);
        echo json_encode(array("status"=>"success","message"=>"Banner added successfully"));
    }

    public function removebannerAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();
        $banners = new Banners();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $banners->removeBanner($this->request->getPost('bannerid')); //Delete banner
        echo json_encode(array("status"=>"success","message"=>"Banner deleted successfully"));
    }

    public function slidebannersAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $admin = new Admin();
        $authService = new AuthService();
        $banners = new Bannersliders();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$banners->getSlideBannersItems(); // Get data from the Database
        foreach($resultData as $bannersData) {
            $itemList['id']=$bannersData->id;
            $itemList['media']=$this->getDi()->getShared('siteurl').'/'.$bannersData->media;
            $itemList['slidebuttonlink']=$bannersData->slidebuttonlink;
            $itemList['type']=$bannersData->type;
            $itemList['details']=$bannersData->details;
            $itemList['author']=$bannersData->author;
            $itemList['date']=$bannersData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);  
    }

    public function addslidebannerAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $banners = new Bannersliders();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        
        //Link name
        $data['link'] = $this->request->getPost('link');
        if (!is_string($data['link'])) {
            $errors['link'] = 'Link expected';
        }
        
        //Type name
        $data['type'] = $this->request->getPost('type');
        if (!is_string($data['type'])) {
            $errors['type'] = 'Media type expected';
        }
        
        //Position name
        $data['position'] = $this->request->getPost('position');
        if (!is_string($data['position'])) {
            $errors['position'] = 'Position title expected';
        }

        //Details name
        $data['details'] = $this->request->getPost('details');

        //Banner photo
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
                        $data['image'] = "$FileName";
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
        $banners->addNewSlideBanner($data);
        echo json_encode(array("status"=>"success","message"=>"Slide banner added successfully"));
    }

    public function removeslidebannerAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();
        $banners = new Bannersliders();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $banners->removeSlideBanner($this->request->getPost('bannerid')); //Delete banner
        echo json_encode(array("status"=>"success","message"=>"Slide banner deleted successfully"));
    }

    public function advertorialsAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $admin = new Admin();
        $authService = new AuthService();
        $advertorials = new Advertorials();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$advertorials->allEntries(); // Get data from the Database
        foreach($resultData as $advertorialsData) {
            $itemList['id']=$advertorialsData->id;
            $itemList['image']=$this->getDi()->getShared('siteurl').'/'.$advertorialsData->image;
            $itemList['link']=$advertorialsData->link;
            $itemList['position']=$advertorialsData->position;
            $itemList['details']=$advertorialsData->details;
            $itemList['status']=$advertorialsData->status;
            $itemList['clicks']=$advertorialsData->clicks;
            $itemList['views']=$advertorialsData->views;
            $itemList['authorid']=$advertorialsData->authorid;
            $itemList['author']=$advertorialsData->author;
            $itemList['date']=$advertorialsData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);  
    }

    public function addadvertorialAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $advertorials = new Advertorials();
        $merchant = new Merchants();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('authorid');
        $userData=$merchant->singleMerchant($data['authorid']); // Get User data from the Database
        $data['author']=$userData->username;
        
        //Link name
        $data['link'] = $this->request->getPost('link');
        if (!is_string($data['link'])) {
            $errors['link'] = 'Link expected';
        }
        
        //Position name
        $data['position'] = $this->request->getPost('position');
        if (!is_string($data['position'])) {
            $errors['position'] = 'Position title expected';
        }

        //Details name
        $data['details'] = $this->request->getPost('details');
        
        //Type name
        $data['type'] = $this->request->getPost('type');
        
        //Status name
        $data['status'] = "active";
        
        //Clicks
        $data['clicks'] = 0;
        
        //Views
        $data['views'] = 0;

        //Banner photo
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
                        $data['image'] = "$FileName";
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
        $advertorials->addEntry($data);
        echo json_encode(array("status"=>"success","message"=>"Advert banner added successfully"));
    }

    public function removeadvertorialAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();
        $advertorials = new Advertorials();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $advertorials->removeEntry($this->request->getPost('advertid')); //Delete banner
        echo json_encode(array("status"=>"success","message"=>"Advert banner deleted successfully"));
    }

    public function galleryAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $admin = new Admin();
        $gallery = new Gallery();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$gallery->getGalleryItems(); // Get data from the Database
        foreach($resultData as $bannersData) {
            $itemList['id']=$bannersData->id;
            $itemList['media']=$bannersData->media;
            $itemList['postid']=$bannersData->postid;
            $itemList['posttype']=$bannersData->posttype;
            $itemList['caption']=$bannersData->caption;
            $itemList['type']=$bannersData->type;
            $itemList['tags']=$bannersData->tags;
            $itemList['categoryid']=$bannersData->categoryid;
            $itemList['category']=$bannersData->category;
            $itemList['subcategoryid']=$bannersData->subcategoryid;
            $itemList['subcategory']=$bannersData->subcategory;
            $itemList['details']=$bannersData->details;
            $itemList['author']=$bannersData->author;
            $itemList['authorid']=$bannersData->authorid;
            $itemList['date']=$bannersData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);  
    }

    public function addgalleryAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $gallery = new Gallery();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        
        //caption
        $data['caption'] = $this->request->getPost('caption');
        if (!is_string($data['caption'])) {
            $errors['caption'] = 'Caption expected';
        }
        
        //type
        $data['type'] = $this->request->getPost('type');
        if (!is_string($data['type'])) {
            $errors['type'] = 'Type expected';
        }

        //categoryid
        $data['categoryid'] = $this->request->getPost('categoryid');
        if (!is_string($data['categoryid'])) {
            $errors['categoryid'] = 'category expected';
        }
        $data['category'] = $generalService->getCategoryName($data['categoryid']);

        //subcategory
        $data['subcategoryid'] = $this->request->getPost('subcategoryid');
        $data['subcategory'] = $generalService->getSubCategoryName($data['subcategoryid']);

        //Post id
        $data['postid'] = $this->request->getPost('postid');

        //Post type
        $data['posttype'] = $this->request->getPost('posttype');

        //tags
        $data['tags'] = $this->request->getPost('tags');

        //details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['type'] = 'Details expected';
        }

        //Banner photo
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
                        $data['media'] = "$FileName";
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
        $gallery->addNewGallery($data);
        echo json_encode(array("status"=>"success","message"=>"Gallery item added successfully"));
    }

    public function addproductgalleryAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $gallery = new Gallery();
        $product = new Products();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        
        //caption
        $data['caption'] = $this->request->getPost('caption');
        if (!is_string($data['caption'])) {
            $errors['caption'] = 'Caption expected';
        }
        
        //type
        $data['type'] = $this->request->getPost('type');
        if (!is_string($data['type'])) {
            $errors['type'] = 'Type expected';
        }

        //Post id
        $data['postid'] = $this->request->getPost('postid');
        if (!is_string($data['postid'])) {
            $errors['postid'] = 'Product ID expected';
        }

        //Post type
        $data['posttype'] = "product";

        //tags
        $data['tags'] = $this->request->getPost('tags');

        //details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Details expected';
        }        

        //Category & Subcategory
        $data['categoryid']="";
        $data['category']="";
        $data['subcategoryid']="";
        $data['subcategory']="";

        //Get current date and time
        $data['date'] = date("d-m-Y");

        //Banner photo
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
                        $data['media'] = "$FileName";
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        }

        try {
            //Error form handling check and Submit Data
            if ($errors) {
                return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
            } else {
                // Store to Database Model and check for errors
                $gallery->addNewGallery($data);
            }
        } catch(Exception $e) {
            return json_encode(array("status"=>"failed","message"=>$e->getMessage()));
        }
        echo json_encode(array("status"=>"success","message"=>"Gallery item added successfully"));
    }

    public function removegalleryAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();
        $gallery = new Gallery();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $gallery->removeGallery($this->request->getPost('galleryid')); //Delete banner
        echo json_encode(array("status"=>"success","message"=>"Gallery deleted successfully"));
    }

    public function faqAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $errors = [];
        $data = [];
        $admin = new Admin();
        $faq = new Faqs();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$faq->getFAQItems(); // Get data from the Database
        foreach($resultData as $bannersData) {
            $itemList['id']=$bannersData->id;
            $itemList['question']=$bannersData->question;
            $itemList['answer']=$bannersData->answer;
            $itemList['tags']=$bannersData->tags;
            $itemList['categoryid']=$bannersData->categoryid;
            $itemList['category']=$bannersData->category;
            $itemList['subcategoryid']=$bannersData->subcategoryid;
            $itemList['subcategory']=$bannersData->subcategory;
            $itemList['author']=$bannersData->author;
            $itemList['authorid']=$bannersData->authorid;
            $itemList['date']=$bannersData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);  
    }

    public function addfaqAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $faq = new Faqs();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Author ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        
        //question
        $data['question'] = $this->request->getPost('question');
        if (!is_string($data['question'])) {
            $errors['question'] = 'Question expected';
        }
        
        //answer
        $data['answer'] = $this->request->getPost('answer');
        if (!is_string($data['answer'])) {
            $errors['answer'] = 'Answer expected';
        }

        //FAQ category
        $data['categoryid'] = "";
        $data['category'] = "";
        if(is_string($this->request->getPost('categoryid'))){
            $data['categoryid'] = $this->request->getPost('categoryid');
            $data['category'] = $generalService->getCategoryName($data['categoryid']);
        }

        //FAQ subcategory
        $data['subcategoryid'] = "";
        $data['subcategory'] = "";
        if(is_string($this->request->getPost('subcategoryid'))){
            $data['subcategoryid'] = $this->request->getPost('subcategoryid');
            $data['subcategory'] = $generalService->getSubCategoryName($data['subcategoryid']);
        }

        //tags
        $data['tags'] = $this->request->getPost('tags');

        //Get current date and time
        $data['date'] = date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        // Store to Database Model and check for errors
        $faq->addNewEntry($data);
        echo json_encode(array("status"=>"success","message"=>"FAQ item added successfully"));
    }

    public function removefaqAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();
        $faq = new Faqs();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $faq->removefaq($this->request->getPost('faqid')); //Delete 
        echo json_encode(array("status"=>"success","message"=>"FAQ item deleted successfully"));
    }

    public function testimonialsAction($resultLimit=50)
    {
        /** Init Block **/
        $authService = new AuthService();
        $clienttestimonials = new Clienttestimonials();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultNum = (int)$resultLimit;
        $resultData=$clienttestimonials->allEntries(); // Get posts data from the Database
        foreach($resultData as $entryData) {
            $itemList['id']=$entryData->id;
            $itemList['author']=$entryData->author;
            $itemList['authorphoto']=$this->getDi()->getShared('siteurl').'/'.$entryData->authorphoto;
            $itemList['authorjob']=$entryData->authorjob;
            $itemList['authorlocation']=$entryData->authorlocation;
            $itemList['details']=$entryData->details;
            $itemList['date']=$entryData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);    
    }

    public function addtestimonialAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $errors = [];
        $data = [];
        $clienttestimonials = new Clienttestimonials();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Author name
        $data['author'] = $this->request->getPost('author');
        if (!is_string($data['author'])) {
            $errors['author'] = 'Author name expected';
        }

        //Author Job
        $data['authorjob'] = $this->request->getPost('authorjob');
        if (!is_string($data['authorjob'])) {
            $errors['authorjob'] = 'Author job expected';
        }

        //Author Location
        $data['authorlocation'] = $this->request->getPost('authorlocation');
        if (!is_string($data['authorlocation'])) {
            $errors['authorlocation'] = 'Author location expected';
        }

        //Details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Details expected';
        }

        //Author Photo
        $data['authorphoto']="";
        $data['media']="";
        if($this->request->hasFiles() == true){ 
            $i=1;
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
                        if($i == 1) {
                            $data['authorphoto'] = "$FileName";
                            $data['type'] = "text";
                        } elseif($i == 2) {
                            $data['media'] = "$FileName";
                            $data['type'] = "video";
                        }
                        $i++;
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        }

        //Status
        $data['status'] = "approved";

        //Get current date and time
        $data['date'] = date("d-m-Y");

        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        }
        //Send Mail to Webmaster
        $clienttestimonials->addNewEntry($data);
        echo json_encode(array("status"=>"success","message"=>"Client testimonial added successfully"));
    }

    public function updatetestimonialAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $clienttestimonials = new Clienttestimonials();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //testimonialid
        $data['testimonialid'] = $this->request->getPost('testimonialid');
        if (!is_string($data['testimonialid'])) {
            $errors['testimonialid'] = 'Testimonial ID expected';
        }

        //testimonial status
        $data['status'] = $this->request->getPost('status');
        if (!is_string($data['status'])) {
            $errors['status'] = 'Status expected';
        } 

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        // Store to Database Model and check for errors
        $clienttestimonials->updateEntry($this->request->getPost('testimonialid'), $this->request->getPost('status'), "status"); 
        echo json_encode(array("status"=>"success","message"=>"Testimonial updated successfully"));
    }

    public function removetestimonialAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();
        $clienttestimonials = new Clienttestimonials();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $clienttestimonials->removeEntry($this->request->getPost('testimonialid')); //Delete User account
        echo json_encode(array("status"=>"success","message"=>"Testimonial deleted successfully"));
    }
    
    public function removeaccountAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $admin = new Admin();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $admin->removeAdmin($this->request->getPost('userid')); //Delete User account
        echo json_encode(array("status"=>"success","message"=>"Account deleted successfully"));
    }

    public function sendnewsletterAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $emailerService = new EmailerService();
        $user = new Users();
        $merchant = new Merchants();
        $itemLists = array();
        $itemList = array();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        //title
        $data['title'] = $this->request->getPost('title');
        if (!is_string($data['title'])) {
            $errors['title'] = 'Title expected';
        }

        //type
        $data['type'] = $this->request->getPost('type');
        if (!is_string($data['type'])) {
            $errors['type'] = 'Type expected';
        }

        //target
        $data['target'] = $this->request->getPost('target');
        if (!is_string($data['target'])) {
            $errors['target'] = 'Target expected';
        }

        //message
        $data['message'] = $this->request->getPost('message');
        if (!is_string($data['message'])) {
            $errors['message'] = 'Message expected';
        }

        //photo
        $data['photo']="null";
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
                        $data['photo']=$this->getDi()->getShared('siteurl').'/'.$FileName;
                    }
                } else {
                    return json_encode(array("status"=>"failed","message"=>"Photo must be either JPEG or PNG or GIF file format"));
                }
            }
        }
        
        //Send Message
        $response="";
        if($data['type']=="notifications"){
            $response=$generalService->send_pushnotificationtoall($data['title'],$data['message'],$data['photo']);
        } elseif($data['type']=="newsletter"){
            if($data['target']=="users"){
                $resultData=$user->allUsers(); // Get User data from the Database
                foreach($resultData as $userData) {
                    $itemList['userid']=$userData->id;
                    $itemList['username']=$userData->username;
                    $itemList['firstname']=$userData->firstname;
                    $itemList['lastname']=$userData->lastname;
                    $itemList['useremail']=$userData->email;
                    $emailerService->sendNewsletterEmail($data['title'], $data['message'], $itemList['username'], $itemList['useremail']);
                }
            } elseif($data['target']=="merchants"){
                $resultData=$merchant->allMerchant(); // Get User data from the Database
                foreach($resultData as $userData) {
                    $itemList['userid']=$userData->id;
                    $itemList['username']=$userData->username;
                    $itemList['firstname']=$userData->firstname;
                    $itemList['lastname']=$userData->lastname;
                    $itemList['useremail']=$userData->email;
                    $emailerService->sendNewsletterEmail($data['title'], $data['message'], $itemList['username'], $itemList['useremail']);
                }
            } else {
                //Send users
                $resultData=$user->allUsers(); // Get User data from the Database
                foreach($resultData as $userData) {
                    $itemList['userid']=$userData->id;
                    $itemList['username']=$userData->username;
                    $itemList['firstname']=$userData->firstname;
                    $itemList['lastname']=$userData->lastname;
                    $itemList['useremail']=$userData->email;
                    $emailerService->sendNewsletterEmail($data['title'], $data['message'], $itemList['username'], $itemList['useremail']);
                }
                //Send merchants
                $resultData=$merchant->allMerchant(); // Get User data from the Database
                foreach($resultData as $userData) {
                    $itemList['userid']=$userData->id;
                    $itemList['username']=$userData->username;
                    $itemList['firstname']=$userData->firstname;
                    $itemList['lastname']=$userData->lastname;
                    $itemList['useremail']=$userData->email;
                    $emailerService->sendNewsletterEmail($data['title'], $data['message'], $itemList['username'], $itemList['useremail']);
                }
            }
        }
        echo json_encode(array("status"=>"success","message"=>"Sent successfully!","response"=>"$response")); 
    }
}
?>
