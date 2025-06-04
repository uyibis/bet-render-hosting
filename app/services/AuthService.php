<?php

class AuthService {

    public function UserAuth($userid, $userlogintoken, $useraccounttype)
    {
        //Auth get User account type
        if($useraccounttype=="user"){
            $user = new Users();
            $user->initialize();
        } elseif($useraccounttype=="merchant"){
            $user = new Merchants();
            $user->initialize();
        } elseif($useraccounttype=="admin"){
            $user = new Admin();
            $user->initialize();
        } else {
            $user = new Users();
            $user->initialize();
        }

        //Auth check on User from the Database
        $authResult=$user->authAccount($userid, $userlogintoken);
        if ($authResult==1) {
            return true;
        } else {
            return false;
        }
    }

}
?>