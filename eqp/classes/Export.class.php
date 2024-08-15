<?php

class Export extends EQParser {
  public function __construct() {
    parent::__construct();
  }
  public function getBBCodeList($items) {
    if(!empty($items) && count($items) > 0) {

      $result = '[longlist]<br>';

      foreach($items as $itemKey => $item) {
        $result .= '&nbsp;&nbsp;[r]<br>';
        $result .= '&nbsp;&nbsp;&nbsp;&nbsp;[cl][zam id=' . $item['id'] . ']' . $item['name'] . '[/zam][/cl]';
        $result .= '[cr]x' . $item['count'] . '[/cr]<br>';
        $result .= '&nbsp;&nbsp;[/r]<br>';
      }

      $result .= '[/longlist]';

      return $result;
    }
  }

  public function getCsvList($items)
  {
    if (!empty($items) && count($items) > 0) {
      $result = 'Name;Link;Count;<br>';
      foreach ($items as $itemKey => $item) {
        $result .= $item['name'] . ';https://items.eqresource.com/items.php?id=' . $item['id'] . ';' . $item['count'] . ';<br>';
      }
      return $result;
    }
  }
}

?>