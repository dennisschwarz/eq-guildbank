<?php

session_start();
require 'eqp/EQParser.php';
$eqp = new EQParser();
$eqp->install();
$eqp->setLogfile('log.txt');
//$eqp->debug();

?>


<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>EverQuest Parser</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">

<!--         <link rel="stylesheet" href="css/normalize.min.css"> -->
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
        <link rel="stylesheet" href="css/main.css">
        <script>
          var rootUrl = '<?php print ROOT_URL; ?>';
          var uploadDir = '<?php print UPLOAD_DIR; ?>';
        </script>
        <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
    </head>
    <body>
        <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        <!-- Modal -->
        <!-- need Admin Rights Check -->

        <div id="upload-modal" class="modal fade" role="dialog">
          <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
              <div class="modal-body">
                <div id="file-upload-container"><h4>Drop File Here!</h4></div>
                <div id="parse-result"></div>
              </div>
            </div>
          </div>
        </div>

        <div id="query-modal" class="modal fade" role="dialog">
          <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
              <div class="modal-body"></div>
            </div>
          </div>
        </div>

        <div id="export-modal" class="modal fade" role="dialog">
          <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
              <div class="modal-header">
                <div class="navbar">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <select id="select-export-option">
                    <option value="0">Please select Export Option</option>
                    <option value="bbcode">phpBB Code</option>
                    <option value="csv">CSV File</option>
                  </select>
                </div>
              </div>
              <div class="modal-body">
                <div>
                  <div id="select-export-and-copy" class="button btn">Copy to Clipboard</div>
                </div>
                <pre id="export-result"></pre>
              </div>
            </div>
          </div>
        </div>

        <div class="header-container">
            <header class="wrapper clearfix">
                <h1 class="title col-xs-8">EQ Parser</h1>
                <nav class="col-xs-4">
                    <ul>
                        <li class="col-xs-6"><a href="#">List Items</a></li>
                        <li class="col-xs-6"><a id="import-dialog">Import Items</a></li>
                    </ul>
                </nav>
            </header>
        </div>

        <div class="main-container">
          <div class="main wrapper clearfix">
            <div id="item-listing" class="row">
              <?php if(($characters = $eqp->execute("Characters", "getAll", array())) !== false) { ?>
              <select id="filter-by-character" class="col-xs-12 col-sm-4 col-sm-offset-8">
              <option value="0">Please select ...</option>
              <?php foreach($characters as $characterKey => $character) { ?>
                <option value="<?php print $character['internal_character_id']; ?>"><?php print $character['character_name']; ?></option>
              <?php } ?>
              </select>
              <?php } else { ?>
              <select id="filter-by-character" class="col-xs-12 col-sm-4 col-sm-offset-8">
                <option value="0">No Characters found</option>
              </select>
<!--               <div id="no-characters" class="col-xs-12 col-sm-4 col-sm-offset-8">No Characters found</div> -->
              <?php } ?>
            </div>
            <div id="result-list" class="row"></div>
          </div> <!-- #main -->
        </div> <!-- #main-container -->

        <div class="footer-container">
            <footer class="wrapper">
                <h3>footer</h3>
            </footer>
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>
        <script type="text/javascript" src="http://zam.zamimg.com/j/tooltips.js"></script>
        <!-- Optional theme -->
<!--         <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous"> -->

        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
        <script src="js/vendor/dropzone.min.js"></script>
        <script src="js/main.js"></script>

        <!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
<!--
        <script>
            (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
            function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
            e=o.createElement(i);r=o.getElementsByTagName(i)[0];
            e.src='//www.google-analytics.com/analytics.js';
            r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
            ga('create','UA-XXXXX-X','auto');ga('send','pageview');
        </script>
-->
    </body>
</html>