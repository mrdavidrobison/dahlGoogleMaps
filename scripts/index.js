function initMap() {

  // center the map
  var map = new google.maps.Map(document.getElementById('map'), {
    center: { lat: 27.899, lng: -82.515 },
    zoom: 13
  });

  // choose map marker icon
  var icon = {
    url: "https://dahlproperties.com/wp-content/uploads/2017/07/icon_property_marker.png",
    scaledSize: new google.maps.Size(30, 30),
  };

  var currWindow = false;

  // create event listener and message after click markers
  function attachSecretMessage(marker, secretMessage) {
    var infowindow = new google.maps.InfoWindow({
      content: secretMessage
    });
    // listen for click event
    marker.addListener('click', function () {
      infowindow.open(marker.get('map'), marker);
    });

    google.maps.event.addListener(marker, 'click', function () {
      if (currWindow) {
        currWindow.close();
      }

      currWindow = infowindow;
      infowindow.open(base.map, marker);
    });

  }

  // create array of messages of addresses for each marker
  var secretMessages = ['<div id="casaMarker"><h3 id="casaTitle">Villa Estrella </h3><p id="casaAddress">2801 W Estrella St </p><a id="casaLink" href="http://brainstorm.solutions/dahl/property/villa-estrella/">Go to Property Details</a><span id="casaArrow"> &#9654;</span></div>', '<div id="casaMarker"><h3 id="casaTitle">Casa Juanita </h3><p id="casaAddress">6806 S Juanita St </p><a id="casaLink" href="http://brainstorm.solutions/dahl/property/casa-juanita/">Go to Property Details</a><span id="casaArrow"> &#9654;</span></div>', '<div id="casaMarker"><h3 id="casaTitle">Casa Luna </h3><p id="casaAddress">2904 W Estrella St </p><a id="casaLink" href="http://brainstorm.solutions/dahl/property/casa-luna/">Go to Property Details</a><span id="casaArrow"> &#9654;</span></div>', '<div id="casaMarker"><h3 id="casaTitle">Casa Feliz </h3><p id="casaAddress">3006 W Estrella St </p><a id="casaLink" href="http://brainstorm.solutions/dahl/property/casa-feliz/">Go to Property Details</a><span id="casaArrow"> &#9654;</span></div>', '<div id="casaMarker"><h3 id="casaTitle">Casa Bella </h3><p id="casaAddress">6824 S Sparkman St </p><a id="casaLink" href="http://brainstorm.solutions/dahl/property/casa-bella/">Go to Property Details</a><span id="casaArrow"> &#9654;</span></div>'];

  // create markers for each property listing
  var estrella = new google.maps.Marker({
    position: new google.maps.LatLng(27.928814, -82.489407),
    map: map,
    icon: icon
  });
  attachSecretMessage(estrella, secretMessages[0]);

  var juanita = new google.maps.Marker({
    position: new google.maps.LatLng(27.868068, -82.529975),
    map: map,
    icon: icon
  });
  attachSecretMessage(juanita, secretMessages[1]);

  var luna = new google.maps.Marker({
    position: new google.maps.LatLng(27.928471, -82.491106),
    map: map,
    icon: icon
  });
  attachSecretMessage(luna, secretMessages[2]);

  var feliz = new google.maps.Marker({
    position: new google.maps.LatLng(27.928510, -82.492345),
    map: map,
    icon: icon
  });
  attachSecretMessage(feliz, secretMessages[3]);

  var bella = new google.maps.Marker({
    position: new google.maps.LatLng(27.866954, -82.524223),
    map: map,
    icon: icon
  });
  attachSecretMessage(bella, secretMessages[4]);
  
  google.maps.event.addDomListener(window, "resize", function () {
    var center = map.getCenter();
    google.maps.event.trigger(map, "resize");
    map.setCenter(center);
  });
}