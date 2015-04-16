function initialize() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      // alert('Coordinates: Latitude: ' + position.coords.latitude + ', Longitude: ' + position.coords.longitude);
      if (window.console) {
        window.console.log(position.coords.latitude + ', ' + position.coords.longitude);
      }
    }, function() {
      handleNoGeolocation(code);
    });
  } else {
    // no native support - is there a fallback?
    // recommendation: force pin the user's "location" to the center of UW campus
    // and find the events in a geocircle from there.
    handleNoGeolocation(4);
  }
}

function handleNoGeolocation(code) {
  switch(code) {
    case 1:
      break;
    case 2:
      break;
  }


  //if (nosupport) { alert("Your browser doesn't seem to support geolocation. Get a real browser."); }
  //if (nopermission) { alert("This website works best with geolocation. Please consider granting permission."); }
}

google.maps.event.addDomListener(window, 'load', initialize);
