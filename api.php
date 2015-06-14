<?php
    
	/* 
		This is an example class script proceeding secured API
		To use this class you should keep same as query string and function name
		Ex: If the query string value rquest=delete_user Access modifiers doesn't matter but function should be
		     function delete_user(){
				 You code goes here
			 }
		Class will execute the function dynamically;
		
		usage :
		
		    $object->response(output_data, status_code);
			$object->_request	- to get santinized input 	
			
			output_data : JSON (I am using)
			status_code : Send status message for headers
			
		Add This extension for localhost checking :
			Chrome Extension : Advanced REST client Application
			URL : https://chrome.google.com/webstore/detail/hgmloofddffdnphfgcellkdfbfbjeloo
		
		I used the below table for demo purpose.
		
		CREATE TABLE IF NOT EXISTS `users` (
		  `user_id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_fullname` varchar(25) NOT NULL,
		  `user_email` varchar(50) NOT NULL,
		  `user_password` varchar(50) NOT NULL,
		  `user_status` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
		
		CREATE TABLE IF NOT EXISTS `comments` (
		  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
		  `comment_event` int(11) NOT NULL,
		  `comment_replyTo` int(11) NOT NULL,
		  `comment_pseudo` varchar(25) NOT NULL,
		  `comment_content` TEXT NOT NULL,
		  `comment_isReply` tinyint(1) NOT NULL DEFAULT '0',
		  `comment_date` DATETIME NOT NULL,
		  PRIMARY KEY (`comment_id`),
		  FOREIGN KEY (`comment_replyTo`),
		  FOREIGN KEY (`comment_eventCommented`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 	*/
	
	require_once("rest/Rest.inc.php");
	
	class API extends REST {
	
		public $data = "";
		
		const DB_SERVER = "app.mysql.db";
		const DB_USER = "app";
		const DB_PASSWORD = "root";
		const DB = "app";
		
		const MAX_EVENT_REQUEST = 101;
		const MAX_MEDIA_REQUEST = 21;
		const MAX_COMMENT_REQUEST = 201;
		
		private $db = NULL;
	
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}
		
		/*
		 *  Database connection 
		*/
		private function dbConnect(){
			$this->db = mysql_connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD);
			if($this->db)
				mysql_select_db(self::DB,$this->db);
		}
		
		/*
		 * Public method for access api.
		 * This method dynmically call the method based on the query string
		 * rquest should be "comments" or "medias" or "events"
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/", "", $_REQUEST['rquest'])));
			if((int)method_exists($this, $func) > 0)
				$this->$func();
			else
				$this->response('', 404);				// If the method not exist with in this class, response would be "Page not found".
		}
		
		/* modify comment section using method request POST or GET */
		
		private function comments() {
			if($this->get_request_method() == "POST"){
				// it's post method --> we will add a comment
				$event = $this->_request['event'];
				$content = $this->_request['content'];
				$isReply = $this->_request['isReply'];
				$pseudo = $this->_request['pseudo'];
				$replyTo = $this->_request['replyTo'];
				
				// Input validations
				if(!empty($event) and !empty($content) and !empty($isReply) and !empty($pseudo) and !empty($replyTo)) {
					$sql = mysql_query("INSERT INTO `comments` (`comment_id`, `comment_pseudo`, `comment_content`, `comment_event`, `comment_isReply`, `comment_replyTo`, `comment_date`)
						VALUES ('', '$pseudo', '$content', '$event', '$isReply', '$replyTo', NOW())", $this->db);
					if($sql){
						$result = array('status' => "Success", "msg" => "Comment has been added");
						// If success everythig is good send header as "OK" and user details
						$this->response($this->json($result), 200);
					}
					$this->response('', 204);	// If no records "No Content" status
				}
				// If invalid inputs "Bad Request" status message and reason
				$error = array('status' => "Failed", "msg" => "Invalid input");
				$this->response($this->json($error), 400);
				
			} else if($this->get_request_method() == "GET"){
				// it's get method --> we should take some comments and return them
				$count = (int)$this->_request['count'];
				if($count > 0 /*and $count < MAX_COMMENT_REQUEST*/){
					$sql = "SELECT COUNT(*) as nb FROM comments";
					$req = mysql_query($sql) or die('Erreur SQL !'.$sql.' --- '.mysql_error());
					$row = mysql_fetch_assoc($req);
					$nombre = $row['nb'];
					if($count > $nombre)
						$count = $nombre;
					mysql_query("SELECT * FROM comments ORDER BY comment_id DESC LIMIT $count");
					if(mysql_num_rows($sql) > 0){
						$result = array();
						while($rlt = mysql_fetch_array($sql,MYSQL_ASSOC)) {
							$result[] = $rlt;
						}
						// If success everythig is good send header as "OK" and return list of users in JSON format
						$this->response($this->json($result), 200);
					}
					$this->response('', 204);	// If no records "No Content" status
				} else
					$this->response('', 204);	// If no records "No Content" status
			} else {
				// we don't have functions for others request method, so we throw an error
				$this->response('', 406);
			}
		}
		
		/*
		 * Media API
		 * Method must be GET
		 * parameter : count (integer) - number of medias (starting from last) to be returned
		 */
		
		private function medias() {
			if($this->get_request_method() != "GET") {
				$this->response('', 406);
			}
			// now, shit's about to GET real
			$count = (int)$this->_request['count'];
			if($count > 0 and $count < MAX_MEDIA_REQUEST){
				$sql = "SELECT COUNT(*) as nb FROM medias";
				$req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
				$row = mysql_fetch_assoc($req);
				$nombre = $row['nb'];
				if($count > $nombre)
					$count = $nombre;
				mysql_query("SELECT * FROM medias ORDER BY media_id DESC LIMIT $count");
				if(mysql_num_rows($sql) > 0){
					$result = array();
					while($rlt = mysql_fetch_array($sql,MYSQL_ASSOC)){
						$result[] = $rlt;
					}
					// If success everythig is good send header as "OK" and return list of users in JSON format
					$this->response($this->json($result), 200);
				}
				$this->response('', 204);	// If no records "No Content" status
			} else
				$this->response('', 204);	// If no records "No Content" status
		}
		
		/*
		 * Events API
		 * Method must be GET
		 * parameter : count (integer) - number of events (starting from last) to be returned
		 */
		
		private function events() {
			if($this->get_request_method() != "GET") {
				$this->response('', 406);
			}
			// now, shit's about to GET real
			$count = (int)$this->_request['count'];
			if($count > 0 and $count < MAX_EVENT_REQUEST){
				$sql = "SELECT COUNT(*) as nb FROM events";
				$req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
				$row = mysql_fetch_assoc($req);
				$nombre = $row['nb'];
				if($count > $nombre)
					$count = $nombre;
				mysql_query("SELECT * FROM events ORDER BY event_id DESC LIMIT $count");
				if(mysql_num_rows($sql) > 0){
					$result = array();
					while($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)){
						$result[] = $rlt;
					}
					// If success everythig is good send header as "OK" and return list of users in JSON format
					$this->response($this->json($result), 200);
				}
				$this->response('', 204);	// If no records "No Content" status
			} else
				$this->response('', 204);	// If no records "No Content" status
		}
		
		/* 
		 *	Simple login API
		 *  Login must be POST method
		 *  email : <USER EMAIL>
		 *  pwd : <USER PASSWORD>
		 */
		
		private function login(){
			// Cross validation if the request method is POST else it will return "Not Acceptable" status
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			
			$email = $this->_request['email'];		
			$password = $this->_request['pwd'];
			
			// Input validations
			if(!empty($email) and !empty($password)){
				if(filter_var($email, FILTER_VALIDATE_EMAIL)){
					$sql = mysql_query("SELECT user_id, user_fullname, user_email FROM users WHERE user_email = '$email' AND user_password = '".md5($password)."' LIMIT 1", $this->db);
					if(mysql_num_rows($sql) > 0){
						$result = mysql_fetch_array($sql,MYSQL_ASSOC);
						
						// If success everythig is good send header as "OK" and user details
						$this->response($this->json($result), 200);
					}
					$this->response('', 204);	// If no records "No Content" status
				}
			}
			
			// If invalid inputs "Bad Request" status message and reason
			$error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
			$this->response($this->json($error), 400);
		}
		
		private function users(){	
			// Cross validation if the request method is GET else it will return "Not Acceptable" status
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$sql = mysql_query("SELECT user_id, user_fullname, user_email FROM users WHERE user_status = 1", $this->db);
			if(mysql_num_rows($sql) > 0){
				$result = array();
				while($rlt = mysql_fetch_array($sql,MYSQL_ASSOC)){
					$result[] = $rlt;
				}
				// If success everythig is good send header as "OK" and return list of users in JSON format
				$this->response($this->json($result), 200);
			}
			$this->response('',204);	// If no records "No Content" status
		}
		
		private function deleteUser(){
			// Cross validation if the request method is DELETE else it will return "Not Acceptable" status
			if($this->get_request_method() != "DELETE"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){
				mysql_query("DELETE FROM users WHERE user_id = $id");
				$success = array('status' => "Success", "msg" => "Successfully one record deleted.");
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	// If no records "No Content" status
		}
		
		/*
		private function addComment() {
			// add a comment in the database
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
		}
		*/
		
		/*
		 *	Encode array into JSON
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}
	}
	
	// Initiiate Library
	
	$api = new API;
	$api->processApi();
?>