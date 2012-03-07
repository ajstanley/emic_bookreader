<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
  <head>
    <title>Islandora Reader</title>

    <link rel="stylesheet" type="text/css" href="css/BookReader.css"/>
    <!-- Custom CSS overrides -->
    <link rel="stylesheet" type="text/css" href="css/BookReaderDemo.css"/>

    <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.5.custom.min.js"></script>
    <script type="text/javascript" src="js/dragscrollable.js"></script>
    <script type="text/javascript" src="js/jquery.colorbox-min.js"></script>
    <script type="text/javascript" src="js/jquery.ui.ipad.js"></script>
    <script type="text/javascript" src="js/jquery.bt.min.js"></script>
    <script type="text/javascript" src="js/BookReader.js"></script>
    <script type="text/javascript" src="js/islandora_loader.js"></script>

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
      br.djatoka_prefix = islandora_params.DJATOKA_PREFIX;
      br.islandora_prefix = islandora_params.ISLANDORA_PREFIX;
      br.fedora_prefix = islandora_params.FEDORA_PREFIX;
      br.width = parseInt(islandora_params.page_width);
      br.height = parseInt(islandora_params.page_height);
      br.structMap = islandora_params.book_pids;
      br.compression = islandora_params.COMPRESSION;

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
       // var url = br.djatoka_prefix + br.islandora_prefix + leafStr + '/JP2/&svc_id=info:lanl-repo/svc/getRegion&svc_val_fmt=info:ofi/fmt:kev:mtx:jpeg2000&svc.format=image/png&svc.level=' + br.compression + '&svc.rotate=0';
       var url = br.djatoka_prefix + br.fedora_prefix + '/objects/' + leafStr + '/datastreams/JP2/content&svc_id=info:lanl-repo/svc/getRegion&svc_val_fmt=info:ofi/fmt:kev:mtx:jpeg2000&svc.format=image/png&svc.level=' + br.compression + '&svc.rotate=0';

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
      br.numLeafs = islandora_params.page_count;

      // Book title and the URL used for the book title link
      br.bookTitle = islandora_params.label;
      if (br.bookTitle.length > 100){
        br.bookTitle =  br.bookTitle.substring(0,97)+'...';
      }
      // book url should be created dynamically
      br.bookUrl = br.islandora_prefix + PID;
      br.bookPid = PID;
      // Override the path used to find UI images
      br.imagesBaseURL = '../images/';

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

