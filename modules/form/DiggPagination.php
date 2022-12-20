<?php

class m_form_DiggPagination
{

/**
 * Builds & returns a variable containing the HTML code to display pagination links based on given params
 *
 * @param int $page - the current page
 * @param int $totalitems - the number of items to paginate (not total number of pages)
 * @param int $limit - the number of items per page
 * @param int $adjacents - the number of page links to put adjacent to the current page
 * @param string $targetpage - URL to the web page requiring pagination links
 * @param string $pagestring - the URL params to pass the new page value e.g. page/ (the number is inserted at the end of the string)
 * @param string $cssClass - the class of the containing DIV tag for the returned pagination
 * @return void
 * @author Stranger Studios, adapted by Rich Milns
 * @see http://www.strangerstudios.com/sandbox/pagination/diggstyle.php
 */
  static public function Pager( $page = 1, $totalitems, $limit = 15, $adjacents = 1, $targetpage = null,
    $pagestring = "?page=", $cssClass = 'pagination')
  {
    $sScriptUrl = $_SERVER['SCRIPT_NAME'];
    $query_string = '&amp;' . preg_replace( "/page=[0-9]{0,10}&/", "", $_SERVER['QUERY_STRING']) . '#submissions';
    //$query_string = $_SERVER['QUERY_STRING'];

    //defaults
    if ( !$adjacents) { $adjacents = 1; }
    if ( !$limit) { $limit = 15; }
    if ( !$page) { $page = 1; }
    if ( !$targetpage) { $targetpage = $sScriptUrl; }
    if ( !isset( $margin)) { $margin = 5; }
    if ( !isset( $padding)) { $padding = 1; }
    //other vars
    $prev = $page - 1; //previous page is page - 1
    $next = $page + 1; //next page is page + 1
    $lastpage = ceil( $totalitems / $limit); //lastpage is = total items / items per page, rounded up.
    $lpm1 = $lastpage - 1; //last page minus 1

    /*
    Now we apply our rules and draw the pagination object.
    We're actually saving the code to a variable in case we want to draw it more than once.
    */
    $pagination = "";
    if ( $lastpage > 1) {
      $pagination .= "<div class=\"" . $cssClass . "\"";
      if ( $margin || $padding) {
        $pagination .= " style=\"";
        if ( $margin) { $pagination .= "margin: $margin;"; }
        if ( $padding) { $pagination .= "padding: $padding;"; }
        $pagination .= "\"";
      }
      $pagination .= ">";

      //previous button
      if ( $page > 1) {
        $pagination .= "<a href=\"$targetpage$pagestring$prev$query_string\">« prev</a>";
      } else {
        $pagination .= "<span class=\"disabled\">« prev</span>";
      }

      //pages
      if ( $lastpage < ( $adjacents * 2) + 7) //not enough pages to bother breaking it up
        {
        for ( $counter = 1; $counter <= $lastpage; $counter++) {
          if ( $counter == $page) {
            $pagination .= "<span class=\"current\">$counter</span>";
          } else {
            $pagination .= "<a href=\"" . $targetpage . $pagestring . $counter . $query_string . "\">$counter</a>";
          }
        }
      } elseif ( $lastpage >= ( $adjacents * 2) + 7) //enough pages to hide some
      {
        //close to beginning; only hide later pages
        if ( $page < 1 + ( $adjacents * 3)) {
          for ( $counter = 1; $counter < 4 + ( $adjacents * 2); $counter++) {
            if ( $counter == $page) {
              $pagination .= "<span class=\"current\">$counter</span>";
            } else {
              $pagination .= "<a href=\"" . $targetpage . $pagestring . $counter . $query_string . "\">$counter</a>";
            }
          }
          $pagination .= "<span class=\"elipses\">...</span>";
          $pagination .= "<a href=\"" . $targetpage . $pagestring . $lpm1 . $query_string . "\">$lpm1</a>";
          $pagination .= "<a href=\"" . $targetpage . $pagestring . $lastpage . $query_string . "\">$lastpage</a>";
        }
        //in middle; hide some front and some back
        elseif ( $lastpage - ( $adjacents * 2) > $page && $page > ( $adjacents * 2)) {
          $pagination .= "<a href=\"" . $targetpage . $pagestring . "1$query_string\">1</a>";
          $pagination .= "<a href=\"" . $targetpage . $pagestring . "2$query_string\">2</a>";
          $pagination .= "<span class=\"elipses\">...</span>";
          for ( $counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
            if ( $counter == $page) {
              $pagination .= "<span class=\"current\">$counter</span>";
            } else {
              $pagination .= "<a href=\"" . $targetpage . $pagestring . $counter . $query_string . "\">$counter</a>";
            }
          }
          $pagination .= "...";
          $pagination .= "<a href=\"" . $targetpage . $pagestring . $lpm1 . $query_string . "\">$lpm1</a>";
          $pagination .= "<a href=\"" . $targetpage . $pagestring . $lastpage . $query_string . "\">$lastpage</a>";
        }
        //close to end; only hide early pages
        else {
          $pagination .= "<a href=\"" . $targetpage . $pagestring . "1$query_string\">1</a>";
          $pagination .= "<a href=\"" . $targetpage . $pagestring . "2$query_string\">2</a>";
          $pagination .= "<span class=\"elipses\">...</span>";
          for ( $counter = $lastpage - ( 1 + ( $adjacents * 3)); $counter <= $lastpage; $counter++) {
            if ( $counter == $page) {
              $pagination .= "<span class=\"current\">$counter</span>";
            } else {
              $pagination .= "<a href=\"" . $targetpage . $pagestring . $counter . $query_string . "\">$counter</a>";
            }
          }
        }
      }

      //next button
      if ( $page < $lastpage - 0) {
        $pagination .= "<a href=\"" . $targetpage . $pagestring . $next . $query_string . "\">next »</a>";
      } else {
        $pagination .= "<span class=\"disabled\">next »</span>";
      }
      $pagination .= "</div>\n";
    }

    return $pagination;

  }
}
