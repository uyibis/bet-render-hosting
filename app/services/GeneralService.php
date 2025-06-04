<?php
use Phalcon\Crypt;

class GeneralService {

    public function getAllForm()
    {
        //Receive form submitted data
        $request = new Phalcon\Http\Request();
        $rawData = $request->getRawBody();
        return $rawData;
    }

    public function generate_logintoken($data,$action="encode") { //Function to generate login token code
        /***
        $key = "example_key";
        $payload = array(
            "iss" => "http://example.org",
            "aud" => $data['username'],
            "iat" => 1356999524,
            "nbf" => 1357000000
        );
        $jwt = JWT::encode($payload, $key);//Encode
        $encoded = $jwt;//Encoded
        $decoded = JWT::decode($jwt, $key, array('HS256'));//Decode
        $decoded_array = (array) $decoded;//Decoded
        **/
        /**TEMP**/
        $crypt = new Crypt();
        $crypt->setCipher('aes-256-ctr');
        $key  = "BoardMan";
        $text = $data['username'].", ".date("d-m-Y");
        if($action=="decode"){
            $decrypted = $crypt->decrypt($data, $key);
            $result = $decrypted;
        } else {
            $encrypted = $crypt->encrypt($text, $key);
            $result = $encrypted;
        }
        //Return
        $result=bin2hex(random_bytes('10')); //Auth login token
        return $result;
    }
    
    public function getSafeURL($data)
    {
        $data=strtolower("$data");
        $validusername = str_replace(' ', '-', $data); // Replaces all spaces with hyphens.
        $validusername = preg_replace('/\s+/', '', $validusername);
        $validusername = preg_replace('/[^A-Za-z0-9\-]/', '', $validusername);
        return $validusername;
    }

    public function trim_text($input, $length, $ellipses = true, $strip_html = true)
    {
        //strip tags, if desired
        if ($strip_html) {
            $input = strip_tags($input);
        }
      
        //no need to trim, already shorter than trim length
        if (strlen($input) <= $length) {
            return $input;
        }
      
        //find last space within length
        $last_space = strrpos(substr($input, 0, $length), ' ');
        $trimmed_text = substr($input, 0, $last_space);
      
        //add ellipses (...)
        if ($ellipses) {
            $trimmed_text .= '...';
        }
      
        return $trimmed_text;
    }

    public function data_encrypt($value) { 
        // Encrypt data using OpenSSL and AES-256-CBC
         $key = 'youkey';
         $newvalue = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $key ), $value, MCRYPT_MODE_CBC, md5( md5( $key ) ) ) );
        return $newvalue;
    }
  
    public function data_decrypt($value) { 
        // Decrypt data using OpenSSL and AES-256-CBC
         $key = 'youkey';
         $newvalue = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $key ), base64_decode( $value ), MCRYPT_MODE_CBC, md5( md5( $key ) ) ), "\0");
        return $newvalue;
    }

    public function get_LocationCoordinates($city, $street, $province) {
        $address = urlencode($city.','.$street.','.$province);
        $url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=Nigeria";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response);
        $status = $response_a->status;

        if ( $status == 'ZERO_RESULTS' )
        {
            return FALSE;
        }
        else
        {
            $return = array('lat' => $response_a->results[0]->geometry->location->lat, 'long' => $long = $response_a->results[0]->geometry->location->lng);
            return $return;
        }
    }

    public function getLocationCoordinates($location,$coordinatetype) {
        $address = urlencode($location);
        $url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=Nigeria";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response);
        $status = $response_a->status;

        if ( $status == 'ZERO_RESULTS' )
        {
            return FALSE;
        }
        else
        {
            if($coordinatetype=="latitude"){
                return $response_a->results[0]->geometry->location->lat;
            } elseif($coordinatetype=="longitude"){
                return $response_a->results[0]->geometry->location->lng;
            } else {
                $return = array('lat' => $response_a->results[0]->geometry->location->lat, 'long' => $long = $response_a->results[0]->geometry->location->lng);
                return $return;
            }
            return true;
        }
    }

    public function get_LocationDrivingDistance($lat1, $lat2, $long1, $long2) {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&mode=driving";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response, true);
        $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
        $time = $response_a['rows'][0]['elements'][0]['duration']['text'];

        return array('distance' => $dist, 'time' => $time);
    }

    public function getTimeAgo($time) { //Function to generate time ago
       $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
       $lengths = array("60","60","24","7","4.35","12","10");

       $now = time();

           $difference     = $now - $time;
           $tense         = "ago";

       for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
           $difference /= $lengths[$j];
       }

       $difference = round($difference);

       if($difference != 1) {
           $periods[$j].= "s";
       }

      return "$difference $periods[$j] ago ";
    }

    public static function generate_code($length) { //Function to generate random codes
        $length = trim($length); // remove whitespaces from begining and end
        
        $string = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code = "";
        for($i=0; $i<$length; $i++){
        $y = rand(0,strlen($string)-1);
        $code .= $string[$y];
        }
        return $code;
    }

    public static function send_pushnotifications($userslist,$title,$content) { //Function to send push notifications
        //Start cURL
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => "https://management-api.wonderpush.com/v1/deliveries?accessToken=Y2YzNGJkMDNlMTJlOTZjODk4NjY5ZjQyNzM0OWMzODA5ZDllZjMxNmNiMzJiMGUyYTMyNDFhY2NhNWNlNDBhNQ",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>'{"targetSegmentIds": "@ALL", "notification": {"alert": {"text": "' .$content. '", "ios":{"attachments":[{"url":"' .$media. '"}]}, "android":{"type":"bigPicture", "bigPicture":"' .$media. '"}, "web":{"image":"' .$media. '"}}}}',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
                ));
                $response_output = curl_exec($curl);
                curl_close($curl);
        //End cURL
        return $response_output;
    }

    public static function send_pushnotificationtoone($userid,$title,$content,$media="null") { //Function to send push notifications
        //Start cURL
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => "https://management-api.wonderpush.com/v1/deliveries?accessToken=Y2YzNGJkMDNlMTJlOTZjODk4NjY5ZjQyNzM0OWMzODA5ZDllZjMxNmNiMzJiMGUyYTMyNDFhY2NhNWNlNDBhNQ",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>'{"targetSegmentIds": "' .$userid. '", "notification": {"alert": {"text": "' .$content. '", "ios":{"attachments":[{"url":"' .$media. '"}]}, "android":{"type":"bigPicture", "bigPicture":"' .$media. '"}, "web":{"image":"' .$media. '"}}}}',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
                ));
                $response_output = curl_exec($curl);
                curl_close($curl);
        //End cURL
        return $response_output;
    }

    public static function send_pushnotificationtoall($title,$content,$media="null") { //Function to send push notifications
        //Start cURL
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => "https://management-api.wonderpush.com/v1/deliveries?accessToken=Y2YzNGJkMDNlMTJlOTZjODk4NjY5ZjQyNzM0OWMzODA5ZDllZjMxNmNiMzJiMGUyYTMyNDFhY2NhNWNlNDBhNQ",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>'{"targetSegmentIds": "@ALL", "notification": {"alert": {"text": "' .$content. '", "ios":{"attachments":[{"url":"' .$media. '"}]}, "android":{"type":"bigPicture", "bigPicture":"' .$media. '"}, "web":{"image":"' .$media. '"}}}}',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
                ));
                $response_output = curl_exec($curl);
                curl_close($curl);
        //End cURL
        return $response_output;
    }

    public function getUserID($userId, $accounttype)
    {
        //Auth get User account type
        if($accounttype=="user"){
            $user = new Users();
            $user->initialize();
        } elseif($accounttype=="admin"){
            $user = new Admin();
            $user->initialize();
        } else {
            $user = new Users();
            $user->initialize();
        }

        //User check on the Database
        $result=$user->getUserID($userId);
        return $result;
    }

    public function getUserName($userId, $accounttype)
    {
        //Auth get User account type
        if($accounttype=="user"){
            $user = new Users();
            $user->initialize();
        } elseif($accounttype=="admin"){
            $user = new Admin();
            $user->initialize();
        } else {
            $user = new Users();
            $user->initialize();
        }

        //User check on the Database
        $result=$user->getUserName($userId);
        return $result;
    }

    public function getUserEmail($userId, $accounttype)
    {
        //Auth get User account type
        if($accounttype=="user"){
            $user = new Users();
            $user->initialize();
        } elseif($accounttype=="admin"){
            $user = new Admin();
            $user->initialize();
        } else {
            $user = new Users();
            $user->initialize();
        }

        //User check on the Database
        $result=$user->getUserEmail($userId);
        return $result;
    }

    public function getUserPhone($userId, $accounttype)
    {
        //Auth get User account type
        if($accounttype=="user"){
            $user = new Users();
            $user->initialize();
        } elseif($accounttype=="admin"){
            $user = new Admin();
            $user->initialize();
        } else {
            $user = new Users();
            $user->initialize();
        }

        //User check on the Database
        $result=$user->getUserPhone($userId);
        return $result;
    }

    public function getUserPhoto($userId, $accounttype)
    {
        //Auth get User account type
        if($accounttype=="user"){
            $user = new Users();
            $user->initialize();
        } elseif($accounttype=="admin"){
            $user = new Admin();
            $user->initialize();
        } else {
            $user = new Users();
            $user->initialize();
        }

        //User check on the Database
        $result=$user->getUserPhoto($userId);
        return $result;
    }
    
    public function getCategoryIDbyLeague($post)
    {
        //Category Model Object
        $category = new Categories();
        $category->initialize();

        $result=$category->getCategoryIDbyLeague($post);
        return $result;
    }
    
    public function getCategoryID($post)
    {
        //Category Model Object
        $category = new Categories();
        $category->initialize();

        //Category check on the Database
        if(filter_var($post, FILTER_VALIDATE_INT) !== false){
            return $post;
        }
        $result=$category->getCategoryID($post);
        return $result;
    }

    public function getCategoryName($postid)
    {
        //Category Model Object
        $category = new Categories();
        $category->initialize();

        //Category check on the Database
        $result=$category->getCategoryName($postid);
        return $result;
    }
    
    public function getCategoryLeagueID($post)
    {
        //Category Model Object
        $category = new Categories();
        $category->initialize();

        //Category check on the Database
        if(filter_var($post, FILTER_VALIDATE_INT) !== false){
            return $post;
        }
        $result=$category->getCategoryLeagueID($post);
        return $result;
    }

    public function getSubCategoryID($post)
    {
        //SubCategory Model Object
        $subcategory = new Subcategories();
        $subcategory->initialize();

        //SubCategory check on the Database
        if(filter_var($post, FILTER_VALIDATE_INT) !== false){
            return $post;
        }
        $result=$subcategory->getSubcategoryID($post);
        return $result;
    }

    public function getSubCategoryName($postid)
    {
        //SubCategory Model Object
        $subcategory = new Subcategories();
        $subcategory->initialize();

        //SubCategory check on the Database
        $result=$subcategory->getSubcategoryName($postid);
        return $result;
    }

    public function getLatestSubCategoryID($post)
    {
        //SubCategory Model Object
        $subcategory = new Subcategories();
        $subcategory->initialize();

        $result=$subcategory->getLatestSubcategoryID($post);
        return $result;
    }

}
?>