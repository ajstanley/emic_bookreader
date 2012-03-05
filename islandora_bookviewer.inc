<?php

// $Id$

class IslandoraBookviewer {

  function __construct($pid = '') {
    module_load_include('inc', 'fedora_repository', 'api/fedora_item');
    module_load_include('inc', 'fedora_repository', 'CollectionClass');
    if (!empty($pid)) {
      $this->pid = $pid;
      $this->item = new Fedora_Item($pid);
    }
  }

  function showBookViewer() {
    global $base_url;
    $path = drupal_get_path('module', 'islandora_bookviewer');
    $fullPath = $path . '/';
    //currently we are loading this as a iframe.
    //can be embeded but it looks terrible when embedded.  the css would have to be rewritten as most things are set as absolute
    //this will take some time but eventually would be worthwhile as the index php does not have access to drupal functions/variables when loaded as
    //an iframe
    $viewer_url = $base_url . '/' . $fullPath . 'BookReaderDemo/index.php?pid=' . $this->pid . '&label=' . $this->item->objectProfile->objLabel;
    $html = '<iframe src="' . $viewer_url . '" scrolling="0" frameborder="0" style="width: 100%; height: 800px;">Errors: unable to load viewer</iframe>';
    return $html;
  }


  // an ajax callback has been added in the module
 
  function showAjaxBookViewer($page) {
    global $base_url;
    $path = drupal_get_path('module', 'islandora_bookviewer');
    $fullPath = $path . '/';
    //currently we are loading this as a iframe.
    //can be embeded but it looks terrible when embedded.  the css would have to be rewritten as most things are set as absolute
    //this will take some time but eventually would be worthwhile as the index php does not have access to drupal functions/variables when loaded as
    //an iframe
    $viewer_url = $base_url . '/' . $fullPath . "$page?pid=" . $this->pid . '&label=' . $this->item->objectProfile->objLabel;
    $html = '<iframe src="' . $viewer_url . '" scrolling="0" frameborder="0" style="width: 100%; height: 800px;">Errors: unable to load viewer</iframe>';
    return $html;
  }

  /**
   * @author: William Panting
   *
   * @date: 16/05/2011
   *
   * @return tabs: the html to display, in the form of a set of tabs
   */
  function showBookStreams() {
    //get the book viewer iframe
    $viewer = $this->showBookViewer();

    //set up tabs
    /*
      module_load_include('inc', 'fedora_repository', 'api/fedora_item');
      if (!empty($pid)) {

      $this->pid = $pid;
      $this->item = new Fedora_Item($pid);
      } */
    $tabs = array('#type' => 'tabset');
    //note the fedora item
    //get metadata info
    $metadata = $this->getMetaData();

    //populate tabs
    //$tabs['Description'] = array(
    //  '#type' => 'tabpage',
    //  '#title' => 'Description',
    //  '#content' => $metadata,
    //  '#weight' => 2
    //);

    $tabs['View'] = array(
      '#type' => 'tabpage',
      '#title' => 'View',
      '#content' => $viewer,
      '#weight' => -1
    );
    //return the tabs for display
    return $tabs;
  }

  /**
   * @author: William Panting
   *
   * @date: 19/05/2011
   *
   * @return output: the html of data to display
   *
   * Small modification of UoM's exif plugin
   */
  function getMetaData() {

    $output = '';
    $MODS = $this->item->get_datastream_dissemination('MODS');

    if (trim($MODS) != '') {
      $MODSDom = DOMDocument::loadXML($this->item->get_datastream_dissemination('MODS'));

      if ($MODSDom != NULL) {
        $description = $MODSDom->getElementsByTagName('*');

        if ($description->length > 0) {
          $description = $description->item(0);
          $output .= '<div class="fedora_technical_metadata"><ul>';

          for ($i = 0; $i < $description->childNodes->length; $i++) {
            $name = $description->childNodes->item($i)->nodeName;
            $value = $description->childNodes->item($i)->nodeValue;
            if ($name != '#text' && !preg_match('/^System\:.*$/', $name) && trim($value) != '') {
              list($type, $name) = preg_split('/\:/', $name);
              $name = trim(preg_replace('/(?<!^)([A-Z][a-z]|(?<=[a-z])[A-Z])/', " $1", $name));
              $output .= '<li><b>' . $name . '</b>:  ' . $value . ' </li>';
            }
          }
          $output .= '</ul></div>';
        }
      }
    }
    return $output;
  }

}
