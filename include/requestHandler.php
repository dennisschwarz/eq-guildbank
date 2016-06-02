<?php
// die(var_dump($_FILES, $_POST));
if(isset($_POST) && !empty($_POST)) {
  var_dump('hier');

} else if(isset($_FILES) && !empty($_FILES)) {
  if(isset($_FILES['inventory-file'])) {
    $uploadedFile = $_FILES['inventory-file'];

    if($uploadedFile['error'] == 0 && $uploadedFile['type'] == 'text/plain' && $uploadedFile['size'] > 0) {
      $newFilename = time() . '_' . $uploadedFile['name'];
      move_uploaded_file($uploadedFile['tmp_name'], '../upload/' . $newFilename);
      require_once('EQParser.php');
      $eqParser = new EQParser;

      if($eqParser->parseFile($newFilename)) {
        die(json_encode($eqParser->getAllItems()));
      }
    }
  }

}


?>