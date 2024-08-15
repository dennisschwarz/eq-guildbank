<?php

class Characters extends EQParser {
  protected $mainTable;
  public function __construct() {
    $this->mainTable = $this->getTablePrefix() . 'tbl_characters';
    parent::__construct();
  }
  private function getSql($args = array()) {
    $sql = " SELECT"
         . " tcha.internal_character_id,"
         . " tcha.character_name,"
         . " tcha.internal_server_id,"
         . " tser.server_name,"
         . " tcha.faction_id,"
         . " tcha.last_import,"
         . " tcha.account_id,"
         . " tcha.server,"
         . " tcha.race,"
         . " tcha.class,"
         . " tcha.level"
         . " FROM " . $this->mainTable . " tcha"
         . " LEFT JOIN " . $this->getTablePrefix() . "tbl_servers tser ON tser.internal_server_id = tcha.internal_server_id"
         . (isset($args['where']) ? $args['where'] : '');
    return $sql;
  }
  public function getAll($args) {
    if(!empty($args)) {
      $where = array();
      $sqlArgs = array();

      if(isset($args['character_name']) && trim((string)$args['character_name']) != '') {
        $where[] = "tcha.character_name LIKE '%" . trim((string)$args['character_name']) . "%'";
      }
      if(isset($args['faction_id']) && (int)$args['faction_id'] > 0) {
        $where[] = "tcha.faction_id = " . $args['faction_id'];
      }
      if(isset($args['server_name']) && trim((string)$args['server_name']) != '') {
        $where[] = "tser.server_name LIKE '%" . trim((string)$args['server_name']) . "%'";
      }

      if(!empty($where)) {
        $sqlArgs['where'] = ' WHERE ' . implode(" AND ", $where);
      }

      $sql = $this->getSql($sqlArgs);
    } else {
      $sql = $this->getSql();
    }

    try {
      $result = $this->db->query($sql);

      if($result = $result->fetchAll(PDO::FETCH_ASSOC)) {
        return $result;
      } else {
        return false;
      }
    } catch (PDOException $e) {
      $this->logMessage("SQL Error: " . $e->getMessage());
      return false;
    }
  }
  public function getOne($internalCharacterId) {
    if(isset($internalCharacterId) && (int)$internalCharacterId > 0) {
      $sqlArgs['where'] = " WHERE tcha.internal_character_id = " . $internalCharacterId;
      $sql = $this->getSql($sqlArgs);

      try {
        $PDOStatement = $this->db->prepare($sql);
        $PDOStatement->execute();
        if(($result = $PDOStatement->fetch(PDO::FETCH_ASSOC)) != false) {
          $this->internalLog[] = ('SQL Result: ' . implode(',', $result));
          return $result;
        } else {
          $this->logMessage('Character not found!');
          return false;
        }
      } catch (PDOException $e) {
        $this->logMessage("SQL Error: " . $e);
        return false;
      }
    } else {
      return false;
      $this->internalLog[] = 'Invalid ID in Characters::getOne';
    }
  }
  public function add($args) {
    if(isset($args) && !empty($args)) {
      $columns = array();
      $values = array();

      if(isset($args['character_name']) && trim((string)$args['character_name']) != '') {
        $columns[] = "`character_name`";
/*         $values[] = mysql_real_escape_string(trim((string)$args['character_name'])); */
        $values[] = addslashes(trim((string)$args['character_name']));
      } else {
        $this->logMessage('Character:add -> Character Name missing!');
        $this->logMessage('ABORT!');
        return false;
      }
      if(isset($args['server_name']) && trim((string)$args['server_name']) != '') {
        $serverInfo = $this->execute('Servers', 'getAll', array('server_name' => $args['server_name']));

        if($serverInfo && count($serverInfo) == 1) {
          $columns[] = "`internal_server_id`";
          $values[] = (int)$serverInfo[0]['internal_server_id'];
        } else {
          $this->logMessage('Server not found, aborting...');
          $this->logMessage('ABORT');
          return false;
        }
      } else {
        $this->logMessage('Character:add -> Server Name missing!');
        $this->logMessage('ABORT!');
        return false;
      }

      $sql = " INSERT INTO"
           . " " . $this->mainTable
           . " (" . implode(', ', $columns) . ")"
           . " VALUES"
           . " ('" . implode("', '", $values) . "')";
      try {
        if(!$this->db->inTransaction()) $this->db->BeginTransaction();
        $this->db->query($sql);
        $internalCharacterId = $this->db->lastInsertId();
        $this->logMessage('Characters::add -> Done!');
        if($this->db->inTransaction()) $this->db->commit();
        return $this->getOne($internalCharacterId);

      } catch (PDOException $e) {
        $this->logMessage("SQL Error: " . $e->getMessage());
        $this->debug();
        return false;
      }
    } else {
      $this->errorArray["function"] = 'Add';
      $this->errorArray["reason"] = 'No Arguments Given';

      parent::setErrorMessage($this->errorArray);
    }
  }
  public function update($args) {
    if(isset($args) && !empty($args)) {
      if(isset($args['internal_character_id']) && (int)$args['internal_character_id'] > 0 && $this->getOne($args['internal_character_id'])) {
        // Character exists, update
        $updateColumns = array();

        if(isset($args['profile_id']) && (string)$args['profile_id'] != '') {
          $updateColumns[] = "`profile_id` = '" . $args['profile_id'] . "'";
        }

        if(is_array($updateColumns) && !empty($updateColumns)) {

          $sql = " UPDATE"
               . " " . $this->mainTable
               . " SET"
               . " " . implode(', ', $updateColumns)
               . " WHERE internal_character_id = " . $args['internal_character_id'];

          try {
            $result = $this->db->query($sql);
            if($result) {
              return $this->getOne($args['internal_character_id']);
            } else {
              $this->logMessage("Characters::update SQL Error: " . $e->getMessage());
            }
          } catch (PDOException $e) {
            $this->logMessage("Characters::update SQL Error: " . $e->getMessage());
            $this->debug();
            return false;
          }
        } else {
          $this->logMessage('Characters::update -> Nothing to Update!');
        }
      }
    } else {
      $this->logMessage('Could not update Character -> Arguments missing!');
    }
  }
  public function hasRecipe($args) {
    $where = array();
    if(isset($args['internal_character_id']) && (int)$args['internal_character_id'] > 0 && $this->getOne($args['internal_character_id'])) {
      $where[] = '`internal_character_id` = ' . $args['internal_character_id'];
    } else {
      $this->logMessage('Could not register Recipe: Character ID missing!');
      return false;
    }
    if(isset($args['internal_recipe_id']) && (int)$args['internal_recipe_id'] > 0 && $this->execute('Recipes', 'getOne', $args['internal_recipe_id'])) {
      $where[] = '`internal_recipe_id` = ' . $args['internal_recipe_id'];
    } else {
      $this->logMessage('Could not register Recipe: Recipe ID missing!');
      return false;
    }

    $where = ' WHERE ' . implode(' AND ', $where);

    $sql = " SELECT 1"
         . " FROM " . $this->getTablePrefix() . "rel_characters_recipes"
         . $where;

    try {
      $PDOStatement = $this->db->query($sql);
      if(($result = $PDOStatement->fetch(PDO::FETCH_ASSOC)) != false) {
        return true;
      } else {
        return false;
      }
    } catch (PDOException $e) {
      $this->logMessage("SQL Error: " . $e->getMessage());
      $this->debug();
      return false;
    }
  }
  public function registerRecipe($args) {
    $columns = array();
    $value = array();

    if(isset($args['internal_character_id']) && (int)$args['internal_character_id'] > 0 && $this->getOne($args['internal_character_id'])) {
      $columns[] = '`internal_character_id`';
      $values[] = $args['internal_character_id'];
    } else {
      $this->logMessage('Could not register Recipe: Character ID missing!');
      return false;
    }
    if(isset($args['internal_recipe_id']) && (int)$args['internal_recipe_id'] > 0 && $this->execute('Recipes', 'getOne', $args['internal_recipe_id'])) {
      $columns[] = '`internal_recipe_id`';
      $values[] = $args['internal_recipe_id'];
    } else {
      $this->logMessage('Could not register Recipe: Recipe ID missing!');
      return false;
    }

    $sql = " INSERT INTO"
         . " " . $this->getTablePrefix() . "rel_characters_recipes"
         . " (" . implode(', ', $columns) . ")"
         . " VALUES"
         . " (" . implode(", ", $values) . ")";

    try {
      $PDOStatement = $this->db->prepare($sql);
      return $PDOStatement->execute();
    } catch (PDOException $e) {
      $this->logMessage("SQL Error: " . $e->getMessage());
      $this->debug();
      return false;
    }
  }
  public function getServer($args) {
    $where = array();
    if(isset($args['server_name']) && trim((string)$args['server_name'])) {
      $where[] = "tser.server_name LIKE '%" . $args['server_name'] . "%'";
    }
    if(isset($args['interal_server_id']) && (int)$args['server_id'] > 0) {
/*       $where[] = "tser." */
    }
  }
  public function matchProfileId($args) {
    if(isset($args) && !empty($args)) {
      if(isset($args['internal_character_id']) && (int)$args['internal_character_id'] > 0 && ($characterInfo = $this->getOne($args['internal_character_id']))) {
        $generatedProfileId = $this->generateProfileId($args['internal_character_id'], $args['faction_id'], $args['character_name'], $args['server_name']);
        $storedProfileId = $characterInfo['profile_id'];
        if($generatedProfileId == $storedProfileId) {
          return true;
        }
      }
    }
    return false;
  }
}


?>