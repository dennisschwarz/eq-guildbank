<?php

  /* Basic Config Information for the EQ Parser Module */
  if(!defined('DB_NAME')) define('DB_NAME', '');
  if(!defined('DB_USER')) define('DB_USER', '');
  if(!defined('DB_PASSWORD')) define('DB_PASSWORD', '');
  if(!defined('DB_HOST')) define('DB_HOST', 'localhost');
  if(!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8');
  if(!defined('DB_COLLATE')) define('DB_COLLATE', '');

  // Base Values
  if(!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));
  if(!defined('VENDOR_PATH')) define('VENDOR_PATH', BASE_PATH.'/vendor');
  if(!defined('EQP_PATH')) define('EQP_PATH', BASE_PATH);
  if(!defined('CLASS_PATH')) define('CLASS_PATH', EQP_PATH.'/classes');
  if(!defined('LOGFILE_PATH')) define('LOGFILE_PATH', EQP_PATH.'/logs');
  if(!defined('UPLOAD_PATH')) define('UPLOAD_PATH', EQP_PATH.'/upload');
  
  if(!defined('ROOT_URL')) define('ROOT_URL', 'http://link.to.your.website/and.the.index.of.this.module');
  if(!defined('UPLOAD_DIR')) define('UPLOAD_DIR', ROOT_URL . '/wsc/upload');
  if(!defined('LOGFILE_DIR')) define('LOGFILE_DIR', ROOT_URL . '/wsc/logs');
  
  if(!defined('DEFAULT_LANGUAGE_ID')) define('DEFAULT_LANGUAGE_ID', 1);

?>