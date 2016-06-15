<?php

class Items extends EQParser {
  protected $mainTable;
  public function __construct() {
    $this->mainTable = $this->getTablePrefix() . 'tbl_items';
    parent::__construct();
  }
  public function getSql($args) {
    $sql = " SELECT"
         . " tite.internal_item_id,"
         . " tite.external_item_id,"
         . " tite.internal_character_id,"
         . " (SELECT tcha.character_name FROM " . $this->getTablePrefix() . "tbl_characters tcha WHERE tcha.internal_character_id = tite.internal_character_id) as character_name,"
         . " tite.item_name as name,"
         . " tite.item_location as location,"
         . " tite.item_count as count"
//          . " (SELECT tls.translation FROM " . $this->getTablePrefix() . "tbl_language_strings tls WHERE tls.language_string = tite.language_string AND tls.language_id = " . $this->getLanguageId() . ") as item_name"
         . " FROM " . $this->mainTable ." tite"
//          . " LEFT JOIN " . $this->getTablePrefix() . "tbl_types ttyp ON ttyp.internal_type_id = tite.internal_type_id"
         . $args['where'];

    return $sql;
  }
  public function getAll($args) {
    if(!empty($args)) {
      $where = array();
      $sqlArgs = array();

      if(isset($args['external_item_id']) && $args['external_item_id'] > 0) {
        $where[] = "tite.external_item_id = " . $args['external_item_id'];
      }
      if(isset($args['external_item_ids']) && is_array($args['external_item_ids']) && !empty($args['external_item_ids']) && count($args['external_item_ids']) > 0) {
        $where[] = "tite.external_item_id IN (" . implode($args['external_item_ids'], ', ') . ")";
      }
      if(isset($args['external_type_ids']) && is_array($args['external_type_ids']) && !empty($args['external_type_ids']) && count($args['external_type_ids']) > 0) {
        $where[] = "ttyp.external_type_id IN (" . implode($args['external_type_ids'], ', ') . ")";
      }
      if(isset($args['internal_character_id']) && (int)$args['internal_character_id'] > 0 && $this->execute('Characters', 'getOne', (int)$args['internal_character_id'])) {
        $where[] = "`internal_character_id` = " . (int)$args['internal_character_id'];
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
        $this->errorArray = array(
          'class' => 'Categories',
          'function' => 'getAll',
          'reason' => $e
        );
        if($this->showDebug) {
          $this->debug();
        }
      }
    }
  }
  public function getOne($internalItemId) {
    if(isset($internalItemId) && (int)$internalItemId > 0) {
      $sqlArgs['where'] = " WHERE tite.internal_item_id = " . $internalItemId;
      $sql = $this->getSql($sqlArgs);

      try {
        $PDOStatement = $this->db->prepare($sql);
        $PDOStatement->execute();
        if(($result = $PDOStatement->fetch(PDO::FETCH_ASSOC)) != false) {
          $this->internalLog[] = ('SQL Result: ' . implode($result, ','));
          return $result;
        } else {
          $this->logMessage('Item not found!');
          return false;
        }
      } catch (PDOException $e) {
        $this->logMessage("SQL Error: " . $e);
        return false;
      }
    } else {
      return false;
      $this->internalLog[] = 'Invalid ID in Items::getOne';
    }
  }
  public function add($args) {
    if(isset($args) && !empty($args)) {
      // Check if ID is set, else abort
      if(isset($args['external_item_id']) && (int)$args['external_item_id'] > 0) {
        $columns[] = "`external_item_id`";
        $values[] = (int)$args['external_item_id'];
/*
        if(!$this->getTranslation('ITEM_' . (int)$args['external_item_id'])) {
          if(isset($args['item_name']) && $args['item_name'] != '') {
            $translationArgs = array(
              'language_id' => $this->getLanguageId(),
              'language_string' => 'ITEM_' . (int)$args['external_item_id'],
              'translation' => trim((string)$args['item_name'])
            );
            if(!$this->execute('Languages','add', $translationArgs)) {
              $this->logMessage('Could not add Translation, aborting...');
              $this->logMessage('ABORT');
              return false;
            }
          } else {
            $this->logMessage('Could not add Translation, Item Name missing. aborting...');
            $this->logMessage('ABORT');
            return false;
          }
        }
        $columns[] = "`language_string`";
        $values[] = 'ITEM_' . $args['external_item_id'];
*/
      } else {
        $this->logMessage('External Item ID missing, ABORT.');
        return false;
      }

      if(isset($args['item_name']) && $args['item_name'] != '') {
        $columns[] = "`item_name`";
        $values[] = filter_var(trim($args['item_name']), FILTER_SANITIZE_STRING);
      } else {
        $this->logMessage('Item Name missing, ABORT.');
        return false;
      }
      if(isset($args['item_location']) && $args['item_location'] != '') {
        $columns[] = "`item_location`";
        $values[] = filter_var(trim($args['item_location']), FILTER_SANITIZE_STRING);
      } else {
        $this->logMessage('Item location missing, ABORT.');
        return false;
      }
      if(isset($args['internal_slot_id']) && (int)$args['internal_slot_id'] > 0 && $this->execute('Slot', 'getOne', (int)$args['internal_slot_id'])) {
        $columns[] = "`internal_slot_id`";
        $values[] = $args['internal_slot_id'];
      }
      if(isset($args['internal_character_id']) && (int)$args['internal_character_id'] > 0 && $this->execute('Characters', 'getOne', (int)$args['internal_character_id'])) {
        $columns[] = "`internal_character_id`";
        $values[] = $args['internal_character_id'];
      } else {
        $this->logMessage('Internal Character ID missing, ABORT.');
        return false;
      }
      if(isset($args['item_count']) && (int)$args['item_count'] > 1) {
        $columns[] = "`item_count`";
        $values[] = (int)$args['item_count'];
      }
/*
      if(isset($args['item_quality']) && (int)$args['item_quality'] > 0) {
        $columns[] = "`item_quality`";
        $values[] = $args['item_quality'];
      } else {
        $this->logMessage('Item Quality missing, ABORT.');
        return false;
      }
      if(isset($args['item_required_level']) && (int)$args['item_required_level'] > 0) {
        $columns[] = "`item_required_level`";
        $values[] = $args['item_required_level'];
      } else {
        $this->logMessage('Item Required Level missing, ABORT.');
        return false;
      }
*/

      if(!empty($columns)) {
        $sql = " INSERT INTO"
             . " " . $this->mainTable
             . " (" . implode($columns, ', ') . ")"
             . " VALUES"
             . " ('" . implode($values, "', '") . "')";

        try {
          $this->db->query($sql);
          $this->logMessage("Successfully added Item: " . $args['item_name'] . " to character " . $args['internal_character_id']);
          return $this->getOne($this->db->lastInsertId());
        } catch (PDOException $e) {
          $this->logMessage("SQL Error: " . $e);
          $this->debug();
          return false;
        }
      } else {
        $this->logMessage("Could not add Item: Missing Values");
        return false;
      }
    } else {
      $this->internalLog[] = 'Items::Add --> No Arguments Given';
      if($this->showDebug) {
        $this->debug();
      }
    }
  }
  public function delete($args) {
    if(isset($args) && !empty($args)) {
      $where = array();
      $sqlArgs = array();

      if(isset($args['internal_character_id'])) {
        $where[] = "internal_character_id = " . $args['internal_character_id'];
      }

      if(!empty($where)) {
        $sqlArgs['where'] = ((!empty($where) && count($where) > 0) ? ' WHERE ' . implode($where, ' AND ') : '');
        $sql = "DELETE FROM " . $this->getTablePrefix() . "tbl_items " . $sqlArgs['where'];

        try {
          $this->db->query($sql);
          $this->logMessage("Successfully deleted Items");
          return true;
        } catch (PDOException $e) {
          $this->logMessage("SQL Error: " . $e);
          $this->debug();
          return false;
        }
      } else {
        $this->logMessage("Items::Delete --> Could not elete Item: Missing Values");
        return false;
      }
    } else {
      $this->internalLog[] = 'Items::Delete --> No Arguments Given';
      if($this->showDebug) {
        $this->debug();
      }
    }
  }
  public function importAll($args) {
    if(isset($args) && !empty($args)) {
      if(isset($args['internal_character_id']) && $args['internal_character_id'] > 0 && ($characterInfo = $this->execute('Characters', 'getOne', $args['internal_character_id']))) {
        if(isset($args['items']) && !empty($args['items']) && count($args['items'])) {
          if(!$this->db->inTransaction()) $this->db->BeginTransaction();
          if(isset($args['clean_import']) && $args['clean_import'] === true) {
            if($this->delete(array('internal_character_id' => $characterInfo['internal_character_id']))) {
              foreach($args['items'] as $itemKey => $item) {
                if($this->add($item) === false) {
                  if($this->db->inTransaction()) $this->db->rollBack();
                  $this->internalLog[] = 'Items::importAll --> Could not add item ' . $addArgs['item_name'];
                  if($this->showDebug) {
                    $this->debug();
                  }
                }
              }
              if($this->db->inTransaction()) $this->db->commit();
              $this->internalLog[] = 'Items::importAll --> All items Added';
              return true;
            } else {
              $this->internalLog[] = 'Items::importAll --> Could not Delete Items';
              if($this->showDebug) {
                $this->debug();
              }
            }
          }
        } else {
          $this->internalLog[] = 'Items::importAll --> No Items to import';
          if($this->showDebug) {
            $this->debug();
          }
        }
      } else {
        $this->internalLog[] = 'Items::importAll --> Internal Character ID missing or invalid - ' . $args['internal_character_id'];
        if($this->showDebug) {
          $this->debug();
        }
      }
    } else {
      $this->internalLog[] = 'Items::importAll --> No Arguments Given';
      if($this->showDebug) {
        $this->debug();
      }
    }
  }
}


?>