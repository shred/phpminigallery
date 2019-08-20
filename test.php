<!DOCTYPE html>
<?php /*
Setup compass, npm, and livereload
---
1. Run Command: compass init
2. Create css and css/scss directories then add them to config.rb
3. Run command: npm init
4. Edit package.json's settings
  "watch": {
    "js_docs_watch": {
      "patterns": [
        "js"
      ],
      "extensions": "js",
      "quiet": false
    }
  }
4. Edit package.json's scripts:
    "watch": "concurrently 'compass watch ./' 'livereload .' 'watch \"npm run js_docs_run\" js/'",
    "js_docs_run": "jsdoc js/*"
5. Run localhost:
    - http-server command
        Drawback: Node does not support Php so no Php extension or code runs. Ignores index.php)
    - MAMP
        a. npm run watch
        b. Activate Livereload browser extension.
*/ ?>
<html lang="en">
  <head>
   <title>Untitled</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <!-- jQuery and Bootstrap  -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
        
    <link href="assets/css/index.css?v=<?php echo time(); ?>" rel="stylesheet">
    <script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
    
</head>
    <body>
        <div class="container">


        </div> <!-- /.container -->
        
        <!-- Designer: Open Sans, Lato, FontAwesome, Waypoints, Skrollr, Pixel-Em-Converter -->
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300|Open+Sans+Condensed:300" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.0/jquery.waypoints.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/skrollr/0.6.30/skrollr.min.js"></script>
        <script src="https://raw.githack.com/filamentgroup/jQuery-Pixel-Em-Converter/master/pxem.jQuery.js"></script>
        
        <!-- Rendering: Handlebars JS, LiveQuery, Sprintf JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.js"></script>
        <script src="https://raw.githack.com/hazzik/livequery/master/src/jquery.livequery.js"></script>
        <script src="https://raw.githack.com/azatoth/jquery-sprintf/master/jquery.sprintf.js"></script>
        
        <!-- Compatibility: Modernizr, jQuery Migrate (check browser) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
        <script src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        
        <!-- Mobile: jQuery UI, jQuery UI Touch Punch -->
        <link href="https://code.jquery.com/ui/1.11.3/themes/ui-lightness/jquery-ui.css" rel="stylesheet"/>
        <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
       
        <!-- Bootstrap JS -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        
        <!-- Friendlier API: ListHandlers, Timeout -->
        <script src="https://raw.githack.com/Inducido/jquery-handler-toolkit.js/master/jquery-handler-toolkit.js"></script>
        <script src="https://raw.githack.com/tkem/jquery-timeout/master/src/jquery.timeout.js"></script>

    </body>
</html>