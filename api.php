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
	
	require_once("config.php");
	require_once("pdo.php");
	require_once("rest/Rest.inc.php");
	
	class API extends REST {
	
		public $data = "";
		
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
			$this->db = PDO2::getInstance();
		}
		
		/*
		 * Public method for access api.
		 * This method dynmically call the method based on the query string
		 * rquest should be "comments" or "medias" or "events"
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/", "", $_REQUEST['rquest'])));
			if(method_exists($this, $func)) $this->$func();
			// Code 404 - Not Found
			else $this->response('', 404);
		}
		
		/* modify comment section using method request POST or GET */
		
		private function comments() {
			if($this->get_request_method() == "POST"){
				// it's post method --> we will add a comment
				$pseudo = $this->_request['pseudo'];
				$content = $this->_request['content'];
				$event = $this->_request['event'];
				$isReply = $this->_request['isReply'];
				$replyTo = $this->_request['replyTo'];
				
				// Input validations
				if(!empty($event) and !empty($content) and !empty($isReply) and !empty($pseudo) and !empty($replyTo)) {
					$pre_requete = "INSERT INTO `comments` (`comment_id`, `comment_pseudo`, `comment_content`, `comment_event`, `comment_isReply`, `comment_replyTo`, `comment_date`)
									VALUES ('',
											:pseudo,
											:content,
											:event,
											:isReply,
											:replyTo,
											NOW())";
					$requete = $pdo->prepare($pre_requete);
					$requete->bindValue(':pseudo', $pseudo);
					$requete->bindValue(':content', $content);
					$requete->bindValue(':event', $event);
					$requete->bindValue(':isReply', $isReply);
					$requete->bindValue(':replyTo', $replyTo);
					
					if($requete->execute()) {
						// Code 200 - OK
						$result = array('status' => "Success", "msg" => "Comment has been added");
						$this->response($this->json($result), 200);
					}
					else{
						// Code 204 - No content
						$this->response('', 204);
					}
				}
				// Code 400 - Bad Request
				$error = array('status' => "Failed", "msg" => "Invalid input");
				$this->response($this->json($error), 400);
				
			}
			else if($this->get_request_method() == "GET"){
				// it's get method --> we should take some comments and return them
				if(ctype_digit(strval($this->_request['count']))) $count = strval($this->_request['count']);
				else $count = 0;
				
				if($count > 0 /*and $count < MAX_COMMENT_REQUEST*/){
					$nombre = $pdo->query("SELECT COUNT(*) as nb FROM comments")->fetchColumn();
					if($count > $nombre) $count = $nombre;
					
					$pre_requete = "SELECT *
									FROM comments
									ORDER BY comment_id
									DESC LIMIT :count";
					$requete = $pdo->prepare($pre_requete);
					$requete->bindValue(':count', $count);
					$requete->execute();
					$result = array();
					
					while($rlt = $requete->fetch(PDO::FETCH_BOTH)){
						$result[] = $rlt;
					}
					// Code 200 - OK
					if(!empty($result)) $this->response($this->json($result), 200);
					// Code 204 - No content
					else $this->response('', 204);
				}
				else $this->response('', 204);
			}
			else {
				// Code 406 - Not Acceptable
				$this->response('', 406);
			}
		}
		
		/*
		 * Media API
		 * Method must be GET
		 * parameter : count (integer) - number of medias (starting from last) to be returned
		 */
		
		private function medias() {
			// Code 406 - Not Acceptable si la requête n'est pas en GET
			if($this->get_request_method() != "GET") {
				$this->response('', 406);
			}
			if(ctype_digit(strval($this->_request['count']))) $count = strval($this->_request['count']);
			else $count = 0;
			if($count > 0 and $count < MAX_MEDIA_REQUEST){
				$nombre = $pdo->query("SELECT COUNT(*) as nb FROM medias")->fetchColumn();
				if($count > $nombre) $count = $nombre;
				$pre_requete = "SELECT *
								FROM medias
								ORDER BY media_id
								DESC LIMIT :count";
				$requete = $pdo->prepare($pre_requete);
				$requete->bindValue(':count', $count);
				$requete->execute();
				$result = array();
				
				while($rlt = $requete->fetch(PDO::FETCH_BOTH)){
					$result[] = $rlt;
				}
				// Code 200 - OK
				if(!empty($result)) $this->response($this->json($result), 200);
				// Code 204 - No content
				else $this->response('', 204);
			}
			else $this->response('', 204);
		}
		
		/*
		 * Events API
		 * Method must be GET
		 * parameter : count (integer) - number of events (starting from last) to be returned
		 */
		
		private function events() {
			// Code 406 - Not Acceptable si la requête n'est pas en GET
			if($this->get_request_method() != "GET") {
				$this->response('', 406);
			}
			if(ctype_digit(strval($this->_request['count']))) $count = strval($this->_request['count']);
			else $count = 0;
			if($count > 0 and $count < MAX_EVENT_REQUEST){
				$nombre = $pdo->query("SELECT COUNT(*) as nb FROM medias")->fetchColumn();
				if($count > $nombre) $count = $nombre;
				$pre_requete = "SELECT *
								FROM events
								ORDER BY media_id
								DESC LIMIT :count";
				$requete = $pdo->prepare($pre_requete);
				$requete->bindValue(':count', $count);
				$requete->execute();
				$result = array();
				
				while($rlt = $requete->fetch(PDO::FETCH_BOTH)){
					$result[] = $rlt;
				}
				// Code 200 - OK
				if(!empty($result)) $this->response($this->json($result), 200);
				// Code 204 - No content
				else $this->response('', 204);
			}
			else $this->response('', 204);	// If no records "No Content" status
		}
		
		/* 
		 *	Simple login API
		 *  Login must be POST method
		 *  email : <USER EMAIL>
		 *  pwd : <USER PASSWORD>
		 */
		
		private function login(){
			// Code 406 - Not Acceptable si la requête n'est pas en POST
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			
			$email = $this->_request['email'];		
			$password = $this->_request['pwd'];
			
			if(!empty($email) and !empty($password)){
				if(filter_var($email, FILTER_VALIDATE_EMAIL)){
					$secure_password = sha1(SALT_1.$password.SALT_2)
					$pre_requete = "SELECT	user_id,
											user_fullname,
											user_email
									FROM	users
									WHERE	user_email = :email
									AND		user_password = :user_password
									LIMIT	1";
					$requete = $pdo->prepare($pre_requete);
					$requete->bindValue(':email', $email);
					$requete->bindValue(':user_password', $secure_password);
					$requete->execute();
					$result = "";
					$result = $requete->fetch(PDO::FETCH_BOTH);
					
					// Code 200 - OK
					if(!empty($result)) $this->response($this->json($result), 200);
					// Code 204 - No content
					else $this->response('', 204);
				}
			}
			// Code 400 - Bad Request
			$error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
			$this->response($this->json($error), 400);
		}
		
		private function users(){	
			// Code 406 - Not Acceptable si la requête n'est pas en GET
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$pre_requete = "SELECT	user_id,
									user_fullname,
									user_email
							FROM	users
							WHERE	user_status = 1";
			$requete = $pdo->prepare($pre_requete);
			while($rlt = $requete->fetch(PDO::FETCH_BOTH)){
				$result[] = $rlt;
			}
			// Code 200 - OK
			if(!empty($result)) $this->response($this->json($result), 200);
			// Code 204 - No content
			else $this->response('', 204);
		}
		
		private function deleteUser(){
			// Code 406 - Not Acceptable si la requête n'est pas en DELETE
			if($this->get_request_method() != "DELETE"){
				$this->response('',406);
			}
			
			if($this->_request['pwd'] == DELETE_PASSWORD){
				if(ctype_digit(strval($this->_request['id']))) $id = strval($this->_request['id']);
				else $id = 0;
				if($id > 0){
					$pre_requete = "DELETE FROM users
									WHERE user_id = :user_id";
					$requete = $pdo->prepare($pre_requete);
					$requete->bindValue(':user_id', $id);
					if($requete->execute()){
						// Code 200 - OK
						$success = array('status' => "Success", "msg" => "Successfully one record deleted.");
						$this->response($this->json($success),200);
					}
					else{
						// Code 204 - No content
						$success = array('status' => "Failed", "msg" => "User with this id not found.");
						$this->response('',204);
					}
				}
			}
			else{
				// Code 403 - Forbidden
				$error = array('status' => "Failed", "msg" => "No you don't!");
				$this->response($this->json($error), 403);
			}
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
