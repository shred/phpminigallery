<?php
  /**
   * The PHP Mini Gallery V1.2
   * (C) 2008 Richard "Shred" K�rber -- all rights reserved
   * http://www.shredzone.net/go/minigallery
   *
   * Requirements: PHP 4.1 or higher, GD (GD2 recommended) or ImageMagick
   *
   * This software is free software; you can redistribute it and/or modify
   * it under the terms of the GNU General Public License as published by
   * the Free Software Foundation; either version 2 of the License, or
   * (at your option) any later version.
   *
   * This program is distributed in the hope that it will be useful,
   * but WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   * GNU General Public License for more details.
   *
   * You should have received a copy of the GNU General Public License
   * along with this program; if not, write to the Free Software
   * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
   *
   * $Id: index.php,v 1.5 2003/12/07 14:14:20 shred Exp $
   */
   
  /*=== CONFIGURATION ===*/
  $CONFIG = array();
  $CONFIG['thumb.width']    = 100;      // Thumbnail width (pixels)
  $CONFIG['thumb.height']   = 100;      // Thumbnail height (pixels)
  $CONFIG['pic.width']    = 300;      // Thumbnail width (pixels)
  $CONFIG['pic.height']   = 300;      // Thumbnail height (pixels)
  $CONFIG['thumb.scale']    = 'gd2';    // Set to 'gd2', 'im' or 'gd'
  $CONFIG['tool.imagick']   = '/usr/bin/convert';   // Path to convert
  $CONFIG['index.cols']     = 6;        // Colums per row on index print
  $CONFIG['template']       = 'template.php';       // Template file
  $path = "img";

  /*=== VALIDATION ===*/
  try {
    $try = @opendir(dirname(__FILE__) . '/' . $path);
    if(is_null($try) || $try===false) {
      die("<br/>Error: Can't open subfolder \"$path\". Does it exist? Is permission set right?");
    }
  } catch (Exception $e) {
    die($e);
  }
  
  /*=== SHOW A THUMBNAIL? ===*/
    // var_dump(is_file($path . "/" . $file));
    // var_dump(is_readable($path . "/" . $file));
  if(isset($_GET['thumb'])) {
    $file = trim($_GET['thumb']);
    //--- Protect against hacker attacks ---
    // if(preg_match('#\.\.|/#', $file)) die("Illegal characters in path!");
    $thfile = "$path/" . 'th_'.substr($file, strlen($path)+1).'.jpg';
    //--- Get the thumbnail ---;
    // var_dump(($file));
    // var_dump(($file));
    // die();
    if(is_file($file) && is_readable($file)) {
      // die("here");
      //--- Check if the thumbnail is missing or out of date ---
      if(!is_file($path . "/" . $thfile) || (filemtime($path . "/" . $file)>filemtime($path . "/" . $thfile))) {
        // die("c");
        //--- Get information about the image ---
        $aySize = getimagesize($file);
        if(!isset($aySize)) die("Picture $file not recognized...");
        //--- Compute the thumbnail size, keep aspect ratio ---
        $srcWidth = $aySize[0];  $srcHeight = $aySize[1];
        if($srcWidth==0 || $srcHeight==0) {   // Avoid div by zero
          $thWidth  = 0;
          $thHeight = 0;
        }else if($srcWidth > $srcHeight) {    // Landscape
          $thWidth  = $CONFIG['thumb.width'];
          $thHeight = round(($CONFIG['thumb.width'] * $srcHeight) / $srcWidth);
        }else {                               // Portrait
          $thWidth  = round(($CONFIG['thumb.height'] * $srcWidth) / $srcHeight);
          $thHeight = $CONFIG['thumb.height'];
        }
        //--- Get scale mode ---
        $scmode = strtolower($CONFIG['thumb.scale']);
        //--- Create source image ---
        if($scmode!='im') {
          // die(dirname(__FILE__) . $file);
          switch($aySize[2]) {
            case 1:  $imgPic = imagecreatefromgif($file);  break;
            case 2:  $imgPic = imagecreatefromjpeg($file); break;
            case 3:  $imgPic = imagecreatefrompng($file);  break;
            default: die("Picture $file must be either JPEG, PNG or GIF..."); 
          }
        }
        // die("d");
        //--- Scale it ---
        switch($scmode) {
          case 'gd2':     // GD2
            $imgThumb = imagecreatetruecolor($thWidth, $thHeight);
            imagecopyresampled($imgThumb, $imgPic, 0,0, 0,0, $thWidth,$thHeight, $srcWidth,$srcHeight);
            break;
          case 'gd':      // GD
            $imgThumb = imagecreate($thWidth,$thHeight);
            imagecopyresized($imgThumb, $imgPic, 0,0, 0,0, $thWidth,$thHeight, $srcWidth,$srcHeight);
            break;
          case 'im':      // Image Magick
            exec(sprintf(
              '%s -geometry %dx%d -interlace plane %s jpeg:%s',
              $CONFIG['tool.imagick'],
              $CONFIG['thumb.width'],
              $CONFIG['thumb.height'],
              $file,
              "$path/" . "th_" . substr($file, strlen($path)+1)
            ));    
            break;
          default:
            die("Unknown scale mode ".$CONFIG['thumb.scale']);
        }
        
        //--- Save it ---
        if($scmode!='im') {
          imagejpeg($imgThumb, $thfile);
          imagedestroy($imgPic);
          imagedestroy($imgThumb);
        }
      }

      //--- Check if there is an if-modified-since header ---
      $fileModified = date('D, d M Y H:i:s \G\M\T', filemtime($thfile));
      if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE']==$fileModified) {
        header('HTTP/1.0 304 Not Modified');
        exit();
      }
      
      //--- Send the thumbnail to the browser ---
      session_cache_limiter('');
      header('Content-Type: image/jpeg');
      header("Content-Length: ".filesize($thfile));
      header("Last-Modified: $fileModified");
      readfile($thfile);
      exit();
    }else {
      //--- Tell there is no image like that ---
      if(is_file($path))
      header('HTTP/1.0 404 Not Found');
      print("Sorry, this picture $thfile was not found or you have zero pictures in the folder.");
      unlink($thfile); // Delete a matching thumbnail file
      exit();
    }
  }
  
  /*=== CREATE CONTEXT ===*/
  $CONTEXT = array();
  
  /*=== GET FILE LISTING ===*/
  $ayFiles = array();
  // die(dirname(__FILE__));
  $dh = opendir(dirname(__FILE__) . '/' . $path);
  while(($file = readdir($dh)) !== false) {
    if($file{0}=='.') continue;                     // No dirs and temp files
    if(substr($file,0,3) == 'th_') continue;        // No thumbnails
    // var_dump($file);
    if(preg_match('#\.(jpe?g|png|gif)$#i', $file)) {
      if(is_file($path . "/" . $file) && is_readable($path . "/" . $file)) {
        $ayFiles[] = $path . "/" . $file;
      }
    }
  }
  sort($ayFiles);
  $CONTEXT['count'] = count($ayFiles);
  $CONTEXT['files'] =& $ayFiles;
  
  /*=== SHOW A PICTURE? ===*/
  if(isset($_GET['pic'])) {
    $file = trim($_GET['pic']);
    //--- Check existence ---
    if(!(is_file($file) && is_readable($file))) {
      header('HTTP/1.0 404 Not Found');
      print('Sorry, this picture was not found');
      exit();
    }
    $CONTEXT['page'] = 'picture';
    //--- Find our index ---
    $index = array_search($file, $ayFiles);
    if(!isset($index) || $index===false) die("Invalid picture $file");
    $CONTEXT['current'] = $index+1;
    //--- Get neighbour pictures ---
    $CONTEXT['first']   = $ayFiles[0];
    $CONTEXT['last']    = $ayFiles[count($ayFiles)-1];
    if($index>0)
      $CONTEXT['prev']  = $ayFiles[$index-1];
    if($index<count($ayFiles)-1)
      $CONTEXT['next']  = $ayFiles[$index+1];
    //--- Assemble the content ---
    // list($pWidth,$pHeight) = getimagesize($file);
    $pWidth = $CONFIG['pic.width'];
    $pHeight = $CONFIG['pic.height']; 
    $page = sprintf(
      '<img class="picimg" src="%s" width="%s" height="%s" alt="#%s" border="0" />',
      htmlspecialchars($file),
      htmlspecialchars($pWidth),
      htmlspecialchars($pHeight),
      htmlspecialchars($index+1)
    );
    if(isset($CONTEXT['next'])) {
      $page = sprintf('<a href="index.php?pic=%s">%s</a>', htmlspecialchars($CONTEXT['next']), $page);
    }
    $CONTEXT['pictag'] = $page;
    if(is_file($path . "/" . $file.'.txt') && is_readable($path . "/" . $file.'.txt')) {
      $CONTEXT['caption'] = join('', file($path . "/" . $file.'.txt'));
    }
  } // $_GET['pic']
  
  /*=== SHOW INDEX PRINT ===*/
  else{
    //--- Set context ---
    $CONTEXT['page']  = 'index';
    $CONTEXT['first'] = @$ayFiles[0];
    $CONTEXT['last']  = @$ayFiles[count($ayFiles)-1];
  }

  //--- Assemble the index table ---
  // $page = '<table class="tabindex">'."\n";
  $page = "";
  $cnt  = 0;
  foreach($ayFiles as $key=>$file) {
    //if($cnt % $CONFIG['index.cols'] == 0) $page .= '<tr>';
    $col_num = $CONFIG['index.cols']>12 ? "1":strval(12/$CONFIG['index.cols']);
    if($cnt % $CONFIG['index.cols'] == 0) $page .= '<div class="row">';
    $page .= sprintf(
      '<div class="%s">
        <a href="index.php?pic=%s">
          <figure>
            <img class="thumbimg" src="index.php?thumb=%s" alt="#%s" border="0" width="%s" height="%s" />
            <figcaption>%s</figcaption>
          </figure>
        </a>
      </div> <!--/ column -->',
      "col-md-" . $col_num,
      htmlspecialchars($file),
      htmlspecialchars($file),
      htmlspecialchars($key+1),
      $CONFIG['thumb.width'],
      $CONFIG['thumb.height'],
      preg_replace("#.png|.jpg|$path/#", "", $file)
    );
    // var_dump($file);
    // die();
    // $ayFiles["files"][ $ayFiles["current"]-1 ]
    $cnt++;
    if($cnt % $CONFIG['index.cols'] == 0) $page .= '</div> <!--/ row -->'."\n";
  } // foreach
  //--- Fill empty cells in last row ---
  $close = false;
  while($cnt % $CONFIG['index.cols'] != 0) {
    // $page .= '<td>&nbsp;</td>';
    $close = true;
    $cnt++;
  }
  // if($close) $page .= '</div>'."\n";
  //--- Set content ---
  $CONTEXT['indextag'] = $page;
  
  /*=== GET TEMPLATE CONTENT ===*/
  ob_start();
  require($CONFIG['template']);
  $template = ob_get_contents();
  ob_end_clean();
  
  /*=== REMOVE UNMATCHING SECTION ===*/
  if($CONTEXT['page']=='index') {
    $template = preg_replace('#<pmg:if\s+page="picture">.*?</pmg:if>#s', '', $template);
    $template = preg_replace('#<pmg:if\s+page="index">(.*?)</pmg:if>#s', '$1', $template);
  }else {
    $template = preg_replace('#<pmg:if\s+page="index">.*?</pmg:if>#s', '', $template);
    $template = preg_replace('#<pmg:if\s+page="picture">(.*?)</pmg:if>#s', '$1', $template);
  }
  
  /*=== REPLACE TEMPLATE TAGS ===*/
  //--- Always present neighbour links ---
  $aySearch  = array(
    '<pmg:first>', '</pmg:first>',
    '<pmg:last>', '</pmg:last>',
    '<pmg:toc>', '</pmg:toc>'
  );
  $ayReplace = array();
  $ayReplace[] = sprintf('<a href="index.php?pic=%s">', htmlspecialchars($CONTEXT['first']));
  $ayReplace[] = '</a>';
  $ayReplace[] = sprintf('<a href="index.php?pic=%s">', htmlspecialchars($CONTEXT['last']));
  $ayReplace[] = '</a>';
  $ayReplace[] = '<a href="index.php">';
  $ayReplace[] = '</a>';
  $template = str_replace($aySearch, $ayReplace, $template);

  //--- Link to previous picture ---
  if(isset($CONTEXT['prev'])) {
    $aySearch  = array('<pmg:prev>', '</pmg:prev>');
    $ayReplace = array(
      sprintf('<a href="index.php?pic=%s">', htmlspecialchars($CONTEXT['prev'])),
      '</a>'
    );
    $template = str_replace($aySearch, $ayReplace, $template);
  }else {
    $template = preg_replace('#<pmg:prev>.*?</pmg:prev>#s', '', $template);
  }

  //--- Link to next picture ---
  if(isset($CONTEXT['next'])) {
    $aySearch  = array('<pmg:next>', '</pmg:next>');
    $ayReplace = array(
      sprintf('<a href="index.php?pic=%s">', htmlspecialchars($CONTEXT['next'])),
      '</a>'
    );
    $template = str_replace($aySearch, $ayReplace, $template);
  }else {
    $template = preg_replace('#<pmg:next>.*?</pmg:next>#s', '', $template);
  }
  
  //--- Image, Index Print, Caption ---
  $aySearch  = array('<pmg:image/>', '<pmg:index/>', '<pmg:caption/>', '<pmg:count/>', '<pmg:current/>');
  $ayReplace = array(
    (isset($CONTEXT['pictag'])   ? $CONTEXT['pictag']   : ''),
    (isset($CONTEXT['indextag']) ? $CONTEXT['indextag'] : ''),
    (isset($CONTEXT['caption'])  ? $CONTEXT['caption']  : ''),
    $CONTEXT['count'],
    (isset($CONTEXT['current'])  ? $CONTEXT['current']  : ''),
  );
  $template = str_replace($aySearch, $ayReplace, $template);
  
  /*=== PRINT TEMPLATE ===*/
  // ob_start('ob_gzhandler');
  print($template);
  print("\n".'<!-- Created by PHP Mini Gallery, (C) Richard Shred Koerber, https://github.com/shred/phpminigallery -->'."\n");
  exit();
?>
</body></html>