<?php

use Phalcon\Mvc\Controller;
use Phalcon\Registry;

class TransactionsController extends \Phalcon\Mvc\Controller {

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

    public function addAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $errors = [];
        $data = [];
        $transactions = new Transactions();
        $user = new Users();
        $emailerService = new EmailerService();
        $smsService = new SMSService();

        /** Validation Block **/
        //User Data
        $data['orderid'] = date("Ymdhis", strtotime("now")).rand(100,1000); //Transaction Order ID;
        $data['userid'] = $this->request->getPost('userid');
        $data['username'] = $this->request->getPost('username');
        $data['useremail'] = $this->request->getPost('useremail');
        $data['userphone'] = $this->request->getPost('userphone');
        
    }

    public function listsAction()
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
        $resultData=$transactions->allTransactions(); // Get transactions data from the Database
        foreach($resultData as $transactionsData) {
            $itemList['id']=$transactionsData->id;
            $itemList['orderid']=$transactionsData->orderid;
            $itemList['details']=$transactionsData->details;
            $itemList['amount']=$transactionsData->amount;
            $itemList['paymentstatus']=$transactionsData->paymentstatus;
            $itemList['paymentmethod']=$transactionsData->paymentmethod;
            $itemList['type']=$transactionsData->type;
            $itemList['customerid']=$transactionsData->customerid;
            $itemList['customername']=$transactionsData->customername;
            $itemList['date']=$transactionsData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);    
    }

    public function searchListsAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $transactions = new Transactions();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$transactions->searchTransactions($this->request->getPost('query')); // Get transactions data from the Database
        foreach($resultData as $transactionsData) {
            $itemList['id']=$transactionsData->id;
            $itemList['orderid']=$transactionsData->orderid;
            $itemList['details']=$transactionsData->details;
            $itemList['amount']=$transactionsData->amount;
            $itemList['paymentstatus']=$transactionsData->paymentstatus;
            $itemList['type']=$transactionsData->type;
            $itemList['paymentmethod']=$transactionsData->paymentmethod;
            $itemList['customerid']=$transactionsData->customerid;
            $itemList['customername']=$transactionsData->customername;
            $itemList['date']=$transactionsData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);       
    }

    public function bycustomerAction($resultLimit=0)
    {
        /** Init Block **/
        $authService = new AuthService();
        $transactions = new Transactions();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultNum = (int)$resultLimit;
        $resultData=$transactions->transactionsByCustomer($this->request->getPost('userid')); // Get customer transactions data from the Database
        foreach($resultData as $transactionsData) {
            $itemList['id']=$transactionsData->id;
            $itemList['orderid']=$transactionsData->orderid;
            $itemList['type']=$transactionsData->type;
            $itemList['details']=$transactionsData->details;
            $itemList['amount']=$transactionsData->amount;
            $itemList['paymentstatus']=$transactionsData->paymentstatus;
            $itemList['paymentmethod']=$transactionsData->paymentmethod;
            $itemList['customerid']=$transactionsData->customerid;
            $itemList['customername']=$transactionsData->customername;
            $itemList['date']=$transactionsData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);       
    }

    public function userpaymentsAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $transactions = new Transactions();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$transactions->transactionsByCustomer($this->request->getPost('userid')); // Get customer transactions data from the Database
        foreach($resultData as $transactionsData) {
            $itemList['id']=$transactionsData->id;
            $itemList['orderid']=$transactionsData->orderid;
            $itemList['details']=$transactionsData->details;
            $itemList['amount']=$transactionsData->amount;
            $itemList['paymentstatus']=$transactionsData->paymentstatus;
            $itemList['paymentmethod']=$transactionsData->paymentmethod;
            $itemList['type']=$transactionsData->type;
            $itemList['customerid']=$transactionsData->customerid;
            $itemList['customername']=$transactionsData->customername;
            $itemList['photo']=$this->getDi()->getShared('siteurl').'/payment-icon.png';
            $itemList['date']=$transactionsData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);       
    }
    
    public function bypartnerAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $transactions = new Transactions();
        $user = new Users();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $userReferralCode=$user->findUserReferralCode($this->request->getPost('userid')); // Get customer referral code data from the Database
        $resultData=$transactions->transactionsByReferrer($userReferralCode); // Get customer referrals transactions data from the Database
        foreach($resultData as $transactionsData) {
            $itemList['id']=$transactionsData->id;
            $itemList['orderid']=$transactionsData->orderid;
            $itemList['details']=$transactionsData->details;
            $itemList['amount']=$transactionsData->amount;
            $itemList['paymentstatus']=$transactionsData->paymentstatus;
            $itemList['deliverystatus']=$transactionsData->deliverystatus;
            $itemList['paymentmethod']=$transactionsData->paymentmethod;
            $itemList['type']=$transactionsData->type;
            $itemList['customerid']=$transactionsData->customerid;
            $itemList['customername']=$transactionsData->customername;
            $itemList['customerreferrer']=$transactionsData->customerreferrer;
            $itemList['customerreferrercommission']=$transactionsData->customerreferrercommission;
            $itemList['customerreferrerpayment']=$transactionsData->customerreferrerpayment;
            $itemList['photo']=$this->getDi()->getShared('siteurl').'/payment-icon.png';
            $itemList['date']=$transactionsData->date;
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);       
    }

    public function singlerecordAction($invoiceid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $transactions = new Transactions();
        $user = new Users();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $transactionsData=$transactions->singleTransaction($invoiceid); // Get Product data from the Database
        $id=$transactionsData->id;
        $orderid=$transactionsData->orderid;
        $type=$transactionsData->type;
        $details=$transactionsData->details;
        $amount=$transactionsData->amount;
        $paymentstatus=$transactionsData->paymentstatus;
        $paymentmethod=$transactionsData->paymentmethod;
        $userid=$transactionsData->customerid;
        $username=$transactionsData->customername;
        $date=$transactionsData->date;
        $userreferrer=$transactionsData->customerreferrer;
        $userreferrercommission=$transactionsData->customerreferrercommission;
        $userreferrerpayment=$transactionsData->customerreferrerpayment;
        $referrerData=$user->singleReferrer($userreferrer); // Get Referrer data from the Database
        $referrername=$referrerData->username;
        echo json_encode(array("id"=>"$id","orderid"=>"$orderid","shipperid"=>"$shipperid","shipper"=>"$shipper","details"=>"$details","amount"=>"$amount","paymentstatus"=>"$paymentstatus","deliverystatus"=>"$deliverystatus","paymentmethod"=>"$paymentmethod","deliverymethod"=>"$deliverymethod","customerid"=>"$userid","customername"=>"$username","customeremail"=>"$useremail","customerphone"=>"$userphone","customeraddress"=>"$useraddress","customercity"=>"$usercity","customerstate"=>"$userstate","customerzipcode"=>"$userzipcode","customercountry"=>"$usercountry","note"=>"$usercomment","customerreferrer"=>"$userreferrer","customerreferrername"=>"$referrername","customerreferrercommission"=>"$userreferrercommission","customerreferrerpayment"=>"$userreferrerpayment","posteddate"=>"$date"));
    }

    public function singleAction($invoiceid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $transactions = new Transactions();
        $user = new Users();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $userData=$user->singleUser($this->request->getPost('userid')); // Get User data from the Database
        $useremail=$userData->email;
        $transactionsData=$transactions->singleCustomerTransaction($invoiceid,$useremail); // Get Product data from the Database
        $id=$transactionsData->id;
        $orderid=$transactionsData->orderid;
        $gatewayreferenceid=$transactionsData->gatewayreferenceid;
        $details=$transactionsData->details;
        $amount=$transactionsData->amount;
        $paymentstatus=$transactionsData->paymentstatus;
        $deliverystatus=$transactionsData->deliverystatus;
        $paymentmethod=$transactionsData->paymentmethod;
        $deliverymethod=$transactionsData->deliverymethod;
        $userid=$transactionsData->customerid;
        $username=$transactionsData->customername;
        $date=$transactionsData->date;
        if($paymentstatus=="unpaid"){
            $gatewayreferenceid = bin2hex(random_bytes('15')); //Random Payment Gateway Reference ID;
            $transactions->updateTransaction($orderid, $gatewayreferenceid, "gatewayreferenceid"); //Update details changes
            $buyer=true;
        }
        echo json_encode(array("id"=>"$id","orderid"=>"$orderid","gatewayreferenceid"=>"$gatewayreferenceid","shipperid"=>"$shipperid","shipper"=>"$shipper","details"=>"$details","amount"=>"$amount","paymentstatus"=>"$paymentstatus","deliverystatus"=>"$deliverystatus","paymentmethod"=>"$paymentmethod","deliverymethod"=>"$deliverymethod","customerid"=>"$userid","customername"=>"$username","customeremail"=>"$useremail","customerphone"=>"$userphone","customeraddress"=>"$useraddress","customercity"=>"$usercity","customerstate"=>"$userstate","customerzipcode"=>"$userzipcode","customercountry"=>"$usercountry","deliverylocation"=>"$deliverylocation","note"=>"$usercomment","buyer"=>"$buyer","posteddate"=>"$date","date"=>"$date"));
    }
    
    public function paymentstatusupdateAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $transactions = new Transactions();
        $notifications = new Notifications();
        $user = new Users();
        $emailerService = new EmailerService();
        $smsService = new SMSService();
        $errors = [];
        $data = [];

        //Proceed with the Business Logic
        //Payment Gateway data
        $all_post_data = $this->request->getPost(); //All gateway data
        $result = json_decode($all_post_data, true); //Decode gateway data
        $orderid = $result['data']['reference'];
        if (!is_string($orderid)) {
            return json_encode(array("status"=>"failed","message"=>"Payment reference must be provided!"));
        }

        //Verify Payment - PAYSTACK
        if (array_key_exists('data', $result) && array_key_exists('status', $result['data']) && ($result['data']['status'] === 'success')) {
            //Perform necessary success action
            $paymentresult = $result['data']['status'];
            $paidamount = $result['data']['amount'];
            $paymentcurrency = $result['data']['currency'];
            $paymentdate = $result['data']['transaction_date'];
            $gatewayresponse = $result['data']['gateway_response'];
            $paymentchannel = $result['data']['channel'];
            $paymentip_address = $result['data']['ip_address'];
            $user_authorization_code = $result['data']['authorization']['authorization_code'];
            $user_card_number = $result['data']['authorization']['last4'];
            $user_email = $result['data']['customer']['email'];
            $paymentstatus="paid";
            $todaydate=date("d-m-Y");
            $paidamount = substr($result['data']['amount'], 0, -2); 

            //Get User Data
            $userData=$user->singleUser($user_email); // Get User data from the Database
            $data['userid']=$userData->id;
            $data['username']=$userData->username;
            $data['useremail']=$userData->email;
            $data['userphone']=$userData->phone;
            $data['userreferrer']=$userData->referrercode;
            $data['walletbalance']=$userData->walletbalance;

            //Add to Transactions 
            $data['orderid'] = $orderid; //Transaction Order ID;
            $data['type'] = "deposit";
            $data['note'] = "Deposit into account wallet";
            $data['amount'] = $paidamount;
            $data['paymentstatus'] = $paymentstatus;
            $data['paymentmethod'] = $paymentchannel;
            $transactions->addTransaction($data);
            $emailerService->sendAdminNewOrderAlertEmail($data['orderid'], $data['type'], $data['amount'], $data['username'], $data['useremail']);
            $emailerService->sendCustomerNewOrderConfirmationEmail($data['orderid'], $data['type'], $data['amount'], $data['username'], $data['useremail']);
            $smsService->sendOrderNotificationCustomerSMS($data['userphone'], $data['orderid']);

            //Update User Wallet Data
            $newAccountWalletBalance=$data['walletbalance']+$paidamount;
            $user->updateUserAccount($data['userid'], $newAccountWalletBalance, "walletbalance"); //Update User account

            /**Add Notification To User starts**/
            $data['userid'] = $this->request->getPost('userid');
            $data['username'] = $this->request->getPost('username');
            $data['title'] = "Deposit";
            $data['type'] = "transaction";
            $data['details'] = "New deposit on your account";
            $data['fromid'] = "";
            $data['from'] = "";
            $data['status'] = "unread";
            $data['actionid'] = "";
            $data['actionsubid'] = "";
            $data['time'] = date("h:i:sa");
            $data['date'] = date("d-m-Y");
            $notifications->addEntry($data); //Add
            /**Add Notification To User ends**/

            //Output result
            return json_encode(array("status"=>"success","message"=>"Payment successful!"));
        } else {
            return json_encode(array("status"=>"failed","message"=>"Payment unsuccessful!"));
        }
    }

    public function paymentstatusverifyAction($myreferenceid="0")
    {
        /** Init Block **/
        $authService = new AuthService();
        $transactions = new Transactions();
        $notifications = new Notifications();
        $user = new Users();
        $emailerService = new EmailerService();
        $smsService = new SMSService();
        $errors = [];
        $data = [];

        //Proceed with the Business Logic
        try {
            //Payment Gateway data
            if ($myreferenceid != "0") {
                $orderid = "$myreferenceid";
            } elseif($this->request->getPost("reference")) {
                $orderid = $this->request->getPost("reference");
            } elseif($this->request->getPost("data")) {
                $orderid = $this->request->getPost("data")['reference'];
            } else {
                return json_encode(array("status"=>"failed","message"=>"Payment reference must be provided!"));
            }

            //Get Order Info
            /**$transactionsData=$transactions->singleTransaction($orderid); // Get order data from the Database
            $transactionid=$transactionsData->orderid;
            $deliverystatus=$transactionsData->deliverystatus;
            $userid=$transactionsData->customerid;
            $username=$transactionsData->customername;
            $userphone=$transactionsData->customerphone;
            $useremail=$transactionsData->customeremail;
            $amount=$transactionsData->amount;
            $transactiontype=$transactionsData->type;
            $packagenextexpirydate=$transactionsData->packagenextexpirydate;**/

            //Access Third-Party Gateway API to verify Payment - PAYSTACK
            $result = array();
            //The parameter after verify/ is the transaction reference to be verified
            $url = "https://api.paystack.co/transaction/verify/$orderid";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt(
            $ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer sk_live_604b3fed49de98c2e64360105aeca50e434c28ee']
            );
            $request = curl_exec($ch);
            curl_close($ch);

            if ($request) {
            $result = json_decode($request, true);
            }

            if (array_key_exists('data', $result) && array_key_exists('status', $result['data']) && ($result['data']['status'] === 'success')) {
                //Perform necessary success action
                $paymentresult = $result['data']['status'];
                $paidamount = $result['data']['amount'];
                $paymentcurrency = $result['data']['currency'];
                $paymentdate = $result['data']['transaction_date'];
                $gatewayresponse = $result['data']['gateway_response'];
                $paymentchannel = $result['data']['channel'];
                $paymentip_address = $result['data']['ip_address'];
                $user_authorization_code = $result['data']['authorization']['authorization_code'];
                $user_card_number = $result['data']['authorization']['last4'];
                $user_email = $result['data']['customer']['email'];
                $paymentstatus="paid";
                $todaydate=date("d-m-Y");
                $paidamount = substr($result['data']['amount'], 0, -2); 

                //Get User Data
                $userData=$user->singleUserByEmail($user_email); // Get User data from the Database
                $data['userid']=$userData->id;
                $data['username']=$userData->username;
                $data['useremail']=$userData->email;
                $data['userphone']=$userData->phone;
                $data['userreferrer']=$userData->referrercode;
                $data['walletbalance']=$userData->walletbalance;

                //Add to Transactions 
                $data['orderid'] = $orderid; //Transaction Order ID;
                $data['type'] = "deposit";
                $data['note'] = "Deposit into account wallet";
                $data['amount'] = $paidamount;
                $data['paymentstatus'] = $paymentstatus;
                $data['paymentmethod'] = $paymentchannel;
                $data['date'] = $todaydate;
                $transactions->addTransaction($data);
                $emailerService->sendAdminNewOrderAlertEmail($data['orderid'], $data['type'], $data['amount'], $data['username'], $data['useremail']);
                $emailerService->sendCustomerNewOrderConfirmationEmail($data['orderid'], $data['type'], $data['amount'], $data['username'], $data['useremail']);
                $smsService->sendOrderNotificationCustomerSMS($data['userphone'], $data['orderid']);

                //Update User Wallet Data
                $newAccountWalletBalance=$data['walletbalance']+$paidamount;
                $user->updateUserAccount($data['userid'], $newAccountWalletBalance, "walletbalance"); //Update User account
                
                /**Add Notification To User starts**/
                //$data['userid'] = $data['userid'];
                //$data['username'] = $data['username'];
                $data['title'] = "Deposit";
                $data['type'] = "transaction";
                $data['details'] = "New deposit on your account";
                $data['fromid'] = "";
                $data['from'] = "";
                $data['status'] = "unread";
                $data['actionid'] = "";
                $data['actionsubid'] = "";
                $data['time'] = date("h:i:sa");
                $data['date'] = date("d-m-Y");
                $notifications->addEntry($data); //Add
                /**Add Notification To User ends**/

                //Output result
                return json_encode(array("status"=>"success","message"=>"Payment successful!"));
            } else {
                return json_encode(array("status"=>"failed","message"=>"Payment unsuccessful!"));
            }
        } catch(Exception $e) {
            return json_encode(array("status"=>"failed","message"=>$e->getMessage()));
        } 
    }

    public function payonlineAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $transactions = new Transactions();
        $user = new Users();
        $emailerService = new EmailerService();
        $smsService = new SMSService();

        //Proceed with the Business Logic

        //Access Third-Party Gateway API to verify Payment - PAYSTACK
        $result = array();
        //The parameter after verify/ is the transaction reference to be verified
        $url = "https://api.paystack.co/transaction/verify/$orderid";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
        $ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer sk_live_604b3fed49de98c2e64360105aeca50e434c28ee']
        );
        $request = curl_exec($ch);
        curl_close($ch);

        if ($request) {
        $result = json_decode($request, true);
        }

    }

    public function withdrawAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $transactions = new Transactions();
        $notifications = new Notifications();
        $user = new Users();
        $games = new Games();
        $gamebets = new Gamebets();
        $emailerService = new EmailerService(); 
        $smsService = new SMSService();
        $errors = [];
        $data = [];
        $itemList = array();      

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Proceed with the Business Logic
        //Withdrawamount
        $data['withdrawamount'] = $this->request->getPost('withdrawamount');
        if (!is_int($data['withdrawamount'])) {
            return json_encode(array("status"=>"failed","message"=>"Withdraw amount expected"));
        }

        //Get User Data
        $userData=$user->singleUser($this->request->getPost('userid')); // Get User data from the Database
        $data['userid']=$userData->id;
        $data['username']=$userData->username;
        $data['useremail']=$userData->email;
        $data['userphone']=$userData->phone;
        $data['userreferrer']=$userData->referrercode;
        $data['walletbalance']=$userData->walletbalance;
        $paystackrecipientcode=$userData->paystackrecipientcode;
        $transactionTotalResultData=$gamebets->sumBetPendingAmountByUser($data['userid']); // Get User Total Orders Sum data from the Database
        $totalpending_amount=$transactionTotalResultData->totalsum;
        //Remove pending bet amount from wallet balance to get Available Balance
        $userAvailableBalance=$data['walletbalance']-$totalpending_amount;
        $withdrawalAmount=$data['withdrawamount'];

        //Check if amount withdraw request is up to available balance
        if($withdrawalAmount>$userAvailableBalance){
            return json_encode(array("status"=>"failed","message"=>"Withdraw amount requested exceeds your available wallet balance"));
        }

        //Check if amount withdraw request is up to available balance
        if(empty($paystackrecipientcode)){
            return json_encode(array("status"=>"failed","message"=>"You need to add your bank account details by editing your profile account before proceeding."));
        }

        //Access Third-Party Gateway API to verify Payment - PAYSTACK
        //Start cURL
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.paystack.co/transfer",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>'{"source": "balance", "reason": "account wallet balance withdrawal", "amount": "' .$withdrawalAmount. '", "recipient": "' .$paystackrecipientcode. '"}',
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
        }
    
        if (array_key_exists('data', $result) && array_key_exists('status', $result['data'])) {
                //Perform necessary success action
                $paymentresult = $result['data']['status'];
                $paidamount = $result['data']['amount'];
                $paymentcurrency = $result['data']['currency'];
                $transfer_code = $result['data']['transfer_code'];
                $paymentstatus="paid";
                $todaydate=date("d-m-Y");
                $paidamount = substr($result['data']['amount'], 0, -2); 

                if($paymentresult=="otp" || $paymentresult=="pending"){
                    //Payment successful
                    $userNewBalance=$data['walletbalance']-$data['withdrawamount'];
                    $user->updateUserAccount($data['userid'], $userNewBalance, "walletbalance"); //Update User account
                    //Add to Transactions
                    $data['orderid'] = date("Ymdhis", strtotime("now")).rand(100,1000); //Transaction Order ID;
                    $data['type'] = "withdrawal";
                    $data['note'] = "Withdrawal from account wallet";
                    $data['amount'] = $paidamount;
                    $data['paymentstatus'] = $paymentstatus;
                    $data['paymentmethod'] = "automated";
                    $transactions->addTransaction($data);
                    $emailerService->sendAdminNewOrderAlertEmail($data['orderid'], $data['type'], $data['amount'], $data['username'], $data['useremail']);
                    $emailerService->sendCustomerNewOrderConfirmationEmail($data['orderid'], $data['type'], $data['amount'], $data['username'], $data['useremail']);
                    $smsService->sendOrderNotificationCustomerSMS($data['userphone'], $data['orderid']);
                    if($paymentresult=="otp"){
                        //Finalize Transfer
                        //Start cURL
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.paystack.co/transfer/finalize_transfer",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS =>'{"transfer_code": "' .$transfer_code. '", "otp": "000"}',
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer sk_live_604b3fed49de98c2e64360105aeca50e434c28ee'
                        ),
                        ));
                        $response_output = curl_exec($curl);
                        curl_close($curl);
                        //End cURL
                    }
                } else {
                    //Payment unsuccessful
                    return json_encode(array("status"=>"failed","message"=>"Payment unsuccessful at the moment, please try again later"));
                }
        } else {
            //Payment unsuccessful
            return json_encode(array("status"=>"failed","message"=>"PayStack error: ".$result['message']));
        }
        /**Add Notification To User starts**/
        $data['userid'] = $this->request->getPost('userid');
        $data['username'] = $this->request->getPost('username');
        $data['title'] = "Withdrawal";
        $data['type'] = "transaction";
        $data['details'] = "New withdrawal on your account";
        $data['fromid'] = "";
        $data['from'] = "";
        $data['status'] = "unread";
        $data['actionid'] = "";
        $data['actionsubid'] = "";
        $data['time'] = date("h:i:sa");
        $data['date'] = date("d-m-Y");
        $notifications->addEntry($data); //Add
        /**Add Notification To User ends**/
        return json_encode(array("status"=>"success","message"=>"Withdrawal successful. Please allow some minutes to receive in your provided bank account."));
    }

    public function removeTransactionAction()
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
        $transactions->removeTransaction($this->request->getPost('transactionid')); //Delete transaction
        echo json_encode(array("status"=>"success","message"=>"Transaction deleted successfully"));
    }

}
?>