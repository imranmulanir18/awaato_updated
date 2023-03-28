<?php

use Coinbase\Wallet\Client;
use Coinbase\Wallet\Resource\Account;
use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Configuration;
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Value\Money;
use Coinbase\Wallet\Resource\Address;
use Coinbase\Wallet\ActiveRecord;
use Coinbase\Wallet\Resource;
use App\User;
use Illuminate\Http\Response;
use App\Models\Country;

    /** 
     *type array convert into array messages to string messages
     *
     */
    function messageCreator($messages){
          $err = '';
          $msgCount  = count($messages->all());
          foreach($messages->all() as $error){
            if($msgCount > 1){
                $err = $err.' '.$error.',';
            } else {
                $err = $error;
            }
          }
          return $err;
    }

    /**
     * get time zone by using ip address
     *
     * @return \Illuminate\Http\Response
     */
    function getTimeZoneByIP($ip_address = null) {
       
        $url = "https://timezoneapi.io/api/ip/$ip_address";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $getdata = curl_exec($ch);
        $data = json_decode($getdata, true);
        if($data['meta']['code'] == '200'){
           //echo "City: " . $data['data']['city'] . "<br>";
           $date=$data['data']['datetime']['date_time'];
           $old_date_timestamp = strtotime($date);
           return  $new_date = date('Y-m-d H:i:s', $old_date_timestamp);   
         
        }else{
            return false;
        }
    }

     /*
     *get all columns from table
     ***/
    function getTableColumns($table) {
        return DB::getSchemaBuilder()->getColumnListing(trim($table));
    }

     /*
     *send json response after each request
      ***/
    function sendresponse($code, $status, $message, $arrData) {

        $output['code'] = $code;
        $output['status'] = $status;
        $output['message'] = $message;
        if (empty($arrData)) {
            $arrData = (object) array();
        }
        $output['data'] = $arrData;
        return response()->json($output);
    }

    /*
     *Check validation after each request
     */
    function checkvalidation($request,$rules,$messsages){

         $validator = Validator::make($request, $rules);
            if ($validator->fails()) {
                $message = $validator->errors();
                $err = '';
                foreach ($message->all() as $error) {
                    if (count($message->all()) > 1) {
                        $err = $err . ' ' . $error;
                    } else {
                        $err = $error;
                    }
                }
           } else{
             $err='';
           }    
       return $err;
    }
     
     /*
     *Send mail
     */
    function sendMail($data, $to_mail, $getsubject) {
     // dd($to_mail);
        $succss = false;
        try {
            $displaypage = $data['pagename'];
            $succss = Mail::send($displaypage, $data, function ($message) use ($to_mail, $getsubject) {
                        $from_mail = Config::get('constants.settings.from_email');
                        $to_email = $to_mail;
                        $project_name = Config::get('constants.settings.projectname');
                        $message->from($from_mail, $project_name);
                        $message->to($to_email)->subject($project_name . " | " . $getsubject);
                    });
        } catch (\Exception $e) {
              //dd($e);
             return $succss;
        }
        return $succss;
    }
     

     /*
     *Send enquiry mail
     */
    function sendEnquiryMail($data, $to_mail, $getsubject, $imageName) {
        
        try { 
            $displaypage = $data['pagename'];
            $succss = Mail::send($displaypage, $data, function ($message) use ($to_mail, $getsubject, $imageName) {
                        $from_mail = Config::get('constants.settings.from_email');
                        $to_email = $to_mail;
                        $project_name = Config::get('constants.settings.projectname');
                        $message->from($from_mail, $project_name);
                        $message->to($to_mail)->subject($project_name . " | " . $getsubject);

                        if (!empty($imageName)) {
                            $sample = public_path() . '/attachment/' . $imageName;
                            $message->attach($sample);
                        }
                    });
            } catch (\Exception $e) {

             return $succss;
            }  
        return $succss;
    }

 
     /*
     * Mask mobile numbetr
     */

    function maskmobilenumber($number) {

        $masked = substr($number, 0, 2) . str_repeat("*", strlen($number) - 4) . substr($number, -2);
        return $masked;
    }

      /*
     * Mask email address
     */

    function maskEmail($email) {

        $masked = preg_replace('/(?:^|.@).\K|.\.[^@]*$(*SKIP)(*F)|.(?=.*?\.)/', '*', $email);
        return $masked;
    }

     /*
     * Generate address
     */
    function getnew_address($label = null) {

        $bitcoin_credential = Config::get('constants.bitcoin_credential');
        $api_code = $bitcoin_credential['api_code'];
        $guid = $bitcoin_credential['guid'];
        $main_password = $bitcoin_credential['main_password'];
        $url = $bitcoin_credential['url'];
        if (!empty($api_code) && !empty($guid) && !empty($main_password) && !empty($url)) {
            $query = "/merchant/$guid/new_address?password=$main_password&label=$label";
            $url .= $query;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $transaction = curl_exec($ch);
            $transaction = json_decode($transaction, true);
            if (!empty($transaction) && empty($transaction['error']) && !empty($api_code) && !empty($guid) && !empty($main_password) && !empty($url)) {

                $arr = array();
                $arr['address'] = $transaction['address'];
                $arr['msg'] = 'success';
                return $arr;
            } else {

                $arr = array();
                $arr['address'] = '';
                $arr['msg'] = 'failed';
                return $arr;
            }
        } else {

            $arr = array();
            $arr['address'] = '';
            $arr['msg'] = 'failed';
            return $arr;
        }
    }
     /*
     * Generate new address using blockchain
     */  

    function getBlockchain_address() {

        $bitcoin_credential = Config::get('constants.bitcoin_credential');

        $key = $bitcoin_credential['block_key'];
        $xpub = $bitcoin_credential['xpub'];
        $path = Config::get('constants.settings.domainpath');
        $gap_limit = 1000;
        $callback_url = urlencode('' . $path . '/public/api/receive_callback');
        if (!empty($key) && !empty($xpub) && !empty($callback_url) && !empty($path)) {
            $url = "https://api.blockchain.info/v2/receive?xpub=$xpub&callback=$callback_url&key=$key&gap_limit=$gap_limit";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $transaction = curl_exec($ch);
            $transaction = json_decode($transaction, true);

            if (!empty($transaction) && empty($transaction['error']) && !empty($key) && !empty($xpub) && !empty($callback_url) && !empty($path)) {

                $arr = array();
                $arr['address'] = $transaction['address'];
                $arr['msg'] = 'success';
                return $arr;
            } else {

                $arr = array();
                $arr['address'] = '';
                $arr['msg'] = 'failed';
                return $arr;
            }
        } else {

            $arr = array();
            $arr['address'] = '';
            $arr['msg'] = 'failed';
            return $arr;
        }
    }
    
    /*
     * Generate new address using coinbase
     */
   /*function getCoinbase_address() {

        $bitcoin_credential = Config::get('constants.bitcoin_credential');
        $coin_apiKey = $bitcoin_credential['coin_apiKey'];
        $coin_apiSecret = $bitcoin_credential['coin_apiSecret'];
        if (!empty($coin_apiKey) && !empty($coin_apiSecret)) {
            $configuration = Configuration::apiKey($coin_apiKey, $coin_apiSecret);
            $client = Client::create($configuration);
            $account = $client->getPrimaryAccount();
            $address = new Address();
            $client->createAccountAddress($account, $address);
            $client->refreshAccount($account);
            $transaction = Transaction::send();
            $transaction->setToBitcoinAddress($address->getAddress());
            if (!empty($address->getAddress()) && !empty($coin_apiKey) && !empty($coin_apiSecret)) {

                $arr = array();
                $arr['address'] = $address->getAddress();
                $arr['msg'] = 'success';
                return $arr;
            } else {

                $arr = array();
                $arr['address'] = '';
                $arr['msg'] = 'failed';
                return $arr;
            }
        } else {

            $arr = array();
            $arr['address'] = '';
            $arr['msg'] = 'failed';
            return $arr;
        }
    }*/

     /*
     * Generate new address using coinbase
     */
   function getCoinbase_address($cmd = '' , $req = array()) {

    //dd($req['address']);


        $bitcoin_credential = Config::get('constants.bitcoin_credential');
        $coin_apiKey = $bitcoin_credential['sender_coin_apiKey'];
        $coin_apiSecret = $bitcoin_credential['sender_coin_apiSecret'];
        if (!empty($coin_apiKey) && !empty($coin_apiSecret)) {
            $configuration = Configuration::apiKey($coin_apiKey, $coin_apiSecret);
            $client = Client::create($configuration);
            $account = $client->getPrimaryAccount();
           // $address = new Address();
           // $client->createAccountAddress($account, $address);
          // $client->refreshAccount($account);
            $transaction = Transaction::send([
    'toBitcoinAddress' => $req['address'],
    'amount'           => new Money($req[
        'amount'], CurrencyCode::USD),
    'description'      => $req['note'],
    //'fee'              => '0.0001' // only required for transactions under BTC0.0001
]);
           // $transaction->setToBitcoinAddress($address->getAddress());

           $client->createAccountTransaction($account, $transaction);

           $client->refreshAccount($account);

           $transactionId = $transaction->getId();
            $transactionStatus = $transaction->getStatus();
            $transactionHash = $transaction->getNetwork();
              



            if ($transactionId != "" && !empty($coin_apiKey) && !empty($coin_apiSecret)) {

                $arr = array();
               // $arr['address'] = $address->getAddress();
                $arr['msg'] = 'success';
                 $arr['transactionId'] = $transactionId;
                return $arr;
            } else {

                $arr = array();
                //$arr['address'] = '';
                $arr['msg'] = 'failed';
                return $arr;
            }
        } else {

            $arr = array();
           // $arr['address'] = '';
            $arr['msg'] = 'failed';
            return $arr;
        }
    }
    
    
    /*
     * Generate new address using coinbase
     */

    function getCoinbaseCurrency_address($Currency) {

        $bitcoin_credential = Config::get('constants.bitcoin_credential');
        $coin_apiKey = $bitcoin_credential['coin_apiKey'];
        $coin_apiSecret = $bitcoin_credential['coin_apiSecret'];
        if (!empty($coin_apiKey) && !empty($coin_apiSecret)) {

            $configuration = Configuration::apiKey($coin_apiKey, $coin_apiSecret);
            $client = Client::create($configuration);
            $account = $client->getAccounts();

            foreach ($account as $k => $v) {

                $getCurreny[] = $account[$k]->getcurrency();
                $acount_id[$account[$k]->getcurrency()] = $account[$k]->getid();
                if (in_array($Currency, $getCurreny)) {
                    $getCurAcntId = $acount_id[$Currency];
                }
            }
            $account1 = $client->getAccount($getCurAcntId);
            $address = new Address();
            $client->createAccountAddress($account1, $address);
            $client->refreshAccount($account1);

            if (!empty($address->getAddress()) && !empty($coin_apiKey) && !empty($coin_apiSecret)) {

                $arr = array();
                $arr['address'] = $address->getAddress();
                $arr['msg'] = 'success';
                return $arr;
            } else {

                $arr = array();
                $arr['address'] = '';
                $arr['msg'] = 'failed';
                return $arr;
            }
        } else {

            $arr = array();
            $arr['address'] = '';
            $arr['msg'] = 'failed';
            return $arr;
        }
    }

    /*
     * Coinbase get transaction hash by api id
     */

    function getCoinbaseTransactionHash($Currency, $transactionId = '') {
        $bitcoin_credential = Config::get('constants.bitcoin_credential');
        $coin_apiKey = $bitcoin_credential['sender_coin_apiKey'];
        $coin_apiSecret = $bitcoin_credential['sender_coin_apiSecret'];
        $arr = [];
        try{
            if (!empty($coin_apiKey) && !empty($coin_apiSecret)) {

                $configuration = Configuration::apiKey($coin_apiKey, $coin_apiSecret);
                $client = Client::create($configuration);
                $account = $client->getAccounts();

                foreach ($account as $k => $v) {

                    $getCurreny[] = $account[$k]->getcurrency();
                    $acount_id[$account[$k]->getcurrency()] = $account[$k]->getid();
                    if (in_array($Currency, $getCurreny)) {
                        $getCurAcntId = $acount_id[$Currency];
                    }
                }
                $account1 = $client->getAccount($getCurAcntId);
                if($transactionId != ''){
                    $transaction = $client->getAccountTransaction($account1, $transactionId);
                    $arr['status'] = "Success";
                    $arr['transaction_hash'] = $transaction->getNetwork()->getHash();
                } else {
                    $arr['status'] = "Fail";
                }
                
            }
        } catch(Exception $e){
            // dd($e->getMessage());
            $arr['status'] = "Fail";
        }
        return $arr;
    }
         
     /*
     * Generate new address using COIN-PAYMENTS
     */  

    function coinpayments_api_call($cmd, $req = array()) {
        // Fill these in from your API Keys page

        $bitcoin_credential = Config::get('constants.bitcoin_credential');

        $public_key = $bitcoin_credential['public_key'];
        $private_key = $bitcoin_credential['private_key'];
        if (!empty($public_key) && !empty($private_key)) {


            // Set the API command and required fields
            $req['version'] = 1;
            $req['cmd'] = $cmd;
            $req['key'] = $public_key;

            $req['format'] = 'json'; //supported values are json and xml
            // Generate the query string
            $post_data = http_build_query($req, '', '&');

            // Calculate the HMAC signature on the POST data
            $hmac = hash_hmac('sha512', $post_data, $private_key);

            // Create cURL handle and initialize (if needed)
            static $ch = NULL;
            if ($ch === NULL) {
                $ch = curl_init('https://www.coinpayments.net/api.php');
                curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('HMAC: ' . $hmac));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

            // Execute the call and close cURL handle
            $data = curl_exec($ch);
            $transaction = json_decode($data, TRUE, 512, JSON_BIGINT_AS_STRING);

            if (!empty($transaction) && ($transaction['error'] == 'ok') && !empty($public_key) && !empty($private_key)) {

                $arr = array();
                $arr['address'] = $transaction['result']['address'];
                $arr['msg'] = 'success';
                return $arr;
            } else {

                $arr = array();
                $arr['address'] = '';
                $arr['msg'] = 'failed';
                return $arr;
            }
        } else {

            $arr = array();
            $arr['address'] = '';
            $arr['msg'] = 'failed';
            return $arr;
        }
    }

    /*
     *FUNCTION TO GET TOTAL RECIEVED
     */ 
    function total_recieved($address = null) {

        $bitcoin_credential = Config::get('constants.bitcoin_credential');
        $api_code = $bitcoin_credential['api_code'];
        $guid = $bitcoin_credential['guid'];
        $main_password = $bitcoin_credential['main_password'];
        $url = $bitcoin_credential['url'];
        if (!empty($api_code) && !empty($guid) && !empty($main_password) && !empty($url)) {
            $query = "/merchant/$guid/address_balance?password=$main_password&address=$address";


            $url .= $query;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $transaction = curl_exec($ch);
            $transaction = json_decode($transaction, true);
            if (!empty($transaction) && empty($transaction['error']) && !empty($api_code) && !empty($guid) && !empty($main_password) && !empty($url)) {

                $arr = array();
                $arr['total_received'] = $transaction['total_received'];
                $arr['msg'] = 'success';
                return $arr;
            } else {

                $arr = array();
                $arr['total_received'] = '';
                $arr['msg'] = 'failed';
                return $arr;
            }
        } else {

            $arr = array();
            $arr['total_received'] = '';
            $arr['msg'] = 'failed';
            return $arr;
        }
    }
    
    /*
     * confirmation using  blockchain address
     */ 
    function blockchain_address($address = null) {

        $bitcoin_credential = Config::get('constants.bitcoin_credential');
        $api_code = $bitcoin_credential['api_code'];
        $guid = $bitcoin_credential['guid'];
        $main_password = $bitcoin_credential['main_password'];
        //$url=$bitcoin_credential['url'];
        if (!empty($api_code) && !empty($guid) && !empty($main_password)) {
            $url = "https://blockchain.info/rawaddr/$address";


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $transaction = curl_exec($ch);
            $transaction = json_decode($transaction, true);

            if (!empty($transaction) && empty($transaction['error']) && !empty($api_code) && !empty($guid) && !empty($main_password)) {

                $arr = array();
                $arr['data'] = $transaction;
                $arr['msg'] = 'success';
                return $arr;
            } else {

                $arr = array();
                $arr['data'] = '';
                $arr['msg'] = 'failed';
                return $arr;
            }
        } else {

            $arr = array();
            $arr['data'] = '';
            $arr['msg'] = 'failed';
            return $arr;
        }
    }

    /*
     *get BlockChainConfirmation
     */ 


    function blockchain_confirmation() {


        $url = "https://blockchain.info/q/getblockcount";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $transaction = curl_exec($ch);
        $current_block_count = json_decode($transaction, true);

        $arr = array();
        $arr['current_block_count'] = $current_block_count;

        return $arr;
    }
     
     /*
     * confirmation using blcokio
     */

    function blockio_address($address = null) {

        $bitcoin_credential = Config::get('constants.bitcoin_credential');
        $api_code = $bitcoin_credential['api_code'];
        $guid = $bitcoin_credential['guid'];
        $main_password = $bitcoin_credential['main_password'];
        $url = "https://block.io/api/v2/get_transactions/?api_key=8ca4-f2cb-b19f-f92e&type=received&addresses=$address";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $transaction = curl_exec($ch);
        $transaction = json_decode($transaction, true);
        $arr = array();
        $arr['data'] = $transaction['data'];
        $arr['msg'] = $transaction['status'];
        return $arr;
    }
     
     /*
     * confirmation using blcok cyper
     */
    function blockcyper_address($address = null) {


        $url = "https://api.blockcypher.com/v1/btc/main/addrs/$address";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $transaction = curl_exec($ch);
        $transaction = json_decode($transaction, true);

        if (!empty($transaction) && empty($transaction['error'])) {

            $arr = array();
            $arr['data'] = $transaction;
            $arr['msg'] = 'success';
            return $arr;
        } else {

            $arr = array();
            $arr['data'] = '';
            $arr['msg'] = 'failed';
            return $arr;
        }
    }
    
    /*
     * confirmation using blcok bitaps
     */
    function blockbitaps_address($address = null) {


        $url = 'https://bitaps.com/api/address/transactions/' . $address . '/0/received/all';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $transaction = curl_exec($ch);
        $transaction = json_decode($transaction, true);

        if (!empty($transaction) && empty($transaction['error_code'])) {

            $arr = array();
            $arr['data'] = $transaction;
            $arr['msg'] = 'success';
            return $arr;
        } else {

            $arr = array();
            $arr['data'] = '';
            $arr['msg'] = 'failed';
            return $arr;
        }
    }

     /*
     *currency conversion
     */ 
    function currency_convert($currency, $price_in_usd) {
        $url = "https://min-api.cryptocompare.com/data/price?fsym=USD&tsyms=$currency";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $currency_rate = curl_exec($ch);
        $json = json_decode($currency_rate, true);

        return $currency_price = $json[$currency] * $price_in_usd;
    }

    /*
     *ETH CONFIRMATION
     */ 
    function ETHConfirmation($address) {

        $bitcoin_credential = Config::get('constants.bitcoin_credential');
        $api_code = $bitcoin_credential['api_code'];
        if (!empty($api_code)) {
            $url = "http://api.etherscan.io/api?module=account&action=txlist&address=$address&startblock=0&endblock=99999999&sort=asc&apikey=$api_code";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $transaction = curl_exec($ch);
            $transaction = json_decode($transaction, true);

            if (!empty($transaction) && $transaction['status'] != 0 && !empty($api_code)) {

                $arr = array();
                $arr['data'] = $transaction['result'];
                $arr['msg'] = 'success';
                return $arr;
            } else {

                $arr = array();
                $arr['data'] = '';
                $arr['msg'] = 'failed';
                return $arr;
            }
        } else {

            $arr = array();
            $arr['data'] = '';
            $arr['msg'] = 'failed';
            return $arr;
        }
    }

    /*
     *print data
     */
    function printData($arrData) {
        echo '<pre>';
        print_r($arrData);
        die();
    }
    
    /*
     *XRP Confimation
     */
    function XRPConfirmation($address) {

        $bitcoin_credential = Config::get('constants.bitcoin_credential');
        $api_code = $bitcoin_credential['api_code'];

        $url = "https://data.ripple.com/v2/accounts/" . $address;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $transaction = curl_exec($ch);
        $transaction = json_decode($transaction, true);

        if (!empty($transaction) && $transaction['result'] === 'success') {
            $arr = array();
            $arr['data'] = $transaction['account_data'];
            $arr['msg'] = 'success';
            return $arr;
        } else {
            $arr = array();
            $arr['data'] = '';
            $arr['msg'] = 'failed';
            return $arr;
        }
    }

    /**
     * [setPaginate description]
     * @param [type] $query  [description]
     * @param [type] $start  [description]
     * @param [type] $length [description]
     */
    function setPaginate($query, $start, $length) {

        $totalRecord = $query->get()->count();
        $arrEnquiry = $query->skip($start)->take($length)->get();

        $data['totalRecord'] = 0;
        $data['filterRecord'] = 0;
        $data['record'] = $arrEnquiry;

        if ($totalRecord > 0) {
            $data['totalRecord'] = $totalRecord;
            $data['filterRecord'] = $totalRecord;
            $data['record'] = $arrEnquiry;
        }
        return $data;
    }

    /**
     * [setPaginate description]
     * @param [type] $query  [description]
     * @param [type] $start  [description]
     * @param [type] $length [description]
     */
    function setPaginate1($query, $start, $length) {

        $totalRecord = $query->count();
        $arrEnquiry = $query->skip($start)->take($length)->get();

        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $data['records'] = $arrEnquiry;

        if ($totalRecord > 0) {
            $data['recordsTotal'] = $totalRecord;
            $data['recordsFiltered'] = $totalRecord;
            $data['records'] = $arrEnquiry;
        }
        return $data;
    }

    /*
     *convertCurrency
     */
    function convertCurrency($amount, $from, $to){
      $url = file_get_contents('https://free.currencyconverterapi.com/api/v5/convert?q=' . $from . '_' . $to . '&compact=ultra');
      $json = json_decode($url, true);
      $rate = implode(" ",$json);
      $total = $rate * $amount;
      $rounded = round($total); //optional, rounds to a whole number
      return $total; //or return $rounded if you kept the rounding bit from above
    }
    
    /*
     *Block chain paymnt
     */ 
    function make_blockchain_payment($to_address,$price_in_usd) {
      
        /*credentials*/
        $main_url       = "http://localhost:3000";
        $guid           = "";
        $main_password  = "";
        $from           = "";

        /*calculate amount usd to satoshi*/
        $currency = "BTC";
        $btc_amount = currency_convert($currency,$price_in_usd);
        $satoshi_amount = $btc_amount*100000000;
        $satoshi_amount = round($satoshi_amount);
        $fee = get_blockchain_fee();

        if($fee > 1000)
        {
            $fee = 13000; 
        } else {
            $fee = 13000;
        }

        if($satoshi_amount)
        {
            $query = "/merchant/$guid/payment?password=$main_password&to=$to_address&amount=$satoshi_amount&from=$from&fee=$fee";

            $url=$main_url;

            $url.=$query;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $transaction = curl_exec($ch);  
            $transaction = json_decode($transaction, true);
            $tx_hash = $transaction['tx_hash'];

            if(!empty($tx_hash)){
                return $tx_hash;
            } else {
                return 0;
            }
        } else {

            return 0; 
        }
    }

    /*
     *GET BLOCKCHIAN FEES
     */

    function get_blockchain_fee(){
        $url = "https://api.blockchain.info/fees";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $currency_rate = curl_exec($ch);
        $json   = json_decode($currency_rate, true);
        $fee    = $json['default']['fee'];
        
        return $fee;
    }

/**
 * Function to set the validation message
 * 
 * @param $validator : Validator Object
 */ 
function setValidationErrorMessage($validator) {
  $arrOutputData      = [];
  $arrErrorMessage    = $validator->messages();
    $arrMessage         = $arrErrorMessage->all();
    $strMessage         = implode("\n", $arrMessage);
    $intCode            = Response::HTTP_PRECONDITION_FAILED;
    $strStatus          = Response::$statusTexts[$intCode];
    return sendResponse($intCode, $strStatus, $strMessage,$arrOutputData);
}


function getCountryCode($country) {
    $countryData = Country::where('iso_code', $country)->first();
   // return $countryData->code;
}


function sendWhatsappMsg($countrycode, $mobile, $whatsapp_msg) {
    $post_data['phone'] = $countrycode . $mobile;
    $post_data['body'] = $whatsapp_msg; //Config::get('constants.settings.waboxapp_text');
    $fields_string = http_build_query($post_data);
    
    /*$url = "https://eu22.chat-api.com/instance18560/message?token=9nr3bkzjtf9z9b60";*/
/*
    $url = "https://eu13.chat-api.com/instance26117/message?token=qwv9t4s07gpnczxe";*/
    
    $url = "https://eu22.chat-api.com/instance18560/message?token=9nr3bkzjtf9z9b60";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    // Execute the call and close cURL handle
    $data = curl_exec($ch);
    $response = json_decode($data);
    return $response;
}


/*************Send sms ********************/
function sendSMS($mobile, $message) {
try{

$username = urlencode(Config::get('constants.settings.sms_username'));
$pass = urlencode(Config::get('constants.settings.sms_pwd'));
$route = urlencode(Config::get('constants.settings.sms_route'));
$senderid = urlencode(Config::get('constants.settings.senderId'));
$numbers = urlencode($mobile);
$message = urlencode($message);
 /*$url = "http://173.45.76.227/send.aspx?username=".$username."&pass=".$pass."&route=".$route."&senderid=".$senderid."&numbers=".$numbers."&message=".$message;
 dd($url);*/

$curl = curl_init();

curl_setopt_array($curl, array(
CURLOPT_URL => "http://173.45.76.227/send.aspx?username=".$username."&pass=".$pass."&route=".$route."&senderid=".$senderid."&numbers=".$numbers."&message=".$message,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => "",
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 30,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => "GET",
CURLOPT_POSTFIELDS => "",
CURLOPT_SSL_VERIFYHOST => 0,
CURLOPT_SSL_VERIFYPEER => 0,
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);
return true;
}
catch(\Exception $e){
return true;
}

}