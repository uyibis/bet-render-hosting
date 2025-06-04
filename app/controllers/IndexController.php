<?php

class IndexController extends ControllerBase
{

    public function indexAction()
    {

    }

    public function autoloadgamesAction($league_id, $fromDate, $toDate)
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $emailerService = new EmailerService();
        $smsService = new SMSService();
        $gameService = new GameService();
        $categories = new Categories();
        $subcategories = new Subcategories();
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();
        $notifications = new Notifications();
        $transactions = new Transactions();
        $user = new Users();
        $errors = [];
        $data = [];
        $itemLists = array();
        $itemList = array();

        //Auth Check
        //$authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        //if(!$authCheckResult) { 
        //    return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        //} 

        //Passed the User Auth Check, Proceed with the Business Logic 
        $runaction=$gameService->autoload_games($league_id, $fromDate, $toDate);       
        return $runaction;
    }

    public function autoloadleaguesAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $gameService = new GameService();
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();
        $categories = new Categories();
        $subcategories = new Subcategories();
        $errors = [];
        $data = [];
        $itemLists = array();
        $itemList = array();

        //Auth Check
        //$authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        //if(!$authCheckResult) { 
        //    return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        //} 

        //Passed the User Auth Check, Proceed with the Business Logic
        $runaction=$gameService->autoload_leagues();       
        return $runaction;
    }
}

