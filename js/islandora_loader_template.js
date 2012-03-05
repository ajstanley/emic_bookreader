
/**
 * On ingest Drupal will create a copy of this file, with the URL localized to the Drupal installation
 */

$.urlParam = function(name){
  var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
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





