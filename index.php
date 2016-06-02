<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">

        <link rel="stylesheet" href="css/normalize.min.css">
        <link rel="stylesheet" href="css/main.css">

        <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
    </head>
    <body>
        <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        <div class="header-container">
            <header class="wrapper clearfix">
                <h1 class="title">h1.title</h1>
                <nav>
                    <ul>
                        <li><a href="#">nav ul li a</a></li>
                        <li><a href="#">nav ul li a</a></li>
                        <li><a href="#">nav ul li a</a></li>
                    </ul>
                </nav>
            </header>
        </div>

        <div class="main-container">
            <div class="main wrapper clearfix">
              <?php
              require_once('include/EQParser.php');
              $eqParser = new EQParser;
              $charInv = file_get_contents('./upload/Caerbank-Inventory.txt');
              $eqParser->parseFile($charInv);
              $characterItems = $eqParser->getCharacterItems();
              $inventoryItems = $eqParser->getInventoryItems();
              $bankItems = $eqParser->getBankItems();
              ?>

              <h4>Equipped Items:</h4>
              <?php if(!empty($characterItems) && count($characterItems) > 0): ?>
                <ul id="inventory">
                  <?php foreach($inventoryItems as $itemKey => $item): ?>
                    <?php if(isset($item['ID']) && (int)$item['ID'] > 0): ?>
                      <li><a href="http://lucy.allakhazam.com/itemraw.html?id=<?php print $item['ID']; ?>"><?php print $item['Location'] . ' - ' . ((int)$item['Count'] > 1 ? $item['Count'] . 'x ' : '') . $item['Name']; ?></a></li>
                    <?php else: ?>
                      <li><?php print $item['Location'] . ' - ' . $item['Name']; ?></li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>

              <h4>Inventory Items:</h4>
              <?php if(!empty($inventoryItems) && count($inventoryItems) > 0): ?>
                <ul id="inventory">
                  <?php foreach($inventoryItems as $itemKey => $item): ?>
                    <?php if(isset($item['ID']) && (int)$item['ID'] > 0): ?>
                      <li><a href="http://lucy.allakhazam.com/itemraw.html?id=<?php print $item['ID']; ?>"><?php print $item['Location'] . ' - ' . ((int)$item['Count'] > 1 ? $item['Count'] . 'x ' : '') . $item['Name']; ?></a></li>
                    <?php else: ?>
                      <li><?php print $item['Location'] . ' - ' . $item['Name']; ?></li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>

              <h4>Bank Items:</h4>
              <?php if(!empty($bankItems) && count($bankItems) > 0): ?>
                <ul id="inventory">
                  <?php foreach($bankItems as $itemKey => $item): ?>
                    <?php if(isset($item['ID']) && (int)$item['ID'] > 0): ?>
                      <li><a href="http://lucy.allakhazam.com/itemraw.html?id=<?php print $item['ID']; ?>"><?php print $item['Location'] . ' - ' . ((int)$item['Count'] > 1 ? $item['Count'] . 'x ' : '') . $item['Name']; ?></a></li>
                    <?php else: ?>
                      <li><?php print $item['Location'] . ' - ' . $item['Name']; ?></li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
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