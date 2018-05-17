<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-WCMp-Vendor-Catagorization-recurring-payments
 *
 * @author ubuntu-1111
 */
class WCMP_Vendor_Membership_Recurring_Payments {

    public $sandbox = '';
    public $api_username = '';
    public $api_password = '';
    public $api_signature = '';
    public $print_headers = '';
    public $log_results = '';
    public $log_path = '';
    public $PROXY_HOST = '127.0.0.1';
    public $PROXY_PORT = '808';
    public $SandboxFlag = true;
    // BN Code 	is only applicable for partners
    public $sBNCode = "PP-ECWizard";
    public $USE_PROXY = false;
    public $version = "64";
    public $API_Endpoint = '';
    public $PAYPAL_URL = '';

    public function __construct($PayPalConfig) {

        $this->sandbox = $PayPalConfig['Sandbox'];
        $this->api_username = $PayPalConfig['APIUsername'];
        $this->api_password = $PayPalConfig['APIPassword'];
        $this->api_signature = $PayPalConfig['APISignature'];
        $this->print_headers = $PayPalConfig['PrintHeaders'];
        $this->log_results = $PayPalConfig['LogResults'];
        $this->log_path = $PayPalConfig['LogPath'];

        if ($this->sandbox) {
            $this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
            $this->PAYPAL_URL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
        } else {
            $this->API_Endpoint = "https://api-3t.paypal.com/nvp";
            $this->PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
        }
        if (session_id() == "")
            session_start();
    }

    public function SetExpressCheckout($returnURL, $cancelURL, $args) {
        $nvpstr = '';
        foreach ($args as $key => $value) {
            $nvpstr .= '&' . $key . '=' . urlencode($value);
        }
        $nvpstr = $nvpstr . "&RETURNURL=" . $returnURL;
        $nvpstr = $nvpstr . "&CANCELURL=" . $cancelURL;

        //'--------------------------------------------------------------------------------------------------------------- 
        //' Make the API call to PayPal
        //' If the API call succeded, then redirect the buyer to PayPal to begin to authorize payment.  
        //' If an error occured, show the resulting errors
        //'---------------------------------------------------------------------------------------------------------------
        $resArray = $this->hash_call("SetExpressCheckout", $nvpstr);
        $ack = strtoupper($resArray["ACK"]);
        if ($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
            $token = urldecode($resArray["TOKEN"]);
        }
        return $resArray;
    }

    public function getExpressCheckout($token) {
        $nvpstr = "&TOKEN=" . $token;
        $resArray = $this->hash_call("GetExpressCheckoutDetails", $nvpstr);
        $ack = strtoupper($resArray["ACK"]);
        if (isset($resArray['PAYERID']) && ($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING")) {
            $_SESSION['payer_id'] = $resArray['PAYERID'];
        }
        return $resArray;
    }

    public function CreateRecurringPaymentsProfile($PayPalRequestData = array()) {

        //'---------------------------------------------------------------------------
        //' Build a second API request to PayPal, using the token as the
        //'  ID to get the details on the payment authorization
        //'---------------------------------------------------------------------------
        $nvpstr = '';
        $accessdetails = isset($PayPalRequestData['AccessDetails']) ? $PayPalRequestData['AccessDetails'] : array();
//        echo '</pre>';
//        print_r($PayPalRequestData);
        if(isset($accessdetails['TOKEN'])) {
            $nvpstr .="&TOKEN=".$accessdetails['TOKEN'];
        }
        if(isset($accessdetails['PAYERID'])){
            $nvpstr .="&PAYERID=".$accessdetails['PAYERID'];
        }
        //$nvpstr="&TOKEN=".$token;
        /*         * ***************************payerInfo**************************************** */
        $PayerInfo = isset($PayPalRequestData['PayerInfo']) ? $PayPalRequestData['PayerInfo'] : array();
        $email = isset($PayerInfo['email']) ? $PayerInfo['email'] : '';
        $nvpstr.="&EMAIL=" . $email;
        /*         * ************************Ship To Address Fields***************************** */
        $ShippingAddress = isset($PayPalRequestData['ShippingAddress']) ? $PayPalRequestData['ShippingAddress'] : array();
        $shiptoname = isset($ShippingAddress['shiptoname']) ? $ShippingAddress['shiptoname'] : '';
        $nvpstr.="&SHIPTONAME=" . $shiptoname;
        $shiptostreet = isset($ShippingAddress['shiptostreet']) ? $ShippingAddress['shiptostreet'] : '';
        $nvpstr.="&SHIPTOSTREET=" . $shiptostreet;
        $shiptocity = isset($ShippingAddress['shiptocity']) ? $ShippingAddress['shiptocity'] : '';
        $nvpstr.="&SHIPTOCITY=" . $shiptocity;
        $shiptostate = isset($ShippingAddress['shiptostate']) ? $ShippingAddress['shiptostate'] : '';
        $nvpstr.="&SHIPTOSTATE=" . $shiptostate;
        $shiptozip = isset($ShippingAddress['shiptozip']) ? $ShippingAddress['shiptozip'] : '';
        $nvpstr.="&SHIPTOZIP=" . $shiptozip;
        $shiptocountry = isset($ShippingAddress['shiptocountrycode']) ? $ShippingAddress['shiptocountrycode'] : '';
        $nvpstr.="&SHIPTOCOUNTRY=" . $shiptocountry;
        /*         * *******************************Recurring Payments Profile Details Fields***************************************** */
        $ProfileDetails = isset($PayPalRequestData['ProfileDetails']) ? $PayPalRequestData['ProfileDetails'] : array();
        $profilestartdate = isset($ProfileDetails['profilestartdate']) ? urlencode($ProfileDetails['profilestartdate']) : '';
        $nvpstr.="&PROFILESTARTDATE=" . $profilestartdate;
        /*         * ***************************Schedule Details Fields*********** */
        $ScheduleDetails = isset($PayPalRequestData['ScheduleDetails']) ? $PayPalRequestData['ScheduleDetails'] : array();
        $desc = isset($ScheduleDetails['desc']) ? urlencode($ScheduleDetails['desc']) : urlencode("WCMp Recurring Payment");
        if (empty($desc))
            $desc = urlencode("WCMp Recurring Payment");
        $nvpstr.="&DESC=" . $desc;
        $maxfailedpayment = isset($ScheduleDetails['maxfailedpayments']) ? $ScheduleDetails['maxfailedpayments'] : '';
        $nvpstr.="&MAXFAILEDPAYMENTS=" . $maxfailedpayment;
        $autobillamt = isset($ScheduleDetails['autobillamt']) ? $ScheduleDetails['autobillamt'] : '';
        $nvpstr.="&AUTOBILLOUTAMT=" . $autobillamt;
        /*         * *************************Billing Period Details Fields*************** */
        $BillingPeriod = isset($PayPalRequestData['BillingPeriod']) ? $PayPalRequestData['BillingPeriod'] : array();
        $billingperiod = isset($BillingPeriod['billingperiod']) ? $BillingPeriod['billingperiod'] : '';
        $nvpstr.="&BILLINGPERIOD=" . $billingperiod;
        $billingfrequency = isset($BillingPeriod['billingfrequency']) ? $BillingPeriod['billingfrequency'] : '';
        $nvpstr.="&BILLINGFREQUENCY=" . $billingfrequency;
        $totalbillingcycles = isset($BillingPeriod['totalbillingcycles']) ? $BillingPeriod['totalbillingcycles'] : '';
        $nvpstr.="&TOTALBILLINGCYCLES=" . $totalbillingcycles;
        $amt = isset($BillingPeriod['amt']) ? $BillingPeriod['amt'] : '';
        $nvpstr.="&AMT=" . $amt;
        if (isset($BillingPeriod['is_trial']) && $BillingPeriod['is_trial'] == 'yes') {
            $trialbillingperiod = isset($BillingPeriod['trialbillingperiod']) ? $BillingPeriod['trialbillingperiod'] : '';
            $nvpstr.="&TRIALBILLINGPERIOD=" . $trialbillingperiod;
            $trialbillingfrequency = isset($BillingPeriod['trialbillingfrequency']) ? $BillingPeriod['trialbillingfrequency'] : '';
            $nvpstr.="&TRIALBILLINGFREQUENCY=" . $trialbillingfrequency;
            $trialtotalbillingcycles = isset($BillingPeriod['trialtotalbillingcycles']) ? $BillingPeriod['trialtotalbillingcycles'] : '';
            $nvpstr.="&TRIALTOTALBILLINGCYCLES=" . $trialtotalbillingcycles;
            $trialamt = isset($BillingPeriod['trialamt']) ? $BillingPeriod['trialamt'] : '';
            $nvpstr.="&TRIALAMT=" . $trialamt;
        }
        /*         * ************************************************ */
        $currencycode = isset($BillingPeriod['currencycode']) ? $BillingPeriod['currencycode'] : '';
        $nvpstr.="&CURRENCYCODE=" . $currencycode;
        /*         * ************************Activation Details Fields************** */
        $ActivationDetails = isset($PayPalRequestData['ActivationDetails']) ? $PayPalRequestData['ActivationDetails'] : array();
        $initamt = isset($ActivationDetails['initamt']) ? $ActivationDetails['initamt'] : '';
        $nvpstr.="&INITAMT=" . $initamt;
        $failedinitamtaction = isset($ActivationDetails['failedinitamtaction']) ? $ActivationDetails['failedinitamtaction'] : '';
        if (!empty($failedinitamtaction))
            $nvpstr.="&FAILEDINITAMTACTION=" . $failedinitamtaction;
        /*         * **********************Credit Card Details Fields****************** */
        $CCDetails = isset($PayPalRequestData['CCDetails']) ? $PayPalRequestData['CCDetails'] : array();
        $acct = isset($CCDetails['acct']) ? $CCDetails['acct'] : '';
        $nvpstr.="&ACCT=" . $acct;
        $expdate = isset($CCDetails['expdate']) ? $CCDetails['expdate'] : '';
        $nvpstr.="&EXPDATE=" . $expdate;
        $cvv2 = isset($CCDetails['cvv2']) ? $CCDetails['cvv2'] : '';
        $nvpstr.="&CVV2=" . $cvv2;
        $nvpstr.="&IPADDRESS=" . $_SERVER['REMOTE_ADDR'];

        //'---------------------------------------------------------------------------
        //' Make the API call and store the results in an array.  
        //'	If the call was a success, show the authorization details, and provide
        //' 	an action to complete the payment.  
        //'	If failed, show the error
        //'---------------------------------------------------------------------------
        $resArray = $this->hash_call("CreateRecurringPaymentsProfile", $nvpstr);
        //$ack = strtoupper($resArray["ACK"]);
        return $resArray;
    }

    public function UpdateRecurringPaymentsProfile($PayPalRequestData = array()) {
        //'---------------------------------------------------------------------------
        //' Build a second API request to PayPal, using the token as the
        //'  ID to get the details on the payment authorization
        //'---------------------------------------------------------------------------
        $nvpstr = '';
        foreach ($PayPalRequestData as $key => $value){
            $nvpstr .= '&' . $key . '=' . urlencode($value);
        }
        $resArray = $this->hash_call("UpdateRecurringPaymentsProfile", $nvpstr);
        return $resArray;
    }

    public function ManageRecurringPaymentsProfileStatus($PayPalRequestData = array()) {
        $nvpstr = '';
        $nvpstr.="&PROFILEID=" . $PayPalRequestData['PROFILEID'];
        $nvpstr.="&ACTION=" . $PayPalRequestData['ACTION'];
        $resArray = $this->hash_call("ManageRecurringPaymentsProfileStatus", $nvpstr);
        //$ack = strtoupper($resArray["ACK"]);
        return $resArray;
    }

    public function GetRecurringPaymentsProfileDetails($PayPalRequestData = array()) {
        $nvpstr = '';
        $nvpstr.="&PROFILEID=" . urlencode($PayPalRequestData['PROFILEID']);
        $resArray = $this->hash_call("GetRecurringPaymentsProfileDetails", $nvpstr);
        return $resArray;
    }

    /**
      '-------------------------------------------------------------------------------------------------------------------------------------------
     * hash_call: Function to perform the API call to PayPal using API signature
     * @methodName is name of API  method.
     * @nvpStr is nvp string.
     * returns an associtive array containing the response from the server.
      '-------------------------------------------------------------------------------------------------------------------------------------------
     */
    function hash_call($methodName, $nvpStr) {

        //setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->API_Endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
        //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
        if ($this->USE_PROXY)
            curl_setopt($ch, CURLOPT_PROXY, $this->PROXY_HOST . ":" . $this->PROXY_PORT);

        //NVPRequest for submitting to server
        $nvpreq = "METHOD=" . urlencode($methodName) . "&VERSION=" . urlencode($this->version) . "&PWD=" . urlencode($this->api_password) . "&USER=" . urlencode($this->api_username) . "&SIGNATURE=" . urlencode($this->api_signature) . "&txn_type=subscr_payment" . $nvpStr . "&BUTTONSOURCE=" . urlencode($this->sBNCode);

        //var_dump($nvpreq);
        //setting the nvpreq as POST FIELD to curl
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

        //getting response from server
        $response = curl_exec($ch);

        //convrting NVPResponse to an Associative Array
        $nvpResArray = $this->deformatNVP($response);
        $nvpReqArray = $this->deformatNVP($nvpreq);
        //$_SESSION['nvpReqArray']=$nvpReqArray;

        if (curl_errno($ch)) {
            // moving to display page to display curl errors
            //$_SESSION['curl_error_no']=curl_errno($ch) ;
            //$_SESSION['curl_error_msg']=curl_error($ch);
            //print_r(curl_errno($ch));
            doVendorMembershipLOG(serialize(curl_error($ch)));
            //Execute the Error handling module to display errors. 
        } else {
            //closing the curl
            curl_close($ch);
        }

        return $nvpResArray;
    }

    /* '----------------------------------------------------------------------------------
      Purpose: Redirects to PayPal.com site.
      Inputs:  NVP string.
      Returns:
      ----------------------------------------------------------------------------------
     */

    function RedirectToPayPal($token) {
        global $PAYPAL_URL;

        // Redirect to paypal.com here
        $payPalURL = $PAYPAL_URL . $token;
        header("Location: " . $payPalURL);
    }

    /* '----------------------------------------------------------------------------------
     * This function will take NVPString and convert it to an Associative Array and it will decode the response.
     * It is usefull to search for a particular key and displaying arrays.
     * @nvpstr is NVPString.
     * @nvpArray is Associative Array.
      ----------------------------------------------------------------------------------
     */

    function deformatNVP($nvpstr) {
        $intial = 0;
        $nvpArray = array();

        while (strlen($nvpstr)) {
            //postion of Key
            $keypos = strpos($nvpstr, '=');
            //position of value
            $valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);

            /* getting the Key and Value values and storing in a Associative Array */
            $keyval = substr($nvpstr, $intial, $keypos);
            $valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
            //decoding the respose
            $nvpArray[urldecode($keyval)] = urldecode($valval);
            $nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
        }
        return $nvpArray;
    }

}
