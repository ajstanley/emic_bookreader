<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
define("DJATOKA_PREFIX", "http://localhost:8080/adore-djatoka/resolver?url_ver=Z39.88-2004&rft_id=");
define("ISLANDORA_PREFIX", "http://localhost/Development/fedora/repository/");
define("FEDORA_PREFIX", "http://localhost:8080/");

define("COMPRESSION", 4); // djatoka compression level (lower is higher compression)

function do_curl($url, $return_to_variable = 1, $number_of_post_vars = 0, $post = NULL) {
  global $user;
  // Check if we are inside Drupal and there is a valid user.
  if ((!isset($user)) || $user->uid == 0) {
    $fedora_user = 'anonymous';
    $fedora_pass = 'anonymous';
  }
  else {
    $fedora_user = $user->name;
    $fedora_pass = $user->pass;
  }

  if (function_exists("curl_init")) {
    $ch = curl_init();
    $user_agent = "Mozilla/4.0 pp(compatible; MSIE 5.01; Windows NT 5.0)";
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_FAILONERROR, TRUE); // Fail on errors
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // allow redirects
    curl_setopt($ch, CURLOPT_TIMEOUT, 90); // times out after 90s
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $return_to_variable); // return into a variable
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, "$fedora_user:$fedora_pass");
    //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    if ($number_of_post_vars > 0 && $post) {
      curl_setopt($ch, CURLOPT_POST, $number_of_post_vars);
      curl_setopt($ch, CURLOPT_POSTFIELDS, "$post");
    }
    return curl_exec($ch);
  }
  else {
    if (function_exists(drupal_set_message)) {
      drupal_set_message(t('No curl support.'), 'error');
    }
    return NULL;
  }
}

function results_to_array($content) {

  $results = explode("\n", $content);
  unset($results[0]);
  $results = array_values(array_filter($results));
  sort($results);
  $returnArray = array();
  $count = 1;
  foreach ($results as $result) {
    $parts = explode(',', $result);
    $page = preg_replace('/info:fedora/', '', $parts[0]);
    $returnArray[$count++] = $page;
  }
return $returnArray;
}

//instead of rely on pid or title for ordering we store the page number in
//the rels-ext with a predicate of <info:islandora/islandora-system:def/pageinfo#isPageNumber>
//we then populate an array page numbers as the key and pids as the value
/*
  // the original itql query
  $query_string = 'select $object $page from <#ri>
  where $object <fedora-rels-ext:isMemberOfCollection> <info:fedora/'. $_GET["pid"] .'>
  and $object <fedora-model:state> <info:fedora/fedora-system:def/model#Active>
  and $object <info:islandora/islandora-system:def/pageinfo#isPageNumber> $page';
 */

// the slightly streamlined itql query
$query_string = 'select $object $page from <#ri>
where $object <fedora-rels-ext:isMemberOf> <info:fedora/' . $_GET["pid"] . '>
and $object <fedora-model:state> <fedora-model:Active>
and $object <info:islandora/islandora-system:def/pageinfo#isPageNumber> $page
order by $page';

/*
  // the equivalent sparql query
  $query_string = '
  PREFIX fre: <info:fedora/fedora-system:def/relations-external#>
  PREFIX fm: <info:fedora/fedora-system:def/model#>
  PREFIX pn: <info:islandora/islandora-system:def/pageinfo#>
  select $object $page
  from <#ri>
  where {
  $object fre:isMemberOfCollection <info:fedora/'. $_GET["pid"] .'> ;
  fm:state fm:Active ;
  pn:isPageNumber $page .
  }';
 */
$query_string = htmlentities(urlencode($query_string));
$query_results = '';
$url = FEDORA_PREFIX . 'fedora/risearch'; //have to define the server here we don't have access to drupal variables/functions here
$url .= "?type=tuples&flush=TRUE&format=csv&lang=itql&stream=on&query=" . $query_string;
$query_results = do_curl($url);
$query_array = results_to_array($query_results);
?>

<html>
  <head>
    <title>Book Reader</title>

    <link rel="stylesheet" type="text/css" href="../BookReader/BookReader.css"/>
    <!-- Custom CSS overrides -->
    <link rel="stylesheet" type="text/css" href="BookReaderDemo.css"/>

    <script type="text/javascript" src="../js/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="../js/jquery-ui-1.8.5.custom.min.js"></script>
    <script type="text/javascript" src="../js/dragscrollable.js"></script>
    <script type="text/javascript" src="../js/jquery.colorbox-min.js"></script>
    <script type="text/javascript" src="../js/jquery.ui.ipad.js"></script>
    <script type="text/javascript" src="../js/jquery.bt.min.js"></script>

    <script type="text/javascript" src="../BookReader/BookReader.js"></script>
  </head>
  <body style="background-color: #939598;">

    <div id="BookReader" style="left:10px; right:10px; top:10px; bottom:2em;">Loading Bookreader, please wait...</div>

    <script type="text/javascript">
      //
      // This file shows the minimum you need to provide to BookReader to display a book
      //
      // Copyright(c)2008-2009 Internet Archive. Software license AGPL version 3.

      // Create the BookReader object
      br = new BookReader();
      br.structMap = new Array();
<?php
$tagImg = "";
foreach ($query_array as $key => $value) {
  echo 'br.structMap[' . $key . '] = "' . substr($value, 1) . '";';
  $tagImg = substr($value, 1);
}

$jsonurl = DJATOKA_PREFIX . ISLANDORA_PREFIX . $tagImg . "/JP2/&svc_id=info:lanl-repo/svc/getMetadata";

$json_output = json_decode(do_curl($jsonurl), true);

echo 'br.djatoka_prefix = "' . DJATOKA_PREFIX . '";';
echo 'br.islandora_prefix = "' . ISLANDORA_PREFIX . '";';
echo 'br.fedora_prefix = "' . FEDORA_PREFIX . '";';
echo 'br.compression = "' . COMPRESSION . '";';
echo 'br.width = ' . $json_output["width"] . ';';
echo 'br.height = ' . $json_output["height"] . ';';
?>

  br.getPageWidth = function(index) {
    return br.width;
  }

  // Return the height of a given page.  Here we assume all images are 1200 pixels high
  br.getPageHeight = function(index) {
    return br.height;
  }

  // We load the images from fedora
  // using a different URL structure
  br.getPageURI = function(index, reduce, rotate) {
    // reduce and rotate are ignored in this simple implementation, but we
    // could e.g. look at reduce and load images from a different directory
    // or pass the information to an image server
    var leafStr = br.structMap[index+1];//get the pid of the object from the struct map islandora specific
    var url = br.djatoka_prefix + br.islandora_prefix + leafStr + '/JP2/&svc_id=info:lanl-repo/svc/getRegion&svc_val_fmt=info:ofi/fmt:kev:mtx:jpeg2000&svc.format=image/png&svc.level=' + br.compression + '&svc.rotate=0';

    return url;
  }

  br.getModsURI = function(index) {
    var leafStr = br.structMap[index+1];//get the pid of the object from the struct map islandora specific
    return br.islandora_prefix + leafStr + "/MODS";
  }

  br.getPid = function (index) {
    var leafStr = br.structMap[index+1];//get the pid of the object from the struct map islandora specific
    return leafStr;
  }

  // Return which side, left or right, that a given page should be displayed on
  br.getPageSide = function(index) {
    $vals = ["R", "L"];
    return $vals[index & 0x1];
  }

  // This function returns the left and right indices for the user-visible
  // spread that contains the given index.  The return values may be
  // null if there is no facing page or the index is invalid.
  br.getSpreadIndices = function(pindex) {
    var spreadIndices = [null, null];
    if ('rl' == this.pageProgression) {
      // Right to Left
      if (this.getPageSide(pindex) == 'R') {
        spreadIndices[1] = pindex;
        spreadIndices[0] = pindex + 1;
      } else {
        // Given index was LHS
        spreadIndices[0] = pindex;
        spreadIndices[1] = pindex - 1;
      }
    } else {
      // Left to right
      if (this.getPageSide(pindex) == 'L') {
        spreadIndices[0] = pindex;
        spreadIndices[1] = pindex + 1;
      } else {
        // Given index was RHS
        spreadIndices[1] = pindex;
        spreadIndices[0] = pindex - 1;
      }
    }

    return spreadIndices;
  }

  // For a given "accessible page index" return the page number in the book.
  //
  // For example, index 5 might correspond to "Page 1" if there is front matter such
  // as a title page and table of contents.
  // for now we just show the image number
  br.getPageNum = function(index) {
    return index+1;
  }

  // Total number of leafs
  br.numLeafs = <?php echo count($query_array); ?>;

  // Book title and the URL used for the book title link
  br.bookTitle = '<?php echo addslashes($_GET['label']); ?>';
  if (br.bookTitle.length > 100){
    br.bookTitle =  br.bookTitle.substring(0,97)+'...';
  }
  // book url should be created dynamically
  br.bookUrl = br.islandora_prefix + '<?php echo $_GET['pid']; ?>';
  br.bookPid = '<?php echo $_GET['pid']; ?>';
  // Override the path used to find UI images
  br.imagesBaseURL = '../BookReader/images/';

  br.getEmbedCode = function(frameWidth, frameHeight, viewParams) {
    return "Embed code not supported in bookreader demo.";
  }

  // Let's go!
  br.init();

  // read-aloud and search need backend compenents and are not supported in the demo
  $('#BRtoolbar').find('.read').hide();
  $('#textSrch').hide();
  $('#btnSrch').hide();

    </script>


  </body>
</html>
<?php
?>
