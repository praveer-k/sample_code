<?php

class Magecart_Users_Helper_V1 extends Mage_Core_Helper_Abstract {

	public  $default_response = array("code" => 500, "message" => "Internal Advershares error.");
	public  $dbconn = false;

	protected function _debug($debugData) {
		Mage::log($debugData, null, 'zobily_api_'.date('d-m-y').'.log', true);
	}

	public function pg_db_connection() {
		try {
			$user = "zobily5_rakesh";
			$pass = "rakesh";
			$dbh = new PDO("mysql:host=localhost;dbname=zobily5_live", $user, $pass);
    	$this->dbhconn = $dbh;
			d($this->dbhconn);
		} catch (PDOException $e) {
		    d("Error!: " . $e->getMessage());
		    die();
		}
		return $this->dbconn;
	}

	public function db_fetch_query($qry,  $params = array(),$statement_name = "zobily_query1") {
		$response	= array();
		$response['result'] = 0;
		try{
			$db = $this->pg_db_connection();
			$qry = $db->prepare($qry, $params);
			$res = $db->execute();
			$result_arr	= $res->fetchAll($result);
			$response['data'] = $result_arr;
		}catch(Exception $e){
				$response['msg'] = $e->getMessage();
		}
		return $response;
	}

	public function db_execute_query($qry,$type = 'select',  $params = array(),$statement_name = "zobily_query1") {
			$rows = array();
			$response = array();
			$response['result'] = 0;
			try{
				$db = $this->pg_db_connection();
				$qry = $db->query($qry, $params);
				$response['result'] = 1;
				$rows = $qry->fetchAll();
				if(!count($rows)){
				 	$response['result']	= 2;
					$response['data'] = $rows;
				}
				$response['result_obj'] = $qry;
			}catch(Exception $e){
				$response['msg'] = $e->getMessage();
			}
			return $response;
	}

	public function db2() {
	  $conn_string 	  = "host=localhost port=5432 dbname=zobily5_testdb user=zobily5_onetea7 password=bwqVR3qi#Q6{  options='--client_encoding=UTF8'";
	  $dbconn 	  	  = pg_connect($conn_string);
	}



	public function syncdb() {
	  $sql    = 'SELECT * FROM accounts';
	  $result1 = $this->db_execute_query($sql);
	  $sql    = 'SELECT * FROM accounts_db';
	  d($sql);
	  $result2 = $this->db_execute_query($sql);
	  d($result2);die();
	  foreach($result2['data'] as $row){
		 d($row);die();
	 }
	}

	public function country($params,$postData,$postDatavalue) {
	  $country_list 		= Mage::getResourceModel('directory/country_collection')->loadData()->toOptionArray(false) ;
	  $response 		    = array("code" => 200, "message" => "success",  "default_country" => 'US',  "country_list" => @$country_list);
	  return $response;
	}

	public function states($params,$postData,$postDatavalue) {
		// $country_code		= $params['country_code'];
		  $country_code		= $params[0];
		  $response 	 		= $this->default_response;
		  if($country_code){
			 $regionsCollection  = Mage::getResourceModel('directory/region_collection')->addCountryFilter($country_code)->load();
			 $states_list        = array();
			 if($regionsCollection){
				$states_list    = $regionsCollection->getData();
			}
			$response 		    = array("code" => 200, "message" => "success",  "country_code" => @$country_code,  "states_list" => @$states_list);
		}
		return $response;
	}


	// "Transaction verification.",
	/*  /cardspring/ */
	public function cardspring($params,$postData,$postDatavalue) {
		$helper     = Mage::helper('users');
		// $_session   = Mage::getSingleton('core/session');
		// $user       = $_session->getUser();
		$db 		     = $this->pg_db_connection();
		$this->_debug("Cardspring Transaction callback response.");
		$this->_debug($_POST);
		$this->_debug($_GET);
		$this->_debug($postData);
		$this->_debug($params);
		d($postData);die();
		$event 		= json_decode($postData['payload']);
		if(isset($event['type']) && ($event['type'] == 'transaction.authorized' || $event['type']== 'transaction.settled' || $event['type'] == 'transaction.reversed' || $event['type'] == 'transaction.refunded')){
			// $conn_string 	  = "host=".PG_HOST." port=5432 dbname=".PG_DB." user=".PG_USER." password=".PG_PWD."  options='--client_encoding=UTF8'";
			// $conn 		 	  = pg_connect($conn_string);

			// $conn 		 	   = $this->dbconn;
			// pg_query($conn, 'LISTEN transactions;');
			// pg_query($conn, 'LISTEN notifications;');
			// pg_query($conn, 'LISTEN emails;');
			// pg_query($conn, 'LISTEN sms;');

			$sql3 			   = 'SELECT * FROM transactions__add_transaction($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)';
			$ac_array 		   = array($event['transaction']['id'], (int)($event['transaction']['store_id']), (int)($event['transaction']['user_id']), (int)($event['transaction']['card_token']), (int)($event['transaction']['amount']), $event['transaction']['purchase_date_time'], $event['transaction']['app_id'], $event['transaction']['currency'], (int)($event['transaction']['discount']) , $event['transaction']['status'], 'card');
			$result3           = $this->db_fetch_query($sql3,$ac_array,"zobily_tran1");

			// $this->transactions_listner();
			// $this->notification_listner();
			// $this->email_listner();
			// $this->sms_listner();
			// d($result3);
		 }
		 $response = array( "code" => 200);
		 return $response;
	}

	public function testcardspring($params,$postData,$postDatavalue) {
		$helper     = Mage::helper('users');
		$db 		     = $this->pg_db_connection();
		$this->_debug("Test Cardspring Transaction callback response.");
		$event 		= json_decode($postData['payload']);

		$conn 		 	   = $this->dbconn;
		pg_query($conn, 'LISTEN transactions;');
		pg_query($conn, 'LISTEN notifications;');
		pg_query($conn, 'LISTEN emails;');
		pg_query($conn, 'LISTEN sms;');

		$ac_array 		   = array(12, (int)960, (int)2000005083, (int)12, (int)318, '2014-03-27 15:16:57', 'AHy1lCCaZP9t','USD', (int)180 , 'settled', 'card');
		$sql3 			   = 'SELECT * FROM transactions__add_transaction($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)';
		$result3           = $this->db_fetch_query($sql3,$ac_array,"zobily_tran1");

		$this->transactions_listner();
		$this->notification_listner();
		$this->email_listner();
		$this->sms_listner();
		// d($result3);
		exit();
	}

	public function transactions_listner() {
		$conn 		 	 = $this->dbconn;
		$end             = false;
		while (!$end) {
			 $notify = pg_get_notify($conn);
			if (!$notify) {
			  usleep(100000);
			} else {
				$transactions_id =(int)($notify['payload']);
				$qry    = 'SELECT * FROM transactions__add_transaction_to_ledgers($1)';
				$result = $this->db_fetch_query($sql,array($transactions_id),"zobily_tran0");
				if($result['result'] == 2){
					$response = 'error running query';
				} else {
					$response = 'Updated Successfully';
				}
			}
		}
	}

	public function notification_listner() {
		$conn 		 	 = $this->dbconn;
		$end			 = false;
		while (!$end) {
			$notify = pg_get_notify($conn);
			if (!$notify) {
				usleep(100000);
			}
			else {
				d($notify);
				$_array['notifications_id'] = (int)($notify['payload']);
				$qry		  	  = 'SELECT * FROM notifications__process_notification($1)';
				$end			  = true;
				$records 	      = $this->db_fetch_query($qry,$_array,"zobily_tran3");
			}
		}
	}

	public function email_listner() {
		$conn 		 	 = $this->dbconn;
		$end             = false;
		while (!$end) {
			 $notify = pg_get_notify($conn);
			 if (!$notify) {
				 usleep(100000);
			 } else {
				d($notify);
				$sendgrid_username = 'Royse1';
				$sendgrid_password = 'Lorenzo11';
				$to                = 'norseman2813@gmail.com';
				$to                = 'venkatesh.hbcc@gmail.com';
				$from              = 'support@advershares.com';
				$email_id          = (int)($notify['payload']);
				d($email_id);
				$qry               = 'SELECT * FROM emails where id = $1 AND sent = 0';
				$result            = $this->db_fetch_query($qry,array($email_id),'zobily_query_email_fetch_5');
				d($result);
				if($result['result'] == 1){
					$email_to      = $result['data'][0]['email_to'];
					$email_from    = $result['data'][0]['email_from'];
					$subject       = $result['data'][0]['subject'];
					$message_text  = $result['data'][0]['message_text'];
					$message_html  = $result['data'][0]['message_html'];
					$contact_fname = $result['data'][0]['contact_fname'].'';
					if($contact_fname == 'null'){$contact_fname = '';}
					$contact_lname = $result['data'][0]['contact_lname'].'';
					if($contact_lname == 'null'){$contact_lname = '';}
					$contact_phone = $result['data'][0]['contact_phone'].'';
					if($contact_phone == 'null'){$contact_phone = '';}
					$contact_email = $result['data'][0]['contact_email'].'';
					if($contact_email == 'null'){$contact_email = '';}
					$contact_message = $result['data'][0]['contact_message'].'';
					if($contact_message == 'null'){$contact_message = '';}
					$password = $result['data'][0]['password'].'';
					if($password == 'null'){$password = '';}
					require_once("../lib/sendgrid-php/sendgrid-php.php");
					$sendgrid = new SendGrid($sendgrid_username,$sendgrid_password);
					$email    = new SendGrid\Email();
					$email->addTo($email_to)->
						   setFrom($email_from)->
						   setSubject($subject)->
						   setText($message_text)->
						   setHtml($message_html)->
						   addSubstitution("%email_to%", array($email_to))->
						   addSubstitution("%contact_fname%", array($contact_fname))->
						   addSubstitution("%contact_lname%", array($contact_lname))->
						   addSubstitution("%contact_phone%", array($contact_phone))->
						   addSubstitution("%contact_email%", array($contact_email))->
						   addSubstitution("%contact_message%", array($contact_message))->
						   addSubstitution("%password%", array($password));

					$result = $sendgrid->send($email);
					d($result);
					if($result->message == 'success'){
						$qry      = 'SELECT * FROM emails__update_status($1, $2)';
						$result   = $this->db_fetch_query($qry,array($email_id,1),'zobily_query_email_fetch_3');
						$response = 'email sent successfully';
					}
					else{
						$qry      = 'SELECT * FROM emails__update_status($1, $2, $3)';
						$result   = $this->db_fetch_query($qry,array($email_id,0,$result->errors),'zobily_query_email_fetch_4');
						$response = 'error in sending email';
					}
				}
				else{
					$response = 'error running query';
				}
			}
		}
	}

	public function sms_listner() {
		$conn 		 	 = $this->dbconn;
		$end             = false;
		while (!$end) {
			$notify = pg_get_notify($conn);
			if (!$notify) {
			  echo "No messages\n";
			} else {
			  d($notify);
			  $_array['sms_id'] = (int)($notify['payload']);
			  $qry              = 'SELECT * FROM sms where id = $1';
			  $result           = $this->db_fetch_query($qry,array($_array['sms_id']),'zobily_fetch_sms');
				   d($result);
				   if($result['result'] == 1){
					$sent_to       = $result['data'][0]['sent_to'];
					$sent_from     = $result['data'][0]['sent_from'];
					$message       = $result['data'][0]['body'];
					$media_url     = $result['data'][0]['media_url'].'';
					$twilio_params = array();
					if ($media_url != 'null') {
						$twilio_params['to']       = $sent_to;
						$twilio_params['from']     = $sent_from;
						$twilio_params['body']     = $message;
						$twilio_params['mediaUrl'] = $media_url;
					} else {
						$twilio_params['to']       = $sent_to;
						$twilio_params['from']     = $sent_from;
						$twilio_params['body']     = $message;
						$twilio_params['mediaUrl'] = "";
					}
					//Send an SMS text message
					require_once('../lib/twilio-php/Services/Twilio.php'); // Loads the library

					// Your Account Sid and Auth Token from twilio.com/user/account
					$sid = "AC201a030b7db467a6d12edd8a89e35800";
					$token = "aaa0990d17bcca78a5aefea81f8928fa";
					$client = new Services_Twilio($sid, $token);
					try {
						if($twilio_params['mediaUrl'] == ''){
							$response = $client->account->messages->sendMessage($twilio_params['from'],$twilio_params['to'],$twilio_params['body']);
						}
						else{
							$response = $client->account->messages->sendMessage($twilio_params['from'],$twilio_params['to'],$twilio_params['body'],$twilio_params['mediaUrl']);
						}
						d($response);
						if($response->sid){
							$sms_status = $response->status;
							$sms_uri = 'https://api.twilio.com'.$response->uri;
							$sms_err = '';
							$qry              = 'SELECT * FROM sms__update_status($1, $2, $3, $4)';
							$result           = $this->db_fetch_query($qry,array($_array['sms_id'],$sms_status,$sms_uri,$sms_err),'zobily_fetch_sms4');
							if($result['result'] == 2){
								$response = 'error running query';
							} else {
								$response = 'Updated Successfully';
							}
						} else {
							$sms_status = 'failed';
							$sms_uri = '';
							$sms_err = $response->error;
							$qry              = 'SELECT * FROM sms__update_status($1, $2, $3, $4)';
							$result           = $this->db_fetch_query($qry,array($_array['sms_id'],$sms_status,$sms_uri,$sms_err),'zobily_fetch_sms2');
							if($result['result'] == 2){
								$response = 'error running query';
							} else {
								$response = 'Updated Successfully';
							}
						}
					}
					catch (Services_Twilio_RestException $e) {
						echo $e->getMessage();die('exception');
					}
				}
				else{
					$response = 'error running query';
				}
				d($response);
			}
		}
	}


	/* retrieve publisher */
	public function retrieve_publisher(){
		$response = Mage::helper('users')->api_transaction_call('','GET','',1);
		d($response);die();
	}
	/* retrieve publisher */

	/* Configure publisher */
	public function configure_publisher(){
		$params['callback_url'] = "http://advershares.com/api/cardspring";
		$response = Mage::helper('users')->api_transaction_call('','PUT',$params,1);
		d($response);die();
	}

	/* Configure publisher */

	/* List event */
	public function list_events()
	{
	   $response = Mage::helper('users')->api_transaction_call('events','GET','',1);
		d($response);die();
	}
	/* List event */

	/* create event */
	public function create_event($value='')
	{
		$helper                = Mage::helper('users');
		$response              = $helper->api_transaction_call('businesses','GET','',1);
		$result                = $response['response']['content'];
		$res                   = json_decode($result);
		$bussiness             = $res->items;
		$bussiness_id          = $bussiness[0]->id;
		$params['business_id'] = $bussiness_id;
		$params['type']        = "business.activated";
		$response              = $helper->api_transaction_call('events','POST',$params,1);
		d($response);die();
	}
	/* create event */

    public function retrieve_user($value='')
    {
        //d($value);die();
        $response = Mage::helper('users')->api_transaction_call('users/'.$value[0],'GET','',1);
        $response           = array("code" => 200, "message" => "success", "response" => $response);
        return $response;
    }

    public function delete_card($value='')
    {
        $response = Mage::helper('users')->api_transaction_call('users/2000005083/cards/047J1UYRGTW55794','DELETE','',1);
        d($response);die();
    }

    public function retrieve_card($value='')
    {
        $response = Mage::helper('users')->api_transaction_call('users/12/cards/2552','GET','',1);
        d($response);die();
    }

    public function create_card($value='')
    {
        $postData['account_id'] = 2000004957;
        $postData['pan'] = '6011722837193674';
        $postData['exp_month'] = 03;
        $postData['exp_year'] = 2025;
        $response = Mage::helper('users/v1post')->newcard($postData,$postData,$postData);
        d($response);die();
    }

	/* Find business */

	public function find_business($value='')
	{
		$response = Mage::helper('users')->api_transaction_call('businesses','GET','',1);
		d($response);die();
	}

	/* Find business */

    public function retrieve_business($value='')
    {
        $response = Mage::helper('users')->api_transaction_call('businesses/1070','GET','',1);
        d($response);die();
    }

    public function update_cardtoken($value='')
    {
        $sql      = "select account_id,id,last_four from cards  order by account_id";
        $response = $this->db_execute_query($sql);
        if($response['result'] == 1){
            foreach ($response['data'] as $value) {
               $response   = Mage::helper('users')->api_transaction_call('users/'.$value['account_id'],'GET','',1);
			   $account_id = $value['account_id'];
			   $cards      = array();
			   // $cards[]    = 11111111111111;
			   // d($value);
               // d($response['result']->cards->items);
               if($response['response']['http_code'] == 200){
                foreach ($response['result']->cards->items as $val) {
                    if(($value['last_four'] == $val->last4) && ($account_id == $val->user_id) ){
                        $token   = $val->token;
						$bank    = $val->bank;
						$brand   = $val->brand_string;
                        $sql     = "update cards set token='".$token."',brand='".$brand."',bank='".$bank."' where id = ".$value['id']."; ";
						$cards[] = $value['id'];
                        d($sql);
						// d($val);
                        $response = $this->db_execute_query($sql,'update');
                        // d($response);
						// die();
                    }
                }
               }
            }


			// if(count($cards))
				// $not_in     = 'id NOT IN('.implode(",",$cards).') and';
			// $not_in_qry = 'DELETE FROM cards where '.$not_in.' account_id = '.$account_id.';';
			// $response  = $this->db_execute_query($not_in_qry,'update');
			// d($not_in_qry);
		   // d($response);
		   // die();
			die();
        }
        // d($response);die();
    }

    public function sms_notification($value='')
    {
        # code...
    }

    public function update_business_status($value='')
    {
        $response = Mage::helper('users')->api_transaction_call('businesses','GET','',1);
        $items = json_decode($response['response']['content']);
        d($items);
        foreach($items->items as $val){
            $merchant_id = (int)$val->id;
            $status = $val->status;
            switch ($status) {
                case 'active':
                    $status = 1;
                    break;
                case 'pending':
                    $status = 2;
                    break;
                case 'deactivated':
                    $status = 3;
                    break;
                case 'none':
                    $status = 0;
                    break;
                case 'not_found':
                    $status = 4;
                    break;
            }
            if(is_int($merchant_id) && $merchant_id != 0 ){
                $sql     = "update merchants set status = '$status' where merchant_id = '$merchant_id' ";
                //d($sql);die();
                //$reponse = $this->db_execute_query($sql,'update');
            }
        }
        return $reponse;
    }

	/* create App */
	public function create_app($value='')
	{
		$response                                     = Mage::helper('users')->api_transaction_call('businesses','GET','',1);
		$result                                       = $response['response']['content'];
		$res                                          = json_decode($result);
		$bussiness                                    = $res->items;
		$bussiness_id                                 = $bussiness[0]->id;
		$params['redemption']['type']                 = 'terminal_discount';
		$params['redemption']['amount']               = '10';
		$params['redemption']['count']                = 0;
		$params['redemption']['discount_description'] = '$10 coupon at Example Store';
		$response                                     = Mage::helper('users')->api_transaction_call('businesses/'.$bussiness_id.'/apps','POST',$params,1);
		d($response);die();
	}

	/* create App */


	/* get Review */
	public function getreview($params,$postData,$postDatavalue)
	{
		$response 	 = $this->default_response;
		//print_r($params);
		 $merchant_id  = (int)$params[1];
		//$account_id  = $params[2];
		//$account[$account_id]=(int)$params[3];
		if($merchant_id != ''){

			$sql				= "SELECT SUM(review_points), COUNT(*) AS count FROM user_review WHERE merchant_id = '$merchant_id'";
			$result            = $this->db_execute_query($sql,true);
			//echo $result[data][0][sum];
			//echo '<br>';
			//echo $result[data][0][count];
			//echo '<br>';
			 $avg=($result[data][0][sum]/$result[data][0][count]);

		}

		if($result[data]!='')
		{
		$response 		    = array("code" => 200, "message" => "success", "review_points" => $avg);
		}
		return $response;
	}

	/* get Review */
public function setreviewvalue($params,$postData,$postDatavalue)
	{

		$merchant_id= (int)$params[1]; //Merchant ID
		$account_id= (int)$params[3]; //Account ID
		$review_points= (float)$params[5]; //review points
		$title= $params[7]; //title
		$description= $params[9]; //description
		$date = date('Y-m-d H:i:s.u');

		if($merchant_id !=NULL && $account_id!=NULL)
		{
			$sql                 = "SELECT * from user_review where merchant_id = '$merchant_id' and account_id='$account_id'";
			$result            = $this->db_execute_query($sql,true);
		}




		if($result[result] =='1')

		{
			 $sql                 = "UPDATE user_review SET review_points='$review_points',title='$title',description='$description',date_added='$date' where merchant_id = '$merchant_id' and account_id='$account_id'";
			$result            = $this->db_execute_query($sql,true);
			$response 		    = array("code" => 200, "message" => "success", "status" => 'Successfully Rated');
		}
		elseif($result[result] == '2')
		{
			$sql        = "INSERT INTO user_review (merchant_id, account_id, review_points, title,description, date_added) VALUES ($merchant_id,$account_id,$review_points,'$title','$description','$date')";

			$result            = $this->db_execute_query($sql,true);
			$response 		    = array("code" => 200, "message" => "success", "status" => 'Successfully Rated');

		}
		return $response;
	}

	// Code for getting the description baed on the merchant id
	public function getcomments($params,$postData,$postDatavalue)
	{
		echo $merchant_id= (int)$params[1]; //Merchant ID

		if($merchant_id !=NULL)
		{
			$sql                 = "SELECT description from user_review where merchant_id = '$merchant_id'";
			$result            = $this->db_execute_query($sql,true);

		}
		//print_r($result);
		$description=array();
				foreach ($result[data] as $value) {

			foreach ($value as  $value2) {
				if($value2!=NULL)
				{
					$description[]=$value2;
				}
			}
		}
		//print_r($description);
		$response 		    = array("code" => 200, "message" => "success", "description" => $description);
		return $response;

	}

	/* Account start here */

	// d($params);
	// d($postData);
	/*
	1.	/v1/account/{account_id}/notifications/totalunread
		desc : Get unread notifications count
		method: 'GET'
		changes to : /totalunread_notifications/{account_id}/
	*/
		public function totalunread_notifications($params,$postData,$postDatavalue) {
			$response 	 = $this->default_response;
			$account_id  = (int)$params['account_id'];
			if($account_id){
				$sql	 = 'SELECT * FROM notifications__get_unread_count_by_id($1)';
				$result  = $this->db_fetch_query($sql,array($account_id));
			// d($result);
				if($result['result']){
					$result 			= $result['data'];
					$notifications__get_unread_count_by_id = $result[0]['notifications__get_unread_count_by_id'];
					$response 		    = array("code" => 200, "message" => "success", "account_id" => $account_id, "unread_notifications" => $notifications__get_unread_count_by_id);
				}
			}
			return $response;
		}

	/*
	2.	/v1/account/{account_id}/notifications
		method: 'GET',
		desc : Get notifications.
		changes to : /notifications/{account_id}/
	*/
		public function notifications($params,$postData,$postDatavalue) {
			$response 	 = $this->default_response;
			$account_id  = (int)$params['account_id'];
			if($account_id){
				$sql = 'SELECT * FROM notifications__get_by_id($1)';
				$result  = $this->db_fetch_query($sql,array($account_id));
			// d($result);
				if($result['result']){
					$result 			= $result['data'];
					$response 			= array("code"=>200, "message"=>"success", "account_id" => $account_id, "notifications"=>$result);
				}
			}
			return $response;
		}


	/*
	7.	/v1/account/{account_id}/cards
		method: 'GET',
		description: "Get cards by account id."
		changes to : /cards/{account_id}/
	*/
		public function cards($params,$postData,$postDatavalue) {
			$response 	 = $this->default_response;
			$account_id  = (int)$params['account_id'];
			$response2 	 =  array("code"=>404, "message"=>"Account/cards not found");
			if($account_id){
				$sql    = 'SELECT * FROM accounts__get_by_id($1)';
				$result = $this->db_fetch_query($sql,array($account_id));
				if($result['result']){
					$sql2 = 'SELECT * FROM cards__get_by_account_id($1)';
					$result  = $this->db_fetch_query($sql2,array($account_id),"zobily_query2");
					if(!empty($result['data'])){
						$response 			= array("code"=>200, "message"=>"success", "account_id" => $account_id, "cards"=>$result['data']);
					}
					else{
						$response 			= array("code"=>200, "message"=>"success", "account_id" => $account_id, "cards"=>'');
					}
				}
				else{
					$response = $response2 ;
				}
			}

			return $response;
		}

	/*
	8.	/v1/account/{account_id}/balance
		 method: 'GET',
		 description: "Get account balance."
		 changes to :	/account_balance/{account_id}/
	 */
		 public function account_balance($params,$postData,$postDatavalue) {
		//d($params);
		 	$response 	 = $this->default_response;
		 	$response2 	 =  array("code"=>404, "message"=>"Account not found");
		 	$account_id  = (int)$params[0];
		//d($account_id);
		 	if($account_id){
		 		$sql    = 'SELECT * FROM account_balance__get_balance_by_id($1)';
		 		$result = $this->db_fetch_query($sql,array($account_id));
		 		if($result['result'] && !empty($result['data'])){
		 			$response 			= array("code"=>200, "message"=>"success", "account_id" => $account_id, "balance" => $result['data'][0]);
		 		}
		 		else{
		 			$response 			= $response2;
		 		}
		 	}
		 	return $response;
		 }

	 /*
	9.	/v1/account/{account_id}/upline
		method: 'GET',
		description: "Get account upline."
		changes to : /account_upline/{account_id}/
	*/
		public function account_upline($params,$postData,$postDatavalue) {

			$response 	 = $this->default_response;
			$response2 	 =  array("code"=>404, "message"=>"Account not found");
			$account_id  = (int)$params[0];
		//d($account_id);
			if($account_id){
				$sql    = 'SELECT * FROM accounts__get_upline_by_id($1)';
				$result = $this->db_fetch_query($sql,array($account_id));
				if(!empty($result['data'])){
					$response 			= array("code"=>200, "message"=>"success", "account_id" => $account_id, "upline" => $result['data']);
				}
				else{
					$response 			= $response2;
				}
			}
			return $response;
		}

	/*
	10.	/v1/account/{account_id}/downline
		method: 'GET',
		description: "Get account downline."
		changes to : /account_downline/{account_id}/
	*/
		public function account_downline($params,$postData,$postDatavalue) {

			$response 	 = $this->default_response;
			$response2 	 =  array("code"=>404, "message"=>"Account not found");
			$account_id  = (int)$params[0];
		//d($account_id);
			if($account_id){
				$sql    = 'SELECT * FROM accounts__get_downline_by_id($1)';
				$result = $this->db_fetch_query($sql,array($account_id));
			//d($result);
				if(!empty($result['data'])){
					$response 			= array("code"=>200, "message"=>"success", "account_id" => $account_id, "downline" => $result['data']);
				}
				else{
					$response 			= $response2;
				}
			}

			return $response;
		}

	/*
	11.	/v1/account/twitter/{twitter_id}
		method: 'GET',
		description: "Get account info by twitter id."
		changes to : /account_by_twitter/{twitter_id}
	*/
		public function account_by_twitter($params,$postData,$postDatavalue) {
			$response 	 = $this->default_response;
			$twitter_id  = $params['twitter_id'];
			$response2 	 =  array("code"=>404, "message"=>"Account not found");

			if($twitter_id){
				$sql    = 'SELECT * FROM accounts__get_by_twitter_id($1)';
				$result = $this->db_fetch_query($sql,array($twitter_id));
			//d($result);die();
				if(!empty($result['data'])){
					$account_info = $result['data'][0];
					$sql2 = 'SELECT * FROM cards__get_by_account_id($1)';
					$result2  = $this->db_fetch_query($sql2,array($result['data'][0]['account_id']),"zobily_query2");
					if(!empty($result2['data'])){
						$account_info['cards'] = $result2['data'];
						$sql3    = 'SELECT * FROM account_balance__get_lifetime_earnings_by_id($1)';
						$result3 = $this->db_fetch_query($sql3,array($result['data'][0]['account_id']),"zobily_query3");
						if(!empty($result3['data'])){
							$account_info['lifetime_earnings'] =  $result3['data'][0]['account_balance__get_lifetime_earnings_by_id']/100;
							$response 			= array("code"=>200, "message"=>"success", "account" =>$account_info);
						}
						else{
							$account_info['lifetime_earnings'] =  0;
							$response 			= array("code"=>200, "message"=>"success", "account" =>$account_info);
						}

						$sql4    = 'select sum(amount) as lifetime_online_cashback from transactions where cashback_type = 1 and account_id = '.$result['data'][0]['account_id'];
						$result4 = $this->db_execute_query($sql4);
						if(!empty($result4['data'])){
							$account_info['lifetime_online_cashback'] =  (float) $result4['data'][0]['lifetime_online_cashback']/100;
							$response 			= array("code"=>200, "message"=>"success", "account" =>$account_info);
						}
						else{
							$account_info['lifetime_online_cashback'] =  0;
							$response 			= array("code"=>200, "message"=>"success", "account" =>$account_info);
						}

					}
					else{
						$account_info['cards']  			= '';
						$account_info['lifetime_earnings']  =  0;
						$account_info['lifetime_online_cashback']  =  0;
						$response 				= array("code"=>200, "message"=>"success", "account" =>$account_info);
					}
				}
				else{
					$response 			= $response2;
				}
			}

			return $response;
		}

	/*
	12.	/v1/account/facebook/{facebook_id}
		method: 'GET',
		description: "Get account info by facebook id."
		changes to : /account_by_fb/{facebook_id}
	*/
		public function account_by_fb($params,$postData,$postDatavalue) {
        // d($params);
		// d($postData);
			$response    = $this->default_response;
			$facebook_id = $params['facebook_id'];
			$response2   =  array("code"=>404, "message"=>"Account not found");

			if($facebook_id){
				$sql    = 'SELECT * FROM accounts__get_by_facebook_id($1)';
				$result = $this->db_fetch_query($sql,array($facebook_id));
			//d($result);die();
				if(!empty($result['data'])){
					$account_info = $result['data'][0];
					$sql2    = 'SELECT * FROM cards__get_by_account_id($1)';
					$result2 = $this->db_fetch_query($sql2,array($result['data'][0]['account_id']),"zobily_query2");
					if(!empty($result2['data'])){
						$account_info['cards'] = $result2['data'];
						$sql3                  = 'SELECT * FROM account_balance__get_lifetime_earnings_by_id($1)';
						$result3               = $this->db_fetch_query($sql3,array($result['data'][0]['account_id']),"zobily_query3");

						if(!empty($result3['data'])){
							$account_info['lifetime_earnings'] =  $result3['data'][0]['account_balance__get_lifetime_earnings_by_id']/100;
							$response                          = array("code"=>200, "message"=>"success", "account" =>$account_info);
						}
						else{
							$account_info['lifetime_earnings'] =  0;
							$response                          = array("code"=>200, "message"=>"success", "account" =>$account_info);
						}

						$sql4    = 'select sum(amount) as lifetime_online_cashback from transactions where cashback_type = 1 and account_id = '.$result['data'][0]['account_id'];
						$result4 = $this->db_execute_query($sql4);
						if(!empty($result4['data'])){
							$account_info['lifetime_online_cashback'] =  (float) $result4['data'][0]['lifetime_online_cashback']/100;
							$response 			= array("code"=>200, "message"=>"success", "account" =>$account_info);
						}
						else{
							$account_info['lifetime_online_cashback'] =  0;
							$response 			= array("code"=>200, "message"=>"success", "account" =>$account_info);
						}
					}
					else{
						$account_info['cards']  			= '';
						$account_info['lifetime_earnings']  =  0;
						$account_info['lifetime_online_cashback']  =  0;
						$response 			= array("code"=>200, "message"=>"success", "account" =>$account_info);
					}
				}
				else{
					$response 			= $response2;
				}
			}

			return $response;
		}

	/*
	13.	/v1/account/{email}/{password}
		method: 'GET',
		description: "Get account info by email and password."
		changes to :	/account/{email}/{password}
	*/
		public function account($params,$postData,$postDatavalue) {

			$response 	 = $this->default_response;
			$email       = $params['email'];
			$password    = $params['password'];
			d($email && $password);

			$response2 	 =  array("code"=>404, "message"=>"Account not found");
			if($email && $password){
				/*$sql    = 'SELECT * FROM accounts__get_by_email_and_password($1, $2)';
				$result = $this->db_fetch_query($sql,array($email,$password));
				*/
				http://php.net/manual/en/function.pg-connect.php

				$mdPassword=md5($password);

				echo $query="SELECT * FROM `users` WHERE `email`='".$email."' and `password`='".$mdPassword."'";
				$sql=mysql_query($query);
				$result=mysql_fetch_assoc($sql);

				var_dump($sql);
				var_dump($result);
				//die();
				if(!empty($result['data'])){
					$account_info = $result['data'][0];
					$sql2    = 'SELECT * FROM cards__get_by_account_id($1)';
					$result2 = $this->db_fetch_query($sql2,array($result['data'][0]['account_id']),"zobily_query2");
					if(!empty($result2['data'])){
						$account_info['cards'] = $result2['data'];
						$sql3                  = 'SELECT * FROM account_balance__get_lifetime_earnings_by_id($1)';
						$result3               = $this->db_fetch_query($sql3,array($result['data'][0]['account_id']),"zobily_query3");

						if(!empty($result3['data'])){
							$account_info['lifetime_earnings'] =  $result3['data'][0]['account_balance__get_lifetime_earnings_by_id']/100;
							$response                          = array("code"=>200, "message"=>"success", "account" =>$account_info);
							$_session                          = Mage::getSingleton('core/session');
							$_session->setApiUser($response);
						}
						else{
							$account_info['lifetime_earnings'] =  0;
							$response                          = array("code"=>200, "message"=>"success", "account" =>$account_info);
							$_session                          = Mage::getSingleton('core/session');
							$_session->setApiUser($response);
						}

						$sql4    = 'select sum(amount) as lifetime_online_cashback from transactions where cashback_type = 1 and account_id = '.$result['data'][0]['account_id'];
						$result4 = $this->db_execute_query($sql4);
						if(!empty($result4['data'])){
							$account_info['lifetime_online_cashback'] =  (float) $result4['data'][0]['lifetime_online_cashback']/100;
							$response 			= array("code"=>200, "message"=>"success", "account" =>$account_info);
							$_session                          = Mage::getSingleton('core/session');
							$_session->setApiUser($response);
						}
						else{
							$account_info['lifetime_online_cashback'] =  0;
							$response 			= array("code"=>200, "message"=>"success", "account" =>$account_info);
							$_session                          = Mage::getSingleton('core/session');
							$_session->setApiUser($response);
						}
					}
					else{
						$account_info['cards']  			= '';
						$account_info['lifetime_earnings']  =  0;
						$account_info['lifetime_online_cashback']  =  0;
						$response              = array("code"=>200, "message"=>"success", "account" =>$account_info);
					}
				}
				else{
					$response 			= $response2;
				}
			}

			return $response;
		}

	/*
	15.	/v1/account/{account_id}
		method: 'GET',
		description: "Get account info by id."
		changes to :  /account_info/{account_id}
	*/

		public function account_info($params,$postData,$postDatavalue) {
			$response 	 = $this->default_response;
			$account_id  = (int)$params['account_id'];
			$response2 	 =  array("code"=>404, "message"=>"Account not found");
			if($account_id){
				$sql    = 'SELECT * FROM accounts__get_by_id($1)';
				$result = $this->db_fetch_query($sql,array($account_id));
			//d($result);die();
				if(!empty($result['data'])){
					$account_info = $result['data'][0];
					$sql2         = 'SELECT * FROM cards__get_by_account_id($1)';
					$result2      = $this->db_fetch_query($sql2,array($account_id),"zobily_query2");
					if(!empty($result2['data'])){
						$account_info['cards'] = $result2['data'];
						$sql3                  = 'SELECT * FROM account_balance__get_lifetime_earnings_by_id($1)';
						$result3               = $this->db_fetch_query($sql3,array($account_id),"zobily_query3");

						if(!empty($result3['data'])){
							$account_info['lifetime_earnings'] =  $result3['data'][0]['account_balance__get_lifetime_earnings_by_id']/100;
							$response                          = array("code"=>200, "message"=>"success", "account" =>$account_info);
						}
						else{
							$account_info['lifetime_earnings'] =  0;
							$response                          = array("code"=>200, "message"=>"success", "account" =>$account_info);
						}

						$sql4    = 'select sum(amount) as lifetime_online_cashback from transactions where cashback_type = 1 and account_id ='.$account_id;
						$result4 = $this->db_execute_query($sql4);
						if(!empty($result4['data'])){
							$account_info['lifetime_online_cashback'] =  (float) $result4['data'][0]['lifetime_online_cashback']/100;
							$response 			= array("code"=>200, "message"=>"success", "account" =>$account_info);
						}
						else{
							$account_info['lifetime_online_cashback'] =  0;
							$response 			= array("code"=>200, "message"=>"success", "account" =>$account_info);
						}
					}
					else{
						$account_info['cards'] = '';
						$response              = array("code"=>200, "message"=>"success", "account" =>$account_info);
					}
				}
				else{
					$response 			= $response2;
				}
			}

			return $response;
		}

		/* account ends  here */


		/* merchants start here */

	/*
		1.	/v1/merchants/online/{account_id}/{limit}/{offset}
		method: 'GET',
		desc : Get set of online merchants by account_id with limit and offset.
		changes to :  /merchants/online/{account_id}/{limit}/{offset}
	*/

		public function online_merchants($params,$postData,$postDatavalue) {
			$response   = $this->default_response;
			$account_id = (int)$params['account_id'];
			$limit      = $params['limit'];
			$offset     = $params['offset'];
			$email      = $params['email'];
			$response2  =  array("code"=>404, "message"=>"Online Merchants not found");
			if($account_id && $limit && $offset){
				//$sql    = 'SELECT * FROM affiliates__get_affiliates($1, $2, $3)';
				$sql    = 'SELECT * FROM affiliates__getdetailed($1, $2, $3)';
				$result = $this->db_fetch_query($sql,array($account_id,$limit,$offset),'zq2');
				//d($result);die();
				if(!empty($result['data'])){
					// $record_count = $result['data'][0]['record_count'];

					//echo $merchant_id =$result['data'][0]['affiliate_id'];



					$record_count = count($result['data']);
					$new_rows     = array();
					for ($index = 0; $index < count($result['data']); ++$index) {
						$row    = $result['data'][$index];
						if(isset($email))
						{
							$link = $row['link'];
							if (strpos($link,'flexlinks') > 0 ) 	{
								$new_link = $link."&FOBS=$email";
								$row['link'] = $new_link;
							   }
						}
						$new_rows[$index] 				   = $row;
						$new_rows[$index]['merchant_id']   = $row['affiliate_id'];
						$merchant_id =$row['affiliate_id'];


						if($merchant_id!= ''){

						 $sql				= "SELECT SUM(review_points), COUNT(*) AS count FROM user_review WHERE merchant_id = '$merchant_id'";
						$result1            = $this->db_execute_query($sql,true);
						//print_r($result1);
						  $result_sum=$result1[data][0][sum];
						 $result_count = $result1[data][0][sum];

						if(($result_sum!= NULL) && ($result_count!=0))
						{

						   $avg=($result1[data][0][sum]/$result1[data][0][count]);
						}
						else
						{

							$avg =0;
						}

							}



						$new_rows[$index]['business_name'] = $row['affiliate_name'];
						$new_rows[$index]['business_type'] = $row['affiliate_type'];
						$new_rows[$index]['ratingpoint'] = $avg;
						unset($new_rows[$index]['affiliate_id']);
						unset($new_rows[$index]['affiliate_name']);
						unset($new_rows[$index]['affiliate_type']);
						//unset($new_rows[$index]['ratingpoint']);
					}
					/*$_session = Mage::getSingleton('core/session');
					$acc_detail = $_session->getApiUser($response);
					d($acc_detail);
					$user_email = $acc_detail['account']['email'];*/
					//print_r($new_rows);

					$response 			= array("code"=>200, "message"=>"success","type" => 'online', "total_merchant_count" => $record_count,"current_offset" => $offset,"merchants" => $new_rows);
				}
				else{
					$response 			= $response2;
				}

			}
			return $response;
		}


		/*
			2. /v1/merchants/local/{latitude}/{longitude}/{distance}/{limit}/{offset}
				method: 'GET',
				desc : Get set of local merchants by latitude, longitude, and distance with limit and offset.
				changes to :  /merchants/local/{latitude}/{longitude}/{distance}/{limit}/{offset}
		*/


		public function local_merchants($params,$postData,$postDatavalue) {
			$response  = $this->default_response;
			$latitude  = $params['latitude'];
			$longitude = $params['longitude'];
			$distance  = (int)$params['distance'];
			$limit     = $params['limit'];
			$offset    = $params['offset'];
			$response2 =  array("code"=>404, "message"=>"Local Merchants not found", "total_merchant_count" => 0 );
			if($latitude && $longitude && $distance && $limit && $offset){
				$sql    = 'SELECT * FROM merchants__get_local_merchants_within_distance($1, $2, $3, $4, $5)';
				$input_array = array($latitude,$longitude,$distance,$limit,$offset);
				$result = $this->db_fetch_query($sql,$input_array);
				//d($sql);
				//d($input_array);
				//d($result);
				//die();
				if(($result['result'])){
					if(!empty($result['data'])){
						// $record_count = $result['data'][0]['record_count'];
						//print_r($result['data']);
						//die();
						$record_count = count($result['data']);
						for ($index = 0; $index < count($result['data']); ++$index) {
							$merchant_id=  $result['data'][$index]['merchant_id'];

							if($merchant_id!= ''){

						 $sql				= "SELECT SUM(review_points), COUNT(*) AS count FROM user_review WHERE merchant_id = '$merchant_id'";
						$result1            = $this->db_execute_query($sql,true);
						//print_r($result1);
						  $result_sum=$result1[data][0][sum];
						 $result_count = $result1[data][0][sum];

						if(($result_sum!= NULL) && ($result_count!=0))
						{

						   $avg=($result1[data][0][sum]/$result1[data][0][count]);
						}
						else
						{

							$avg =0;
						}

							}
							$result['data'][$index]['ratingpoint']=$avg;
							unset($result['data'][$index]['record_count']);
						}
						$response 			= array("code"=>200, "message"=>"success","type" => 'local', "total_merchant_count" => $record_count,"current_offset" => $offset,"merchants" => $result['data']);
					}
					else{
						$response 			= $response2;
					}
				}
				else{
					$response 				= $response2;
				}
				$nparams['account_id'] 						= 12;
				$nparams['limit'] 							= 20;
				$nparams['offset'] 						    = 1;
				$response['online_merchants'] 				= $this->online_merchants($nparams,$postData,$postDatavalue);
			}
			return $response;
		}
		/* merchants ends here */

		public function get_merchants($params) {
			$response  		   = $this->default_response;
			$business_name	   = $params['business_name'];
			$limit     = $params['limit'];
			$offset    = $params['offset'];
			//d($params);die();
			$response2 =  array("code"=>404, "message"=>"Local Merchants not found", "params"=>$params);
			if($business_name != ''){
				$sql    = "SELECT * FROM merchants where (business_name like '%$business_name%' or contact_name like '%$business_name%' or  postal_code like '%$business_name%' or city like '%$business_name%' or state like '%$business_name%' or business_type like '%$business_name%' or descriptor like '%$business_name%' or short_descriptor like '%$business_name%') and online = 0 and status = 1";
				//d($sql);die();
				$result = $this->db_execute_query($sql);
				// d($result);
				if(($result['result'])){
					if(!empty($result['data'])){
						$response 			= array("code"=>200, "message"=>"success","type" => 'local',"merchants" =>$result['data']);
					}
				}
				else{
					$response 			= $response2;
				}
			}
			return $response;
		}

		public function get_ring_user_summary_by_account($account_id = 0) {
			$rings = array();
			if($account_id){
				$sql = "select count(ring_1) as total, sum(CASE WHEN logged_in=1 THEN 1 ELSE 0 END) as total_logged_in from my_rings where ring_1 = $account_id group by ring_1;";
				$result = $this->db_execute_query($sql);
				//d($result);
				if(($result['result'])){
					if(!empty($result['data'])){
						$rings['ring_1'] = $result['data']['0'];
					}
				}
				$sql = "select count(ring_2) as total, sum(CASE WHEN logged_in=1 THEN 1 ELSE 0 END) as total_logged_in from my_rings where ring_2 = $account_id group by ring_2;";
				$result = $this->db_execute_query($sql);
				//d($result);
				if(($result['result'])){
					if(!empty($result['data'])){
						$rings['ring_2'] = $result['data']['0'];
					}
				}
			}
			return $rings;
		}

		public function callback($input_datas)
		{
			//$params = Mage::app()->getRequest()->getParam();
		    //var_dump($input_datas);


			$query_inc = 0;
			$merchantid        = (int) $input_datas['merchant_id'];
			$transaction_id    = $input_datas['transaction_id'];
			$affliate_source   = $input_datas['affiliate_source'];
			$user_id           = (int) $input_datas['account_id'];
			$purchase_amount1  = $input_datas['purchase_amount'] * 100;
			$cashback_amount1  = $input_datas['cash_back_amount'] * 100;
			$purchase_amount   = (int) $purchase_amount1;
			$cashback_amount   = (int) $cashback_amount1;
			//var_dump($purchase_amount);
			//var_dump($cashback_amount);
			//exit;

			$transaction_date  = $input_datas['transaction_date'];
			$transaction_date  = date("Y-m-d H:i:s", strtotime($transaction_date));


			$start_date        = $input_datas['start_date'];
			$end_date          = $input_datas['end_date'];
			if(isset($input_datas['gui'])) $gui = (string) $input_datas['gui']; else	$gui = 'NULL';
			if(isset($input_datas['api_secret_key'])) $api_secret_key = (string) $input_datas['api_secret_key']; else $api_secret_key = 'NULL';
			if(isset($input_datas['token'])) $token = (string) $input_datas['token']; else $token = 'NULL';
			if(isset($input_datas['program_id'])) $program_id = (string) $input_datas['program_id']; else $program_id = 'NULL';

			$aff_id = 0;
			$query_inc = $input_datas['query_inc'];

			$sql2    = "SELECT * FROM online_cashback_transaction where transaction_id = '$transaction_id'";
			$response = $this->db_execute_query($sql2);
			//d($response);
			//exit;
			if($response['result'] != 1 && (empty($response['data'])) ){
			//print_r($input_datas); exit;
			$sql      = 'SELECT * FROM affiliates__get_online_cashback($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13)';
			$result   = $this->db_fetch_query($sql,array($merchantid,$user_id,$purchase_amount,$transaction_id,$affliate_source,$aff_id,$token,$start_date,$end_date,$api_secret_key,$gui,$program_id,$cashback_amount),"zobily_query$query_inc");
			//d(array($account_id,$lastFour,$exp_month,$exp_year,$secret));
			$cashback_id  = $result['data'][0]['affiliates__get_online_cashback'];
			//echo 'Test';
			//d($cashback_id);
			//d($result);
			//die();

			$is_processed = 0;
			$is_online = 1;
			$status = 'authorized';
			$purch_amount   = $cashback_amount;
			if($purch_amount != 0) {
				$sql1      = 'SELECT * FROM add_new_transaction($1, $2, $3, $4, $5, $6)';
				$result1   = $this->db_fetch_query($sql1,array($merchantid,$user_id,$purch_amount,$status,$is_processed,$is_online),"zobily_tquery$query_inc");
				$cashback_id  = $result1['data'][0]['add_new_transaction'];
			  }
			}
			else { }
		}
		public function linkshare()
		{

		}
		public function cjcom()
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://advertiser-lookup.api.cj.com/v3/advertiser-lookup?advertiser-ids=notjoined");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$returnResult = curl_exec($ch);
			/*$returnResult = '<?xml version="1.0" encoding="UTF-8" ?> <items> <item> <id>573300715</id> <type>Click</type> <date> </date> <transaction_date>10/28/2014 11:22:12 AM</transaction_date> <domain><![CDATA[http://www.zobily.com]]></domain> <product_name><![CDATA[Save 60% on a Sam&#39;s Club New Member Package including a 1-Year Membership, $20 Gift Card &amp; more.]]></product_name> <program_id>158026</program_id> <program_name><![CDATA[LivingSocial]]></program_name> <category_name><![CDATA[Virtual Malls]]></category_name> <other_categories><![CDATA[ ]]></other_categories> <campaign>None</campaign> <subtracking><![CDATA[TRANS445USER1234]]></subtracking> <useragent><![CDATA[Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.104 Safari/537.36]]></useragent> <ip>23.252.207.162</ip> <referer><![CDATA[ ]]></referer> <sale_amount> 100</sale_amount> <merchant_amount> </merchant_amount> <order_number> </order_number> </item> <item> <id>573901994</id> <type>Click</type> <date> </date> <transaction_date>10/31/2014 3:54:08 PM</transaction_date> <domain><![CDATA[http://www.zobily.com]]></domain> <product_name><![CDATA[Save 60% on a Sam&#39;s Club New Member Package including a 1-Year Membership, $20 Gift Card &amp; more.]]></product_name> <program_id>158026</program_id> <program_name><![CDATA[LivingSocial]]></program_name> <category_name><![CDATA[Virtual Malls]]></category_name> <other_categories><![CDATA[ ]]></other_categories> <campaign>None</campaign> <subtracking><![CDATA[TRANS445USER1234]]></subtracking> <useragent><![CDATA[Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.36 Safari/537.36]]></useragent> <ip>68.43.49.42</ip> <referer><![CDATA[ ]]></referer> <sale_amount> </sale_amount> <merchant_amount> </merchant_amount> <order_number> </order_number> </item> <item> <id>573902770</id> <type>Click</type> <date> </date> <transaction_date>10/31/2014 4:00:04 PM</transaction_date> <domain><![CDATA[http://www.zobily.com]]></domain> <product_name><![CDATA[Save 60% on a Sam&#39;s Club New Member Package including a 1-Year Membership, $20 Gift Card &amp; more.]]></product_name> <program_id>158026</program_id> <program_name><![CDATA[LivingSocial]]></program_name> <category_name><![CDATA[Virtual Malls]]></category_name> <other_categories><![CDATA[ ]]></other_categories> <campaign>None</campaign> <subtracking><![CDATA[TRANS445USER1234]]></subtracking> <useragent><![CDATA[Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/600.1.17 (KHTML, like Gecko) Version/6.2 Safari/537.85.10]]></useragent> <ip>50.194.213.246</ip> <referer><![CDATA[ ]]></referer> <sale_amount> </sale_amount> <merchant_amount> </merchant_amount> <order_number> </order_number> </item> <item> <id>573904795</id> <type>Click</type> <date> </date> <transaction_date>10/31/2014 4:14:37 PM</transaction_date> <domain><![CDATA[http://www.zobily.com]]></domain> <product_name><![CDATA[Save 60% on a Sam&#39;s Club New Member Package including a 1-Year Membership, $20 Gift Card &amp; more.]]></product_name> <program_id>158026</program_id> <program_name><![CDATA[LivingSocial]]></program_name> <category_name><![CDATA[Virtual Malls]]></category_name> <other_categories><![CDATA[ ]]></other_categories> <campaign>None</campaign> <subtracking><![CDATA[*UserID*]]></subtracking> <useragent><![CDATA[Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/600.1.17 (KHTML, like Gecko) Version/6.2 Safari/537.85.10]]></useragent> <ip>50.194.213.246</ip> <referer><![CDATA[ ]]></referer> <sale_amount> </sale_amount> <merchant_amount> </merchant_amount> <order_number> </order_number> </item> <item> <id>573908039</id> <type>Click</type> <date> </date> <transaction_date>10/31/2014 4:38:02 PM</transaction_date> <domain><![CDATA[http://www.zobily.com]]></domain> <product_name><![CDATA[Save 60% on a Sam&#39;s Club New Member Package including a 1-Year Membership, $20 Gift Card &amp; more.]]></product_name> <program_id>158026</program_id> <program_name><![CDATA[LivingSocial]]></program_name> <category_name><![CDATA[Virtual Malls]]></category_name> <other_categories><![CDATA[ ]]></other_categories> <campaign>None</campaign> <subtracking><![CDATA[*UserID*]]></subtracking> <useragent><![CDATA[Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.111 Safari/537.36]]></useragent> <ip>23.252.207.162</ip> <referer><![CDATA[ ]]></referer> <sale_amount> </sale_amount> <merchant_amount> </merchant_amount> <order_number> </order_number> </item> </items>';
			echo $returnResult;			*/
			$xml=simplexml_load_string($returnResult, 'SimpleXMLElement', LIBXML_NOCDATA) or die("Error: Cannot create object");
			d($xml);

		}

	  /* public function myApiCurl($url, $method = 'POST', $postParams = null, $header) {
        $ch = curl_init();
        //d($postParams);d($url);die();
        $this->http_build_query_for_curl($postParams, $postParams2);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $header ['errno'] = $err;
        $header ['errmsg'] = $errmsg;
        $header ['content'] = $content;
        $body = $header ['content'];
        //$this->_debugsap($header);
        //d($header);
        //d($body);
		//d($postParams);
        //die('s');
        try {
            $result = json_decode($body);
            if ($result) {
                return $result;
            }
        } catch (Exception $e) {
            Mage::log('Mage_Core' . $e->getMessage(), Zend_Log::WARN);
        }
        return false;
    }

	public function http_build_query_for_curl($arrays, &$new = array(), $prefix = null) {

        if (is_object($arrays)) {
            $arrays = get_object_vars($arrays);
        }

        foreach ($arrays AS $key => $value) {
            $k = isset($prefix) ? $prefix . '[' . $key . ']' : $key;
            if (is_array($value) OR is_object($value)) {
                $this->http_build_query_for_curl($value, $new, $k);
            } else {
                $new[$k] = $value;
            }
			}
		}
	 */
		public function shareasale()
		{
			$token        = 'yFXC0OGt3qloK4iy';
			$APISecretKey = 'ZNt1yb9b3OIbca9lWRe3eb3k8BLhtx9h';
			$action       = 'activity';
			$affiliate_id = 996714;
			$sort_column = 'commission';
			$sort_order  = 'desc';
			$api_version = 1.3;
			$myTimeStamp = gmdate(DATE_RFC1123);

			$start_date  = '10/30/2014';
			$end_date    = '11/28/2014';

			$sig = $token.':'.$myTimeStamp.':'.$action.':'.$APISecretKey;

			$sigHash = hash("sha256",$sig);
			$myHeaders = array("x-ShareASale-Date: $myTimeStamp","x-ShareASale-Authentication: $sigHash");
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://shareasale.com/x.cfm?
			affiliateId=$affiliate_id&token=$token&version=$api_version&action=$action&dateStart=$start_date&dateEnd=$end_date&sortCol=$sort_column&sortDir=$sort_order&XMLFormat=1");
			curl_setopt($ch, CURLOPT_HTTPHEADER,$myHeaders);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$returnResult = curl_exec($ch);

			$returnResult = '<?xml version="1.0" encoding="UTF-8"?>
			<activitydetailsreport xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.shareasale.com/XMLSchema/api/affiliate/1.3/schema.xsd">
			<activitydetailsreportrecord>
			<transid>38217586</transid>
			<userid>711187</userid>
			<merchantid>44611</merchantid>
			<transdate>11/17/2012 11:26:30 PM</transdate>
			<transamount>0</transamount>
			<commission>0.15</commission>
			<comment>Lead - 49</comment>
			<voided></voided>
			<pendingdate></pendingdate>
			<locked>1</locked>
			<affcomment></affcomment>
			<bannerpage></bannerpage>
			<reversaldate></reversaldate>
			<clickdate>2012-11-17 00:00:00.0</clickdate>
			<clicktime>11:23:40 PM</clicktime>
			<bannerid>433255</bannerid>
			<skulist></skulist>
			<quantitylist></quantitylist>
			<lockdate>2012-12-20</lockdate>
			<paiddate></paiddate>
			</activitydetailsreportrecord>
			<activitydetailsreportrecord>
			<transid>38217798</transid>
			<userid>711187</userid>
			<merchantid>44611</merchantid>
			<transdate>11/17/2012 11:51:36 PM</transdate>
			<transamount>0.00</transamount>
			<commission>0.1</commission>
			<comment>VOIDED Incomplete Registration Lead - 50</comment>
			<voided>1</voided>
			<pendingdate></pendingdate>
			<locked>1</locked>
			<affcomment></affcomment>
			<bannerpage></bannerpage>
			<reversaldate>2012-12-10 00:00:00.0</reversaldate>
			<clickdate>2012-11-17 00:00:00.0</clickdate>
			<clicktime>11:23:40 PM</clicktime>
			<bannerid>433255</bannerid>
			<skulist></skulist>
			<quantitylist></quantitylist>
			<lockdate>2012-12-20</lockdate>
			<paiddate></paiddate>
			</activitydetailsreportrecord>
			<activitydetailsreportrecord>
			<transid>38242292</transid>
			<userid>711187</userid>
			<merchantid>44611</merchantid>
			<transdate>11/19/2012 02:42:49 PM</transdate>
			<transamount>0.00</transamount>
			<commission>0.1</commission>
			<comment>Lead - 53</comment>
			<voided></voided>
			<pendingdate></pendingdate>
			<locked>1</locked>
			<affcomment></affcomment>
			<bannerpage></bannerpage>
			<reversaldate></reversaldate>
			<clickdate>2012-11-19 00:00:00.0</clickdate>
			<clicktime>02:42:05 PM</clicktime>
			<bannerid>433255</bannerid>
			<skulist></skulist>
			<quantitylist></quantitylist>
			<lockdate>2012-12-20</lockdate>
			<paiddate></paiddate>
			</activitydetailsreportrecord>
			<activitydetailsreportrecord>
			<transid>38272549</transid>
			<userid>711187</userid>
			<merchantid>44611</merchantid>
			<transdate>11/20/2012 07:45:39 PM</transdate>
			<transamount>0.00</transamount>
			<commission>0.1</commission>
			<comment>Lead - 55</comment>
			<voided></voided>
			<pendingdate></pendingdate>
			<locked>1</locked>
			<affcomment></affcomment>
			<bannerpage></bannerpage>
			<reversaldate></reversaldate>
			<clickdate>2012-11-17 00:00:00.0</clickdate>
			<clicktime>11:23:40 PM</clicktime>
			<bannerid>433255</bannerid>
			<skulist></skulist>
			<quantitylist></quantitylist>
			<lockdate>2012-12-20</lockdate>
			<paiddate></paiddate>
			</activitydetailsreportrecord>
			</activitydetailsreport>';
			//echo $returnResult;
			$xml=simplexml_load_string($returnResult, 'SimpleXMLElement', LIBXML_NOCDATA) or die("Error: Cannot create object");
			//print_r($xml);
			//exit;
			$query_inc = 1;
			//foreach($xml->item as $xml_data)
			foreach($xml as $xml_data)
			{
				$flex_data = array();
				$flex_data['transaction_id'] = (int) $xml_data->transid;
				$flex_data['merchant_id']    = (int) $xml_data->merchantid;
				$flex_data['purchase_amount'] = (float) $xml_data->transamount;
				$flex_data['transaction_date'] = (string) $xml_data->transdate;
				$flex_data['affiliate_source'] = 'shareasale';

				$flex_data['account_id']   = (int) $xml_data->userid;
				/*$userid    = $xml_data->userid;
				//$flex_data['email']    = 'venkatesh.hbcc@gmail.com';
				$sql       = "SELECT * FROM accounts where LOWER(email) = LOWER('$email') LIMIT 1";
				$result    = $this->db_execute_query($sql);
				if(($result['result'])){
					if(!empty($result['data'])){
						$flex_data['account_id'] = $result['data'][0]['account_id'];
					}
				}*/

				$merchant_id = (int) $xml_data->merchantid;
				$sql1      = "select rebate_percentage from affiliates where affiliate_id = ".$merchant_id;
				$response = $this->db_execute_query($sql1);
				if($response['result'] == 1){
				 $rebate_percent = $response['data'][0]['rebate_percentage'];
				 $cash_back_amount = ($rebate_percent / 100 ) * $xml_data->sale_amount;
				 $flex_data['cash_back_amount'] = (double) $cash_back_amount;
				}
				else { $flex_data['cash_back_amount'] = 0; }

			$flex_data['start_date'] = $start_date;
			$flex_data['end_date']   = $end_date;
			$flex_data['api_secret_key']  = (string) $APISecretKey;
			$flex_data['token']  = (string) $token;
			$flex_data['query_inc']       = $query_inc;

			$this->callback($flex_data);
			$query_inc++;
		}
		$response 			= array("code"=>200, "message"=>"success","type" => 'local',"shareasale_record_count" =>$query_inc);
		return $response;
		}


		public function accountcreate($params,$postData,$postDatavalue)
		{
			$response      = $this->default_response;
		$account_id    = 0;
		$referrer_id   = (int)(12);
		$email         = pg_escape_string($postData['email']);
		$password      = pg_escape_string($postData['password']);
		$referral_code = pg_escape_string($postData['referral_code']);
		$facebook_id   = pg_escape_string($postData['facebook_id']);
		$twitter_id    = pg_escape_string($postData['twitter_id']);

        $phone        = pg_escape_string(@$postData['mobile']);
        $country_code = pg_escape_string(@$postData['country_code']);

		$firstname     = pg_escape_string($postData['firstname']);
		$lastname      = pg_escape_string($postData['lastname']);
		$postal_code   = pg_escape_string($postData['zip']);
		$user_type     = pg_escape_string($postData['user_type']);
		if($user_type == "") $user_type = 'USER';

		$helper		   = Mage::helper('users');
		if($email != "" && $password != ""){
			if($referral_code != ''){
				$sql    = 'SELECT * FROM accounts__get_by_referral_code($1)';
				$result = $this->db_fetch_query($sql,array($referral_code));
				if(($result['result'] == 1 ) && (!empty($result['data']))){
					$referrer_id = $result['data'][0]['account_id'];
				}
			}

			// $ac_array = array($referrer_id,$email,$password,$facebook_id,$twitter_id,$firstname,$lastname,$postal_code,$user_type);
			$ac_array = array($referrer_id,$email,$password,$facebook_id,$twitter_id,$firstname,$lastname,$postal_code,$phone,$country_code,$user_type);
            //d($ac_array);die();
			$sql2   	  = 'SELECT * FROM accounts__get_by_email($1)';
			$resultemail  = $this->db_fetch_query($sql2,array($email),"zobily_query_email");
			 //d($resultemail);
			// if(isset($resultemail['data'][0]) && count($resultemail['data'][0]) && 1 !=1){
			if(isset($resultemail['data'][0]) && count($resultemail['data'][0])){
				$response = array("code" => 400, "message" => $email."  is already registered. Please log in.","status" => "info");
			}
			else{
				$sql2    = 'SELECT * FROM accounts__get_by_id($1)';
				$result2 = $this->db_fetch_query($sql2,array($referrer_id),"zobily_query2");

				if(($result2['result'] == 1 ) && (!empty($result2['data']))){
					$sql3             = 'SELECT * FROM accounts__create_account_new($1, $2, $3, $4, $5, $6, $7, $8, $9,$10,$11)';
					$result3          = $this->db_fetch_query($sql3,$ac_array,"zobily_query3");
					// d($ac_array);d($result3);
					$account_id       = $result3['data'][0]['accounts__create_account_new'];
					// d($account_id);
					// die();
					if ($account_id < 1){
						$response = array("code" => 500, "message" => "Internal Server Error on creating account");
					}
					else if(($result3['result'] == 1 ) && (!empty($result3['data']))){
						$sql4    = 'SELECT * FROM accounts__update_status($1, $2)';
						if($user_type != 'BUSINESS')
							$result4  = $this->db_fetch_query($sql4,array($account_id,2),"zobily_query4");
						else
							$result4  = $this->db_fetch_query($sql4,array($account_id,3),"zobily_query4");
						$sql5     = 'SELECT * FROM accounts__get_by_email_and_password($1, $2)';
						$result5 		= $this->db_fetch_query($sql5,array($email,$password),"zobily_query5");
                        $account_info   = @$result5['data'][0];

                        // cardspring api call
                        try{
							$account_id_array['id'] = $account_id;
                            $res = Mage::helper('users')->api_transaction_call('users','POST',$account_id_array);
                            if($res['response']['http_code'] == 201){
                                $sql5    = 'SELECT * FROM accounts__update_status($1, $2)';
                                $result8  = $this->db_fetch_query($sql5,array($account_id,1),"zobily_query9");
                                if(($result8['result'] == 1 ) && (!empty($result8['data']))){
                                    $response = array("code" => 201, "message" => "Account created","account_id" => $account_id,"account" => @$account_info);
                                }
                                else{
                                     $response = array("code" => 500, "message" => "Internal Server Error on selecting user 2");
                                }
                            }
                            else{
                                    $response = array("code" => 500, "message" => "Connection problem in Card Spring API");
                            }
                        }
                        catch(Exception $err){
                            $response = array("code" => 500, "message" => "Account created , Internal Server Error on adding the user to Card spring api.","status"=>"warning", "account_id"=>$account_id,"account" => @$account_info,"error"=>$err);
                        }

                        // cardspring api call

                        if(($result4['result'] == 1 ) && (!empty($result4['data']))){
                            $response = array("code" => 201, "message" => "Account created","account_id" => $account_id,"account" => @$account_info);
                        }

						if(@$postData['card_number'] != "" && $user_type != 'BUSINESS'){
							$params['account_id']   		 =  $postData['account_id']   = $account_id;
							$postData['first_name']          =  pg_escape_string($postData['firstname']);
							$postData['last_name']           =  pg_escape_string($postData['lastname']);
							$postData['postal_code']         =  pg_escape_string($postData['zip']);
                            $postData['phone']       =  pg_escape_string($postData['mobile']);
							$account_update 				 =  $this->update_account($params,$postData,$postDatavalue);
							//d($account_update);

							if($account_update['code'] == 200){
								//d($card_api_reponse);
								$sql5         = 'SELECT * FROM accounts__get_by_email_and_password($1, $2)';
								$result5      = $this->db_fetch_query($sql5,array($email,$password),"zobily_query_updated");
								$account_info = @$result5['data'][0];
								$cardpostData['account_id'] = $account_id;
								$cardpostData['pan']        = pg_escape_string($postData['card_number']);
								$cardpostData['exp_month']  = pg_escape_string($postData['card_expiration_month']);
								$cardpostData['exp_year']   = pg_escape_string($postData['card_expiration_year']);
								$cardparams  	  = $cardpostData;
								$card_api_reponse = $this->newcard($cardparams,$cardpostData,$postDatavalue);
								$response 	      = array("code" => 201, "message" => "Account created","account_id" => $account_id,"account" => @$account_info,"card" => $card_api_reponse);
							}
							else{
								$response = array("code" => 500, "message" => "Internal Server Error");
							}
						}


						$content_array 	  = array();
						$content_array['fullname']      = $firstname != '' ? $firstname: $email;
						$content_array['email']    		= $email;
						$content_array['password'] 		= $password;

						if($user_type == 'BUSINESS'){
							$mail_template 					= 'new_merchant_account';
							// d($mail_template);
							$helper->prepare_email_template_general($content_array,"Welcome to Zobily!",$mail_template,'advershares-merchant-authorization.html','Advershares Merchants Signup Form');
						}
						else{
							$mail_template 					= 'new_user_account';
							$helper->prepare_email_template_general($content_array,"Welcome to Zobily!",$mail_template);
						}

					}
				}
			}
		}
		//d($response);
		return $response;
		}


		public function flexoffers()
		{
			$gui = "1c659021-64fc-4032-9ba2-9c5662ab18ee";
			$date1 = date('m/d/Y',strtotime("-1 days"));
			$date2 = date('m/d/Y');

			$date1 = '12/01/2014';
			$date2 = '12/16/2014';
			//echo "https://publisher.flexoffers.com/public/report.aspx?gui=$gui&d1=$date1&d2=$date2&t=A&o=3"; exit;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://publisher.flexoffers.com/public/report.aspx?gui=$gui&d1=$date1&d2=$date2&t=A&o=3");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$returnResult = curl_exec($ch);
			/*$returnResult = '<?xml version="1.0" encoding="UTF-8" ?> <items> <item> <id>573300715</id> <type>Click</type> <date> </date> <transaction_date>10/28/2014 11:22:12 AM</transaction_date> <domain><![CDATA[http://www.zobily.com]]></domain> <product_name><![CDATA[Save 60% on a Sam&#39;s Club New Member Package including a 1-Year Membership, $20 Gift Card &amp; more.]]></product_name> <program_id>158026</program_id> <program_name><![CDATA[LivingSocial]]></program_name> <category_name><![CDATA[Virtual Malls]]></category_name> <other_categories><![CDATA[ ]]></other_categories> <campaign>None</campaign> <subtracking><![CDATA[TRANS445USER1234]]></subtracking> <useragent><![CDATA[Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.104 Safari/537.36]]></useragent> <ip>23.252.207.162</ip> <referer><![CDATA[ ]]></referer> <sale_amount> 100</sale_amount> <merchant_amount> </merchant_amount> <order_number> </order_number> </item> <item> <id>573901994</id> <type>Click</type> <date> </date> <transaction_date>10/31/2014 3:54:08 PM</transaction_date> <domain><![CDATA[http://www.zobily.com]]></domain> <product_name><![CDATA[Save 60% on a Sam&#39;s Club New Member Package including a 1-Year Membership, $20 Gift Card &amp; more.]]></product_name> <program_id>158026</program_id> <program_name><![CDATA[LivingSocial]]></program_name> <category_name><![CDATA[Virtual Malls]]></category_name> <other_categories><![CDATA[ ]]></other_categories> <campaign>None</campaign> <subtracking><![CDATA[TRANS445USER1234]]></subtracking> <useragent><![CDATA[Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.36 Safari/537.36]]></useragent> <ip>68.43.49.42</ip> <referer><![CDATA[ ]]></referer> <sale_amount> </sale_amount> <merchant_amount> </merchant_amount> <order_number> </order_number> </item> <item> <id>573902770</id> <type>Click</type> <date> </date> <transaction_date>10/31/2014 4:00:04 PM</transaction_date> <domain><![CDATA[http://www.zobily.com]]></domain> <product_name><![CDATA[Save 60% on a Sam&#39;s Club New Member Package including a 1-Year Membership, $20 Gift Card &amp; more.]]></product_name> <program_id>158026</program_id> <program_name><![CDATA[LivingSocial]]></program_name> <category_name><![CDATA[Virtual Malls]]></category_name> <other_categories><![CDATA[ ]]></other_categories> <campaign>None</campaign> <subtracking><![CDATA[TRANS445USER1234]]></subtracking> <useragent><![CDATA[Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/600.1.17 (KHTML, like Gecko) Version/6.2 Safari/537.85.10]]></useragent> <ip>50.194.213.246</ip> <referer><![CDATA[ ]]></referer> <sale_amount> </sale_amount> <merchant_amount> </merchant_amount> <order_number> </order_number> </item> <item> <id>573904795</id> <type>Click</type> <date> </date> <transaction_date>10/31/2014 4:14:37 PM</transaction_date> <domain><![CDATA[http://www.zobily.com]]></domain> <product_name><![CDATA[Save 60% on a Sam&#39;s Club New Member Package including a 1-Year Membership, $20 Gift Card &amp; more.]]></product_name> <program_id>158026</program_id> <program_name><![CDATA[LivingSocial]]></program_name> <category_name><![CDATA[Virtual Malls]]></category_name> <other_categories><![CDATA[ ]]></other_categories> <campaign>None</campaign> <subtracking><![CDATA[*UserID*]]></subtracking> <useragent><![CDATA[Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/600.1.17 (KHTML, like Gecko) Version/6.2 Safari/537.85.10]]></useragent> <ip>50.194.213.246</ip> <referer><![CDATA[ ]]></referer> <sale_amount> </sale_amount> <merchant_amount> </merchant_amount> <order_number> </order_number> </item> <item> <id>573908039</id> <type>Click</type> <date> </date> <transaction_date>10/31/2014 4:38:02 PM</transaction_date> <domain><![CDATA[http://www.zobily.com]]></domain> <product_name><![CDATA[Save 60% on a Sam&#39;s Club New Member Package including a 1-Year Membership, $20 Gift Card &amp; more.]]></product_name> <program_id>158026</program_id> <program_name><![CDATA[LivingSocial]]></program_name> <category_name><![CDATA[Virtual Malls]]></category_name> <other_categories><![CDATA[ ]]></other_categories> <campaign>None</campaign> <subtracking><![CDATA[*UserID*]]></subtracking> <useragent><![CDATA[Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.111 Safari/537.36]]></useragent> <ip>23.252.207.162</ip> <referer><![CDATA[ ]]></referer> <sale_amount> </sale_amount> <merchant_amount> </merchant_amount> <order_number> </order_number> </item> </items>';
			echo $returnResult;			*/
			$xml=simplexml_load_string($returnResult, 'SimpleXMLElement', LIBXML_NOCDATA) or die("Error: Cannot create object");
			//print_r($xml);
			//exit;

			$query_inc = 1;
			try{
			foreach($xml->item as $xml_data)
			{

				$flex_data = array();
				$flex_data['transaction_id'] = (int) $xml_data->id;
				$flex_data['purchase_amount'] = (float) $xml_data->sale_amount;
				$flex_data['transaction_date'] = (string) $xml_data->transaction_date;
				$flex_data['affiliate_source'] = 'flexoffers';

				$email_array = (array) $xml_data->subtracking;
				$email_id = trim($email_array[0]);
				if( isset($email_id) && $email_id != "" )
				{
				 $flex_data['email']   = (string) $xml_data->subtracking;
				 $email    = $xml_data->subtracking;
				 //$flex_data['email']    = 'venkatesh.hbcc@gmail.com';
				 $sql       = "SELECT * FROM accounts where LOWER(email) = LOWER('$email') LIMIT 1";
				 $result    = $this->db_execute_query($sql);
				 if(($result['result'])){
					if(!empty($result['data'])){
						$flex_data['account_id'] = $result['data'][0]['account_id'];
					}
				}
				//$flex_data['account_id'] = 2000005083;


				$program_id = (int) $xml_data->program_id;
				$sql1      = "select rebate_percentage,affiliate_id from affiliates where program_id = '$program_id'";
				$response = $this->db_execute_query($sql1);
				//print_r($response);

				if($response['result'] && isset($response['data'])){
				 $rebate_percent = $response['data'][0]['rebate_percentage'];
				 $cash_back_amount = ($rebate_percent / 100 ) * $xml_data->sale_amount;
				 $flex_data['cash_back_amount'] = $cash_back_amount;
				 $flex_data['merchant_id']    = $response['data'][0]['affiliate_id'];
				}
				else { $flex_data['cash_back_amount'] = 0; $flex_data['merchant_id'] = 0; }


			$flex_data['start_date'] = $date1;
			$flex_data['end_date']   = $date2;
			$flex_data['gui']        = $gui;
			$flex_data['program_id'] = (int) $xml_data->program_id;
			$flex_data['query_inc']  = $query_inc;

			$this->callback($flex_data);
			$query_inc++;
			} else {  }
		}

		 }
		catch(Exception $e)
		{
			 $response['msg'] = $e->getMessage();
		}
		$response 			= array("code"=>200, "message"=>"success","type" => 'local',"flexoffers_record_count" =>$query_inc);
		return $response;
    }

   /* get Review */
public function setaccount($params,$postData,$postDatavalue)
    {
    	// d($params);
		//d($postData);
		//echo $postData[0];
		//die();
		//$response 			= array("code"=>200, "message"=>"success","type" => 'local',"flexoffers_record_count" =>$query_inc);
		//return $response;
    }

     public function updatemerchant()
    {
    	$path     = Mage::getBaseDir();
$realpath = $path.'/dump.csv';
$iw   = 0;
$read = fopen($realpath, 'r');
try{
    while (($row = fgetcsv($read)) !== false)
    {
        // var_dump($row);
      echo  $merchant_id= $row[0];

      echo  $rebate_percentage=$row[1];

        echo '<br/>';

        if ($row[0] !=NULL && $row[1]!=NULL)
        {
        	 echo $sql1  ="UPDATE affiliates SET rebate_percentage=$rebate_percentage where affiliate_id = $merchant_id";
            $sql                 = "UPDATE affiliates SET rebate_percentage=3 where affiliate_id = 10";
            $result            = $this->db_execute_query($sql,true);
             $result2           = $this->db_execute_query($sql1,true);

        }

    }
}
catch (Exception $e) {
    d($e->getMessage());
}
echo "finished";
    }


    // Code for showing earnings

    public function earnings($params)
    {

    	$accountid=$params;
       	if($accountid!=NULL)
		{
			$sql                 = "SELECT c_posted,r1_posted,r2_posted,c_pending  from account_balance where account_id='$accountid'";
			$result            = $this->db_execute_query($sql,true);
		}
		if($result!=NULL)
		{

			return $result;
		}

    }

    public function merchantviaearning($params)
    {
    	if($params!=NULL)
    	{
    		$sql                 = "SELECT DISTINCT business_name from notifications where account_id='$params'";
			$result            = $this->db_execute_query($sql,true);
    	}
    	//print_r($result);
    	foreach($result as $merchantname)
   		 {
      		 foreach($merchantname as $merchant)
      			 {
      			 		$merchantname1=trim($merchant['business_name']);
      			 		$sql1                 = "SELECT SUM(amount) AS totalamount from notifications where business_name='$merchantname1'";
      			 		$result1            = $this->db_execute_query($sql1,true);
      			 		echo '<br/>';
      			 }
      	}
    	//print_r($result1);
    }

    public function trans_account($params)
    {
    	if($params!=NULL)
    	{
    		$sql                 = "SELECT COUNT(account_id) FROM transactions where account_id='$params'";
			$result            = $this->db_execute_query($sql,true);
    	}
    	//print_r($result);
    	foreach ($result as $key) {
    		foreach ($key as $k) {

    			$trans_count= $k[count];
    		}

    	}
    	if($trans_count!=NULL)
    	{
    		return $trans_count;
    	}
    }

    public function lifetimeearning($params)
    {
    	//echo $params;die();
    	if($params!=NULL)
    	{
    	$sql3 = 'SELECT * FROM account_balance__get_lifetime_earnings_by_id($1)';
		$result3               = $this->db_fetch_query($sql3,array($params),"zobily_query3");
		}
		foreach ($result3 as $key) {
    		foreach ($key as $k) {

    			$life_time_earning= $k[account_balance__get_lifetime_earnings_by_id];
    			$life_time_earning =$life_time_earning;

    		}

    	}
    	return $life_time_earning;
    }

    public function total_savings($params)
    {
    	if($params!=NULL)
    	{
    		$sql                 = "SELECT SUM(discount) FROM transactions where account_id='$params'";
			$result            = $this->db_execute_query($sql,true);
    	}
    	//print_r($result);die();
    	foreach ($result as $key) {
    		foreach ($key as $k) {

    			$trans_count= ($k[sum])*.30;
    			$total_savings =$trans_count;    		}

    	}
    	if($total_savings!=NULL)
    	{
    		$total_savings=number_format($total_savings, 2)  ;
    		return $total_savings;
    	}
    }

     public function refferal_code($params)
    {
    	if($params!=NULL)
    	{
    		$sql                 = "SELECT referral_code FROM accounts where account_id='$params'";
			$result            = $this->db_execute_query($sql,true);
    	}
    	foreach($result as $k)
    	{
    		foreach ($k as $v) {
    			$referral_code= $v[referral_code];
    		}
    	}
    	//echo $referral_code;
    	if(($referral_code == '4D8A1S') ||($referral_code == NULL))
    	{
    		$referral_code='';
    	}
    	else
    	{
    		$referral_code .='/';
    	}
    	return $referral_code;
    }


   public function allusers($params)
    {
    	$params = '2000002538';
    	$parent_rcode = array();
    	 //$sql                 = "SELECT email,referral_code,first_name,last_name,account_id  FROM accounts WHERE account_id BETWEEN 2 AND 2000005759 ORDER BY account_id ASC";
    	//$sql                 = "SELECT account_id FROM accounts WHERE account_id BETWEEN 2 AND 11 ORDER BY account_id ASC";
    	$sql                 = "SELECT account_id,email FROM accounts WHERE account_id<referrer_id";
			$result            = $this->db_execute_query($sql,true);
			$i=1;
			//echo count($result);
			foreach ($result as $key ) {
				foreach ($key as $v) {
					//echo $v[email];
					$sql2                 = "SELECT referrer_id,first_name,last_name,email,referral_code,account_id FROM accounts where account_id='$v[account_id]'";
					echo "\n";
					$result2            = $this->db_execute_query($sql2,true);

					foreach ($result2 as $k) {
						foreach($k as $n)
						{

							$sql3                = "SELECT  referral_code,account_id FROM accounts where account_id='$n[referrer_id]'";

							$result3            = $this->db_execute_query($sql3,true);
							//print_r($result3);
							foreach($result3 as $v)
							{
								foreach($v as $a)
								{
									$parent_rcode[$i][parent_referral_code] =$a[referral_code];
									//echo "\n";
									$parent_rcode[$i][parent_account] =$a[account_id];
									//$i++;
								}
							}

							$parent_rcode[$i][user_referral_code]= $n[referral_code];
							$parent_rcode[$i][firstname]= $n[first_name];
							$parent_rcode[$i][last_name]= $n[last_name];
							$parent_rcode[$i][email]= $n[email];
							$parent_rcode[$i][account_id]= $n[account_id];
							$i++;
						}
					}


				}
			}

			foreach($parent_rcode as $r)
			{

					//echo $r[parent_referral_code];
					//echo "\n";
					//echo $r[user_referral_code];
					echo $r[firstname].'-->'.$r[last_name].'-->'.$r[email].'-->'.$r[account_id];
					//echo $r[firstname];
					//echo $r[last_name];
					//echo $r[email];
				//echo $r[account_id];
					echo "\n";



			}


    }

    public function parentchange()
    {
    		//$n= NULL;
    		//$sql                 = "SELECT account_id,email FROM accounts WHERE referrer_id=0";
			//$result            = $this->db_execute_query($sql,true);

			//print_r($result);
		//foreach ($result as $key ) {
				//foreach ($key as $v) {

    	$accountdetails = array("47","57","351","500","780","836","844","878","883","884","906","9840020","9840069","9840086","9840118","9840135","9840146","9840173",
"9840267","9840269","9840287","9840288","9840313","9840324","9840368","9840369","9840371","9840372","9840373","9840374","9840379",
"9840422","9840423","9840426","9840428","9840430","9840431","9840435","9840437","9840440","9840441","9840474","9840504","9840512",
"9840528","9840530","9840541","9840543","9840544","9840552","9840553","9840560","9840567","9840583","9840588","9840592","9840629",
"9840633","9840637","2000002144","2000002966","2000003404","2000005191");

						//if($v[account_id] !=1)
					//	{
						//	echo $v[account_id];
    							for($i=0;$i<count($accountdetails);$i++){
    								echo $accountdetails[$i];
							$qry      = 'SELECT * FROM account_parent_zobily($1)';
						$result   = $this->db_fetch_query($qry,array($accountdetails[$i]),$accountdetails[$i]);
							echo "\n";
						}
				//}
			//}
    }

    public function my_transactions($account_id = 0) {
    	  //$account_id = 2000002481;
    	  $account_id = 2000003239;
    	  //field ambiguity can be removed  also by usin alias of ambiguous field name

    	  $response = array();

	  $sql    = "SELECT transactions.*, accounts.* FROM transactions";
	  $sql    .= " LEFT JOIN merchants ON transactions.merchant_id = merchants.merchant_id";
	  $sql    .= " LEFT JOIN accounts ON transactions.account_id = accounts.account_id";
	  $sql .= " where merchants.account_id = {$account_id}";
	  $result = $this->db_execute_query($sql);
	  if(!empty($result['data']) && count($result['data'] > 0)){
	  	$response = $result['data'];
	  }
	  //d($result);die('hallo');

	  return $response;
    }

}
