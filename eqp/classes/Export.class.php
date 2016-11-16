<?php

class Export extends EQParser {
  public function __construct() {
    parent::__construct();
  }
  public function getBBCodeList($items) {
    if(!empty($items) && count($items) > 0) {

      $result = '[lalelist]<br>';

      foreach($items as $itemKey => $item) {
        $result .= '&nbsp;&nbsp;[r]<br>';
        $result .= '&nbsp;&nbsp;&nbsp;&nbsp;[cl][zam id=' . $item['id'] . ']' . $item['name'] . '[/zam][/cl]';
        $result .= '[cr]x' . $item['count'] . '[/cr]<br>';
        $result .= '&nbsp;&nbsp;[/r]<br>';
      }

      $result .= '[/lalelist]';

      return $result;
    }
  }
}

?>