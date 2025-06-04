<?php
use Phalcon\DI;

class EmailerService {

    public $mailer; 
    public $siteTitle="BoardMan NG"; 
    public $siteURL="https://boardman.com.ng"; 
    public $adminEmail="webmaster@boardman.com.ng"; 

    public function setupMailer()
    {
          // To send HTML mail, the Content-type header must be set
          $headers  = 'MIME-Version: 1.0' . "\r\n";
          $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

          // Additional headers
          // $headers .= 'To: User Name <info@boardman.com.ng>, Boss <ceo@boardman.com.ng>' . "\r\n";
          $headers .= 'From: BoardMan NG <noreply@boardman.com.ng>' . "\r\n";
          $headers .= 'Reply-To: info@boardman.com.ng' . "\r\n";
          //$headers .= 'Cc: sales@boardman.com.ng' . "\r\n";
          //$headers .= 'Bcc: info@boardman.com.ng' . "\r\n";
          return $headers;
    }

    public function htmlEmailTemplate($bodycontent)
    {
          // message
          $bodycontent = str_replace("\n","<br>","$bodycontent");

          // HTML Design Template
          $content = "
            <!doctype html>
            <html>
              <head>
                <meta name='viewport' content='width=device-width' />
                <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
                <title>Email</title>
                <style>
                  /* -------------------------------------
                      GLOBAL RESETS
                  ------------------------------------- */
                  img {
                    border: none;
                    -ms-interpolation-mode: bicubic;
                    max-width: 100%; }

                  body {
                    background-color: #f6f6f6;
                    font-family: sans-serif;
                    -webkit-font-smoothing: antialiased;
                    font-size: 14px;
                    line-height: 1.4;
                    margin: 0;
                    padding: 0;
                    -ms-text-size-adjust: 100%;
                    -webkit-text-size-adjust: 100%; }

                  table {
                    border-collapse: separate;
                    mso-table-lspace: 0pt;
                    mso-table-rspace: 0pt;
                    width: 100%; }
                    table td {
                      font-family: sans-serif;
                      font-size: 14px;
                      vertical-align: top; }

                  /* -------------------------------------
                      BODY & CONTAINER
                  ------------------------------------- */

                  .body {
                    background-color: #f6f6f6;
                    width: 100%; }

                  /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
                  .container {
                    display: block;
                    Margin: 0 auto !important;
                    /* makes it centered */
                    max-width: 580px;
                    padding: 10px;
                    width: 580px; }

                  /* This should also be a block element, so that it will fill 100% of the .container */
                  .content {
                    box-sizing: border-box;
                    display: block;
                    Margin: 0 auto;
                    max-width: 580px;
                    padding: 10px; }

                  /* -------------------------------------
                      HEADER, FOOTER, MAIN
                  ------------------------------------- */
                  .main {
                    background: #ffffff;
                    border-radius: 3px;
                    width: 100%; }

                  .wrapper {
                    box-sizing: border-box;
                    padding: 20px; }

                  .content-block {
                    padding-bottom: 10px;
                    padding-top: 10px;
                  }

                  .footer {
                    clear: both;
                    Margin-top: 10px;
                    text-align: center;
                    width: 100%; }
                    .footer td,
                    .footer p,
                    .footer span,
                    .footer a {
                      color: #999999;
                      font-size: 12px;
                      text-align: center; }

                  /* -------------------------------------
                      TYPOGRAPHY
                  ------------------------------------- */
                  h1,
                  h2,
                  h3,
                  h4 {
                    color: #000000;
                    font-family: sans-serif;
                    font-weight: 400;
                    line-height: 1.4;
                    margin: 0;
                    Margin-bottom: 30px; }

                  h1 {
                    font-size: 35px;
                    font-weight: 300;
                    text-align: center;
                    text-transform: capitalize; }

                  p,
                  ul,
                  ol {
                    font-family: sans-serif;
                    font-size: 14px;
                    font-weight: normal;
                    margin: 0;
                    Margin-bottom: 15px; }
                    p li,
                    ul li,
                    ol li {
                      list-style-position: inside;
                      margin-left: 5px; }

                  a {
                    color: #3498db;
                    text-decoration: underline; }

                  /* -------------------------------------
                      BUTTONS
                  ------------------------------------- */
                  .btn {
                    box-sizing: border-box;
                    width: 100%; }
                    .btn > tbody > tr > td {
                      padding-bottom: 15px; }
                    .btn table {
                      width: auto; }
                    .btn table td {
                      background-color: #ffffff;
                      border-radius: 5px;
                      text-align: center; }
                    .btn a {
                      background-color: #ffffff;
                      border: solid 1px #3498db;
                      border-radius: 5px;
                      box-sizing: border-box;
                      color: #3498db;
                      cursor: pointer;
                      display: inline-block;
                      font-size: 14px;
                      font-weight: bold;

                      margin: 0;
                      padding: 12px 25px;
                      text-decoration: none;
                      text-transform: capitalize; }

                  .btn-primary table td {
                    background-color: #3498db; }

                  .btn-primary a {
                    background-color: #3498db;
                    border-color: #3498db;
                    color: #ffffff; }

                  /* -------------------------------------
                      OTHER STYLES THAT MIGHT BE USEFUL
                  ------------------------------------- */
                  .last {
                    margin-bottom: 0; }

                  .first {
                    margin-top: 0; }

                  .align-center {
                    text-align: center; }

                  .align-right {
                    text-align: right; }

                  .align-left {
                    text-align: left; }

                  .clear {
                    clear: both; }

                  .mt0 {
                    margin-top: 0; }

                  .mb0 {
                    margin-bottom: 0; }

                  .preheader {
                    color: transparent;
                    display: none;
                    height: 0;
                    max-height: 0;
                    max-width: 0;
                    opacity: 0;
                    overflow: hidden;
                    mso-hide: all;
                    visibility: hidden;
                    width: 0; }

                  .powered-by a {
                    text-decoration: none; }

                  hr {
                    border: 0;
                    border-bottom: 1px solid #f6f6f6;
                    Margin: 20px 0; }

                  /* -------------------------------------
                      RESPONSIVE AND MOBILE FRIENDLY STYLES
                  ------------------------------------- */
                  @media only screen and (max-width: 620px) {
                    table[class=body] h1 {
                      font-size: 28px !important;
                      margin-bottom: 10px !important; }
                    table[class=body] p,
                    table[class=body] ul,
                    table[class=body] ol,
                    table[class=body] td,
                    table[class=body] span,
                    table[class=body] a {
                      font-size: 16px !important; }
                    table[class=body] .wrapper,
                    table[class=body] .article {
                      padding: 10px !important; }
                    table[class=body] .content {
                      padding: 0 !important; }
                    table[class=body] .container {
                      padding: 0 !important;
                      width: 100% !important; }
                    table[class=body] .main {
                      border-left-width: 0 !important;
                      border-radius: 0 !important;
                      border-right-width: 0 !important; }
                    table[class=body] .btn table {
                      width: 100% !important; }
                    table[class=body] .btn a {
                      width: 100% !important; }
                    table[class=body] .img-responsive {
                      height: auto !important;
                      max-width: 100% !important;
                      width: auto !important; }}

                  /* -------------------------------------
                      PRESERVE THESE STYLES IN THE HEAD
                  ------------------------------------- */
                  @media all {
                    .ExternalClass {
                      width: 100%; }
                    .ExternalClass,
                    .ExternalClass p,
                    .ExternalClass span,
                    .ExternalClass font,
                    .ExternalClass td,
                    .ExternalClass div {
                      line-height: 100%; }
                    .apple-link a {
                      color: inherit !important;
                      font-family: inherit !important;
                      font-size: inherit !important;
                      font-weight: inherit !important;
                      line-height: inherit !important;
                      text-decoration: none !important; }
                    .btn-primary table td:hover {
                      background-color: #34495e !important; }
                    .btn-primary a:hover {
                      background-color: #34495e !important;
                      border-color: #34495e !important; } }

                </style>
              </head>
              <body class=''>
                <table border='0' cellpadding='0' cellspacing='0' class='body'>
                  <tr>
                    <td>&nbsp;</td>
                    <td class='container'>
                      <div class='content'>
                        <center><a href='https://www.boardman.com.ng'><img src='https://www.boardman.com.ng/assets/img/logo.png'></a></center>
                        <!-- START CENTERED WHITE CONTAINER -->
                        <span class='preheader'></span>
                        <table class='main'>

                          <!-- START MAIN CONTENT AREA -->
                          <tr>
                            <td class='wrapper'>
                              <table border='0' cellpadding='0' cellspacing='0'>
                                <tr>
                                  <td>
                                    <p>$bodycontent</p>
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>

                        <!-- END MAIN CONTENT AREA -->
                        </table>

                        <!-- START FOOTER -->
                        <div class='footer'>
                          <table border='0' cellpadding='0' cellspacing='0'>
                            <!--<tr>
                              <td class='content-block'>
                                <span class='apple-link'>Company Inc, 3 Abbey Road, San Francisco CA 94102</span>
                                <br> Don't like these emails? <a href=''>Unsubscribe</a>.
                              </td>
                            </tr> -->
                            <tr>
                              <td class='content-block powered-by'>
                                Copyright &copy; 2020 BoardMan Nigeria Ltd.
                              </td>
                            </tr>
                          </table>
                        </div>
                        <!-- END FOOTER -->

                      <!-- END CENTERED WHITE CONTAINER -->
                      </div>
                    </td>
                    <td>&nbsp;</td>
                  </tr>
                </table>
              </body>
            </html>
            ";
          return $content;
    }
    
    public function sendWelcomeEmail($username, $useremail, $verificationcode, $newpassword=null)
    { //A Welcome email that sends to newly registered users
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="Welcome To $siteTitle";
        $message="Hi $username, thanks for signing up at $siteTitle, below are your login details and your activation link\n\n 
                  Username: $username\n
                  Email: $useremail\n
                  Password: $newpassword\n
                  \n
                  Please activate your account by clicking on the following link <a href=$siteURL/verifyaccount/$username/$verificationcode>$siteURL/verifyaccount/$username/$verificationcode</a> and enter the activation code sent to your phone.\n
                  If the above link is not clickable, kindly copy the address and paste it in the address bar of your browser to access the page instead.\n
                  \n
                  Regards,\n
                  $siteTitle Team.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendNewPasswordResetEmail($useremail, $userpassword)
    { //An email that sends to registered users on their password recovery
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="New Account Password On $siteTitle";
        $message="Hello, \n\n 
                  You requested a password reset action on your account via our forgotten password form and here is your new password.\n 
                  \n
                  New password: $userpassword\n
                  \n
                  Please use it to login to your account and you can also change it thereafter to keep your account secure.\n
                  \n
                  Regards,\n
                  $siteTitle Team.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendPasswordResetLinkEmail($useremail, $userpasswordresetcode)
    { //An email that sends to registered users on their password recovery
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="Password Reset Request On $siteTitle";
        $message="Hello, \n\n 
                  You requested a password reset action on your account via our forgotten password form and here is your password recovery link.\n 
                  \n
                  Reset Link: <a href=$siteURL/passwordresetchange/$useremail/$userpasswordresetcode>$siteURL/passwordresetchange/$useremail/$userpasswordresetcode</a>\n
                  \n
                  Please click this link to reset your password.\n
                  \n
                  Regards,\n
                  $siteTitle Team.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }
    
    public function sendPasswordRecoveryEmail($useremail, $userpasswordresetcode)
    { //An email that sends to registered users on their password recovery
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="Password Reset Request On $siteTitle";
        $message="Hello, \n\n 
                  You requested a password reset action on your account via our forgotten password form and here is your password recovery code.\n 
                  \n
                  Code: $userpasswordresetcode\n
                  \n
                  Please enter this code on your forgotten password page to reset your password.\n
                  \n
                  Regards,\n
                  $siteTitle Team.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }
    
    public function sendPassCodeEmail($useremail, $userpasscode)
    { //An email that sends to registered users on their password recovery
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="Login OTP Code On $siteTitle";
        $message="Hello, \n\n 
                  You are attempting to login on $siteTitle and below is your OTP code.\n 
                  \n
                  Code: $userpasscode\n
                  \n
                  Please enter this code on the app to proceed with your login.\n
                  \n
                  Regards,\n
                  $siteTitle Team.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendAdminNewOrderAlertEmail($orderid, $orderdetails, $ordertotal, $customername, $customeremail)
    { //An email that sends to Admin alerting them of the new order posted by customer
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to = $this->adminEmail;
        $headers=$this->setupMailer();
        $subject="New Transaction For User On $siteTitle";
        $message="Hello Admin, \n\n 
                  A user $customername just had a $orderdetails transaction of N$ordertotal with transaction ID #$orderid on the platform.\n 
                  \n
                  You can login to your Admin Portal to monitor this transaction.\n
                  \n
                  Regards,\n
                  $siteTitle Team.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendCustomerNewOrderConfirmationEmail($orderid, $orderdetails, $ordertotal, $username, $useremail)
    { //An email that sends to Customer confirming their new order 
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="New Transaction Performed On $siteTitle";
        $message="Hello $username, \n
                  You just had a $orderdetails transaction on $siteTitle of N$ordertotal in your account.\n 
                  Your transaction ID is #$orderid.\n
                  Thanks for choosing us.\n
                  Best regards,\n
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendCustomerGameBetNewStatusAlertEmail($orderid, $newstatus, $username, $useremail)
    { //An email that sends to Customer alerting them of the new status of their order delivery
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="Game Bet Update";
        $message="Hello $username, \n\n 
                  Your game bet #$orderid result status changed to $newstatus on the platform.\n 
                  \n
                  You can login to your Account to view and for more details.\n
                  \n
                  Regards,
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendReferrerNewOrderAlertEmail($orderid, $customername, $username, $useremail)
    { //An email that sends to Merchant alerting them of an order of their product by A Customer
      /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="New Order By Your Referred Customer";
        $message="Hello $username, \n\n 
                  A customer $customername you referred just made an order on $siteTitle with Order ID $orderid.\n 
                  \n
                  You can login to your Partner Portal for more details.\n
                  \n
                  Best regards,\n
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendReferrerCommissionPaymentStatusUpdateEmail($orderid, $paymentresult, $commissionamount, $username, $useremail)
    { //An email that sends to Customer alerting them of their order payment attempt and result
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="Referral Commission Payment Updated $siteTitle";
        $message="Dear $username, \n\n 
                  Your commission payment for order #$orderid on $siteTitle for referrals has changed and below is the result.\n 
                  \n
                  Status: $paymentresult\n
                  Amount: N$commissionamount\n
                  \n
                  You can contact us or submit a support ticket if you have any issue with this payment. Thanks for being a great partner.\n
                  \n
                  Best regards,\n
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendAdminNewTicketAlertEmail($ticketid, $title, $author, $username, $useremail)
    { //An email that sends to Admin alerting them of new support ticket submission
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to = $this->adminEmail;
        $headers=$this->setupMailer();
        $subject="New Support Ticket Submission On $siteTitle";
        $message="Dear Admin, \n\n 
                  $author just submitted a new support ticket #$ticketid to $username on $siteTitle with title\n 
                  \n
                  $title\n
                  \n
                  Please login to your Admin Portal to access and post ticket reply.\n
                  \n
                  Best regards,
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }
    
    public function sendNewTicketAlertEmail($ticketid, $title, $username, $useremail)
    { //An email that sends to User confirming them of their new support ticket submission
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="New Support Ticket Submission On $siteTitle";
        $message="Dear $username, \n\n 
                  You just submitted a new support ticket #$ticketid on $siteTitle with title\n 
                  \n
                  $title\n
                  \n
                  You would be notified once you get a ticket reply.\n
                  \n
                  Best regards,\n
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendNewTicketAlertRecepientEmail($ticketid, $title, $author, $username, $useremail)
    { //An email that sends to User confirming them of their new support ticket submission
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="New Support Ticket Submission On $siteTitle";
        $message="Dear $username, \n\n 
                  $author just submitted a new support ticket #$ticketid to you on $siteTitle with title\n 
                  \n
                  $title\n
                  \n
                  Please login to your account to view and respond.\n
                  \n
                  Best regards,\n
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendAdminNewTicketReplyAlertEmail($ticketid, $title, $username, $useremail)
    { //An email that sends to Admin alerting them of new support ticket reply
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to = $this->adminEmail;
        $headers=$this->setupMailer();
        $subject="Support Ticket Reply On $siteTitle";
        $message="Dear Admin, \n\n 
                  $username just submitted a reply to ticket #$ticketid on $siteTitle with title\n 
                  \n
                  $title\n
                  \n
                  Please login to your Admin Portal to access and post ticket reply.\n
                  \n
                  Best regards,
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendNewTicketReplyAlertEmail($ticketid, $title, $username, $useremail)
    { //An email that sends to User alerting them of new support ticket reply
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="Support Ticket Reply On $siteTitle";
        $message="Dear $username, \n\n 
                  You just posted a new reply to your ticket #$ticketid on $siteTitle with title\n 
                  \n
                  $title\n
                  \n
                  You can login to your account to view and manage.\n
                  \n
                  Best regards,\n
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendNewTicketReplyAlertRecepientEmail($ticketid, $title, $author, $username, $useremail)
    { //An email that sends to User alerting them of new support ticket reply
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="Support Ticket Reply On $siteTitle";
        $message="Dear $username, \n\n 
                  $author just posted a new reply to your ticket #$ticketid on $siteTitle with title\n 
                  \n
                  $title\n
                  \n
                  You can login to your account to view and respond.\n
                  \n
                  Best regards,\n
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendCustomerNewBetEmail($gametitle, $bet, $betamount, $username, $useremail)
    { //An email that sends to User alerting them of new bet added
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="New Bet Placed On $siteTitle";
        $message="Dear $username, \n\n 
                  You just submitted a new bet on $siteTitle\n 
                  \n
                  Game: $gametitle\n
                  \n
                  Amount: N$betamount\n
                  \n
                  Please login to your account to view and monitor this bet.\n
                  \n
                  Best regards,
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendCustomerNewBetPeerEmail($gametitle, $bet, $betamount, $username, $useremail)
    { //An email that sends to User alerting them of new bet added
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="New Bet Peer Placed On $siteTitle";
        $message="Dear $username, \n\n 
                  You just submitted a new bet peer on $siteTitle\n 
                  \n
                  Game: $gametitle\n
                  \n
                  Amount: N$betamount\n
                  \n
                  Please login to your account to view and monitor this bet.\n
                  \n
                  Best regards,
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendCustomerNewBetPeerAlertEmail($gametitle, $bet, $betamount, $player, $username, $useremail)
    { //An email that sends to User alerting them of new bet added
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="New Bet Peer For You On $siteTitle";
        $message="Dear $username, \n\n 
                  $player just submitted a new game bet peer with you on $siteTitle\n 
                  \n
                  Game: $gametitle\n
                  \n
                  Amount: N$betamount\n
                  \n
                  Please login to your account to view and monitor this bet.\n
                  \n
                  Best regards,
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendAdminNewTestimonialAlertEmail($author, $authorjob, $authorlocation, $details)
    { //An email that sends to Admin alerting them of new product review added
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to = $this->adminEmail;
        $headers=$this->setupMailer();
        $subject="New Customer Testimonial Posted On $siteTitle";
        $message="Dear Admin, \n\n 
                  $author just submitted a new customer testimonial feedback on $siteTitle\n 
                  \n
                  Customer Name: $author\n
                  \n
                  Customer Profession: $authorjob\n
                  \n
                  Customer Location: $authorlocation\n
                  \n
                  Details: $details\n
                  \n
                  Please login to your Admin Portal to view and approve this feedback testimonial.\n
                  \n
                  Best regards,
                  $siteTitle.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendContactFeedbackEmail($name, $email, $phone, $subject, $details)
    { //A Welcome email that sends to newly registered users
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$this->adminEmail;
        $headers=$this->setupMailer();
        $subject="New Contact Form Entry On $siteTitle";
        $message="Hello Admin, \n
                  Here is a new Contact form entry on $siteTitle, below are the details\n\n 
                  Full name: $name\n
                  Email: $email\n
                  Phone: $phone\n
                  Subject: $subject\n
                  Details: $details\n                  
                  \n
                  Regards,\n
                  $siteTitle Team.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendNewsletterSubscriptionEmail($email, $name=null)
    { //A Welcome email that sends to newly registered users
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$this->adminEmail;
        $headers=$this->setupMailer();
        $subject="New Newsletter Subscription Entry On $siteTitle";
        $message="Hello Admin, \n
                  Here is a new Newsletter Subscription form entry on $siteTitle, below are the details\n\n 
                  Full name: $name\n
                  Email: $email\n              
                  \n
                  Regards,\n
                  $siteTitle Team.\n\n";
        $content=$this->htmlEmailTemplate($message);

        // Mail it
        mail($to, $subject, $content, $headers);
    }

    public function sendNewsletterEmail($title, $message, $username, $useremail)
    { //A Welcome email that sends to newly registered users
        /** Init Block **/
        $siteTitle = $this->siteTitle;
        $siteURL = $this->siteURL;
        $to=$useremail;
        $headers=$this->setupMailer();
        $subject="$title";
        $message="Hello $username, \n
                  $message            
                  \n
                  Regards,\n
                  $siteTitle Team.\n\n";
        $content=$this->htmlEmailTemplate($message);
        // Mail it
        mail($to, $subject, $content, $headers);
    }
}
?>