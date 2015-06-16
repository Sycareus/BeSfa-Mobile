<?php
/*	Classe implémentant le singleton pour PDO */
class PDO2 extends PDO {

	private static $_instance;

	/*	Constructeur : héritage public obligatoire par héritage de PDO */
	public function __construct() {
	
	}

	/*	Singleton */
	public static function getInstance() {
		if (!isset(self::$_instance)) {
			try {
				self::$_instance = new PDO(SQL_DSN, SQL_USERNAME, SQL_PASSWORD);
				self::$_instance->exec('SET NAMES utf8');
			}
			catch (PDOException $e) {
				echo $e;
			}
		}
		return self::$_instance; 
	}
}
?>
