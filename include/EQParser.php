<?php

class EQParser {
  protected $fileRawData = null;
  protected $parsedItems = null;
  protected $dbRawData = null;
  public $characterItems;
  public $inventoryItems;
  public $bankItems;
  public $guildbankItems;
  public $dbItems;
  public $filterType = 'character';
  public $excludedSlots = array(
    "all" => array(),
    "character" => array("charm", "ear", "head", "face", "neck", "shoulders", "arms", "back", "wrist", "range", "hands", "primary", "secondary", "fingers", "chest", "legs", "feet", "waist", "ammo", "power source", "held"),
    "inventory" => array("general"),
    "bank" => array("bank", "sharedbank"),
    "guildbank" => array("bank", "sharedbank", "general"),
  );

  public function __construct() {
//     $this->_generateDB();
  }

  private function _generateDB() {
    $dbItems = file_get_contents('./upload/items.txt');
    if(isset($dbItems) && strlen($dbItems) > 0) {
      $this->dbRawData = $dbItems;
      $result = array();
      $rows = explode("\n", $dbItems);
      $header = explode("|", array_shift($rows));
$i = 1;
      foreach($rows as $rowKey => $row) {
        $item = explode("|", $row);
        $filterItem = array();
        foreach($item as $colKey => $col) {
          $filterItem[$header[$colKey]] = $col;
        }

        $result[] = $filterItem;

        if($i == 10) {
          break;
        }
        $i++;
      }

      if(!empty($result) && count($result) > 0) {
        $this->dbItems = $result;
      }
    }
  }

  public function parseFile($file) {
    if(isset($file) && strlen($file) > 0) {
      $this->fileRawData = $file;
      $result = array();
      $rows = explode("\n", $file);
      $header = explode("\t", array_shift($rows));

      foreach($rows as $rowKey => $row) {
        $item = explode("\t", $row);
        $filterItem = array();
        foreach($item as $colKey => $col) {
          switch($header[$colKey]) {
            case 'ID':
              $col = (int)trim($col);
            break;
          }

          $filterItem[$header[$colKey]] = $col;
        }

        $result[] = $filterItem;
      }

      if(!empty($result) && count($result) > 0) {
        $this->parsedItems = $result;
        $this->parseItems();
      }
    }
  }

  private function parseItems() {
    if($this->parsedItems !== null && count($this->parsedItems) > 0) {
      $items = $this->parsedItems;
      $filteredResult = array();

      foreach($items as $itemKey => $item) {
        if(isset($item['Location']) && $item['Location'] != '' && $item['Location'] !== null) {
          if(($slotCategory = $this->filterSlot($item['Location'])) !== false) {
            switch($slotCategory) {
              case 'character':
                $this->characterItems[] = $item;
              break;
        
              case 'inventory':
                $this->inventoryItems[] = $item;
              break;
        
              case 'bank':
                $this->bankItems[] = $item;
              break;
      
              case 'guildbank':
                $this->guildbankItems[] = $item;
              break;
      
              default:
              break;
            }
          }
        }
      }

      if(!empty($filteredResult) && count($filteredResult)) {

        return $filteredResult;
      }
    }
  }

  private function filterSlot($itemSlot) {
    foreach($this->excludedSlots['character'] as $slotKey => $excludedSlot) {
      if(stripos($itemSlot, $excludedSlot) !== false) {
        return 'character';
      }
    }
    foreach($this->excludedSlots['inventory'] as $slotKey => $excludedSlot) {
      if(stripos($itemSlot, $excludedSlot) !== false) {
        return 'inventory';
      }
    }
    foreach($this->excludedSlots['bank'] as $slotKey => $excludedSlot) {
      if(stripos($itemSlot, $excludedSlot) !== false) {
        return 'bank';
      }
    }
    return false;
  }

  private function filterItems($items, $filter = array()) {
    if(!empty($items) && count($items) > 0) {
      $result = array();
      if(!empty($filter)) {
      } else {
        foreach($items as $itemKey => $item) {
          if(isset($item['Name']) && strtolower($item['Name']) != 'empty') {
            $result[] = $item;
          }
        }
      }
      return $result;
    }
  }

  public function getCharacterItems($filter = array()) {
    return $this->filterItems($this->characterItems, array());
  }

  public function getInventoryItems($filter = array()) {
    return $this->filterItems($this->inventoryItems, array());
  }

  public function getBankItems($filter = array()) {
    return $this->filterItems($this->bankItems, array());
  }

  public function getAllItems($filter = array()) {
    return $this->filterItems($this->parsedItems, array());
  }

  public function getDBItems($filter = array()) {
    return $this->dbItems;
  }
}



?>

