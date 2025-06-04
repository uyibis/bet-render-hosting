<?php

use Phalcon\Mvc\Controller;

class ChatsController extends \Phalcon\Mvc\Controller {

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

    public function privatechatAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $user = new Users();
        $privatechats = new Privatechats();
        $authService = new AuthService();
        $generalService = new GeneralService();
        $emailerService = new EmailerService();

        //Auth Check
        /**$authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } **/

        /** Validation Block **/        
        //action type
        $data['action'] = $this->request->getPost('action');
        if (!is_string($data['action'])) {
            return json_encode(array("status"=>"failed","message"=>"chat action expected!")); 
        }

        //targetid
        $data['targetid'] = $this->request->getPost('targetid');
        if (!is_string($data['targetid'])) {
            return json_encode(array("status"=>"failed","message"=>"chat targetid expected!")); 
        }

        //lastmessageid
        $data['lastmessageid'] = $this->request->getPost('lastmessageid');

        //mobileapp
        $data['mobileapp'] = $this->request->getPost('mobileapp');

        //User Details
        $data['userid'] = $this->request->getPost('userid');
        $data['username'] = $this->request->getPost('username');

        //Get current date and time
        $data['date'] = date("d-m-Y");
        $data['currenttime'] = date('h:i A');
        $data['time'] = time();
        $data['howlong'] = date('o-m-d H:i:s',time() - 5 * 60);
        $data['is_admin'] = '0';

        //Action starts
        if($data['action']=="display_chat"){ //Display All Chat Messages In The Room
            $messagesLists = array();
            $messagesList = array();
            $limitnum=20;
            $resultData=$privatechats->allChats($this->request->getPost('userid'),$data['targetid'],$limitnum); // Get data from the Database
            foreach($resultData as $pageData) {
                $messagesList['chatID'] = $pageData->id;
                $messagesList['userID'] = $pageData->from;
                $messagesList['userName'] = $pageData->username;
                $messagesList['user_read'] = $pageData->user_read;
                $messagesList['chatMessage'] = $pageData->message;
                $messagesList['time'] = date('H:i:s', $pageData->time);
                $messagesList['date'] = $pageData->date;
                $messagesList['userPhoto'] = $this->getDi()->getShared('siteurl').'/'.$generalService->getUserPhoto($pageData->from,'user');
                $messagesList['time_ago'] = $generalService->getTimeAgo($pageData->time);
                $messagesLists[] = $messagesList;
            } 
            //Update chats to read
            $privatechats->updateChatsToRead($this->request->getPost('userid'),$data['targetid']);
            //Print out result
            echo json_encode(array_reverse($messagesLists));
        } elseif($data['action']=="post_chat"){ //Post New Chat Message To Database In The Room
            //message
            $data['message'] = $this->request->getPost('message');
            if (!is_string($data['message'])) {
                return json_encode(array("status"=>"failed","message"=>"chat message expected!")); 
            }
            //Post chat message to DB
            $privatechats->addChatEntry($this->request->getPost('userid'),$data['targetid'],$data);
            //Print out result
            echo json_encode(array("status"=>"success","message"=>"Chat posted successfully")); 
        }
    }

    public function groupchatAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $user = new Users();  
        $groupchats = new Groupchats();
        $authService = new AuthService();
        $generalService = new GeneralService();
        $emailerService = new EmailerService();

        //Auth Check
        /**$authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } **/

        /** Validation Block **/        
        //action type
        $data['action'] = $this->request->getPost('action');
        if (!is_string($data['action'])) {
            return json_encode(array("status"=>"failed","message"=>"chat action expected!")); 
        }

        //targetid
        $data['targetid'] = $this->request->getPost('targetid');
        if (!is_string($data['targetid'])) {
            return json_encode(array("status"=>"failed","message"=>"chat targetid expected!")); 
        }

        //lastmessageid
        $data['lastmessageid'] = $this->request->getPost('lastmessageid');

        //mobileapp
        $data['mobileapp'] = $this->request->getPost('mobileapp');

        //User Details
        $data['userid'] = $this->request->getPost('userid');
        $data['username'] = $this->request->getPost('username');

        //Get current date and time
        $data['date'] = date("d-m-Y");
        $data['currenttime'] = date('h:i A');
        $data['time'] = time();
        $data['howlong'] = date('o-m-d H:i:s',time() - 5 * 60);
        $data['is_admin'] = '0';

        //Action starts
        if($data['action']=="display_chat"){ //Display All Chat Messages In The Room
            $messagesLists = array();
            $messagesList = array();
            $limitnum=20;
            $resultData=$groupchats->allChats($data['targetid'],$limitnum); // Get data from the Database
            foreach($resultData as $pageData) {
                $messagesList['chatID']=$pageData->id;
                $messagesList['userID']=$pageData->user_id;
                $messagesList['userName']=$pageData->username;
                $messagesList['userAdmin']=$pageData->is_admin;
                $messagesList['chatMessage']=$pageData->message;
                $messagesList['time']=date('H:i:s', $pageData->time);
                $messagesList['date']=$pageData->date;
                $messagesList['userPhoto']=$this->getDi()->getShared('siteurl').'/'.$generalService->getUserPhoto($pageData->user_id,'user');
                $messagesList['time_ago']=$generalService->getTimeAgo($pageData->time);
                $messagesLists[]=$messagesList;
            } 
            //Print out result
            echo json_encode(array_reverse($messagesLists));
        } elseif($data['action']=="post_chat"){ //Post New Chat Message To Database In The Room
            //message
            $data['message'] = $this->request->getPost('message');
            if (!is_string($data['message'])) {
                return json_encode(array("status"=>"failed","message"=>"chat message expected!")); 
            }
            //Post chat message to DB
            $groupchats->addChatEntry($data['targetid'],$this->request->getPost('userid'),$data);
            //Print out result
            echo json_encode(array("status"=>"success","message"=>"Chat posted successfully")); 
        } elseif($data['action']=="online_users"){ //Display All Online Active Users In The Room
            //Array
            $usersLists = array();
            $usersList = array();
            $resultData=$groupchats->getAllOnlineUsers($data['targetid'],$data); // Get data from the Database
            foreach($resultData as $pageData) {
                $usersList['userID']=$pageData->user_id;
                $usersList['userName']=$pageData->username;
                $usersList['userPhoto'] = $this->getDi()->getShared('siteurl').'/'.$generalService->getUserPhoto($pageData->user_id,'user');
                $usersLists[] = $usersList;
            } 
            echo json_encode($usersLists); 
        }
    }

    public function messagelistsAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $user = new Users();     
        $privatechats = new Privatechats(); 
        //$groupchats = new Groupchats();
        $authService = new AuthService();
        $generalService = new GeneralService();
        $emailerService = new EmailerService();
        
        //Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$privatechats->allChatHistory($this->request->getPost('userid')); // Get chat history data from the Database
        foreach($resultData as $chathistoryData) {
            $itemList['id']=$chathistoryData->id;
            $itemList['userid']=$chathistoryData->from;
            $itemList['username']=$chathistoryData->username;
            $itemList['userphoto']=$this->getDi()->getShared('siteurl').'/'.$generalService->getUserPhoto($chathistoryData->from,'user');
            $itemList['message']=$privatechats->getLastChatMessage($this->request->getPost('userid'),$itemList['userid']);
            $itemList['date']=$generalService->getTimeAgo($chathistoryData->time);
            $itemLists[] = $itemList;
        } 
        echo json_encode($itemLists);   
    }

    public function profiledetailsAction()
    {
        /** Init Block **/
        $errors = [];
        $data = [];
        $user = new Users();      
        //$groupchats = new Groupchats();
        $authService = new AuthService();
        $generalService = new GeneralService();
        $emailerService = new EmailerService();

    }
}
?>