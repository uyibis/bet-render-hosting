<?php

use Phalcon\Mvc\Controller;

class GamesController extends \Phalcon\Mvc\Controller {

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
        $generalService = new GeneralService();
        $emailerService = new EmailerService();
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();
        $errors = [];
        $data = [];

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        try {        
        //Game ID
        $data['gameid'] = $this->request->getPost('gameid');

        //Sport Type
        $data['type'] = $this->request->getPost('type');
        if (!is_string($data['type']) || $data['type']=="undefined") {
            $errors['type'] = 'Sport type expected';
        }

        //Home Team
        $data['hometeamid'] = $this->request->getPost('hometeamid');
        if (!is_string($data['hometeamid']) || $data['hometeamid']=="undefined") {
            $errors['hometeamid'] = 'Home team expected';
        }
        $teamData=$teams->singleEntry($data['hometeamid']); // Get data from the Database
        $data['hometeam']=$teamData->team;
        $data['hometeamscore']=0;

        //Away Team
        $data['awayteamid'] = $this->request->getPost('awayteamid');
        if (!is_string($data['awayteamid']) || $data['awayteamid']=="undefined") {
            $errors['awayteamid'] = 'Away team expected';
        }
        $teamData=$teams->singleEntry($data['awayteamid']); // Get data from the Database
        $data['awayteam']=$teamData->team;
        $data['awayteamscore']=0;

        //League - Category ID
        $data['categoryid'] = $this->request->getPost('categoryid');
        if (!is_string($data['categoryid']) || $data['categoryid']=="undefined") {
            $errors['categoryid'] = 'League ID expected';
        }
        $data['gameleague'] = $generalService->getCategoryName($data['categoryid']);

        //League season - Subcategory ID
        if($this->request->getPost('subcategoryid')){
            $data['subcategoryid'] = $this->request->getPost('subcategoryid');
            $data['gameleagueseason'] = $generalService->getSubCategoryName($data['subcategoryid']);
        } else{
            $data['subcategoryid'] = "";
            $data['gameleagueseason'] = "";
        }

        //Game venue
        $data['venue'] = $this->request->getPost('venue');
        if (!is_string($data['venue']) || $data['venue']=="undefined") {
            $errors['venue'] = 'Game venue expected';
        }

        //Game stage
        $data['stage'] = $this->request->getPost('stage');
        if (!is_string($data['stage']) || $data['stage']=="undefined") {
            $errors['stage'] = 'Game stage expected';
        }

        //Game date
        $data['gamedate'] = $this->request->getPost('gamedate');
        if (!is_string($data['gamedate']) || $data['gamedate']=="undefined") {
            $errors['gamedate'] = 'Game date expected';
        }

        //Game time
        $data['gametime'] = $this->request->getPost('gametime');
        if (!is_string($data['gametime']) || $data['gametime']=="undefined") {
            $errors['gametime'] = 'Game time expected';
        }

        //Game details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details']) || $data['details']=="undefined") {
            $errors['details'] = 'Game details expected';
        }

        //Game status
        $data['status'] = "pending";

        //Game photo
        if($this->request->hasFiles() == true){ 
            $extIMG = array(
                'image/jpeg',
                'image/JPEG',
                'image/png',
                'image/PNG',
                'image/gif',
                'image/GIF',
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
        $games->addEntry($data);
        echo json_encode(array("status"=>"success","message"=>"Game added successfully"));
        } catch(Exception $e) {
            return json_encode(array("status"=>"failed","message"=>$e->getMessage()));
        } 
    }

    public function updateAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $emailerService = new EmailerService();
        $smsService = new SMSService();
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();
        $user = new Users();
        $data = [];

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        //Passed the User Auth Check, Proceed with the Business Logic
        if($this->request->getPost('hometeamscore')){ //hometeamscore change check
            $data['hometeamscore'] = $this->request->getPost('hometeamscore');
            if (is_string($data['hometeamscore'])) {
                $games->updateEntry($this->request->getPost('gameid'), $data['hometeamscore'], "hometeamscore"); //Update details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Home team score must be provided"));
            }
        }

        if($this->request->getPost('awayteamscore')){ //awayteamscore change check
            $data['awayteamscore'] = $this->request->getPost('awayteamscore');
            if (is_string($data['awayteamscore'])) {
                $games->updateEntry($this->request->getPost('gameid'), $data['awayteamscore'], "awayteamscore"); //Update details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Away team score must be provided"));
            }
        }

        if($this->request->getPost('result')){ //result change check
            $data['result'] = $this->request->getPost('result');
            if (is_string($data['result'])) {
                $games->updateEntry($this->request->getPost('gameid'), $data['result'], "result"); //Update details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Result must be provided"));
            }
        }

        if($this->request->getPost('status')){ //status change check
            $data['status'] = $this->request->getPost('status');
            if (is_string($data['status'])) {
                $games->updateEntry($this->request->getPost('gameid'), $data['status'], "status"); //Update details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Status must be provided"));
            }
        }

        if($this->request->getPost('details')){ //details change check
            $data['details'] = $this->request->getPost('details');
            if (is_string($data['details'])) {
                $games->updateEntry($this->request->getPost('gameid'), $data['details'], "details"); //Update details changes
            } else {
                return json_encode(array("status"=>"failed","message"=>"Details must be provided"));
            }
        }
        
        //Perform necessary updates to game bets if status is played/full-time
        if($data['status']=="played" || $data['status']=="fulltime"){
            //Game is completed 
            //update winner record
            if($data['hometeamscore'] > $data['awayteamscore']){
                $finalwinner="hometeam";
            } elseif($data['awayteamscore'] > $data['hometeamscore']){
                $finalwinner="awayteam";
            } else {
                $finalwinner="draw";
            }
            //update bets records
            $resultbetsData=$gamebets->allEntriesByGame($this->request->getPost('gameid')); // Get bet data from the Database
            foreach($resultbetsData as $entryData) {
                $completedgamebet_id=$entryData->id;
                $completedgamebet_hometeambetterid=$entryData->hometeambetterid;
                $completedgamebet_hometeambetter=$entryData->hometeambetter;
                $completedgamebet_hometeambetterbet=$entryData->hometeambetterbet;
                $completedgamebet_awayteambetterid=$entryData->awayteambetterid;
                $completedgamebet_awayteambetter=$entryData->awayteambetter;
                $completedgamebet_awayteambetterbet=$entryData->awayteambetterbet;
                $completedgamebet_betamount=$entryData->betamount;
                if(!empty($completedgamebet_hometeambetterid) && !empty($completedgamebet_awayteambetterid)) {
                    if($finalwinner=="hometeam") {
                        $winnerPlayer_id=$completedgamebet_hometeambetterid;
                        $loserPlayer_id=$completedgamebet_awayteambetterid;
                        $winnerAvailable="yes";
                        $gamebets->updateEntry($completedgamebet_id, "win", "hometeambetterbetstatus"); //Update bet
                        $gamebets->updateEntry($completedgamebet_id, "loss", "awayteambetterbetstatus"); //Update bet
                    } elseif($finalwinner=="awayteam"){
                        $winnerPlayer_id=$completedgamebet_awayteambetterid;
                        $loserPlayer_id=$completedgamebet_hometeambetterid;
                        $winnerAvailable="yes";
                        $gamebets->updateEntry($completedgamebet_id, "win", "awayteambetterbetstatus"); //Update bet
                        $gamebets->updateEntry($completedgamebet_id, "loss", "hometeambetterbetstatus"); //Update bet
                    } else {
                        $winnerAvailable="no";
                    }
                    $gamebets->updateEntry($completedgamebet_id, "completed", "status"); //Update bet status
                    if($winnerAvailable=="yes"){                    
                        //Deduct from Loser Player Account Wallet for the Winner Player
                        $userData=$user->singleUser($loserPlayer_id);
                        $loserPlayer_username=$userData->username;
                        $loserPlayer_email=$userData->email;
                        $loserPlayer_phone=$userData->phone;
                        $loserPlayer_walletbalance=$userData->walletbalance;
                        $loserPlayer_NewBalance=$loserPlayer_walletbalance-$completedgamebet_betamount;
                        $user->updateUserAccount($loserPlayer_id, $loserPlayer_NewBalance, "walletbalance"); //Update User account
                        //Add to Transactions
                        $data['orderid'] = date("Ymdhis", strtotime("now")).rand(100,1000); //Transaction Order ID;
                        $data['type'] = "loss";
                        $data['note'] = "Bet loss from game bet #completedgamebet_id";
                        $data['amount'] = $completedgamebet_betamount;
                        $data['paymentstatus'] = "paid";
                        $data['paymentmethod'] = "automated";
                        $transactions->addTransaction($data);
                        $emailerService->sendCustomerNewOrderConfirmationEmail($data['orderid'], $data['type'], $data['amount'], $loserPlayer_username, $loserPlayer_email);
                        $smsService->sendOrderNotificationCustomerSMS($loserPlayer_phone, $data['orderid']);
                        //Update Winner Player Account Wallet for the Win
                        $userData=$user->singleUser($winnerPlayer_id);
                        $winnerPlayer_username=$userData->username;
                        $winnerPlayer_email=$userData->email;
                        $winnerPlayer_phone=$userData->phone;
                        $winnerPlayer_walletbalance=$userData->walletbalance;
                        $winnerPlayer_NewBalance=$winnerPlayer_walletbalance+$completedgamebet_betamount;
                        $user->updateUserAccount($winnerPlayer_id, $winnerPlayer_NewBalance, "walletbalance"); //Update User account
                        //Add to Transactions
                        $data['orderid'] = date("Ymdhis", strtotime("now")).rand(100,1000); //Transaction Order ID;
                        $data['type'] = "win";
                        $data['note'] = "Bet win from game bet #completedgamebet_id";
                        $data['amount'] = $completedgamebet_betamount;
                        $data['paymentstatus'] = "paid";
                        $data['paymentmethod'] = "automated";
                        $transactions->addTransaction($data);
                        $emailerService->sendCustomerNewOrderConfirmationEmail($data['orderid'], $data['type'], $data['amount'], $winnerPlayer_username, $winnerPlayer_email);
                        $smsService->sendOrderNotificationCustomerSMS($winnerPlayer_phone, $data['orderid']);
                    }
                }
            }
        }
        echo json_encode(array("status"=>"success","message"=>"Game updated successfully"));
    }    
    
    public function addteamAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //League - Category ID
        $data['leagueid'] = $this->request->getPost('categoryid');
        if (!is_string($data['leagueid']) || $data['leagueid']=="undefined") {
            $errors['leagueid'] = 'League ID expected';
        }
        $data['league'] = $generalService->getCategoryName($data['leagueid']);

        //League season - Subcategory ID
        $data['leagueseason'] = $this->request->getPost('subcategoryid');
        
        //type
        $data['type'] = $this->request->getPost('type');
        if (!is_string($data['type'])) {
            $errors['type'] = 'Type expected';
        }
        
        //team title
        $data['team'] = $this->request->getPost('title');
        if (!is_string($data['team'])) {
            $errors['team'] = 'Team name expected';
        }
        
        //team details
        $data['details'] = $this->request->getPost('details');
        if (!is_string($data['details'])) {
            $errors['details'] = 'Team details expected';
        }

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
                        $data['photo'] = "$FileName";
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
                // Store to Database Model
                $teams->addEntry($data);
            }
        } catch(Exception $e) {
            return json_encode(array("status"=>"failed","message"=>$e->getMessage()));
        }
        echo json_encode(array("status"=>"success","message"=>"Team added successfully"));
    }

    public function updateteamAction()
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $errors = [];
        $data = [];
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "admin"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/

    }

    public function teamsAction($type="all")
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        if($type=="all"){
            $resultData=$teams->allEntries(); // Get data from the Database
        } else {
            $resultData=$teams->allEntriesSearch($type); // Get data from the Database
        }
        foreach($resultData as $productData) {
            $itemList['id']=$productData->id;
            $itemList['team']=$productData->team;
            $itemList['type']=$productData->type;
            $itemList['photo']=$this->getDi()->getShared('siteurl').'/'.$productData->photo;
            $itemList['leagueid']=$productData->leagueid;
            $itemList['league']=$productData->league;
            $itemList['details']=$productData->details;
            $itemList['date']=$productData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);
    }

    public function categoriesAction($type="sport")
    {
        /** Init Block **/
        $categories = new Categories();
        $subcategories = new Subcategories();
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();

        //Logic
        $itemLists = array();
        $itemList = array();
        $itemSubLists = array();
        $itemSubList = array();
        if($type=="sport"){
            $allsports = array("football", "tennis", "basketball", "icehockey", "volleyball", "badminton", "baseball");
            $resultData=$categories->sportCategories($allsports); // Get sport categories data from the Database
        } else {
            $resultData=$categories->filterCategories($type); // Get sport categories data from the Database
        }
        foreach($resultData as $categoryData) {
            $itemList['id']=$categoryData->id;            
            $itemList['leagueid']=$categoryData->leagueid;            
            $itemList['categoryid']=$categoryData->id;            
            $itemList['category']=$categoryData->category;
            $itemList['categoryimage']=$this->getDi()->getShared('siteurl').'/'.$categoryData->image;
            $itemList['categorytype']=$categoryData->type;
            $itemList['safeurl']=$categoryData->safeurl;
            $itemList['type']=$categoryData->type;
            $itemList['details']=$categoryData->details;
            $itemList['categorydate']=$categoryData->date;
            $categoryid=$categoryData->id;
              //SubCategories
              $subResultData=$subcategories->filterSubcategories($categoryid); // Get product subcategories data from the Database
              foreach($subResultData as $subcategoryData) {
                $itemSubList['subcategoryid']=$subcategoryData->id;            
                $itemSubList['subcategory']=$subcategoryData->subcategory;
                $itemSubList['subcategoryimage']=$this->getDi()->getShared('siteurl').'/'.$subcategoryData->image;
                $itemSubList['subcategorytype']=$subcategoryData->type;
                $itemSubList['safeurl']=$subcategoryData->safeurl;
                $itemSubList['subcategorydate']=$subcategoryData->date;
                $itemSubLists[] = $itemSubList;
              }
            $itemList['subcategories']=$itemSubLists;
            $itemSubLists=[]; $itemSubList=[]; //Clear Sub Vars
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists); 
    }

    public function subcategoriesAction($categoryid=0)
    {
        /** Init Block **/
        $subcategories = new Subcategories();

        //Logic
        $itemLists = array();
        $itemList = array();
        $resultData=$subcategories->filterSubcategories($categoryid); // Get product subcategories data from the Database
        foreach($resultData as $categoryData) {
            $itemList['id']=$categoryData->id;            
            $itemList['categoryid']=$categoryData->categoryid;            
            $itemList['subcategoryid']=$categoryData->id;            
            $itemList['subcategory']=$categoryData->subcategory;
            $itemList['subcategoryimage']=$this->getDi()->getShared('siteurl').'/'.$categoryData->image;
            $itemList['subcategorytype']=$categoryData->type;
            $itemList['safeurl']=$categoryData->safeurl;
            $itemList['type']=$categoryData->type;
            $itemList['details']=$categoryData->details;
            $itemList['subcategorydate']=$categoryData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists); 
    }

    public function listsAction($sorttype="all",$limit=200)
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        $todayDate=date("d-m-Y");
        $dates = array();
        //This Week
        $dates[] = date("d-m-Y");
        $dates[] = date('d-m-Y', strtotime("+1 day"));
        $dates[] = date('d-m-Y', strtotime("+2 day"));
        $dates[] = date('d-m-Y', strtotime("+3 day"));
        $dates[] = date('d-m-Y', strtotime("+4 day"));
        $dates[] = date('d-m-Y', strtotime("+5 day"));
        $dates[] = date('d-m-Y', strtotime("+6 day")); 
        $dates[] = date('d-m-Y', strtotime("+7 day")); 
        $dates[] = date('d-m-Y', strtotime("+8 day")); 
        $dates[] = date('d-m-Y', strtotime("+9 day")); 
        $dates[] = date('d-m-Y', strtotime("+10 day")); 
        $dates[] = date('d-m-Y', strtotime("+11 day")); 
        $dates[] = date('d-m-Y', strtotime("+12 day")); 

        if($sorttype=="all" || $sorttype=="null"){
            $resultData=$games->allEntries($todayDate); // Get data from the Database
        } elseif($sorttype=="played"){
            $resultData=$games->allPlayedEntries($todayDate); // Get data from the Database
        } elseif($sorttype=="pending"){
            $resultData=$games->allPendingEntries($todayDate); // Get data from the Database
        } elseif($sorttype=="type"){
            $resultData=$games->allEntriesByType($limit,$todayDate); // Get data from the Database
        } elseif($sorttype=="category"){ 
            $categoryid=$generalService->getCategoryID($limit);
            $resultData=$games->allEntriesByLeague($categoryid); // Get data from the Database
        } elseif($sorttype=="league"){
            $resultData=$games->allEntriesByLeague($limit); // Get data from the Database
        } elseif($sorttype=="latest"){
            $resultData=$games->EntriesByLatest($limit,$dates); // Get data from the Database
        } elseif($sorttype=="featured"){
            $resultData=$games->EntriesByLatest($limit,$dates); // Get data from the Database
        } elseif($sorttype=="search"){
            $resultData=$games->searchEntries($limit); // Get data from the Database
        } elseif($sorttype=="live"){
            $resultData=$games->allLiveEntries(); // Get data from the Database
        } elseif($sorttype=="date"){
            if($limit=="today"){
                $resultDate=date("d-m-Y");
                $resultData=$games->allEntriesByDate($resultDate); // Get data from the Database
            } elseif($limit=="tomorrow"){
                $resultDate=date("d-m-Y", strtotime('tomorrow'));
                $resultData=$games->allEntriesByDate($resultDate); // Get data from the Database
            } else {
                $new_date = date('d-m-Y', strtotime($limit));
                $resultData=$games->allEntriesByDate($new_date); // Get data from the Database
            }
        } else {
            $resultData=$games->allEntriesByLeagueSeason($sorttype,$limit); // Get data from the Database
        }
        foreach($resultData as $productData) {
            $itemList['id']=$productData->id;
            $itemList['gameid']=$productData->id;
            $itemList['type']=$productData->type;
            $itemList['photo']=$this->getDi()->getShared('siteurl').'/'.$productData->photo;
            $itemList['hometeamid']=$productData->hometeamid;
            $itemList['hometeam']=$productData->hometeam;
            $teamData=$teams->singleEntry($itemList['hometeamid']); // Get data from the Database
            $itemList['hometeamlogo']=$this->getDi()->getShared('siteurl').'/'.$teamData->photo;
            $itemList['awayteamid']=$productData->awayteamid;
            $itemList['awayteam']=$productData->awayteam;
            $teamData=$teams->singleEntry($itemList['awayteamid']); // Get data from the Database
            $itemList['awayteamlogo']=$this->getDi()->getShared('siteurl').'/'.$teamData->photo;
            $itemList['gameleagueid']=$productData->gameleagueid;
            $itemList['gameleague']=$productData->gameleague;
            $itemList['gameleagueseason']=$productData->gameleagueseason;
            $itemList['venue']=$productData->venue;
            $itemList['stage']=$productData->stage;
            $itemList['gamedate']=$productData->gamedate;
            $itemList['gametime']=$productData->gametime;
            $itemList['hometeamscore']=$productData->hometeamscore;
            $itemList['awayteamscore']=$productData->awayteamscore;
            $itemList['status']=$productData->status;
            $itemList['result']=$productData->result;
            $itemList['details']=$productData->details;
            $itemList['date']=$productData->date;
            $itemList['sourcegameleagueid'] = $generalService->getCategoryLeagueID($itemList['gameleagueid']);
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);
    }

    public function resultsAction($sorttype="all",$limit=0,$time="all")
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();
        $todayDate=date("d-m-Y");

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();
        if($sorttype=="all" || $sorttype=="null" || $sorttype=="undefined"){
            $resultData=$games->allPlayedEntries($todayDate); // Get data from the Database
        } else {
            //search
            $data = [];
            $data['type'] = $this->request->getPost('resultsport');
            $data['gameleagueid'] = $this->request->getPost('resultleague');
            $data['resulttime'] = $this->request->getPost('resulttime');
            $resultData=$games->allEntriesByLeague($limit); // Get data from the Database
        }
        foreach($resultData as $productData) {
            $itemList['id']=$productData->id;
            $itemList['gameid']=$productData->id;
            $itemList['type']=$productData->type;
            $itemList['photo']=$this->getDi()->getShared('siteurl').'/'.$productData->photo;
            $itemList['hometeamid']=$productData->hometeamid;
            $itemList['hometeam']=$productData->hometeam;
            $teamData=$teams->singleEntry($itemList['hometeamid']); // Get data from the Database
            $itemList['hometeamlogo']=$this->getDi()->getShared('siteurl').'/'.$teamData->photo;
            $itemList['awayteamid']=$productData->awayteamid;
            $itemList['awayteam']=$productData->awayteam;
            $teamData=$teams->singleEntry($itemList['awayteamid']); // Get data from the Database
            $itemList['awayteamlogo']=$this->getDi()->getShared('siteurl').'/'.$teamData->photo;
            $itemList['gameleagueid']=$productData->gameleagueid;
            $itemList['gameleague']=$productData->gameleague;
            $itemList['gameleagueseason']=$productData->gameleagueseason;
            $itemList['venue']=$productData->venue;
            $itemList['stage']=$productData->stage;
            $itemList['gamedate']=$productData->gamedate;
            $itemList['gametime']=$productData->gametime;
            $itemList['hometeamscore']=$productData->hometeamscore;
            $itemList['awayteamscore']=$productData->awayteamscore;
            $itemList['status']=$productData->status;
            $itemList['result']=$productData->result;
            $itemList['details']=$productData->details;
            $itemList['date']=$productData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);
    }

    public function betsAction($sorttype="all",$limit=0)
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();
        $user = new Users();

        //Passed the User Auth Check, Proceed with the Business Logic
        $itemLists = array();
        $itemList = array();        
        $todayDate=date("d-m-Y");
        $dates = array();
        //This Week
        $dates[] = date("d-m-Y");
        $dates[] = date('d-m-Y', strtotime("+1 day"));
        $dates[] = date('d-m-Y', strtotime("+2 day"));
        $dates[] = date('d-m-Y', strtotime("+3 day"));
        $dates[] = date('d-m-Y', strtotime("+4 day"));
        $dates[] = date('d-m-Y', strtotime("+5 day"));
        $dates[] = date('d-m-Y', strtotime("+6 day")); 
        $dates[] = date('d-m-Y', strtotime("+7 day")); 
        $dates[] = date('d-m-Y', strtotime("+8 day")); 
        $dates[] = date('d-m-Y', strtotime("+9 day")); 
        $dates[] = date('d-m-Y', strtotime("+10 day")); 
        $dates[] = date('d-m-Y', strtotime("+11 day")); 
        $dates[] = date('d-m-Y', strtotime("+12 day")); 

        if($sorttype=="all" || $sorttype=="null"){
            $resultData=$games->allEntries($todayDate); // Get data from the Database
        } elseif($sorttype=="played"){
            $resultData=$games->allPlayedEntries($todayDate); // Get data from the Database
        } elseif($sorttype=="pending"){
            $resultData=$games->allPendingEntries($todayDate); // Get data from the Database
        } elseif($sorttype=="type"){
            $resultData=$games->allEntriesByType($limit,$todayDate); // Get data from the Database
        } elseif($sorttype=="league"){
            $resultData=$games->allEntriesByLeague($limit); // Get data from the Database
        } elseif($sorttype=="latest"){
            $resultData=$games->EntriesByLatest($limit,$dates); // Get data from the Database
        } elseif($sorttype=="featured"){
            $resultData=$games->EntriesByLatest($limit,$dates); // Get data from the Database
        } elseif($sorttype=="search"){
            $resultData=$games->searchEntries($limit); // Get data from the Database
        } else {
            $resultData=$games->allEntriesByLeagueSeason($sorttype,$limit); // Get data from the Database
        }
        //Game results
        $gamesList=[];
        foreach($resultData as $searchData) {
            $gamesList[]=$searchData->id;
        }
        //Bet results
        if($sorttype=="featured"){
            $resultData=$gamebets->allFeaturedEntries($limit); // Get data from the Database
        } elseif($sorttype=="user"){
            $resultData=$gamebets->allEntriesByUser($limit); // Get data from the Database
        } elseif($sorttype=="mine" && $limit=="open"){
            $resultData=$gamebets->allUserOpenEntries($this->request->getPost('userid')); // Get data from the Database
        } elseif($sorttype=="mine" && $limit=="completed"){
            $resultData=$gamebets->allUserCompletedEntries($this->request->getPost('userid')); // Get data from the Database
        } elseif($sorttype=="game"){
            $resultData=$gamebets->allEntriesByGame($limit); // Get data from the Database
        } elseif($sorttype=="single"){
            //Single Bet
            $entryData=$gamebets->singleEntry($limit); // Get data from the Database
            $itemList['id']=$entryData->id;
            $itemList['betid']=$entryData->id;
            $itemList['gameid']=$entryData->gameid;
            $itemList['hometeambetterid']=$entryData->hometeambetterid;
            $itemList['hometeambetter']=$entryData->hometeambetter;
            $itemList['hometeambetterbetstatus']=$entryData->hometeambetterbetstatus;
            $itemList['hometeambetterbet']=$entryData->hometeambetterbet;
            $itemList['hometeambetterbetcorrectscore']=$entryData->hometeambetterscore;
            if($itemList['hometeambetterid']){
            $userData=$user->singleUser($itemList['hometeambetterid']);
            $itemList['hometeambetterphoto']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
            }            
            $itemList['awayteambetterid']=$entryData->awayteambetterid;
            $itemList['awayteambetter']=$entryData->awayteambetter;
            $itemList['awayteambetterbetstatus']=$entryData->awayteambetterbetstatus;
            $itemList['awayteambetterbet']=$entryData->awayteambetterbet;
            $itemList['awayteambetterbetcorrectscore']=$entryData->awayteambetterscore;
            if($itemList['awayteambetterid']){
            $userData=$user->singleUser($itemList['awayteambetterid']);
            $itemList['awayteambetterphoto']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
            }
            $itemList['betamount']=$entryData->betamount;
            $itemList['betresult']=$entryData->result;
            $itemList['betstatus']=$entryData->status;
            $itemList['date']=$entryData->date;
            return json_encode($itemList);
        } else {
            $resultData=$gamebets->filterEntries($gamesList); // Get data from the Database
        }
        foreach($resultData as $entryData) {
            $itemList['id']=$entryData->id;
            $itemList['betid']=$entryData->id;
            $itemList['gameid']=$entryData->gameid;
            $itemList['hometeambetterid']=$entryData->hometeambetterid;
            $itemList['hometeambetter']=$entryData->hometeambetter;
            $itemList['hometeambetterbetstatus']=$entryData->hometeambetterbetstatus;
            $itemList['hometeambetterbet']=$entryData->hometeambetterbet;
            $itemList['hometeambetterbetcorrectscore']=$entryData->hometeambetterscore;
            if($itemList['hometeambetterid']){
            $userData=$user->singleUser($itemList['hometeambetterid']);
            $itemList['hometeambetterphoto']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
            }            
            $itemList['awayteambetterid']=$entryData->awayteambetterid;
            $itemList['awayteambetter']=$entryData->awayteambetter;
            $itemList['awayteambetterbetstatus']=$entryData->awayteambetterbetstatus;
            $itemList['awayteambetterbet']=$entryData->awayteambetterbet;
            $itemList['awayteambetterbetcorrectscore']=$entryData->awayteambetterscore;
            if($itemList['awayteambetterid']){
            $userData=$user->singleUser($itemList['awayteambetterid']);
            $itemList['awayteambetterphoto']=$this->getDi()->getShared('siteurl').'/'.$userData->photo;
            }
            $itemList['betamount']=$entryData->betamount;
            $itemList['finalbetamount']=$entryData->finalbetamount;
            $itemList['betpotentialwin']=$itemList['finalbetamount']*2;
            $itemList['betresult']=$entryData->result;
            if($itemList['hometeambetterid']==$this->request->getPost('userid')){
                $itemList['betresult']=$itemList['hometeambetterbetstatus'];
            } elseif($itemList['awayteambetterid']==$this->request->getPost('userid')){
                $itemList['betresult']=$itemList['awayteambetterbetstatus'];
            } 
            $itemList['betstatus']=$entryData->status;
            $itemList['date']=$entryData->date;
            if($itemList['betstatus']=="open"){
                if($itemList['hometeambetter']){
                    $itemList['better']=$itemList['hometeambetter'];
                    $itemList['betterphoto']=$itemList['hometeambetterphoto'];
                    $itemList['betterbet']=$itemList['hometeambetterbet'];
                    $itemList['betterbetcorrectscore']=$itemList['hometeambetterbetcorrectscore'];
                    $itemList['betterbetside']="Home";
                } elseif($itemList['awayteambetter']){
                    $itemList['better']=$itemList['awayteambetter'];
                    $itemList['betterphoto']=$itemList['awayteambetterphoto'];
                    $itemList['betterbet']=$itemList['awayteambetterbet'];
                    $itemList['betterbetcorrectscore']=$itemList['awayteambetterbetcorrectscore'];
                    $itemList['betterbetside']="Away";
                }
            }
            //Game data
            $productData=$games->singleEntry($itemList['gameid']);
            $itemList['id']=$productData->id;
            //$itemList['gameid']=$productData->gameid;
            $itemList['type']=$productData->type;
            $itemList['photo']=$this->getDi()->getShared('siteurl').'/'.$productData->photo;
            $itemList['hometeamid']=$productData->hometeamid;
            $itemList['hometeam']=$productData->hometeam;
            $teamData=$teams->singleEntry($itemList['hometeamid']); // Get data from the Database
            $itemList['hometeamlogo']=$this->getDi()->getShared('siteurl').'/'.$teamData->photo;
            $itemList['awayteamid']=$productData->awayteamid;
            $itemList['awayteam']=$productData->awayteam;
            $teamData=$teams->singleEntry($itemList['awayteamid']); // Get data from the Database
            $itemList['awayteamlogo']=$this->getDi()->getShared('siteurl').'/'.$teamData->photo;
            $itemList['gameleagueid']=$productData->gameleagueid;
            $itemList['gameleague']=$productData->gameleague;
            $itemList['gameleagueseason']=$productData->gameleagueseason;
            $itemList['venue']=$productData->venue;
            $itemList['stage']=$productData->stage;
            $itemList['gamedate']=$productData->gamedate;
            $itemList['gametime']=$productData->gametime;
            $itemList['hometeamscore']=$productData->hometeamscore;
            $itemList['awayteamscore']=$productData->awayteamscore;
            $itemList['status']=$productData->status;
            $itemList['result']=$productData->result;
            $itemList['details']=$productData->details;
            $itemList['date']=$productData->date;
            $itemLists[] = $itemList;
        }
        echo json_encode($itemLists);
    }

    public function singleAction($gameid)
    {
        /** Init Block **/
        $generalService = new GeneralService();
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();
        $user = new Users();
        $itemList = array();

        //Business Logic
        $productData=$games->singleEntry($gameid); // Get data from the Database
        $itemList['id']=$productData->id;
        $itemList['gameid']=$productData->gameid;
        $itemList['type']=$productData->type;
        $itemList['photo']=$this->getDi()->getShared('siteurl').'/'.$productData->photo;
        $itemList['hometeamid']=$productData->hometeamid;
        $itemList['hometeam']=$productData->hometeam;
        $teamData=$teams->singleEntry($itemList['hometeamid']); // Get data from the Database
        $itemList['hometeamlogo']=$this->getDi()->getShared('siteurl').'/'.$teamData->photo;
        $itemList['awayteamid']=$productData->awayteamid;
        $itemList['awayteam']=$productData->awayteam;
        $teamData=$teams->singleEntry($itemList['awayteamid']); // Get data from the Database
        $itemList['awayteamlogo']=$this->getDi()->getShared('siteurl').'/'.$teamData->photo;
        $itemList['gameleagueid']=$productData->gameleagueid;
        $itemList['gameleague']=$productData->gameleague;
        $itemList['gameleagueseason']=$productData->gameleagueseason;
        $itemList['venue']=$productData->venue;
        $itemList['stage']=$productData->stage;
        $itemList['gamedate']=$productData->gamedate;
        $itemList['gametime']=$productData->gametime;
        $itemList['hometeamscore']=$productData->hometeamscore;
        $itemList['awayteamscore']=$productData->awayteamscore;
        $itemList['status']=$productData->status;
        $itemList['result']=$productData->result;
        $itemList['details']=$productData->details;
        $itemList['date']=$productData->date;
        echo json_encode($itemList);  
    }    
    
    public function addbetAction($gameid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $emailerService = new EmailerService();
        $smsService = new SMSService();
        $errors = [];
        $data = [];
        $itemLists = array();
        $itemList = array();
        $games = new Games();
        $gamebets = new Gamebets();
        $teams = new Teams();
        $user = new Users();
        $notifications = new Notifications();
        $transactions = new Transactions();

        //Auth Check
        $authCheckResult=$authService->UserAuth($this->request->getPost('userid'), $this->request->getPost('userlogintoken'), "user"); 
        if(!$authCheckResult) { 
            return json_encode(array("status"=>"failed","message"=>"Access denied. Invalid auth credentials!")); 
        } 

        /** Validation Block **/
        //Bet ID
        $data['betid'] = $this->request->getPost('betid');
        if (empty($data['betid']) || $data['betid']=="undefined" || $data['betid']=="null") {
            $betType="new";
        } else {
            $betType="peer";
        }

        //Bet Game Data
        $data['gameid'] = $gameid;
        $gameData=$games->singleEntry($data['gameid']);
        $game_id=$gameData->id;
        $game_status=$gameData->status;
        $game_type=$gameData->type;
        $game_hometeamid=$gameData->hometeamid;
        $game_hometeam=$gameData->hometeam;
        $game_awayteamid=$gameData->awayteamid;
        $game_awayteam=$gameData->awayteam;
        $game_gamedate=$gameData->gamedate;
        $game_gametime=$gameData->gametime;
        $game_title="$game_hometeam vs $game_awayteam on $game_gamedate at $game_gametime";
        if($game_status=="played" || $game_status=="on-going"){
            return json_encode(array("status"=>"failed","message"=>"Game already played. Please choose another game yet to be played!")); 
        }
        
        //bet amount
        if($betType=="new"){
            $data['betamount'] = $this->request->getPost('betamount');
            if (empty($data['betamount']) || $data['betamount']=="undefined" || $data['betamount']=="null") {
                $errors['betamount'] = 'Bet amount expected';
            }
        }
        
        //bet team
        $data['betteamchosen'] = $this->request->getPost('betteam');
        if (!is_string($data['betteamchosen'])) {
            $errors['betteamchosen'] = 'Bet team expected';
        }
        if($data['betteamchosen']=="hometeam"){
            $data['betteamid']=$game_hometeamid;
            $data['betteam']=$game_hometeam;
        } elseif($data['betteamchosen']=="awayteam"){
            $data['betteamid']=$game_awayteamid;
            $data['betteam']=$game_awayteam;
        }
        
        //bet
        $data['bet'] = $this->request->getPost('bet');
        if (!is_string($data['bet'])) {
            $errors['bet'] = 'Bet details expected';
        }

        //bet scores
        $data['betscore'] = $this->request->getPost('betscore');
        if(empty($this->request->getPost('betscore')) && $data['bet']=="correct score"){
            $errors['betscore'] = 'Please provide your correct score for your bet';
        }

        //Get current date and time
        $data['date'] = date("d-m-Y");

        //Get User data
        $userData=$user->singleUser($this->request->getPost('userid')); // Get User data from the Database
        $data['userid']=$userData->id;
        $data['username']=$userData->username;
        $data['useremail']=$userData->email;
        $data['userphone']=$userData->phone;
        $data['walletbalance']=$userData->walletbalance;
        $transactionTotalResultData=$gamebets->sumBetPendingAmountByUser($data['userid']); // Get User Total Orders Sum data from the Database
        $totalpending_amount=$transactionTotalResultData->totalsum;
        //Remove pending bet amount from wallet balance to get Available Balance
        $userAvailableBalance=$data['walletbalance']-$totalpending_amount;

        //Check for errors
        if($errors) { 
            return json_encode(array("status"=>"failed","message"=>implode( ", ", $errors )));
        } 

        //Get bet details from database if type is PEER or add to database if NEW
        if($betType=="peer"){
            //Peer Bet
            $data['betamount'] = $this->request->getPost('betamount');
            $betData=$gamebets->singleEntry($data['betid']); // Get data from the Database
            if($data['betamount'] != $betData->betamount){
                return json_encode(array("status"=>"failed","message"=>"Your bet amount ".$data['betamount']." must be same with the bet offer amount".$betData->betamount."!"));
            }
            $data['betamount']=$betData->betamount;
            $betstatus=$betData->status;
            $hometeambetterid=$betData->hometeambetterid;
            $hometeambetter=$betData->hometeambetter;
            $hometeambetterbet=$betData->hometeambetterbet;
            $hometeambetterscore=$betData->hometeambetterscore;
            $awayteambetterid=$betData->awayteambetterid;
            $awayteambetter=$betData->awayteambetter;
            $awayteambetterbet=$betData->awayteambetterbet;
            $awayteambetterscore=$betData->awayteambetterscore;
            //Check if bet already peered completely
            if(!empty($hometeambetterid) && !empty($awayteambetterid)) { 
                return json_encode(array("status"=>"failed","message"=>"Game bet already peered. Please place a new bet or find another bet peer!")); 
            } 
            //Check if amount withdraw request is up to available balance
            if($data['betamount']>$userAvailableBalance){
                return json_encode(array("status"=>"failed","message"=>"Game bet amount exceeds your available wallet balance. Please deposit into your account wallet to fund it.")); 
            } 
            //Check if team chosen has been already chosen
            if(!empty($hometeambetterid) && $data['betteamchosen']=="hometeam") { 
                return json_encode(array("status"=>"failed","message"=>"Bet team already chosen. You can not peer bet on same team!")); 
            }
            //Check if team chosen has been already chosen
            if(!empty($awayteambetterid) && $data['betteamchosen']=="awayteam") { 
                return json_encode(array("status"=>"failed","message"=>"Bet team already chosen. You can not peer bet on same team!")); 
            }
            //Check if teams bet chosen conflicts
            /**if(!empty($hometeambetterbet) && $data['betteamchosen']=="awayteam" && $data['bet']=="$hometeambetterbet" && $data['bet']=="lose") { 
                return json_encode(array("status"=>"failed","message"=>"Team bet is same. Please choose the opposite bet!")); 
            }**/
            //Determine bet
            if($data['betteamchosen']=="hometeam"){
                //get opponent data
                $opponent_betteam="awayteam";
                $opponent_bet="$awayteambetterbet";
                $opponent_betscore="$awayteambetterscore";
                $opponentData=$user->singleUser($awayteambetterid); // Get User data from the Database
                $opponent_id=$opponentData->id;
                $opponent_username=$opponentData->username;
                $opponent_email=$opponentData->email;
            } elseif($data['betteamchosen']=="awayteam"){
                //get opponent data
                $opponent_betteam="hometeam";
                $opponent_bet="$hometeambetterbet";
                $opponent_betscore="$hometeambetterscore";
                $opponentData=$user->singleUser($hometeambetterid); // Get User data from the Database
                $opponent_id=$opponentData->id;
                $opponent_username=$opponentData->username;
                $opponent_email=$opponentData->email;
            } 
            //Validate bet
            if($opponent_bet=="win"){
                //Win bet opposite only allowed
                if($data['bet']!="win" && $data['bet']!="draw"){
                    return json_encode(array("status"=>"failed","message"=>"Bet is same. Please choose the opposite bet!")); 
                }
            } elseif($opponent_bet=="draw"){
                //Draw bet opposite only allowed
                if($data['bet']!="win"){
                    return json_encode(array("status"=>"failed","message"=>"Bet is same. Please choose the opposite bet!")); 
                }
            } elseif($opponent_bet=="halftime win"){
                //Halftime win bet opposite only allowed
                if($data['bet']!="halftime win"){
                    return json_encode(array("status"=>"failed","message"=>"Please choose same bet for the opposite team!")); 
                }
            } elseif($opponent_bet=="halftime draw"){
                //Halftime draw bet opposite only allowed
                if($data['bet']=="halftime draw"){
                    return json_encode(array("status"=>"failed","message"=>"Bet is same. Please choose the opposite bet!")); 
                }
            } elseif($opponent_bet=="first to score"){
                //First to score bet opposite only allowed
                if($data['bet']!="first to score"){
                    return json_encode(array("status"=>"failed","message"=>"Please choose same bet for the opposite team!")); 
                }
            } elseif($opponent_bet=="last to score"){
                //Last to score bet opposite only allowed
                if($data['bet']!="last to score"){
                    return json_encode(array("status"=>"failed","message"=>"Please choose same bet for the opposite team!")); 
                }
            } elseif($opponent_bet=="first yellowcard"){
                //First yellowcard bet opposite only allowed
                if($data['bet']!="first yellowcard"){
                    return json_encode(array("status"=>"failed","message"=>"Please choose same bet for the opposite team!")); 
                }
            } elseif($opponent_bet=="last yellowcard"){
                //Last yellowcard bet opposite only allowed
                if($data['bet']!="last yellowcard"){
                    return json_encode(array("status"=>"failed","message"=>"Please choose same bet for the opposite team!")); 
                }
            } elseif($opponent_bet=="first redcard"){
                //First redcard bet opposite only allowed
                if($data['bet']!="first redcard"){
                    return json_encode(array("status"=>"failed","message"=>"Please choose same bet for the opposite team!")); 
                }
            } elseif($opponent_bet=="last redcard"){
                //Last redcard bet opposite only allowed
                if($data['bet']!="last redcard"){
                    return json_encode(array("status"=>"failed","message"=>"Please choose same bet for the opposite team!")); 
                }
            } elseif($opponent_bet=="goal goal"){
                //Goal goal bet opposite only allowed
                if($data['bet']!="no goal"){
                    return json_encode(array("status"=>"failed","message"=>"Bet is same. Please choose the opposite bet!")); 
                }
            } elseif($opponent_bet=="no goal"){
                //No goal bet opposite only allowed
                if($data['bet']!="goal goal"){
                    return json_encode(array("status"=>"failed","message"=>"Bet is same. Please choose the opposite bet!")); 
                }
            } elseif($opponent_bet=="correct score"){
                //Correct score bet opposite only allowed
                if($data['bet']!="correct score" && empty($data['betscore'])){
                    return json_encode(array("status"=>"failed","message"=>"Please choose same bet for the opposite team and provide prediction score!")); 
                }
                if($data['betscore']==$opponent_betscore){
                    return json_encode(array("status"=>"failed","message"=>"Bet is same. Please choose the opposite bet score!")); 
                }
            } elseif($opponent_bet=="win either half"){
                //Win either half bet opposite only allowed
                if($data['bet']!="win either half"){
                    return json_encode(array("status"=>"failed","message"=>"Please choose same bet for the opposite team!")); 
                }
            } elseif($opponent_bet=="win both half"){
                //Win both half bet opposite only allowed
                if($data['bet']!="win both half"){
                    return json_encode(array("status"=>"failed","message"=>"Please choose same bet for the opposite team!")); 
                }
            } elseif($opponent_bet=="team 1 goal or more" || $opponent_bet=="team 2 goals or more" || $opponent_bet=="team 3 goals or more" || $opponent_bet=="team 4 goals or more" || $opponent_bet=="team 5 goals or more" || $opponent_bet=="team 6 goals or more" || $opponent_bet=="team 7 goals or more"){
                //Team goals or more bet opposite only allowed
                if($data['bet']!=$opponent_bet){
                    return json_encode(array("status"=>"failed","message"=>"Please choose same bet for the opposite team!")); 
                }
            } elseif($opponent_bet=="match 1 goal or more" || $opponent_bet=="match 2 goals or more" || $opponent_bet=="match 3 goals or more" || $opponent_bet=="match 4 goals or more" || $opponent_bet=="match 5 goals or more" || $opponent_bet=="match 6 goals or more" || $opponent_bet=="match 7 goals or more"){
                //Team goals or more bet opposite only allowed
                if($data['bet']==$opponent_bet){
                    return json_encode(array("status"=>"failed","message"=>"Bet is same. Please choose the opposite bet score!")); 
                }
            } 
            //Place bet
            if($data['betteamchosen']=="hometeam"){
                $gamebets->updateEntry($data['betid'], $data['userid'], "hometeambetterid"); //Update bet
                $gamebets->updateEntry($data['betid'], $data['username'], "hometeambetter"); //Update bet
                $gamebets->updateEntry($data['betid'], $data['bet'], "hometeambetterbet"); //Update bet
            } elseif($data['betteamchosen']=="awayteam"){
                $gamebets->updateEntry($data['betid'], $data['userid'], "awayteambetterid"); //Update bet
                $gamebets->updateEntry($data['betid'], $data['username'], "awayteambetter"); //Update bet
                $gamebets->updateEntry($data['betid'], $data['bet'], "awayteambetterbet"); //Update bet
            }
            $emailerService->sendCustomerNewBetPeerEmail($game_title, $data['bet'], $data['betamount'], $data['username'], $data['useremail']);
            $emailerService->sendCustomerNewBetPeerAlertEmail($game_title, $data['bet'], $data['betamount'], $data['username'], $opponent_username, $opponent_email);
            $gamebets->updateEntry($data['betid'], "closed", "status"); //Update bet status
            /**Add Notification To User starts**/
                $data['userid'] = $opponent_id;
                $data['username'] = $opponent_username;
                $data['title'] = "Bet Peered";
                $data['type'] = "gamebet";
                $data['details'] = "A new peer for your game bet";
                $data['fromid'] = $this->request->getPost('userid');
                $data['from'] = $this->request->getPost('username');
                $data['status'] = "unread";
                $data['actionid'] = $data['gameid'];
                $data['actionsubid'] = $data['betid'];
                $data['time'] = date("h:i:sa");
                $data['date'] = date("d-m-Y");
                $notifications->addEntry($data); //Add
            /**Add Notification To User ends**/
            /**Start Commission Charges**/
                $betID=$data['betid'];
                $percentage = 15;
                $commission = ($percentage / 100) * $data['betamount'];
                $commission_Amount = $commission*2;
                $final_betamount = $data['betamount'] - $commission;
                $gamebets->updateEntry($data['betid'], $final_betamount, "finalbetamount"); //Update bet amount
                //Add to Transactions ADMIN
                $adminData=$user->singleUser('1');
                $admin_username=$adminData->username;
                $admin_email=$adminData->email;
                $admin_phone=$adminData->phone;
                $admin_walletbalance=$adminData->walletbalance;
                $admin_NewBalance=$admin_walletbalance+$commission_Amount;
                $user->updateUserAccount('1', $admin_NewBalance, "walletbalance"); //Update Admin account
                $data['orderid'] = date("Ymdhis", strtotime("now")).rand(100,1000); //Transaction Order ID;
                $data['type'] = "commission";
                $data['note'] = "Bet commission revenue from game bet #$betID";
                $data['amount'] = $commission_Amount;
                $data['paymentstatus'] = "paid";
                $data['paymentmethod'] = "automated";
                $data['userid'] = "1";
                $data['username'] = "$admin_username";
                $data['userreferrer'] = "";
                $data['date'] = date("d-m-Y");
                $transactions->addTransaction($data);
                $emailerService->sendCustomerNewOrderConfirmationEmail($data['orderid'], $data['type'], $data['amount'], $admin_username, $admin_email);
                $smsService->sendOrderNotificationCustomerSMS($admin_phone, $data['orderid']);
                //Add to Transactions Player One
                $playerData=$user->singleUser($this->request->getPost('userid')); //Deduct from Player Account Wallet for the Charges
                $player_id=$playerData->id;
                $player_username=$playerData->username;
                $player_walletbalance=$playerData->walletbalance;
                $player_NewBalance=$player_walletbalance-$commission;
                $user->updateUserAccount($player_id, $player_NewBalance, "walletbalance"); //Update User wallet
                $data['orderid'] = date("Ymdhis", strtotime("now")).rand(100,1000); //Transaction Order ID;
                $data['type'] = "charges";
                $data['note'] = "Bet commission charges from game bet #$betID";
                $data['amount'] = $commission;
                $data['paymentstatus'] = "paid";
                $data['paymentmethod'] = "automated";
                $data['userid'] = "$player_id";
                $data['username'] = "$player_username";
                $data['userreferrer'] = "";
                $data['date'] = date("d-m-Y");
                $transactions->addTransaction($data);
                //Add to Transactions Player Two
                $playerData=$user->singleUser($opponent_id); //Deduct from Player Account Wallet for the Charges
                $player_id=$playerData->id;
                $player_username=$playerData->username;
                $player_walletbalance=$playerData->walletbalance;
                $player_NewBalance=$player_walletbalance-$commission;
                $user->updateUserAccount($player_id, $player_NewBalance, "walletbalance"); //Update User wallet
                $data['orderid'] = date("Ymdhis", strtotime("now")).rand(100,1000); //Transaction Order ID;
                $data['type'] = "charges";
                $data['note'] = "Bet commission charges from game bet #$betID";
                $data['amount'] = $commission;
                $data['paymentstatus'] = "paid";
                $data['paymentmethod'] = "automated";
                $data['userid'] = "$player_id";
                $data['username'] = "$player_username";
                $data['userreferrer'] = "";
                $data['date'] = date("d-m-Y");
                $transactions->addTransaction($data);
            /**End Commission Charges**/
        } else {
            //New Bet
            //Check if amount withdraw request is up to available balance
            if($data['betamount']>$userAvailableBalance){
                return json_encode(array("status"=>"failed","message"=>"Game bet amount exceeds your available wallet balance. Please deposit into your account wallet to fund it.")); 
            } 
            //Chosen Team
            if($data['betteamchosen']=="hometeam"){
                $data['hometeambetterid']=$data['userid'];
                $data['hometeambetter']=$data['username'];
                $data['hometeambetterbet']=$data['bet'];
                $data['hometeambetterscore']=$data['betscore'];
                $data['awayteambetterid']="";
                $data['awayteambetter']="";
                $data['awayteambetterbet']="";
            } elseif($data['betteamchosen']=="awayteam"){
                $data['hometeambetterid']="";
                $data['hometeambetter']="";
                $data['hometeambetterbet']="";
                $data['awayteambetterid']=$data['userid'];
                $data['awayteambetter']=$data['username'];
                $data['awayteambetterbet']=$data['bet'];
                $data['awayteambetterscore']=$data['betscore'];
            }
            //Place bet
            $data['status']="open";
            $emailerService->sendCustomerNewBetEmail($game_title, $data['bet'], $data['betamount'], $data['username'], $data['useremail']);
            $gamebets->addEntry($data);
        }
        //done
        echo json_encode(array("status"=>"success","message"=>"Bet added successfully"));
    }

    public function removegameAction()
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