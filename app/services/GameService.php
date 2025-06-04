<?php
use Phalcon\Crypt;

class GameService {

    public $thirdpartygamesprovider_key="ebec7e19edmsh6a1819f02c7d4c3p196102jsn9865251a5c86"; 
    public $thirdpartygamesprovider_host="api-football-v1.p.rapidapi.com"; 
    public $thirdpartygamesprovider_baseurl="https://api-football-v1.p.rapidapi.com/v3"; 

    public function autoload_leagues() { 
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

        //Start cURL
        $APIKey=$this->thirdpartygamesprovider_key;
        $APIHost=$this->thirdpartygamesprovider_host;
        $request_url=$this->thirdpartygamesprovider_baseurl."/leagues";
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "$request_url",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS =>'{"id": "", "country": ""}',
        CURLOPT_HTTPHEADER => array(
            "X-RapidAPI-Key: $APIKey",
            "X-RapidAPI-Host: $APIHost"
        ),
        ));
        $response_output = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response_output, true);
        //End cURL
    
        $resultData = json_decode (json_encode ($result), FALSE);
        foreach($resultData->response as $entryData) {
            $itemList['country_id']=$entryData->country->name;
            $itemList['country_name']=$entryData->country->name;
            $itemList['league_id']=$entryData->league->id;
            $itemList['league_name']=$entryData->league->name;
            $itemList['league_logo']=$entryData->league->logo;
            $itemList['country_logo']=$entryData->league->logo;
            foreach($entryData->seasons as $seasonsEntryData) {
                $itemList['league_season']=$seasonsEntryData->year;
            }
            $itemLists[] = $itemList;
            //Start Processing ThirdPartyAPI
            $leagueExistResult=$categories->checkEntryExist($itemList['league_id']); //Check If League Exist
            if($leagueExistResult == 0) {
                //Add Category / League 
                $data['type']="football";
                $data['leagueid']=$itemList['league_id'];
                $data['category']=$itemList['league_name'];
                $NewFileName=date("Ymdhis", strtotime("now")).rand(100,1000); //Random file new name
                $newfile      = "files/" . $NewFileName . ".png";
                if ( copy($itemList['league_logo'], $newfile) ) {
                    $data['image'] = "$newfile";
                } else{
                    $data['image'] = "files/league-icon.png";
                }
                $data['details']=$itemList['league_name'];
                $data['author']="automated";
                $data['safeurl']=$generalService->getSafeURL($itemList['league_name']);
                $data['date']=date("d-m-Y");
                $categories->addCategory($data);
                $data['categoryid'] = $generalService->getCategoryIDbyLeague($itemList['league_id']);
                $data['gameleague'] = $generalService->getCategoryName($data['categoryid']);
                $seasonExistResult=$subcategories->checkEntryExist($data['categoryid']); //Check if League Season Exist
                if($seasonExistResult == 0) {
                    //Add Subcategory / League Season
                    $data['type']="sport";
                    $data['category']=$data['categoryid'];
                    $data['subcategory']=$itemList['league_season'];
                    $data['image']="season-icon.png";
                    $data['details']="sport season";
                    $data['author']="automated";
                    $data['safeurl']="";
                    $data['date']=date("d-m-Y");
                    $subcategories->addSubcategory($data);
                } 
            } 
        }
        return json_encode(array("status"=>"success","message"=>"Done"));
    }
    
    public function autoload_games($league_id, $fromDate, $toDate)
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $gameService = new GameService();
        $emailerService = new EmailerService();
        $smsService = new SMSService();
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

        try {  
        //Start cURL
        $APIKey=$this->thirdpartygamesprovider_key;
        $APIHost=$this->thirdpartygamesprovider_host;
        $league_season = $generalService->getLatestSubCategoryID($league_id); //Get Subcategory / League Season
        $request_url=$this->thirdpartygamesprovider_baseurl."/fixtures?league=$league_id&season=$league_season&from=$fromDate&to=$toDate";
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "$request_url",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS =>'{"id": "", "season": "' .$league_season. '", "league": "' .$league_id. '", "from": "' .$fromDate. '", "to": "' .$toDate. '"}',
        CURLOPT_HTTPHEADER => array(
            "X-RapidAPI-Key: $APIKey",
            "X-RapidAPI-Host: $APIHost"
        ),
        ));
        $response_output = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response_output, true);
        //End cURL

        $resultData = json_decode (json_encode ($result), FALSE);
        $numberOfGamesAdded=0;
        if (is_array($resultData->response)) {
            foreach($resultData->response as $entryData) {
                $itemList['match_id']=$entryData->fixture->id;
                $itemList['country_id']=$entryData->fixture->venue->name;
                $itemList['country_name']=$entryData->league->country;
                $itemList['league_id']=$entryData->league->id;
                $itemList['league_name']=$entryData->league->name;
                $itemList['league_season']=$entryData->league->season;
                $itemList['match_date']=$entryData->fixture->date;
                $itemList['match_status']=$entryData->fixture->status->long;
                $rawMatchTime=$entryData->fixture->date;
                $DateTime = new DateTime("$rawMatchTime");
                $DateTime->modify('-2 hours');
                $itemList['match_time']=$DateTime->format("H:i")." GMT";
                $itemList['match_hometeam_id']=$entryData->teams->home->id;
                $itemList['match_hometeam_name']=$entryData->teams->home->name;
                $itemList['match_hometeam_score']=$entryData->goals->home;
                $itemList['match_awayteam_name']=$entryData->teams->away->name;
                $itemList['match_awayteam_id']=$entryData->teams->away->id;
                $itemList['match_awayteam_score']=$entryData->goals->away;
                $itemList['match_hometeam_halftime_score']=$entryData->score->halftime->home;
                $itemList['match_awayteam_halftime_score']=$entryData->score->halftime->away;
                $itemList['match_hometeam_extra_score']=$entryData->score->extratime->home;
                $itemList['match_awayteam_extra_score']=$entryData->score->extratime->away;
                $itemList['match_hometeam_penalty_score']=$entryData->score->penalty->home;
                $itemList['match_awayteam_penalty_score']=$entryData->score->penalty->away;
                $itemList['match_hometeam_ft_score']=$entryData->score->fulltime->home;
                $itemList['match_awayteam_ft_score']=$entryData->score->fulltime->away;
                $itemList['match_live']=$entryData->fixture->status->short;
                $itemList['match_round']=$entryData->league->round;
                $itemList['match_stadium']=$entryData->fixture->venue->name;
                $itemList['team_home_badge']=$entryData->teams->home->logo;
                $itemList['team_away_badge']=$entryData->teams->away->logo;
                $itemList['league_logo']=$entryData->league->logo;
                $itemList['country_logo']=$entryData->league->logo;
                $itemList['goalscorer']="";
                $itemList['cards']="";
                $itemList['substitutions']="";
                $itemList['lineup']="";
                $itemList['statistics']="";
                $itemLists[] = $itemList;

                //Start Processing ThirdPartyAPI
                $leagueExistResult=$categories->checkEntryExist($league_id); //Check If League Exist
                if($leagueExistResult == 0) {
                    //Add Category / League 
                    $data['type']="football";
                    $data['leagueid']=$itemList['league_id'];
                    $data['category']=$itemList['league_name'];
                    $NewFileName=date("Ymdhis", strtotime("now")).rand(100,1000); //Random file new name
                    $newfile      = "files/" . $NewFileName . ".png";
                    if ( copy($itemList['league_logo'], $newfile) ) {
                        $data['image'] = "$newfile";
                    } else{
                        $data['image'] = "files/league-icon.png";
                    }
                    $data['details']=$itemList['league_name'];
                    $data['author']="automated";
                    $data['safeurl']=$generalService->getSafeURL($itemList['league_name']);
                    $data['date']=date("d-m-Y");
                    $categories->addCategory($data);
                    $data['categoryid'] = $generalService->getCategoryIDbyLeague($itemList['league_id']);
                } else {
                    //Get Category / League 
                    $data['categoryid'] = $generalService->getCategoryIDbyLeague($itemList['league_id']);
                }
                $data['gameleague'] = $generalService->getCategoryName($data['categoryid']);
                $seasonExistResult=$subcategories->checkEntryExist($data['categoryid']); //Check if League Season Exist
                if($seasonExistResult == 0) {
                    //Add Subcategory / League Season
                    $data['type']="sport";
                    $data['category']=$data['categoryid'];
                    $data['subcategory']=$itemList['league_season'];
                    $data['image']="season-icon.png";
                    $data['details']="sport season";
                    $data['author']="automated";
                    $data['safeurl']="";
                    $data['date']=date("d-m-Y");
                    $subcategories->addSubcategory($data);
                } 
                $data['gameleagueseason'] = $league_season;
                $data['venue'] = $itemList['match_stadium'];
                $data['stage'] = $itemList['match_round'];
                $data['gamedate'] = date("d-m-Y", strtotime($itemList['match_date']));
                $data['gametime'] = $itemList['match_time'];

                //Check if team exist or add new entry
                //Home Team
                $existResult=$teams->checkEntryExist($itemList['match_hometeam_name']); //Check if entry already exist in the Database
                if($existResult == 0) {
                    //Add Team to Database
                    $data['leagueid'] = $data['categoryid'];
                    $data['league'] = $data['gameleague'];
                    $data['type'] = "automated";
                    $data['teamid'] = $itemList['match_hometeam_id'];
                    $data['team'] = $itemList['match_hometeam_name'];
                    $data['details'] = "automated";
                    $data['date'] = date("d-m-Y");
                    $NewFileName=date("Ymdhis", strtotime("now")).rand(100,1000); //Random file new name
                    $newfile      = "files/" . $NewFileName . ".png";
                    if ( copy($itemList['team_home_badge'], $newfile) ) {
                        $data['photo'] = "$newfile";
                    } else{
                        $data['photo'] = "files/team-icon.png";
                    }
                    $teams->addEntry($data);
                } 
                //Away Team
                $existResult=$teams->checkEntryExist($itemList['match_awayteam_name']); //Check if entry already exist in the Database
                if($existResult == 0) {
                    //Add Team to Database
                    $data['leagueid'] = $data['categoryid'];
                    $data['league'] = $data['gameleague'];
                    $data['type'] = "automated";
                    $data['teamid'] = $itemList['match_awayteam_id'];
                    $data['team'] = $itemList['match_awayteam_name'];
                    $data['details'] = "automated";
                    $data['date'] = date("d-m-Y");
                    $NewFileName=date("Ymdhis", strtotime("now")).rand(100,1000); //Random file new name
                    $newfile      = "files/" . $NewFileName . ".png";
                    if ( copy($itemList['team_away_badge'], $newfile) ) {
                        $data['photo'] = "$newfile";
                    } else{
                        $data['photo'] = "files/team-icon.png";
                    }
                    $teams->addEntry($data);
                } 

                //Add Game
                $data['gameid'] = $itemList['match_id'];
                $data['type'] = "football";
                $data['hometeamid'] = $teams->getEntryID($itemList['match_hometeam_name']);
                $data['hometeam'] = $itemList['match_hometeam_name'];
                $data['hometeamscore'] = $itemList['match_hometeam_score'];
                $data['awayteamid'] = $teams->getEntryID($itemList['match_awayteam_name']);
                $data['awayteam'] = $itemList['match_awayteam_name'];
                $data['awayteamscore'] = $itemList['match_awayteam_score'];
                $data['details'] = "match";
                $data['status'] = "pending";
                if($itemList['match_status']=="Match Finished" || $itemList['match_status']=="Finished" || $itemList['match_status']=="Match Finished After Penalty" || $itemList['match_status']=="Match Finished After Extra Time" || $itemList['match_status']=="Match Postponed" || $itemList['match_status']=="Match Suspended"){
                    $data['status'] = "played";
                } elseif($itemList['match_status']=="Kick Off" || $itemList['match_status']=="First Half" || $itemList['match_status']=="Halftime" || $itemList['match_status']=="2nd Half Started" || $itemList['match_status']=="Second Half" || $itemList['match_status']=="Extra Time" || $itemList['match_status']=="Break Time" || $itemList['match_status']=="Penalty In Progress" || $itemList['match_status']=="In Play") {
                    $data['status'] = "on-going";
                } else {
                    $data['status'] = "pending";
                }
                $data['date'] = date("d-m-Y");
                $existResult=$games->checkEntryExist($itemList['match_id']); //Check if entry already exist in the Database
                if($existResult == 0) {
                    $games->addEntry($data);
                } else {
                    //Update Game
                    $gameData=$games->singleEntryByGameID($itemList['match_id']); // Get data from the Database
                    $gameID=$gameData->id;
                    $gameStatus=$gameData->status;
                    if($itemList['match_status']=="Match Finished" && $gameStatus!="played") {
                        //Update Game Result
                        $games->updateEntry($gameID, "played", "status"); //Update details changes
                        //Update Game Winners & Losers
                        $gameService->determinegamebet_winner_loser($gameID);
                    } elseif($itemList['match_status']=="Kick Off" || $itemList['match_status']=="First Half" || $itemList['match_status']=="Halftime" || $itemList['match_status']=="2nd Half Started" || $itemList['match_status']=="Second Half" || $itemList['match_status']=="Extra Time" || $itemList['match_status']=="Break Time" || $itemList['match_status']=="Penalty In Progress" || $itemList['match_status']=="In Play") {
                        //Update Game Live Score
                        $games->updateEntry($gameID, "on-going", "status"); //Update details changes
                        $games->updateEntry($gameID, $itemList['match_hometeam_score'], "hometeamscore"); //Update details changes
                        $games->updateEntry($gameID, $itemList['match_awayteam_score'], "awayteamscore"); //Update details changes
                    }
                }
                $numberOfGamesAdded++;
                //End Processing ThirdPartyAPI 
            }
        } else { 
            return print_r($resultData, true); 
        }
        } catch(Exception $e) {
            return json_encode(array("status"=>"failed","message"=>$e->getMessage()));
        } 
        return json_encode(array("status"=>"success","message"=>"$numberOfGamesAdded games added/updated successfully"));
    }

    public function determinegamebet_winner_loser($gameid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $gameService = new GameService();
        $emailerService = new EmailerService();
        $smsService = new SMSService();
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

        //Get Game Details
        $gameData=$games->singleEntry($gameid); // Get data from the Database
        $entry_gameid=$gameData->id;
        $entry_providergameid=$gameData->gameid;

        //Start cURL
        $APIKey=$this->thirdpartygamesprovider_key;
        $APIHost=$this->thirdpartygamesprovider_host;
        $request_url=$this->thirdpartygamesprovider_baseurl."/fixtures?id=$entry_providergameid";
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "$request_url",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS =>'{"id": "' .$entry_providergameid. '"}',
        CURLOPT_HTTPHEADER => array(
            "X-RapidAPI-Key: $APIKey",
            "X-RapidAPI-Host: $APIHost"
        ),
        ));
        $response_output = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response_output, true);
        //End cURL

        $resultData = json_decode (json_encode ($result), FALSE);
        if (is_array($resultData->response)) {
            foreach($resultData->response as $entryData) {
                $itemList['match_id']=$entryData->fixture->id;
                $itemList['country_id']=$entryData->fixture->venue->name;
                $itemList['country_name']=$entryData->league->country;
                $itemList['league_id']=$entryData->league->id;
                $itemList['league_name']=$entryData->league->name;
                $itemList['league_season']=$entryData->league->season;
                $itemList['match_date']=$entryData->fixture->date;
                $itemList['match_status']=$entryData->fixture->status->long;
                $rawMatchTime=$entryData->fixture->date;
                $DateTime = new DateTime("$rawMatchTime");
                $DateTime->modify('-2 hours');
                $itemList['match_time']=$DateTime->format("H:i")." GMT";
                $itemList['match_hometeam_id']=$entryData->teams->home->id;
                $itemList['match_hometeam_name']=$entryData->teams->home->name;
                $itemList['match_hometeam_score']=$entryData->goals->home;
                $itemList['match_awayteam_name']=$entryData->teams->away->name;
                $itemList['match_awayteam_id']=$entryData->teams->away->id;
                $itemList['match_awayteam_score']=$entryData->goals->away;
                $itemList['match_hometeam_halftime_score']=$entryData->score->halftime->home;
                $itemList['match_awayteam_halftime_score']=$entryData->score->halftime->away;
                $itemList['match_hometeam_extra_score']=$entryData->score->extratime->home;
                $itemList['match_awayteam_extra_score']=$entryData->score->extratime->away;
                $itemList['match_hometeam_penalty_score']=$entryData->score->penalty->home;
                $itemList['match_awayteam_penalty_score']=$entryData->score->penalty->away;
                $itemList['match_hometeam_ft_score']=$entryData->score->fulltime->home;
                $itemList['match_awayteam_ft_score']=$entryData->score->fulltime->away;
                $itemList['match_live']=$entryData->fixture->status->short;
                $itemList['match_round']=$entryData->league->round;
                $itemList['match_stadium']=$entryData->fixture->venue->name;
                $itemList['team_home_badge']=$entryData->teams->home->logo;
                $itemList['team_away_badge']=$entryData->teams->away->logo;
                $itemList['league_logo']=$entryData->league->logo;
                $itemList['country_logo']=$entryData->league->logo;
                $itemList['goalscorer']="";
                $itemList['cards']="";
                $itemList['substitutions']="";
                $itemList['lineup']="";
                $itemList['statistics']="";
                $itemLists[] = $itemList;
                
                //Get all bets records
                $resultbetsData=$gamebets->allEntriesByGame($entry_gameid); // Get bet data from the Database
                foreach($resultbetsData as $entryData) {
                    $completedgamebet_id=$entryData->id;
                    $completedgamebet_hometeambetterid=$entryData->hometeambetterid;
                    $completedgamebet_hometeambetter=$entryData->hometeambetter;
                    $completedgamebet_hometeambetterbet=$entryData->hometeambetterbet;
                    $completedgamebet_hometeambetterscore=$entryData->hometeambetterscore;
                    $completedgamebet_awayteambetterid=$entryData->awayteambetterid;
                    $completedgamebet_awayteambetter=$entryData->awayteambetter;
                    $completedgamebet_awayteambetterbet=$entryData->awayteambetterbet;
                    $completedgamebet_awayteambetterscore=$entryData->awayteambetterscore;
                    $completedgamebet_betamount=$entryData->betamount;
                    $completedgamebet_finalbetamount=$entryData->finalbetamount;
                    if(!empty($completedgamebet_hometeambetterid)){
                        /**Add Notification To Better starts**/
                        $data['userid'] = $completedgamebet_hometeambetterid;
                        $data['username'] = $completedgamebet_hometeambetter;
                        $data['title'] = "Game Completed";
                        $data['type'] = "game";
                        $data['details'] = "Your game bet is complete";
                        $data['fromid'] = "";
                        $data['from'] = "";
                        $data['status'] = "unread";
                        $data['actionid'] = "$gameID";
                        $data['actionsubid'] = "";
                        $data['time'] = date("h:i:sa");
                        $data['date'] = date("d-m-Y");
                        $notifications->addEntry($data); //Add
                        /**Add Notification To User ends**/
                    }
                    if(!empty($completedgamebet_awayteambetterid)){
                        /**Add Notification To Better starts**/
                        $data['userid'] = $completedgamebet_awayteambetterid;
                        $data['username'] = $completedgamebet_awayteambetter;
                        $data['title'] = "Game Completed";
                        $data['type'] = "game";
                        $data['details'] = "Your game bet is complete";
                        $data['fromid'] = "";
                        $data['from'] = "";
                        $data['status'] = "unread";
                        $data['actionid'] = "$gameID";
                        $data['actionsubid'] = "";
                        $data['time'] = date("h:i:sa");
                        $data['date'] = date("d-m-Y");
                        $notifications->addEntry($data); //Add
                        /**Add Notification To User ends**/
                    }
                    if(!empty($completedgamebet_hometeambetterid) && !empty($completedgamebet_awayteambetterid)) {
                        //DETERMINE THE WINNER AND LOSER Depending On Bet Types
                        if($completedgamebet_hometeambetterbet=="win" || $completedgamebet_awayteambetterbet=="win"){
                            //Win or Loss BET #1
                            if($itemList['match_hometeam_score'] > $itemList['match_awayteam_score']){
                                if($completedgamebet_hometeambetterbet=="win"){
                                    $finalwinner="hometeam";
                                } 
                            } elseif($itemList['match_awayteam_score'] > $itemList['match_hometeam_score']){
                                if($completedgamebet_awayteambetterbet=="win"){
                                    $finalwinner="awayteam";
                                } 
                            } 
                        } elseif($completedgamebet_hometeambetterbet=="draw" || $completedgamebet_awayteambetterbet=="draw"){
                            //Draw BET #2
                            if($itemList['match_hometeam_score'] == $itemList['match_awayteam_score']){
                                if($completedgamebet_hometeambetterbet=="draw"){
                                    $finalwinner="hometeam";
                                } elseif($completedgamebet_awayteambetterbet=="draw"){
                                    $finalwinner="awayteam";
                                }
                            } else {
                                if($completedgamebet_hometeambetterbet=="draw"){
                                    $finalwinner="awayteam";
                                } elseif($completedgamebet_awayteambetterbet=="draw"){
                                    $finalwinner="hometeam";
                                } 
                            }
                        } elseif($completedgamebet_hometeambetterbet=="halftime win" || $completedgamebet_awayteambetterbet=="halftime win"){
                            //Half-Time win BET #3
                            if($itemList['match_hometeam_halftime_score'] > $itemList['match_awayteam_halftime_score']){
                                if($completedgamebet_hometeambetterbet=="halftime win"){
                                    $finalwinner="hometeam";
                                } 
                            } elseif($itemList['match_awayteam_halftime_score'] > $itemList['match_hometeam_halftime_score']){
                                if($completedgamebet_awayteambetterbet=="halftime win"){
                                    $finalwinner="awayteam";
                                } 
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="halftime draw" || $completedgamebet_awayteambetterbet=="halftime draw"){
                            //Half-Time draw BET #4
                            if($itemList['match_hometeam_halftime_score'] == $itemList['match_awayteam_halftime_score']){
                                if($completedgamebet_hometeambetterbet=="halftime draw"){
                                    $finalwinner="hometeam";
                                } elseif($completedgamebet_awayteambetterbet=="halftime draw"){
                                    $finalwinner="awayteam";
                                } 
                            } 
                        } elseif($completedgamebet_hometeambetterbet=="first to score" || $completedgamebet_awayteambetterbet=="first to score"){
                            //First to score BET #5
                            $sortRanking=1;
                            foreach($itemList['goalscorer'] as $entryData) {
                                $time=$entryData->time;
                                $home_scorer=$entryData->home_scorer;
                                $away_scorer=$entryData->away_scorer;
                                if($sortRanking==1){
                                    //First
                                    if($home_scorer){
                                        if($completedgamebet_hometeambetterbet=="first to score"){
                                            $finalwinner="hometeam";
                                        } 
                                    } elseif($away_scorer){
                                        if($completedgamebet_awayteambetterbet=="first to score"){
                                            $finalwinner="awayteam";
                                        } 
                                    }
                                }
                                $sortRanking++;
                            }
                            if(empty($finalwinner)){
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="last to score" || $completedgamebet_awayteambetterbet=="last to score"){
                            //Last to score BET #6
                            $sortRanking=1;
                            foreach($itemList['goalscorer'] as $entryData) {
                                $time=$entryData->time;
                                $home_scorer=$entryData->home_scorer;
                                $away_scorer=$entryData->away_scorer;
                                $sortRanking++;
                            }
                            //Last
                            if($home_scorer){
                                if($completedgamebet_hometeambetterbet=="last to score"){
                                    $finalwinner="hometeam";
                                } 
                            } elseif($away_scorer){
                                if($completedgamebet_awayteambetterbet=="last to score"){
                                    $finalwinner="awayteam";
                                } 
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="first yellowcard" || $completedgamebet_awayteambetterbet=="first yellowcard"){
                            //First yellowcard BET #7
                            $sortRanking=1;
                            foreach($itemList['cards'] as $entryData) {
                                $time=$entryData->time;
                                $home_fault=$entryData->home_fault;
                                $away_fault=$entryData->away_fault;
                                $card=$entryData->card;
                                if($sortRanking==1){
                                    //First
                                    if($home_fault && $card=="yellow card"){
                                        if($completedgamebet_hometeambetterbet=="first yellowcard"){
                                            $finalwinner="hometeam";
                                        } 
                                    } elseif($away_fault && $card=="yellow card"){
                                        if($completedgamebet_awayteambetterbet=="first yellowcard"){
                                            $finalwinner="awayteam";
                                        } 
                                    }
                                }
                                $sortRanking++;
                            }
                            if(empty($finalwinner)){
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="last yellowcard" || $completedgamebet_awayteambetterbet=="last yellowcard"){
                            //Last yellowcard BET #8
                            $sortRanking=1;
                            foreach($itemList['cards'] as $entryData) {
                                $time=$entryData->time;
                                $home_fault=$entryData->home_fault;
                                $away_fault=$entryData->away_fault;
                                $card=$entryData->card;
                                $sortRanking++;
                            }
                            //Last
                            if($home_fault && $card=="yellow card"){
                                if($completedgamebet_hometeambetterbet=="last yellowcard"){
                                    $finalwinner="hometeam";
                                } 
                            } elseif($away_fault && $card=="yellow card"){
                                if($completedgamebet_awayteambetterbet=="last yellowcard"){
                                    $finalwinner="awayteam";
                                } 
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="first redcard" || $completedgamebet_awayteambetterbet=="first redcard"){
                            //First redcard BET #9
                            $sortRanking=1;
                            foreach($itemList['cards'] as $entryData) {
                                $time=$entryData->time;
                                $home_fault=$entryData->home_fault;
                                $away_fault=$entryData->away_fault;
                                $card=$entryData->card;
                                if($sortRanking==1){
                                    //First
                                    if($home_fault && $card=="red card"){
                                        if($completedgamebet_hometeambetterbet=="first redcard"){
                                            $finalwinner="hometeam";
                                        } 
                                    } elseif($away_fault && $card=="red card"){
                                        if($completedgamebet_awayteambetterbet=="first redcard"){
                                            $finalwinner="awayteam";
                                        } 
                                    }
                                }
                                $sortRanking++;
                            }
                            if(empty($finalwinner)){
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="last redcard" || $completedgamebet_awayteambetterbet=="last redcard"){
                            //Last redcard BET #10
                            $sortRanking=1;
                            foreach($itemList['cards'] as $entryData) {
                                $time=$entryData->time;
                                $home_fault=$entryData->home_fault;
                                $away_fault=$entryData->away_fault;
                                $card=$entryData->card;
                                $sortRanking++;
                            }
                            //Last
                            if($home_fault && $card=="red card"){
                                if($completedgamebet_hometeambetterbet=="last redcard"){
                                    $finalwinner="hometeam";
                                } 
                            } elseif($away_fault && $card=="red card"){
                                if($completedgamebet_awayteambetterbet=="last redcard"){
                                    $finalwinner="awayteam";
                                } 
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="goal goal" || $completedgamebet_awayteambetterbet=="goal goal"){
                            //Goal Goal BET #11
                            if($itemList['match_hometeam_score'] >=1 && $itemList['match_awayteam_score'] >=1){
                                if($completedgamebet_hometeambetterbet=="goal goal"){
                                    $finalwinner="hometeam";
                                } elseif($completedgamebet_awayteambetterbet=="goal goal"){
                                    $finalwinner="awayteam";
                                } 
                            } else { 
                                if($completedgamebet_hometeambetterbet=="goal goal"){
                                    $finalwinner="awayteam";
                                } elseif($completedgamebet_awayteambetterbet=="goal goal"){
                                    $finalwinner="hometeam";
                                } 
                                //$finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="no goal" || $completedgamebet_awayteambetterbet=="no goal"){
                            //No Goal BET #12
                            if($itemList['match_hometeam_score'] == "0" && $itemList['match_awayteam_score'] == "0"){
                                if($completedgamebet_hometeambetterbet=="no goal"){
                                    $finalwinner="hometeam";
                                } elseif($completedgamebet_awayteambetterbet=="no goal"){
                                    $finalwinner="awayteam";
                                } 
                            } else { 
                                if($completedgamebet_hometeambetterbet=="no goal"){
                                    $finalwinner="awayteam";
                                } elseif($completedgamebet_awayteambetterbet=="no goal"){
                                    $finalwinner="hometeam";
                                } 
                                //$finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="correct score" || $completedgamebet_awayteambetterbet=="correct score"){
                            //Correct Score BET #13
                            $finalGameScore=$itemList['match_hometeam_score'].":".$itemList['match_awayteam_score'];
                            if($completedgamebet_hometeambetterscore==$finalGameScore){
                                $finalwinner="hometeam";
                            } elseif($completedgamebet_awayteambetterscore==$finalGameScore){
                                $finalwinner="awayteam";
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="win either half" || $completedgamebet_awayteambetterbet=="win either half"){
                            //Win either Half BET #14
                            if($itemList['match_hometeam_halftime_score'] > $itemList['match_awayteam_halftime_score']){
                                if($completedgamebet_hometeambetterbet=="win either half"){
                                    $finalwinner="hometeam";
                                } 
                            } elseif($itemList['match_awayteam_halftime_score'] > $itemList['match_hometeam_halftime_score']){
                                if($completedgamebet_awayteambetterbet=="win either half"){
                                    $finalwinner="awayteam";
                                } 
                            } elseif($itemList['match_hometeam_score'] > $itemList['match_awayteam_score']){
                                if($completedgamebet_hometeambetterbet=="win either half"){
                                    $finalwinner="hometeam";
                                } 
                            } elseif($itemList['match_awayteam_score'] > $itemList['match_hometeam_score']){
                                if($completedgamebet_awayteambetterbet=="win either half"){
                                    $finalwinner="awayteam";
                                } 
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="win both half" || $completedgamebet_awayteambetterbet=="win both half"){
                            //Win Both Half BET #15
                            if($itemList['match_hometeam_halftime_score'] > $itemList['match_awayteam_halftime_score']){
                                if($itemList['match_hometeam_score'] > $itemList['match_awayteam_score']){
                                    if($completedgamebet_hometeambetterbet=="win both half"){
                                        $finalwinner="hometeam";
                                    }
                                }                                         
                            } elseif($itemList['match_awayteam_halftime_score'] > $itemList['match_hometeam_halftime_score']){
                                if($itemList['match_awayteam_score'] > $itemList['match_hometeam_score']){
                                    if($completedgamebet_awayteambetterbet=="win both half"){
                                        $finalwinner="awayteam";
                                    }
                                }                                         
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="team 1 goal or more" || $completedgamebet_awayteambetterbet=="team 1 goal or more"){
                            //Team 1 goal or more BET #16
                            if($itemList['match_hometeam_score'] >= 1){
                                if($completedgamebet_hometeambetterbet=="team 1 goal or more"){
                                    $finalwinner="hometeam";
                                }                                        
                            } elseif($itemList['match_awayteam_score'] >= 1){
                                if($completedgamebet_awayteambetterbet=="team 1 goal or more"){
                                    $finalwinner="awayteam";
                                }                                        
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="team 2 goal or more" || $completedgamebet_awayteambetterbet=="team 2 goal or more"){
                            //Team 2 goal or more BET #17
                            if($itemList['match_hometeam_score'] >= 2){
                                if($completedgamebet_hometeambetterbet=="team 2 goal or more"){
                                    $finalwinner="hometeam";
                                }                                        
                            } elseif($itemList['match_awayteam_score'] >= 2){
                                if($completedgamebet_awayteambetterbet=="team 2 goal or more"){
                                    $finalwinner="awayteam";
                                }                                        
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="team 3 goal or more" || $completedgamebet_awayteambetterbet=="team 3 goal or more"){
                            //Team 3 goal or more BET #18
                            if($itemList['match_hometeam_score'] >= 3){
                                if($completedgamebet_hometeambetterbet=="team 3 goal or more"){
                                    $finalwinner="hometeam";
                                }                                        
                            } elseif($itemList['match_awayteam_score'] >= 3){
                                if($completedgamebet_awayteambetterbet=="team 3 goal or more"){
                                    $finalwinner="awayteam";
                                }                                        
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="team 4 goal or more" || $completedgamebet_awayteambetterbet=="team 4 goal or more"){
                            //Team 4 goal or more BET #19
                            if($itemList['match_hometeam_score'] >= 4){
                                if($completedgamebet_hometeambetterbet=="team 4 goal or more"){
                                    $finalwinner="hometeam";
                                }                                        
                            } elseif($itemList['match_awayteam_score'] >= 4){
                                if($completedgamebet_awayteambetterbet=="team 4 goal or more"){
                                    $finalwinner="awayteam";
                                }                                        
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="team 5 goal or more" || $completedgamebet_awayteambetterbet=="team 5 goal or more"){
                            //Team 5 goal or more BET #20
                            if($itemList['match_hometeam_score'] >= 5){
                                if($completedgamebet_hometeambetterbet=="team 5 goal or more"){
                                    $finalwinner="hometeam";
                                }                                        
                            } elseif($itemList['match_awayteam_score'] >= 5){
                                if($completedgamebet_awayteambetterbet=="team 5 goal or more"){
                                    $finalwinner="awayteam";
                                }                                        
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="team 6 goal or more" || $completedgamebet_awayteambetterbet=="team 6 goal or more"){
                            //Team 6 goal or more BET #21
                            if($itemList['match_hometeam_score'] >= 6){
                                if($completedgamebet_hometeambetterbet=="team 6 goal or more"){
                                    $finalwinner="hometeam";
                                }                                        
                            } elseif($itemList['match_awayteam_score'] >= 6){
                                if($completedgamebet_awayteambetterbet=="team 6 goal or more"){
                                    $finalwinner="awayteam";
                                }                                        
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="team 7 goal or more" || $completedgamebet_awayteambetterbet=="team 7 goal or more"){
                            //Team 7 goal or more BET #22
                            if($itemList['match_hometeam_score'] >= 7){
                                if($completedgamebet_hometeambetterbet=="team 7 goal or more"){
                                    $finalwinner="hometeam";
                                }                                        
                            } elseif($itemList['match_awayteam_score'] >= 7){
                                if($completedgamebet_awayteambetterbet=="team 7 goal or more"){
                                    $finalwinner="awayteam";
                                }                                        
                            } else {
                                $finalwinner="draw";
                            }
                        } elseif($completedgamebet_hometeambetterbet=="match 1 goal or more" || $completedgamebet_awayteambetterbet=="match 1 goal or more"){
                            //Match 1 goal or more BET #23
                            $totalMatchScores=$itemList['match_hometeam_score']+$itemList['match_awayteam_score'];
                            if($totalMatchScores >= 1){
                                if($completedgamebet_hometeambetterbet=="match 1 goal or more"){
                                    $finalwinner="hometeam";
                                } elseif($completedgamebet_awayteambetterbet=="match 1 goal or more"){
                                    $finalwinner="awayteam";
                                } else {
                                    $finalwinner="draw";
                                }                                        
                            } else {
                                if($completedgamebet_hometeambetterbet=="match 1 goal or more"){
                                    $finalwinner="awayteam";
                                } elseif($completedgamebet_awayteambetterbet=="match 1 goal or more"){
                                    $finalwinner="hometeam";
                                } else {
                                    $finalwinner="draw";
                                }
                            }
                        } elseif($completedgamebet_hometeambetterbet=="match 2 goal or more" || $completedgamebet_awayteambetterbet=="match 2 goal or more"){
                            //Match 2 goal or more BET #24
                            $totalMatchScores=$itemList['match_hometeam_score']+$itemList['match_awayteam_score'];
                            if($totalMatchScores >= 2){
                                if($completedgamebet_hometeambetterbet=="match 2 goal or more"){
                                    $finalwinner="hometeam";
                                } elseif($completedgamebet_awayteambetterbet=="match 2 goal or more"){
                                    $finalwinner="awayteam";
                                } else {
                                    $finalwinner="draw";
                                }                                        
                            } else {
                                if($completedgamebet_hometeambetterbet=="match 2 goal or more"){
                                    $finalwinner="awayteam";
                                } elseif($completedgamebet_awayteambetterbet=="match 2 goal or more"){
                                    $finalwinner="hometeam";
                                } else {
                                    $finalwinner="draw";
                                }
                            }
                        } elseif($completedgamebet_hometeambetterbet=="match 3 goal or more" || $completedgamebet_awayteambetterbet=="match 3 goal or more"){
                            //Match 3 goal or more BET #25
                            $totalMatchScores=$itemList['match_hometeam_score']+$itemList['match_awayteam_score'];
                            if($totalMatchScores >= 3){
                                if($completedgamebet_hometeambetterbet=="match 3 goal or more"){
                                    $finalwinner="hometeam";
                                } elseif($completedgamebet_awayteambetterbet=="match 3 goal or more"){
                                    $finalwinner="awayteam";
                                } else {
                                    $finalwinner="draw";
                                }                                        
                            } else {
                                if($completedgamebet_hometeambetterbet=="match 3 goal or more"){
                                    $finalwinner="awayteam";
                                } elseif($completedgamebet_awayteambetterbet=="match 3 goal or more"){
                                    $finalwinner="hometeam";
                                } else {
                                    $finalwinner="draw";
                                }
                            }
                        } elseif($completedgamebet_hometeambetterbet=="match 4 goal or more" || $completedgamebet_awayteambetterbet=="match 4 goal or more"){
                            //Match 4 goal or more BET #26
                            $totalMatchScores=$itemList['match_hometeam_score']+$itemList['match_awayteam_score'];
                            if($totalMatchScores >= 4){
                                if($completedgamebet_hometeambetterbet=="match 4 goal or more"){
                                    $finalwinner="hometeam";
                                } elseif($completedgamebet_awayteambetterbet=="match 4 goal or more"){
                                    $finalwinner="awayteam";
                                } else {
                                    $finalwinner="draw";
                                }                                        
                            } else {
                                if($completedgamebet_hometeambetterbet=="match 4 goal or more"){
                                    $finalwinner="awayteam";
                                } elseif($completedgamebet_awayteambetterbet=="match 4 goal or more"){
                                    $finalwinner="hometeam";
                                } else {
                                    $finalwinner="draw";
                                }
                            }
                        } elseif($completedgamebet_hometeambetterbet=="match 5 goal or more" || $completedgamebet_awayteambetterbet=="match 5 goal or more"){
                            //Match 5 goal or more BET #27
                            $totalMatchScores=$itemList['match_hometeam_score']+$itemList['match_awayteam_score'];
                            if($totalMatchScores >= 5){
                                if($completedgamebet_hometeambetterbet=="match 5 goal or more"){
                                    $finalwinner="hometeam";
                                } elseif($completedgamebet_awayteambetterbet=="match 5 goal or more"){
                                    $finalwinner="awayteam";
                                } else {
                                    $finalwinner="draw";
                                }                                        
                            } else {
                                if($completedgamebet_hometeambetterbet=="match 5 goal or more"){
                                    $finalwinner="awayteam";
                                } elseif($completedgamebet_awayteambetterbet=="match 5 goal or more"){
                                    $finalwinner="hometeam";
                                } else {
                                    $finalwinner="draw";
                                }
                            }
                        } elseif($completedgamebet_hometeambetterbet=="match 6 goal or more" || $completedgamebet_awayteambetterbet=="match 6 goal or more"){
                            //Match 6 goal or more BET #28
                            $totalMatchScores=$itemList['match_hometeam_score']+$itemList['match_awayteam_score'];
                            if($totalMatchScores >= 6){
                                if($completedgamebet_hometeambetterbet=="match 6 goal or more"){
                                    $finalwinner="hometeam";
                                } elseif($completedgamebet_awayteambetterbet=="match 6 goal or more"){
                                    $finalwinner="awayteam";
                                } else {
                                    $finalwinner="draw";
                                }                                        
                            } else {
                                if($completedgamebet_hometeambetterbet=="match 6 goal or more"){
                                    $finalwinner="awayteam";
                                } elseif($completedgamebet_awayteambetterbet=="match 6 goal or more"){
                                    $finalwinner="hometeam";
                                } else {
                                    $finalwinner="draw";
                                }
                            }
                        } elseif($completedgamebet_hometeambetterbet=="match 7 goal or more" || $completedgamebet_awayteambetterbet=="match 7 goal or more"){
                            //Match 7 goal or more BET #29
                            $totalMatchScores=$itemList['match_hometeam_score']+$itemList['match_awayteam_score'];
                            if($totalMatchScores >= 7){
                                if($completedgamebet_hometeambetterbet=="match 7 goal or more"){
                                    $finalwinner="hometeam";
                                } elseif($completedgamebet_awayteambetterbet=="match 7 goal or more"){
                                    $finalwinner="awayteam";
                                } else {
                                    $finalwinner="draw";
                                }                                        
                            } else {
                                if($completedgamebet_hometeambetterbet=="match 7 goal or more"){
                                    $finalwinner="awayteam";
                                } elseif($completedgamebet_awayteambetterbet=="match 7 goal or more"){
                                    $finalwinner="hometeam";
                                } else {
                                    $finalwinner="draw";
                                }
                            }
                        } 
                        //Winner Decided Now, Proceed To Update DB
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
                            $gamebets->updateEntry($completedgamebet_id, "draw", "awayteambetterbetstatus"); //Update bet
                            $gamebets->updateEntry($completedgamebet_id, "draw", "hometeambetterbetstatus"); //Update bet
                        }
                        $gamebets->updateEntry($completedgamebet_id, "completed", "status"); //Update bet status
                        if($winnerAvailable=="yes"){                    
                            //Deduct from Loser Player Account Wallet for the Winner Player
                            $userData=$user->singleUser($loserPlayer_id);
                            $loserPlayer_username=$userData->username;
                            $loserPlayer_email=$userData->email;
                            $loserPlayer_phone=$userData->phone;
                            $loserPlayer_walletbalance=$userData->walletbalance;
                            $loserPlayer_NewBalance=$loserPlayer_walletbalance-$completedgamebet_finalbetamount;
                            $user->updateUserAccount($loserPlayer_id, $loserPlayer_NewBalance, "walletbalance"); //Update User account
                            //Add to Transactions
                            $data['orderid'] = date("Ymdhis", strtotime("now")).rand(100,1000); //Transaction Order ID;
                            $data['type'] = "loss";
                            $data['note'] = "Bet loss from game bet #$completedgamebet_id";
                            $data['amount'] = $completedgamebet_finalbetamount;
                            $data['paymentstatus'] = "paid";
                            $data['paymentmethod'] = "automated";
                            $data['userid'] = "$loserPlayer_id";
                            $data['username'] = "$loserPlayer_username";
                            $data['userreferrer'] = "";
                            $data['date'] = date("d-m-Y");
                            $transactions->addTransaction($data);
                            $emailerService->sendCustomerNewOrderConfirmationEmail($data['orderid'], $data['type'], $data['amount'], $loserPlayer_username, $loserPlayer_email);
                            $smsService->sendOrderNotificationCustomerSMS($loserPlayer_phone, $data['orderid']);
                            //Update Winner Player Account Wallet for the Win
                            $userData=$user->singleUser($winnerPlayer_id);
                            $winnerPlayer_username=$userData->username;
                            $winnerPlayer_email=$userData->email;
                            $winnerPlayer_phone=$userData->phone;
                            $winnerPlayer_walletbalance=$userData->walletbalance;
                            $winnerPlayer_NewBalance=$winnerPlayer_walletbalance+$completedgamebet_finalbetamount;
                            $user->updateUserAccount($winnerPlayer_id, $winnerPlayer_NewBalance, "walletbalance"); //Update User account
                            //Add to Transactions
                            $data['orderid'] = date("Ymdhis", strtotime("now")).rand(100,1000); //Transaction Order ID;
                            $data['type'] = "win";
                            $data['note'] = "Bet win from game bet #$completedgamebet_id";
                            $data['amount'] = $completedgamebet_finalbetamount;
                            $data['paymentstatus'] = "paid";
                            $data['paymentmethod'] = "automated";
                            $data['userid'] = "$winnerPlayer_id";
                            $data['username'] = "$winnerPlayer_username";
                            $data['userreferrer'] = "";
                            $data['date'] = date("d-m-Y");
                            $transactions->addTransaction($data);
                            $emailerService->sendCustomerNewOrderConfirmationEmail($data['orderid'], $data['type'], $data['amount'], $winnerPlayer_username, $winnerPlayer_email);
                            $smsService->sendOrderNotificationCustomerSMS($winnerPlayer_phone, $data['orderid']);
                        }
                    }
                }                
            }
        } else { 
            return print_r($resultData, true); 
        }
        return;
    }
    
    public function autoupdate_games($gameid)
    {
        /** Init Block **/
        $authService = new AuthService();
        $generalService = new GeneralService();
        $gameService = new GameService();
        $emailerService = new EmailerService();
        $smsService = new SMSService();
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

        //Get Game Details
        $gameData=$games->singleEntry($gameid); // Get data from the Database
        $entry_gameid=$gameData->id;
        $entry_providergameid=$gameData->gameid;

        //Start cURL
        $APIKey=$this->thirdpartygamesprovider_key;
        $APIHost=$this->thirdpartygamesprovider_host;
        $request_url=$this->thirdpartygamesprovider_baseurl."/fixtures?id=$entry_providergameid";
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "$request_url",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS =>'{"id": "' .$entry_providergameid. '"}',
        CURLOPT_HTTPHEADER => array(
            "X-RapidAPI-Key: $APIKey",
            "X-RapidAPI-Host: $APIHost"
        ),
        ));
        $response_output = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response_output, true);
        //End cURL

        $resultData = json_decode (json_encode ($result), FALSE);
        if (is_array($resultData->response)) {
            foreach($resultData->response as $entryData) {
                $itemList['match_id']=$entryData->fixture->id;
                $itemList['country_id']=$entryData->fixture->venue->name;
                $itemList['country_name']=$entryData->league->country;
                $itemList['league_id']=$entryData->league->id;
                $itemList['league_name']=$entryData->league->name;
                $itemList['league_season']=$entryData->league->season;
                $itemList['match_date']=$entryData->fixture->date;
                $itemList['match_status']=$entryData->fixture->status->long;
                $rawMatchTime=$entryData->fixture->date;
                $DateTime = new DateTime("$rawMatchTime");
                $DateTime->modify('-2 hours');
                $itemList['match_time']=$DateTime->format("H:i")." GMT";
                $itemList['match_hometeam_id']=$entryData->teams->home->id;
                $itemList['match_hometeam_name']=$entryData->teams->home->name;
                $itemList['match_hometeam_score']=$entryData->goals->home;
                $itemList['match_awayteam_name']=$entryData->teams->away->name;
                $itemList['match_awayteam_id']=$entryData->teams->away->id;
                $itemList['match_awayteam_score']=$entryData->goals->away;
                $itemList['match_hometeam_halftime_score']=$entryData->score->halftime->home;
                $itemList['match_awayteam_halftime_score']=$entryData->score->halftime->away;
                $itemList['match_hometeam_extra_score']=$entryData->score->extratime->home;
                $itemList['match_awayteam_extra_score']=$entryData->score->extratime->away;
                $itemList['match_hometeam_penalty_score']=$entryData->score->penalty->home;
                $itemList['match_awayteam_penalty_score']=$entryData->score->penalty->away;
                $itemList['match_hometeam_ft_score']=$entryData->score->fulltime->home;
                $itemList['match_awayteam_ft_score']=$entryData->score->fulltime->away;
                $itemList['match_live']=$entryData->fixture->status->short;
                $itemList['match_round']=$entryData->league->round;
                $itemList['match_stadium']=$entryData->fixture->venue->name;
                $itemList['team_home_badge']=$entryData->teams->home->logo;
                $itemList['team_away_badge']=$entryData->teams->away->logo;
                $itemList['league_logo']=$entryData->league->logo;
                $itemList['country_logo']=$entryData->league->logo;
                $itemList['goalscorer']="";
                $itemList['cards']="";
                $itemList['substitutions']="";
                $itemList['lineup']="";
                $itemList['statistics']="";
                $itemLists[] = $itemList;

                //Start Update Game
                $gameData=$games->singleEntryByGameID($itemList['match_id']); // Get data from the Database
                $gameID=$gameData->id;
                $gameStatus=$gameData->status;
                if($itemList['match_status']=="Match Finished" && $gameStatus!="played") {
                    //Update Game Result
                    $games->updateEntry($gameID, "played", "status"); //Update details changes
                    //Update Game Winners & Losers
                    $gameService->determinegamebet_winner_loser($gameID);
                } elseif($itemList['match_status']=="Kick Off" || $itemList['match_status']=="First Half" || $itemList['match_status']=="Halftime" || $itemList['match_status']=="2nd Half Started" || $itemList['match_status']=="Second Half" || $itemList['match_status']=="Extra Time" || $itemList['match_status']=="Break Time" || $itemList['match_status']=="Penalty In Progress" || $itemList['match_status']=="In Play") {
                    //Update Game Live Score
                    $games->updateEntry($gameID, "on-going", "status"); //Update details changes
                    $games->updateEntry($gameID, $itemList['match_hometeam_score'], "hometeamscore"); //Update details changes
                    $games->updateEntry($gameID, $itemList['match_awayteam_score'], "awayteamscore"); //Update details changes
                }
                //End Update Game
            }
            } else { 
                return print_r($resultData, true); 
            }
            return;
    }
}
?>