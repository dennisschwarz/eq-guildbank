<?php

class Servers extends EQParser {
  public $mainTable;
  public function __construct() {
    $this->mainTable = $this->getTablePrefix() . 'tbl_servers';
    parent::__construct();
  }
  public function getSql($args) {
    $sql = " SELECT"
         . " tser.internal_server_id,"
         . " tser.server_name,"
         . " tser.server_setting,"
         . " tser.language_id"
         . " FROM " . $this->mainTable . " tser"
         . $args['where'];

    return $sql;
  }
  public function getAll($args) {
    if(!empty($args)) {
      $where = array();
      $sqlArgs = array();

      if(isset($args['server_name']) && (string)$args['server_name'] != '') {
        $where[] = "tser.server_name LIKE '%" . (string)$args['server_name'] . "%'";
      }
      $sqlArgs['where'] = ((!empty($where) && count($where) > 0) ? ' WHERE ' . implode($where, ' AND ') : '');
      $sql = $this->getSql($sqlArgs);

      try {
        $result = $this->db->query($sql);
        if($result = $result->fetchAll(PDO::FETCH_ASSOC)) {
          return $result;
        } else {
          return false;
        }
      } catch (PDOException $e) {
        $this->logMessage('Could not get Servers -> ' . $e->getMessage());
      }
    }
  }
  public function getOne($internalServerId) {
    if(isset($internalServerId) && (int)$internalServerId > 0) {
      $sqlArgs['where'] = " WHERE tser.internal_server_id = " . $internalServerId;
      $sql = $this->getSql($sqlArgs);

      try {
        $PDOStatement = $this->db->prepare($sql);
        $PDOStatement->execute();
        if(($result = $PDOStatement->fetch(PDO::FETCH_ASSOC)) != false) {
          $this->internalLog[] = ('SQL Result: ' . implode($result, ','));
          return $result;
        } else {
          $this->logMessage('Item Type not found!');
          return false;
        }
      } catch (PDOException $e) {
        $this->logMessage("SQL Error: " . $e);
        return false;
      }
    } else {
      return false;
      $this->internalLog[] = 'Invalid ID in types::getOne';
    }
  }
}


?>