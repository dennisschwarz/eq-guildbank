<?php

class Database extends PDO {
  private static $INSTANCE;
/*
  private $HOST = 'localhost';
  private $USER = '';
  private $PASS = '';
  private $BASE = '';
  private $CHAR = 'utf8';
  private $PORT = 3306;
*/

  private $HOST = DB_HOST;
  private $USER = DB_USER;
  private $PASS = DB_PASSWORD;
  private $BASE = DB_NAME;
  private $CHAR = DB_CHARSET;
  private $PORT = 3306;
  
  public function __construct() {
      parent::__construct('mysql:host='.$this->HOST.';dbname='.$this->BASE.';charset='.$this->CHAR.';', $this->USER, $this->PASS);
  }

  public static function getInstance() {
      if(!self::$INSTANCE) {
          self::$INSTANCE = new self;
      }
      return self::$INSTANCE;
  }
}

?>