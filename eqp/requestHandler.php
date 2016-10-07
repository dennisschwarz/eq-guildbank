<?php
require_once('EQParser.php');
$eqParser = new EQParser;

// die(var_dump($_FILES, $_POST));
if(isset($_POST) && !empty($_POST)) {
// var_dump($_POST);
  if(isset($_POST['action'])) {
    switch($_POST['action']) {
      case 'checkProfile':
        if(!($characterInfo = $eqParser->execute('Characters', 'getAll', array('character_name' => $_POST['profileName'])))) {
          $responseHTML = '<div id="response"></div>'
                        . '<p>This Character does not exist in our Database.<br /><br />Do you want to create the proceed and then import the items?</p>'
                        . '<div id="cancel-import" class="button">Cancel</div>'
                        . '<div id="create-and-import" class="button">Create Character and Import</div>';
          die(json_encode(array('success' => false, 'html' => $responseHTML)));
        } else {
          if(count($characterInfo) == 1) {
            $characterInfo = $characterInfo[0];
            $responseHTML = '<div id="response"></div>'
                          . '<p>' . $characterInfo['character_name'] . ' already has items in our Database.<br /><br />Do you want to import and overwrite the items?</p>'
                          . '<div id="cancel-import" class="button">Cancel</div>'
                          . '<div id="erase-and-import" class="button">Import and Overwrite</div>';
            die(json_encode(array('success' => true, 'html' => $responseHTML, 'characterId' => $characterInfo['internal_character_id'])));
          } else {
            // Multiple Characters
          }
        }
      break;

      case 'addCharacterAndImport':
        if(isset($_POST['profileName']) && $_POST['profileName'] != '') {
          $args = array(
            'character_name' => $_POST['profileName'],
            'server_name' => 'Phinigel',
          );

          if(($characterInfo = $eqParser->execute('Characters', 'add', $args)) !== false) {
            if(isset($_POST['items']) && !empty($_POST['items'])) {
              $addArgs = array(
                'internal_character_id' => $characterInfo['internal_character_id'],
                'clean_import' => true,
                'items' => array()
              );

              // Iterate each Item
              foreach($_POST['items'] as $itemKey => $item) {
                // Prepare first:
                // Check if Items already exists in DB, else add
                if(($itemResult = $eqParser->execute('Items', 'getAll', array('external_item_id' => (int)$item['id']))) !== false) {
                } else {
                  $addArgs['items'][] = array(
                    'external_item_id' => $item['id'],
                    'item_name' => $item['name'],
                    'item_location' => $item['location'],
                    'internal_character_id' => $characterInfo['internal_character_id'],
                    'item_count' => $item['count']
                  );
                }
                // Check if Slot exists in DB, else add
                // Add item / char relation
              }

              // All Items imported
              if($eqParser->execute('Items', 'importAll', $addArgs)) {
                $responseHTML = '<div id="response">'
                              . '<p>All items have been successfully imported</p>'
                              . '<div id="finish-import" class="button">Ok</div>'
                              . '</div>';

                die(json_encode(array('success' => true, 'response' => $responseHTML)));
              }
            } else {
              die('No items to import');
            }
          } else {
            die('Could not add Character');
          }
        }
        
      break;

      case 'eraseAndImport':
        if(isset($_POST['characterId']) && (int)$_POST['characterId'] > 0 && ($characterInfo = $eqParser->execute('Characters', 'getOne', (int)$_POST['characterId'])) !== false) {
          // Delete Items
          if($eqParser->execute("Items", "delete", array('internal_character_id' => $characterInfo['internal_character_id']))) {
            if(isset($_POST['items']) && !empty($_POST['items'])) {
              $addArgs = array(
                'internal_character_id' => $characterInfo['internal_character_id'],
                'clean_import' => true,
                'items' => array()
              );
              
              // Iterate each Item
              foreach($_POST['items'] as $itemKey => $item) {
                // Prepare first:
                // Check if Items already exists in DB, else add
                if(($itemResult = $eqParser->execute('Items', 'getAll', array('external_item_id' => (int)$item['id']))) !== false) {
                } else {
                  $addArgs['items'][] = array(
                    'external_item_id' => $item['id'],
                    'item_name' => $item['name'],
                    'item_location' => $item['location'],
                    'internal_character_id' => $characterInfo['internal_character_id'],
                    'item_count' => $item['count']
                  );
                }
                // Check if Slot exists in DB, else add
                // Add item / char relation
              }

              // All Items imported
              if($eqParser->execute('Items', 'importAll', $addArgs)) {
                $responseHTML = '<div id="response">'
                              . '<p>All items have been successfully imported</p>'
                              . '<div id="finish-import" class="button">Ok</div>'
                              . '</div>';

                die(json_encode(array('success' => true, 'response' => $responseHTML)));
              }
            }
          } else {
            // Could not delete
          }
        }
      break;

      case 'getItems':
        if(isset($_POST['characterId']) && (int)$_POST['characterId'] > 0 && ($characterInfo = $eqParser->execute('Characters', 'getOne', (int)$_POST['characterId'])) !== false) {
          $getArgs = array(
            'internal_character_id' => (int)$_POST['characterId'],
            'group_by' => 'external_item_id',
          );

          if((isset($_POST['field']) && $_POST['field'] != '') && (isset($_POST['direction']) && $_POST['direction'] != '')) {
            $getArgs['order_by'] = array(
              'field' => filter_var(trim($_POST['field']), FILTER_SANITIZE_STRING),
              'direction' => filter_var(trim($_POST['direction']), FILTER_SANITIZE_STRING),
            );
          }

          if(($items = $eqParser->execute('Items', 'getAll', $getArgs)) !== false) {
            $eqParser->setCharacterName($characterInfo['character_name']);
            $eqParser->parseItems($items);

            die(json_encode($eqParser->getAllItems(array('unfiltered' => true))));
          }
        }
      break;

      case 'getCharacters':
        if(($characters = $eqParser->execute('Characters', 'getAll', array())) !== false) {
          die(json_encode(array('success' => 'true', 'characters' => $characters)));
        } else {
          die(json_encode(array('success' => 'false', 'errorMsg' => 'Could not load Characters')));
        }
      break;
    }
  }

} else if(isset($_FILES) && !empty($_FILES)) {
  if(isset($_FILES['inventory-file'])) {
    $uploadedFile = $_FILES['inventory-file'];

    if($uploadedFile['error'] == 0 && $uploadedFile['type'] == 'text/plain' && $uploadedFile['size'] > 0) {
      $newFilename = time() . '_' . $uploadedFile['name'];
      move_uploaded_file($uploadedFile['tmp_name'], UPLOAD_PATH . '/' . $newFilename);

      $filenameParts = explode('-', $uploadedFile['name']);
      $eqParser->setCharacterName($filenameParts[0]);

      if($eqParser->parseFile($newFilename)) {
        die(json_encode($eqParser->getAllItems()));
      }
    }
  }
}


?>