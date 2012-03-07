// Wed Mar  7 08:51:53 2012
// This file is generated dynamically during the Drupal installation process.
// Any changes made to this file will be lost on reinstallation.
// Clone and rename this file if changes are to survive reinstallation.

    $.urlParam = function(name){
  var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
  if (!results)
  {
    return 0;
  }
  return results[1] || 0;
}

  PID = $.urlParam('pid');

  $.ajax({
    url:'http://localhost/Development/bookreader/setup/' + PID,
    async:false,
    success: function(data, status, xhr) {
      islandora_params = data;
    },
    error: function() {
      alert("AJAX call failed");
    },
    dataType: 'json'
  });

