
/**
 * Basic loader for reader.
 */

$.urlParam = function(name){
  var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
  if (!results)
  {
    return 0;
  }
  return results[1] || 0;
}

//$('document').ready(function(){
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
//});





