<?php

// Autoload for EverQuest Parser
require_once 'config/config.inc.php';
require_once 'classes/Database.class.php';

// Ugly Linux System Case Sensitiviy Fix for Autoloading!
function SPL_autoload_case_sensitivity($name) {
  $rc = FALSE;
  $exts = explode(',', spl_autoload_extensions());
  $sep = (substr(PHP_OS, 0, 3) == 'Win') ? ';' : ':';
  $paths = explode($sep, ini_get('include_path'));
  foreach($paths as $path) {
    foreach($exts as $ext) {
      $file = $path . DIRECTORY_SEPARATOR . $name . $ext;
      if(is_readable($file)) {
        require_once $file;
        $rc = $file;
        break;
      }
    }
  }
  return $rc;
}

class EQParser {
  protected $fileRawData = null;
  protected $parsedItems = null;
  protected $dbRawData = null;
  protected $db;
  protected static $logfile;
  protected $languageId;
  public $showDebug = false;
  public $statusLog = array();
  public $internalLog = array("init");
  public $languages;
//   protected $uploadPath = '/var/www/html/eq-guildbank/eqpupload/';
//   protected $uploadPath = '/home/dschwarz/websites/sandbox.dennisschwarz.org/htdocs/eqparser/upload/';
  public $characterItems;
  public $inventoryItems;
  public $bankItems;
  public $guildbankItems;
  public $characterName;
  public $dbItems;
  public $filterType = 'character';
  public $excludedSlots = array(
    "all" => array(),
    "character" => array("charm", "ear", "head", "face", "neck", "shoulders", "arms", "back", "wrist", "range", "hands", "primary", "secondary", "fingers", "chest", "legs", "feet", "waist", "ammo", "power source", "held"),
    "inventory" => array("general"),
    "bank" => array("bank", "sharedbank"),
    "guildbank" => array("bank", "sharedbank", "general"),
  );
  public $bagSlots = array("general1", "general2", "general3", "general4", "general5", "general6", "general7", "general8");
  public $bankSlots = array("bank1", "bank2", "bank3", "bank4", "bank5", "bank6", "bank7", "bank8", "bank9", "bank10", "bank11", "bank12", "bank13", "bank14", "bank15", "sharedbank1", "sharedbank2");

  public function __construct() {
    $this->db = Database::getInstance();
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->db->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);

    set_include_path(CLASS_PATH.'/');
    spl_autoload_extensions(spl_autoload_extensions() . ',.class.php');
    spl_autoload_register('SPL_autoload_case_sensitivity');
  }
  public function start() {
    return $this->getReadyState();
  }
  public function getDbRevision() {
    if(!is_null($this->db)) {
      try {
        $dbRevisionResult = $this->db->query("SELECT revision_id FROM `" . $this->getTablePrefix() . "db_revision`");
        $dbRevisionResult = $dbRevisionResult->fetch();
        return (int)$dbRevisionResult['revision_id'];
      } catch (PDOException $e) {
        $this->internalLog[] = 'EQParser::install -> ' . $e->getMessage() . ' - Selecting DB Revision';
        if($this->showDebug) {
          $this->debug();
        }
        return false;
      }
    }
  }
  public function install($override = false) {
    if($this->db instanceof PDO) {
      $this->internalLog[] = "--> installing EqParser DB...";
      $this->internalLog[] =  "--> loading Changeset...";
      require EQP_PATH . '/db/dbChangesets.inc.php';
      $this->internalLog[] = "--> loaded";

      $dbRevision = 0;

      if($override == true) {
        try {
//           $this->db->query("DROP DATABASE IF EXISTS `".DB_NAME."`");
          $this->db->query("CREATE DATABASE IF NOT EXISTS `".DB_NAME."`");
          $this->db->query("USE ".DB_NAME);
          $this->db->query("DROP TABLE IF EXISTS `" . $this->getTablePrefix() . "db_revision`");
          $this->db->query("CREATE TABLE `" . $this->getTablePrefix() . "db_revision` (`revision_id` int(11) NOT NULL DEFAULT '0') ENGINE=InnoDB DEFAULT CHARSET=latin1");
          $this->db->query("INSERT INTO `" . $this->getTablePrefix() . "db_revision` (`revision_id`) VALUES (0)");
        } catch (PDOException $e) {
          $this->internalLog[] = "Main::Install --> SQL ERROR --> " .  $e->getMessage();
          $this->debug();
          if($this->showDebug) {
            $this->debug();
          }
        }
      }

      $dbRevision = $this->getDbRevision();
      ksort($dbChangesets);

      foreach($dbChangesets as $changesetKey => $changeset) {
        if($changesetKey > $dbRevision) {
          // Begin Transaction for possible Rollback
          $this->db->beginTransaction();
          foreach($changeset as $queryKey => $query) {
            $query = str_replace('tbl_', $this->getTablePrefix().'tbl_', $query);
            $query = str_replace('rel_', $this->getTablePrefix().'rel_', $query);
            try {
              $this->db->query($query);
            } catch (PDOException $e) {
              $this->internalLog[] = implode(array(
                'class' => 'Main',
                'function' => 'Install',
                'reason' => $e,
                'query' => $query
              ), ', ');
              if($this->showDebug) {
                $this->debug();
              }
              if($this->db->inTransaction()) $this->db->Rollback();
              return false;
              break;
            }
          }
          // Update Revision
          try {
            $this->db->query("UPDATE `" . $this->getTablePrefix() . "db_revision` SET revision_id = " . $changesetKey);
          } catch (PDOException $e) {
            $this->internalLog[] = implode(array(
              'class' => 'Main',
              'function' => 'Install',
              'reason' => $e,
              'additional Info' => 'Updating DB Revision'
            ), ', ');
            if($this->showDebug) {
              $this->debug();
            }
            if($this->db->inTransaction()) $this->db->Rollback();
            return false;
            break;
          }
          $this->db->commit();
        }
      }
      $this->internalLog[] =  "--> installed!";
    } else {
      $this->internalLog[] = "No Database Connection, Installation aborted.";
    }
  }
  public function execute($object, $command, $arguments) {
    if(class_exists($object)) {
      $class = new $object();
      if(method_exists($class, $command)) {
        return $class->{$command}($arguments);
      }
    }
  }
  public function debug() {
    print '<pre>';
    var_dump(implode($this->internalLog, "\n"));
    print '</pre>';
  }
  public function setDebug($value = false) {
    $this->showDebug = $value;
  }
  public function setErrorMessage($args) {
    $this->errorMessage($args);
  }
  public function logMessage($string) {
    $this->internalLog[] = $string;
    if(isset(self::$logfile) && self::$logfile != '') {
      $logfileHandle = fopen(LOGFILE_PATH . '/' . self::$logfile, 'a+');
      fwrite($logfileHandle, date('Y-m-d H:i:s', time()) . ': ' . $string . '<br>');
      fclose($logfileHandle);
    }
  }
  public function getReadyState() {
    $readyState = false;
    if(!is_null($this->db)) $this->logMessage("DB Connection successful");
    if(($dbRevision = $this->getDbRevision()) > 0) {
      if($this->showDebug) {
        $this->logMessage("DB Revision --> " . $dbRevision);
      }
      require EQP_PATH . '/db/dbChangesets.inc.php';
      if($this->showDebug) {
        $this->logMessage("DB Changeset Revision --> " . max(array_keys($dbChangesets)));
      }
      if($dbRevision < max(array_keys($dbChangesets))) {
        if($this->showDebug) {
          $this->logMessage("DB needs Update!");
        }
      } else {
        if($this->showDebug) {
          $this->logMessage("DB is up to date!");
        }
        $readyState = true;
      }
    }

    if($readyState) {
/*       $this->debug(); */
      return true;
    } else {
      $this->debug();
      return false;
    }
  }
  public function setLogfile($filename) {
    if(isset($filename) && $filename != '') {
      self::$logfile = $filename;
    }
  }
  public function getLogfile() {
    if(isset(self::$logfile) && self::$logfile != '' && file_exists(LOGFILE_PATH . '/' . self::$logfile)) {
      return LOGFILE_DIR . '/' . self::$logfile;
    } else {
      return false;
    }
  }
  public function getTablePrefix() {
    return (defined('TABLE_PREFIX') ? TABLE_PREFIX : '');
  }
  public function parseFile($filename) {
    if(isset($filename) && file_exists(UPLOAD_PATH . '/' . $filename)) {
      $file = file_get_contents(UPLOAD_PATH . '/' . $filename);

      if(strlen($file) > 0) {
        $this->fileRawData = $file;
        $result = array();
        $rows = explode("\n", $file);
        $header = explode("\t", array_shift($rows));
  
        foreach($rows as $rowKey => $row) {
          $item = explode("\t", $row);
          $filterItem = array();
          foreach($item as $colKey => $col) {
            $colName = strtolower($header[$colKey]);

            switch($colName) {
              case 'id':
                $col = (int)trim($col);
              break;

              case 'name':
                $col = trim($col);
              break;
            }
  
            $filterItem[trim($colName)] = $col;
          }
          $result[] = $filterItem;
        }

        if(!empty($result) && count($result) > 0) {
          $this->parsedItems = $result;
          return $this->parseItems();
        }
      }
    }
  }

  public function parseItems($items = array()) {
    if(!empty($items) && count($items) > 0) {
      $this->parsedItems = $items;
    }
    if($this->parsedItems !== null && count($this->parsedItems) > 0) {
      $items = $this->parsedItems;
      $filteredResult = array();

      // Clear old Items
      $this->characterItems = array();
      $this->inventoryItems = array();
      $this->bankItems = array();
      $this->guildbankItems = array();

      foreach($items as $itemKey => $item) {
        if(isset($item['location']) && $item['location'] != '' && $item['location'] !== null) {
          if(($slotCategory = $this->filterSlot($item['location'])) !== false) {
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

      return true;
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
      foreach($items as $itemKey => $item) {
        if((!isset($filter['showEmpty']) || $filter['showEmpty'] != 1) && (isset($item['name']) && strtolower($item['name']) == 'empty')) {
          $item = false;
        }
        if((!isset($filter['showBags']) || $filter['showBags'] != 1) && ($this->filterSlot($item['location']) == 'inventory' || $this->filterSlot($item['location']) == 'bank')) {
          // Check if item is a Bag by comparing Contents
          // Check if Bag or Bank Slot
          if(in_array(strtolower($item['location']), $this->bagSlots) || in_array(strtolower($item['location']), $this->bankSlots)) {
            // Check if Bag or Bank by Comparison
            if(isset($items[($itemKey+1)]) && strpos(strtolower($items[($itemKey+1)]['location']), strtolower($item['location'])) == 0) {
              $item = false;
            }
          }
        }

        if(isset($item) && !empty($item)) {
          $result[] = $item;
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
    $result = array();
    $result['characterName'] = $this->characterName;
    $result['characterItems'] = $this->filterItems($this->characterItems, array());
    $result['inventoryItems'] = $this->filterItems($this->inventoryItems, array());
    $result['bankItems'] = $this->filterItems($this->bankItems, array());

    return $result;
  }
  public function setCharacterName($name = false) {
    if(isset($name) && strlen(trim($name)) > 0) {
      $this->characterName = trim($name);
    }
  }
  public function getDBItems($filter = array()) {
    return $this->dbItems;
  }
}



?>

