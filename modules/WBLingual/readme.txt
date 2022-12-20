#
#
How to use MultiLingual
##########################################################################
The easiest way is the combination with Droplet [[iMultiLingual]]

##########################################################################
but you also can handle it in the  old manner:

insert the following PHP-code in your templates index.php in the place you want
show the languages link bar.

<?php $iMultiLang = 0; if (function_exists('language_menu')) { $sMultiLang = language_menu('png'); $iMultiLang = (int)((!empty($sMultiLang))?1:0);} ?>

the var $iMultiLang set the show_menu2 startlevel
e.g.
<?php
echo show_menu2(SM2_ALLMENU, SM2_ROOT+$iMultiLang, SM2_CURR+1, SM2_ALL|SM2_BUFFER|SM2_PRETTY|SM2_NUMCLASS,'<li><span class="menu-default">[ac][menu_title]</a></span>','</li>','<ul>','</ul>');
?>

##########################################################################
If you wish to modify the template, just open the file
 /modules/MultiLingual/tpl/lang.html.twig
and make your modifications.

