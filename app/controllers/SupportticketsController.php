<?php

use Phalcon\Mvc\Controller;

class SupportticketsController extends \Phalcon\Mvc\Controller {

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

    public function adminaddAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $supporttickets = new Supporttickets();
        $emailerService = new EmailerService();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //User ID and Name
        $data['origin'] = $this->request->getPost('origin');
        $data['fromid'] = $this->request->getPost('userid');
        $data['from'] = $this->request->getPost('username');
        $data['updatedby'] = $this->request->getPost('username');
        
        //Ticket title
        $data['title'] = $this->request->getPost('title');
        if (!is_string($data['title'])) {
            $errors['title'] = 'Ticket title expected';
        }

        //Ticket recepient
        $data['toid'] = $this->request->getPost('toid');
        if (!is_string($data['toid'])) {
            $errors['toid'] = 'Ticket recepient expected';
        }
        $data['to'] = $generalService->getUserName($data['toid'], $this->request->getPost('target'));
        $data['toemail'] = $generalService->getUserEmail($data['toid'], $this->request->getPost('target'));
        $data['target'] = $this->request->getPost('target');

        //Ticket type
        $data['type'] = $this->request->getPost('type');

        //Ticket details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Ticket details expected';
        }

        //Ticket status
        $data['status'] = "open";

        //Get current date and time
        $data['updateddate'] = date("d-m-Y");
        $data['date'] = date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        // Store to Database Model and check for errors
        $ticketid = $supporttickets->addTicket($data);
        $emailerService->sendAdminNewTicketAlertEmail($ticketid, $data['title'], $this->request->getPost('username'), $data['to'], $data['toemail']);
        $emailerService->sendNewTicketAlertRecepientEmail($ticketid, $data['title'], $this->request->getPost('username'), $data['to'], $data['toemail']);
        echo json_encode(array("status"=>"success","message"=>"Ticket added successfully"));
    }

    public function merchantaddAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $supporttickets = new Supporttickets();
        $emailerService = new EmailerService();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "merchant"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //User ID and Name
        $data['origin'] = $this->request->getPost('origin');
        $data['fromid'] = $this->request->getPost('userid');
        $data['from'] = $this->request->getPost('username');
        $data['fromemail'] = $generalService->getUserEmail($data['fromid'], 'merchant');
        $data['updatedby'] = $this->request->getPost('username');
        
        //Ticket title
        $data['title'] = $this->request->getPost('title');
        if (!is_string($data['title'])) {
            $errors['title'] = 'Ticket title expected';
        }

        //Ticket recepient
        $data['toid'] = $this->request->getPost('toid');
        if (!is_string($data['toid'])) {
            $errors['toid'] = 'Ticket recepient expected';
        }
        $data['to'] = $generalService->getUserName($data['toid'], $this->request->getPost('target'));
        $data['target'] = $this->request->getPost('target');
        $data['toemail'] = $generalService->getUserEmail($data['toid'], $data['target']);

        //Ticket type
        $data['type'] = $this->request->getPost('type');

        //Ticket details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Ticket details expected';
        }

        //Ticket status
        $data['status'] = "open";

        //Get current date and time
        $data['updateddate'] = date("d-m-Y");
        $data['date'] = date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        // Store to Database Model and check for errors
        $ticketid = $supporttickets->addTicket($data);
        $emailerService->sendAdminNewTicketAlertEmail($ticketid, $data['title'], $this->request->getPost('username'), $this->request->getPost('to'), $data['toemail']);
        $emailerService->sendNewTicketAlertEmail($ticketid, $data['title'], $this->request->getPost('from'), $data['fromemail']);
        echo json_encode(array("status"=>"success","message"=>"Ticket added successfully"));
    }
    
    public function addAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $supporttickets = new Supporttickets();
        $emailerService = new EmailerService();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "customer"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //User ID and Name
        $data['origin'] = $this->request->getPost('origin');
        $data['fromid'] = $this->request->getPost('userid');
        $data['from'] = $this->request->getPost('username');
        $data['fromemail'] = $generalService->getUserEmail($data['fromid'], 'customer');
        $data['updatedby'] = $this->request->getPost('username');
        
        //Ticket title
        $data['title'] = $this->request->getPost('title');
        if (!is_string($data['title'])) {
            $errors['title'] = 'Ticket title expected';
        }

        //Ticket recepient
        $data['toid'] = $this->request->getPost('toid');
        if (!is_string($data['toid'])) {
            $errors['toid'] = 'Ticket recepient expected';
        }
        $data['to'] = $generalService->getUserName($data['toid'], $this->request->getPost('target'));
        $data['toemail'] = $generalService->getUserEmail($data['toid'], $this->request->getPost('target'));

        //Ticket type
        $data['type'] = $this->request->getPost('type');

        //Ticket details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Ticket details expected';
        }

        //Ticket status
        $data['status'] = "open";

        //Get current date and time
        $data['updateddate'] = date("d-m-Y");
        $data['date'] = date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        // Store to Database Model and check for errors
        $ticketid = $supporttickets->addTicket($data);
        $emailerService->sendAdminNewTicketAlertEmail($ticketid, $data['title'], $this->request->getPost('username'), $this->request->getPost('to'), $data['toemail']);
        $emailerService->sendNewTicketAlertEmail($ticketid, $data['title'], $this->request->getPost('from'), $data['fromemail']);
        echo json_encode(array("status"=>"success","message"=>"Ticket added successfully"));
    }

    public function adminreplyAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $supporttickets = new Supporttickets();
        $supportticketreplies = new Supportticketreplies();
        $emailerService = new EmailerService();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        //User ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        $data['updatedby'] = $this->request->getPost('username');
        
        //Ticket ID
        $data['ticketid'] = $this->request->getPost('ticketid');
        if (!is_string($data['ticketid'])) {
            $errors['ticketid'] = 'Ticket ID expected';
        }

        //Ticket reply details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Ticket reply details expected';
        }

        //Ticket status
        $data['status'] = "open";

        //Get current date and time
        $data['updateddate'] = date("d-m-Y");
        $data['date'] = date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        //Get Ticket Data
        $ticketsData=$supporttickets->singleTicket($data['ticketid']); // Get ticket data from the Database
        $data['title']=$ticketsData->title;
        $data['fromid']=$ticketsData->fromid;
        $data['from']=$ticketsData->from;
        $data['toid']=$ticketsData->toid;
        $data['to']=$ticketsData->to;
        $data['target']=$ticketsData->target;
        $data['toemail'] = $generalService->getUserEmail($data['toid'], $data['target']);

        // Store to Database Model and check for errors
        $supporttickets->updateTicket($data['ticketid'], $data['updatedby'], $data['status'], $data['date']);
        $supportticketreplies->addTicketReply($data);
        $emailerService->sendAdminNewTicketReplyAlertEmail($data['ticketid'], $data['title'], $data['to'], $data['toemail']);
        $emailerService->sendNewTicketReplyAlertRecepientEmail($data['ticketid'], $data['title'], $this->request->getPost('username'), $data['to'], $data['toemail']);
        echo json_encode(array("status"=>"success","message"=>"Ticket reply added successfully"));
    }

    public function merchantreplyAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $supporttickets = new Supporttickets();
        $supportticketreplies = new Supportticketreplies();
        $emailerService = new EmailerService();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "merchant"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        //User ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        $data['updatedby'] = $this->request->getPost('username');
        
        //Ticket ID
        $data['ticketid'] = $this->request->getPost('ticketid');
        if (!is_string($data['ticketid'])) {
            $errors['ticketid'] = 'Ticket ID expected';
        }

        //Ticket reply details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Ticket reply details expected';
        }

        //Ticket status
        $data['status'] = "open";

        //Get current date and time
        $data['updateddate'] = date("d-m-Y");
        $data['date'] = date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        //Get Ticket Data
        $ticketsData=$supporttickets->singleTicket($data['ticketid']); // Get ticket data from the Database
        $data['title']=$ticketsData->title;
        $data['fromid']=$ticketsData->fromid;
        $data['from']=$ticketsData->from;
        $data['toid']=$ticketsData->toid;
        $data['to']=$ticketsData->to;
        $data['target']=$ticketsData->target;
        $data['fromemail'] = $generalService->getUserEmail($data['fromid'], 'merchant');

        // Store to Database Model and check for errors
        $supporttickets->updateTicket($data['ticketid'], $data['updatedby'], $data['status'], $data['date']);
        $supportticketreplies->addTicketReply($data);
        $emailerService->sendAdminNewTicketReplyAlertEmail($data['ticketid'], $data['title'], $data['from'], $data['fromemail']);
        $emailerService->sendNewTicketReplyAlertEmail($data['ticketid'], $data['title'], $data['from'], $data['fromemail']);
        echo json_encode(array("status"=>"success","message"=>"Ticket reply added successfully"));
    }
    
    public function replyAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $supporttickets = new Supporttickets();
        $supportticketreplies = new Supportticketreplies();
        $emailerService = new EmailerService();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "customer"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        //User ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        $data['updatedby'] = $this->request->getPost('username');
        
        //Ticket ID
        $data['ticketid'] = $this->request->getPost('ticketid');
        if (!is_string($data['ticketid'])) {
            $errors['ticketid'] = 'Ticket ID expected';
        }

        //Ticket reply details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Ticket reply details expected';
        }

        //Ticket status
        $data['status'] = "open";

        //Get current date and time
        $data['updateddate'] = date("d-m-Y");
        $data['date'] = date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        //Get Ticket Data
        $ticketsData=$supporttickets->singleTicket($data['ticketid']); // Get ticket data from the Database
        $data['title']=$ticketsData->title;
        $data['fromid']=$ticketsData->fromid;
        $data['from']=$ticketsData->from;
        $data['toid']=$ticketsData->toid;
        $data['to']=$ticketsData->to;
        $data['target']=$ticketsData->target;
        $data['fromemail'] = $generalService->getUserEmail($data['fromid'], 'customer');

        // Store to Database Model and check for errors
        $supporttickets->updateTicket($data['ticketid'], $data['updatedby'], $data['status'], $data['date']);
        $supportticketreplies->addTicketReply($data);
        $emailerService->sendAdminNewTicketReplyAlertEmail($data['ticketid'], $data['title'], $data['from'], $data['fromemail']);
        $emailerService->sendNewTicketReplyAlertEmail($data['ticketid'], $data['title'], $data['from'], $data['fromemail']);
        echo json_encode(array("status"=>"success","message"=>"Ticket reply added successfully"));
    }

    public function adminlistsAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $supporttickets = new Supporttickets();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$supporttickets->adminAllTickets(); // Get tickets data from the Database
        foreach($resultData as $ticketsData) {
            $itemList['id']=$ticketsData->id;
            $itemList['title']=$ticketsData->title;
            $itemList['details']=$ticketsData->details;
            $itemList['fromid']=$ticketsData->fromid;
            $itemList['from']=$ticketsData->from;
            $itemList['toid']=$ticketsData->toid;
            $itemList['to']=$ticketsData->to;
            $itemList['type']=$ticketsData->type;
            $itemList['status']=$ticketsData->status;
            $itemList['updateddate']=$ticketsData->updateddate;
            $itemList['updatedby']=$ticketsData->updatedby;
            $itemList['replies']=$ticketsData->replies;
            $itemList['date']=$ticketsData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);     
    }

    public function merchantlistsAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $supporttickets = new Supporttickets();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "merchant"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$supporttickets->merchantAllTickets($this->request->getPost('userid')); // Get tickets data from the Database
        foreach($resultData as $ticketsData) {
            $itemList['id']=$ticketsData->id;
            $itemList['title']=$ticketsData->title;
            $itemList['details']=$ticketsData->details;
            $itemList['fromid']=$ticketsData->fromid;
            $itemList['from']=$ticketsData->from;
            $itemList['toid']=$ticketsData->toid;
            $itemList['to']=$ticketsData->to;
            $itemList['type']=$ticketsData->type;
            $itemList['status']=$ticketsData->status;
            $itemList['updateddate']=$ticketsData->updateddate;
            $itemList['updatedby']=$ticketsData->updatedby;
            $itemList['replies']=$ticketsData->replies;
            $itemList['date']=$ticketsData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);     
    }
    
    public function listsAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $supporttickets = new Supporttickets();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "customer"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$supporttickets->allTickets($this->request->getPost('userid')); // Get tickets data from the Database
        foreach($resultData as $ticketsData) {
            $itemList['id']=$ticketsData->id;
            $itemList['title']=$ticketsData->title;
            $itemList['details']=$ticketsData->details;
            $itemList['fromid']=$ticketsData->fromid;
            $itemList['from']=$ticketsData->from;
            $itemList['toid']=$ticketsData->toid;
            $itemList['to']=$ticketsData->to;
            $itemList['type']=$ticketsData->type;
            $itemList['status']=$ticketsData->status;
            $itemList['updateddate']=$ticketsData->updateddate;
            $itemList['updatedby']=$ticketsData->updatedby;
            $itemList['replies']=$ticketsData->replies;
            $itemList['date']=$ticketsData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);     
    }

    public function adminrepliesAction($ticketid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $supporttickets = new Supporttickets();
        $supportticketreplies = new Supportticketreplies();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$supportticketreplies->allTicketReplies($ticketid); // Get ticket replies data from the Database
        foreach($resultData as $ticketrepliesData) {
            $itemList['id']=$ticketrepliesData->id;
            $itemList['ticketid']=$ticketrepliesData->ticketid;
            $itemList['authorid']=$ticketrepliesData->authorid;
            $itemList['author']=$ticketrepliesData->author;
            $itemList['details']=$ticketrepliesData->details;
            $itemList['status']=$ticketrepliesData->status;
            $itemList['date']=$ticketrepliesData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);     
    }

    public function merchantrepliesAction($ticketid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $supporttickets = new Supporttickets();
        $supportticketreplies = new Supportticketreplies();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "merchant"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$supportticketreplies->allTicketReplies($ticketid); // Get ticket replies data from the Database
        foreach($resultData as $ticketrepliesData) {
            $itemList['id']=$ticketrepliesData->id;
            $itemList['ticketid']=$ticketrepliesData->ticketid;
            $itemList['authorid']=$ticketrepliesData->authorid;
            $itemList['author']=$ticketrepliesData->author;
            $itemList['details']=$ticketrepliesData->details;
            $itemList['status']=$ticketrepliesData->status;
            $itemList['date']=$ticketrepliesData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);     
    }
    
    public function repliesAction($ticketid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $supporttickets = new Supporttickets();
        $supportticketreplies = new Supportticketreplies();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "customer"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$supportticketreplies->allTicketReplies($ticketid); // Get ticket replies data from the Database
        foreach($resultData as $ticketrepliesData) {
            $itemList['id']=$ticketrepliesData->id;
            $itemList['ticketid']=$ticketrepliesData->ticketid;
            $itemList['authorid']=$ticketrepliesData->authorid;
            $itemList['author']=$ticketrepliesData->author;
            $itemList['details']=$ticketrepliesData->details;
            $itemList['status']=$ticketrepliesData->status;
            $itemList['date']=$ticketrepliesData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);     
    }

    public function updateAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $supporttickets = new Supporttickets();
        $supportticketreplies = new Supportticketreplies();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        //User ID and Name
        $data['authorid'] = $this->request->getPost('userid');
        $data['author'] = $this->request->getPost('username');
        $data['updatedby'] = $this->request->getPost('username');
        
        //Ticket ID
        $data['ticketid'] = $this->request->getPost('ticketid');
        if (!is_string($data['ticketid'])) {
            $errors['ticketid'] = 'Ticket ID expected';
        }

        //Ticket status
        $data['status'] = $this->request->getPost('status');

        //Get current date and time
        $data['updateddate'] = date("d-m-Y");
        $data['date'] = date("d-m-Y");

        //Error form handling check and Submit Data
        if ($errors) {
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        // Store to Database Model and check for errors
        $supporttickets->updateTicket($data['ticketid'], $data['updatedby'], $data['status'], $data['date']);
        echo json_encode(array("status"=>"success","message"=>"Ticket updated successfully"));
    }

    public function singlerecordAction($ticketid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $supporttickets = new Supporttickets();
        $supportticketreplies = new Supportticketreplies();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemList = array();
        $ticketsData=$supporttickets->singleTicket($ticketid); // Get ticket data from the Database
        $itemList['id']=$ticketsData->id;
        $itemList['title']=$ticketsData->title;
        $itemList['details']=$ticketsData->details;
        $itemList['fromid']=$ticketsData->fromid;
        $itemList['from']=$ticketsData->from;
        $itemList['toid']=$ticketsData->toid;
        $itemList['to']=$ticketsData->to;
        $itemList['type']=$ticketsData->type;
        $itemList['status']=$ticketsData->status;
        $itemList['updateddate']=$ticketsData->updateddate;
        $itemList['updatedby']=$ticketsData->updatedby;
        $itemList['replies']=$ticketsData->replies;
        $itemList['date']=$ticketsData->date;
        echo json_encode($itemList);       
    }
    
    public function merchantsingleAction($ticketid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $supporttickets = new Supporttickets();
        $supportticketreplies = new Supportticketreplies();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "merchant"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemList = array();
        $ticketsData=$supporttickets->singleTicket($ticketid); // Get ticket data from the Database
        $itemList['id']=$ticketsData->id;
        $itemList['title']=$ticketsData->title;
        $itemList['details']=$ticketsData->details;
        $itemList['fromid']=$ticketsData->fromid;
        $itemList['from']=$ticketsData->from;
        $itemList['toid']=$ticketsData->toid;
        $itemList['to']=$ticketsData->to;
        $itemList['type']=$ticketsData->type;
        $itemList['status']=$ticketsData->status;
        $itemList['updateddate']=$ticketsData->updateddate;
        $itemList['updatedby']=$ticketsData->updatedby;
        $itemList['replies']=$ticketsData->replies;
        $itemList['date']=$ticketsData->date;
        echo json_encode($itemList);       
    }

    public function singleAction($ticketid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $supporttickets = new Supporttickets();
        $supportticketreplies = new Supportticketreplies();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "customer"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemList = array();
        $ticketsData=$supporttickets->singleTicket($ticketid); // Get ticket data from the Database
        $itemList['id']=$ticketsData->id;
        $itemList['title']=$ticketsData->title;
        $itemList['details']=$ticketsData->details;
        $itemList['fromid']=$ticketsData->fromid;
        $itemList['from']=$ticketsData->from;
        $itemList['toid']=$ticketsData->toid;
        $itemList['to']=$ticketsData->to;
        $itemList['type']=$ticketsData->type;
        $itemList['status']=$ticketsData->status;
        $itemList['updateddate']=$ticketsData->updateddate;
        $itemList['updatedby']=$ticketsData->updatedby;
        $itemList['replies']=$ticketsData->replies;
        $itemList['date']=$ticketsData->date;
        echo json_encode($itemList);       
    }

    public function removeticketAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $supporttickets = new Supporttickets();
        $supportticketreplies = new Supportticketreplies();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        $supporttickets->removeTicket($this->request->getPost('ticketid')); //Delete ticket
        $supportticketreplies->removeTicket($this->request->getPost('ticketid')); //Delete ticket replies
        echo json_encode(array("status"=>"success","message"=>"Ticket deleted successfully"));
    }

}
?>