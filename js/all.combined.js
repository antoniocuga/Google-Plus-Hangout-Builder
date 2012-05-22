/*
<!--#include file="your_file.js" -->
*/

$(function() {
  gapi.hangout.onApiReady.add(function(event) {
    if (event.isApiReady) {
      alert('The API is now ready!');
    }
  });
});