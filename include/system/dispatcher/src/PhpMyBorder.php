<?php
/*
   _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _
  |                                                           |
  |                        DISCLAIMER                         |
  |                                                           |
  |              PhpMyBorder is provided "as is",             |
  |               without warranty of any kind,               |
  |                either express or implied.                 |
  |_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _|


  *************************************************************
  || PhpMyBorder v2.0             http://www.phpmyborder.com ||
  *************************************************************
  ||                                                         ||
  || This class is able to generates 3 types of borders.     ||
  ||                                                         ||
  ||  - ROUND    (rounded corners)                           ||
  ||  - RAISED  (rounded corners with 3D effect)             ||
  ||  - SHADOW   (square corners)                            ||
  ||                                                         ||
  ||                                                         ||
  || Enjoy!                                                  ||
  ||_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _||
  ||_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_||
                                      Author : Vidar Vestnes


  -- changelog --

   1. dec 2005  added raised (3D) and shadowed border support.
  25. nov 2005  first version.


  About PhpMyBorder :

  At the moment PhpMyBorder supports 3 shapes (round, raised and shadow).
  The bordereffects are achived by using plain HTML / XHTML and CSS styling.
  No images or tables are used in the output code. This results in faster
  downloading and rendering of your page compared to other techniques.
  The code is freely distributed and may be changed and copied.

  Please give feedback if you have any suggestions to make the code better.

    phpmyborder@gmail.com


  HOW TO USE:

  First include phpMyBorder2.class.php to your script:

    include_once('phpMyBorder2.class.php');

    // Create a bordergenerator:
    $pmb = new PhpMyBorder();  // or new PhpMyBorder(true), read about stylesheet-support below


  Add a ROUND border (round corners):

    $pmb->begin_round("250px", "DDEEFF", "005555"); // width, fillcolor, edgecolor
    echo "Content...";
    $pmb->end_round(); //


  Add a RAISED border (round corners):

    $pmb->begin_raised("100%", "DDEEFF"); // width, fillcolor
    echo "Content...";
    $pmb->end_raised();


  Add a SHADOW border (square corners):

    $pmb->begin_shadow("250px", "DDEEFF","000000","555577");  //  width, fillcolor, edgecolor, shadowcolor
    echo "Content...";
    $pmb->end_shadow();




  USING STYLESHEET:

  To save styling-overhead you may use PhpMyBorder with stylesheet.

  Tell PhpMyBorder to generate code depending on stylesheet by using argument true in the constructor.

    $pmb = new PhpMyBorder(true);


  And  simply add these lines to your existing stylesheet.

  Round border (add following 8 lines):

    .pmb1-b, .pmb1-s {font-size:1px; }
    .pmb1-1, .pmb1-2, .pmb1-3, .pmb1-4, .pmb1-b, .pmb1-s {display:block; overflow:hidden;}
    .pmb1-1, .pmb1-2, .pmb1-3, .pmb1-s {height:1px;}
    .pmb1-2, .pmb1-3, .pmb1-4 {border-style: solid; border-width: 0 1px; }
    .pmb1-1 {margin:0 5px; }
    .pmb1-2 {margin:0 3px; border-width:0 2px;}
    .pmb1-3 {margin:0 2px;}
    .pmb1-4 {height:2px; margin:0 1px;}
    .pmb1-c {display:block; border-style: solid ; border-width: 0 1px;}


  Raised border (add following 17 lines) :

    .pmb2-1, .pmb2-2, .pmb2-3, .pmb2-4, .pmb2-5, .pmb2-6, .pmb2-7, .pmb2-8 { overflow:hidden; font-size:1px; display:block; }
    .pmb2-1, .pmb2-2, .pmb2-3, .pmb2-6, .pmb2-7, .pmb2-8, .pmb2-s { height:1px; }
    .pmb2-2, .pmb2-3, .pmb2-4, .pmb2-5, .pmb2-6, .pmb2-7, .pmb2-c {  border-style: solid; border-width: 0 1px; }
    .pmb2-2, .pmb2-3, .pmb2-4, .pmb2-c { border-left-color: #fff; }
    .pmb2-7, .pmb2-6, .pmb2-5, .pmb2-c { border-right-color: #999; }
    .pmb2-1 { margin:0 5px; background: #fff;}
    .pmb2-2 { border-right:1px solid #eee; }
    .pmb2-3 { border-right:1px solid #ddd; }
    .pmb2-4 { border-right:1px solid #aaa; }
    .pmb2-5 { border-left:1px solid #eee; }
    .pmb2-6 { border-left:1px solid #ddd; }
    .pmb2-7 { border-left:1px solid #aaa; }
    .pmb2-8 { margin:0 5px; background:#999; }
    .pmb2-2, .pmb2-7 { margin:0 3px; border-width:0 2px; }
    .pmb2-3, .pmb2-6 { margin:0 2px; }
    .pmb2-4, .pmb2-5 { margin:0 1px; height:2px; }
    .pmb2-c { padding: 0 4px; display:block; }
    .pmb2-s {display : block; font-size:1px;}


  Shadow border (add following 2 lines):

    .pmb3-1 { border-width: 1px; border-style: solid; position: relative; left:-3px; top:-3px; }
    .pmb3-2 { overflow:hidden; width:100%; padding:0 3px; }
    .pmb3-s { height: 1px; font-size: 1px; display: block; }




  More examples:

  ------ example yourpage1.php -----------
  <?
  include_once('phpMyBorder2.class.php');
  $pmb = new PhpMyBorder(true);  // using stylesheet

  echo $pmb -> begin_round("300px","DDEEFF","000000"); //  (width, fillcolor, edgecolor)
  echo "content...";
  echo $pmb -> end_round();
  ?>
  ------------------------------


  ------ example yourpage2.php -----------
  <?
  include_once('phpMyBorder2.class.php');
  $pmb = new PhpMyBorder();

  echo $pmb -> begin_raised("300px","DDEEFF"); //  (width, fillcolor)
  echo "content...";
  echo $pmb -> end_raised();
  ?>
  ------------------------------


  ------ example yourpage3.php -----------
  <?
  include_once('phpMyBorder2.class.php');
  $pmb = new PhpMyBorder(true);    // using stylesheet

  echo $pmb -> begin_shadow("300px","DDEEFF","000000","555555"); //  (width, fillcolor, edgecolor, shadowcolor)
  echo "content...";
  echo $pmb -> end_shadow();
  ?>
  ------------------------------




  Allways check for the newest version at :

    http://www.phpmyborder.com

*/
namespace dispatch;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

class PhpMyBorder{

  var $width;        // width of the border
  var $fill;         // fillcolor
  var $edge;         // edgecolor
  var $shadow;       // shadowcolor
  var $stylesheet;   // using stylesheet or not

  function __construct($stylesheet = false){
    $this->setWidth("100%");           // default width
    $this->setFill("6EBE89");          // default fillcolor
    $this->setEdge("3A3AC1");          // default edgecolor
    $this->setShadow("888888");        // default shadowcolor
    $this->stylesheet = $stylesheet;   // using stylesheet (default = false)
  }

  function setWidth($value){
    $this->width = trim($value);
  }

  function getWidth(){
    return $this->width;
  }

  function setFill($value){
    $this->fill = trim($value);
  }

  function getFill($prefix = false)  {
    if(!$prefix) return $this->fill;
    if(strlen($this->fill)<3) return "transparent";
    return strtolower($this->fill) == "transparent" ? "transparent" : "#".$this->fill;
  }

  function setEdge($value)  {
    $this->edge = trim($value);
  }

  function getEdge($prefix = false)  {
    if(!$prefix) return $this->edge;

    if(  $this->edge===false
        ||
        strlen($this->edge)<3
        ||
        strtolower($this->edge) == "transparent"
    ) return "transparent";

    return "#".$this->edge;
  }

  function setShadow($value)  {
    $this->shadow = trim($value);
  }

  function getShadow($prefix = false)  {
    if(!$prefix) return $this->shadow;
    if(strlen($this->shadow)<3) return "transparent";
    return strtolower($this->shadow) == "transparent" ? "transparent" : "#".$this->shadow;
  }


  function stylesheet_round(){
?>
    .pmb1-b, .pmb1-s {font-size:1px; }
    .pmb1-1, .pmb1-2, .pmb1-3, .pmb1-4, .pmb1-b, .pmb1-s {display:block; overflow:hidden;}
    .pmb1-1, .pmb1-2, .pmb1-3, .pmb1-s {height:1px;}
    .pmb1-2, .pmb1-3, .pmb1-4 {border-style: solid; border-width: 0 1px; }
    .pmb1-1 {margin:0 5px; }
    .pmb1-2 {margin:0 3px; border-width:0 2px;}
    .pmb1-3 {margin:0 2px;}
    .pmb1-4 {height:2px; margin:0 1px;}
    .pmb1-c {display:block; border-style: solid ; border-width: 0 1px;}
<?php
  }

  function begin_round($width = false, $fill = false, $edge = false){
    if($width)   $this->setWidth  ($width );
    if($fill)    $this->setFill  ($fill);
    if($edge)    $this->setEdge  ($edge);
     ob_start();
     if($this->stylesheet){
?>

<!-- begin PhpMyBorder -->
<div style="width:<?php echo  $this->getWidth(true)?>;">
 <b class="pmb1-b">
  <b class="pmb1-1" style="background:<?php echo  $this->getEdge(true)?>; color: inherit;">&nbsp;</b>
  <b class="pmb1-2" style="background:<?php echo  $this->getFill(true)?>; color: inherit; border-color: <?php echo  $this->getEdge(true)?>;">&nbsp;</b>
  <b class="pmb1-3" style="background:<?php echo  $this->getFill(true)?>; color: inherit; border-color: <?php echo  $this->getEdge(true)?>;">&nbsp;</b>
  <b class="pmb1-4" style="background:<?php echo  $this->getFill(true)?>; color: inherit; border-color: <?php echo  $this->getEdge(true)?>;">&nbsp;</b>
 </b>
 <div class="pmb1-c" style="background:<?php echo  $this->getFill(true)?>; color: inherit; border-color: <?php echo  $this->getEdge(true)?>;">
  <b class="pmb1-s">&nbsp;</b>
<?php
    }else{
?>

<!-- begin PhpMyBorder -->
<div style="width: <?php echo  $this->getWidth(true)?>;">
 <b style="font-size:1px;display:block; overflow:hidden;">&nbsp;
  <b style="background:<?php echo  $this->getEdge(true)?>; color: inherit; display:block; overflow:hidden;height:1px;margin:0 5px;">&nbsp;</b>
  <b style="background:<?php echo  $this->getFill(true)?>; border-color: <?php echo  $this->getEdge(true)?>; color: inherit; display:block; overflow:hidden;height:1px;border-style: solid; border-width: 0 1px;margin:0 3px; border-width:0 2px;">&nbsp;</b>
  <b style="background:<?php echo  $this->getFill(true)?>;border-color: <?php echo  $this->getEdge(true)?>; color: inherit; display:block; overflow:hidden;height:1px;border-style: solid; border-width: 0 1px;margin:0 2px;">&nbsp;</b>
  <b style="background:<?php echo  $this->getFill(true)?>;border-color: <?php echo  $this->getEdge(true)?>; color: inherit; display:block; overflow:hidden;border-style: solid; border-width: 0 1px;height:2px; margin:0 1px;">&nbsp;</b>
 </b>
 <div style="background:<?php echo  $this->getFill(true)?>; border-color: <?php echo  $this->getEdge(true)?>; color: inherit; display:block; border-style: solid ; border-width: 0 1px;">
  <b style="font-size:1px;display:block; overflow:hidden;height:1px;">&nbsp;</b>
<?php
    }
    $buffer = ob_get_contents();
    ob_end_clean();
    return $buffer;
  }

  function end_round(){
    ob_start();

     if($this->stylesheet){
?>

  <b class="pmb1-s">&nbsp;</b>
 </div>
 <b class="pmb1-b">
  <b class="pmb1-4" style="background:<?php echo  $this->getFill(true)?>;border-color: <?php echo  $this->getEdge(true)?>; color: inherit;">&nbsp;</b>
  <b class="pmb1-3" style="background:<?php echo  $this->getFill(true)?>;border-color: <?php echo  $this->getEdge(true)?>; color: inherit;">&nbsp;</b>
  <b class="pmb1-2" style="background:<?php echo  $this->getFill(true)?>;border-color: <?php echo  $this->getEdge(true)?>; color: inherit;">&nbsp;</b>
  <b class="pmb1-1" style="background:<?php echo  $this->getEdge(true)?>; color: inherit;">&nbsp;</b>
 </b>
</div>
<!-- end PhpMyBorder -->

<?php } else {?>

  <b style="font-size:1px;display:block; overflow:hidden;height:1px;">&nbsp;</b>
 </div>
 <b style="font-size:1px;display:block; overflow:hidden;">&nbsp;
  <b style="background:<?php echo  $this->getFill(true)?>;border-color: <?php echo  $this->getEdge(true)?>; color: inherit; display:block; overflow:hidden;border-style: solid; border-width: 0 1px;height:2px; margin:0 1px;">&nbsp;</b>
  <b style="background:<?php echo  $this->getFill(true)?>;border-color: <?php echo  $this->getEdge(true)?>; color: inherit; display:block; overflow:hidden;height:1px;border-style: solid; border-width: 0 1px;margin:0 2px;">&nbsp;</b>
  <b style="background:<?php echo  $this->getFill(true)?>;border-color: <?php echo  $this->getEdge(true)?>; color: inherit; display:block; overflow:hidden;height:1px;border-style: solid; border-width: 0 1px;margin:0 3px; border-width:0 2px;">&nbsp;</b>
  <b style="background:<?php echo  $this->getEdge(true)?>; color: inherit; display:block; overflow:hidden;height:1px;margin:0 5px;"></b>
 </b>
</div>
<!-- end PhpMyBorder -->

<?php
  }
    $buffer = ob_get_contents();
    ob_end_clean();
    return $buffer;
  }

  function stylesheet_raised(){
?>

.pmb2-1, .pmb2-2, .pmb2-3, .pmb2-4, .pmb2-5, .pmb2-6, .pmb2-7, .pmb2-8 { overflow:hidden; font-size:1px; display:block; }
.pmb2-1, .pmb2-2, .pmb2-3, .pmb2-6, .pmb2-7, .pmb2-8, .pmb2-s { height:1px; }
.pmb2-2, .pmb2-3, .pmb2-4, .pmb2-5, .pmb2-6, .pmb2-7, .pmb2-c {  border-style: solid; border-width: 0 1px; }
.pmb2-2, .pmb2-3, .pmb2-4, .pmb2-c { border-left-color: #fff; }
.pmb2-7, .pmb2-6, .pmb2-5, .pmb2-c { border-right-color: #999; }
.pmb2-1 { margin:0 5px; background-color: #fff; color: inherit;}
.pmb2-2 { border-right:1px solid #eee; }
.pmb2-3 { border-right:1px solid #ddd; }
.pmb2-4 { border-right:1px solid #aaa; }
.pmb2-5 { border-left:1px solid #eee; }
.pmb2-6 { border-left:1px solid #ddd; }
.pmb2-7 { border-left:1px solid #aaa; }
.pmb2-8 { margin:5px 5px; background-color:#999; color: inherit;}
.pmb2-2, .pmb2-7 { margin:0 3px; border-width:0 2px; }
.pmb2-3, .pmb2-6 { margin:0 2px; }
.pmb2-4, .pmb2-5 { margin:0 1px; height:2px; }
.pmb2-c { padding: 4px 4px; display:block; }
.pmb2-s {display : block; font-size:1px;}
<?php
  }


  function begin_raised($width = false, $fill = false){
    if($width)   $this->setWidth  ($width );
    if($fill)    $this->setFill  ($fill);
     ob_start();
     if($this->stylesheet){
?>

<!-- begin PhpMyBorder -->
<div style="width: <?php echo  $this->getWidth(true)?>;">
 <b class="pmb2-1">&nbsp;</b>
 <b class="pmb2-2" style="background: <?php echo  $this->getFill(true)?>; color: inherit;">&nbsp;</b>
 <b class="pmb2-3" style="background: <?php echo  $this->getFill(true)?>; color: inherit;">&nbsp;</b>
 <b class="pmb2-4" style="background: <?php echo  $this->getFill(true)?>; color: inherit;">&nbsp;</b>
 <div class="pmb2-c" style="background: <?php echo  $this->getFill(true)?>; color: inherit;">
  <b class="pmb2-s">&nbsp;</b>
<?php
    }else{
?>

<!-- begin PhpMyBorder -->
<div style="width: <?php echo  $this->getWidth(true)?>;">
 <b style="overflow:hidden; font-size:1px; display:block;height:1px; margin:0 5px; background:#fff; color: inherit;">&nbsp;</b>
 <b style="background: <?php echo  $this->getFill(true)?>; color: inherit; overflow:hidden; font-size:1px; display:block;height:1px; border-style: solid; border-width: 0 1px; border-left-color: #fff; border-right:1px solid #eee; margin:0 3px; border-width:0 2px;">&nbsp;</b>
 <b style="background: <?php echo  $this->getFill(true)?>; color: inherit; overflow:hidden; font-size:1px; display:block;height:1px;border-style: solid; border-width: 0 1px;border-left-color: #fff;border-right:1px solid #ddd;margin:0 2px;">&nbsp;</b>
 <b style="background: <?php echo  $this->getFill(true)?>; color: inherit; overflow:hidden; font-size:1px; display:block;border-style: solid; border-width: 0 1px; border-left-color: #fff; border-right:1px solid #aaa;margin:0 1px; height:2px;">&nbsp;</b>
 <div style="background: <?php echo  $this->getFill(true)?>; color: inherit; padding: 0 4px; display:block;border-style: solid; border-width: 0 1px;border-left-color: #fff;border-right-color: #999;">
  <b style="height:1px; display:block; font-size:1px;">&nbsp;</b>
<?php
    }
    $buffer = ob_get_contents();
    ob_end_clean();
    return $buffer;
  }

  function end_raised(){
    ob_start();

     if($this->stylesheet){
?>

  <b class="pmb2-s">&nbsp;</b>
 </div>
 <b class="pmb2-5" style="background: <?php echo  $this->getFill(true)?>; color: inherit;">&nbsp;</b>
 <b class="pmb2-6" style="background: <?php echo  $this->getFill(true)?>; color: inherit;">&nbsp;</b>
 <b class="pmb2-7" style="background: <?php echo  $this->getFill(true)?>; color: inherit;">&nbsp;</b>
 <b class="pmb2-8">&nbsp;</b>
</div>
<!-- end PhpMyBorder -->

<?php
  }else{
?>

  <b style="height:1px; display:block; font-size:1px;">&nbsp;</b>
 </div>
 <b style="background: <?php echo  $this->getFill(true)?>; color: inherit; overflow:hidden; font-size:1px; display:block;border-style: solid; border-width: 0 1px;border-right-color: #999;border-left:1px solid #eee; margin:0 1px; height:2px;">&nbsp;</b>
 <b style="background: <?php echo  $this->getFill(true)?>; color: inherit; overflow:hidden; font-size:1px; display:block;height:1px;border-style: solid; border-width: 0 1px;border-right-color: #999;border-left:1px solid #ddd; margin:0 2px;">&nbsp;</b>
 <b style="background: <?php echo  $this->getFill(true)?>; color: inherit; overflow:hidden; font-size:1px; display:block;height:1px;border-style: solid; border-width: 0 1px;border-right-color: #999;border-left:1px solid #aaa;margin:0 3px; border-width:0 2px;">&nbsp;</b>
 <b style="overflow:hidden; font-size:1px; display:block;height:1px;margin:0 5px; background:#999; color: inherit;"></b>
</div>
<!-- end PhpMyBorder -->

<?php
  }
    $buffer = ob_get_contents();
    ob_end_clean();
    return $buffer;
  }


  function stylesheet_shadow(){
?>

.pmb3-1 { border-width: 1px; border-style: solid; position: relative; left:-3px; top:-3px; }
.pmb3-2 { overflow:hidden; width:100%; padding:3px  3px; }
.pmb3-s { height: 1px; font-size: 1px; display: block; }
<?php
  }

  function begin_shadow($width = false, $fill = false, $edge = false, $shadow = false){
    if($width)   $this->setWidth  ($width );
    if($fill)    $this->setFill  ($fill);
    if($edge)    $this->setEdge  ($edge);
    if($shadow) $this->setShadow($shadow);
     ob_start();
     if($this->stylesheet){
?>

<!-- begin PhpMyBorder -->
<div style="width: <?php echo  $this->getWidth(true)?>; background: <?php echo  $this->getShadow(true)?>;">
 <div class="pmb3-1" style="background: <?php echo  $this->getFill(true)?>; border-color: <?php echo  $this->getEdge(true)?>; color: inherit;">
  <div class="pmb3-2">
   <b class="pmb3-s">&nbsp;</b>
<?php
    }else{
?>

<!-- begin PhpMyBorder -->
<div style="width: <?php echo  $this->getWidth(true)?>; background: <?php echo  $this->getShadow(true)?>; color: inherit;">
 <div style="background: <?php echo  $this->getFill(true)?>; border-color: <?php echo  $this->getEdge(true)?>; color: inherit; border-width: 1px; border-style: solid; position: relative; left:-3px; top:-3px;">
  <div style="overflow:hidden; width:100%; padding:3px 3px; ">
   <b style="height:1px; display:block; font-size:1px;">&nbsp;</b>
<?php
    }
    $buffer = ob_get_contents();
    ob_end_clean();
    return $buffer;
  }

  function end_shadow(){
    ob_start();

     if($this->stylesheet){
?>

   <b class="pmb3-s">&nbsp;</b>
  </div>
 </div>
</div>
<!-- end PhpMyBorder -->

<?php }else{ ?>

   <b style="height:1px; display:block; font-size:1px;">&nbsp;</b>
  </div>
 </div>
</div>
<!-- end PhpMyBorder -->

<?php
  }
    $buffer = ob_get_contents();
    ob_end_clean();
    return $buffer;
  }

}

?>