function initMap() {

  // center the map
  var map = new google.maps.Map(document.getElementById('map'), {
    center: { lat: 27.899, lng: -82.515 },
    zoom: 13
  });

  // create event listener and message after click markers
  function attachSecretMessage(marker, secretMessage) {
    var infowindow = new google.maps.InfoWindow({
      content: secretMessage
    });
    
    // listen for click event
    marker.addListener('click', function () {
      infowindow.open(marker.get('map'), marker);
    });

    var currWindow = false;

    google.maps.event.addListener(marker, 'click', function () {
      if (currWindow) {
        currWindow.close();
      }

      currWindow = infowindow;
      infowindow.open(base.map, marker);
    });

  }

  // create array of messages of addresses for each marker
  var secretMessages = ['<div id="casaMarker"><h4 id="casaTitle">Casa Estrella </h4><p id="casaAddress">2801 W Estrella St </p><a id="casaLink" href="http://brainstorm.solutions/dahl/property/villa-estrella/">Go to Property Details <span id="casaArrow">&#9654;</span></a></div>', '<div id="casaMarker"><h4 id="casaTitle">Casa Juanita </h4><p id="casaAddress">6806 S Juanita St </p><a id="casaLink" href="http://brainstorm.solutions/dahl/property/casa-juanita/">Go to Property Details <span id="casaArrow">&#9654;</span></a></div>', '<div id="casaMarker"><h4 id="casaTitle">Casa Luna </h4><p id="casaAddress">2904 W Estrella St </p><a id="casaLink" href="http://brainstorm.solutions/dahl/property/casa-luna/">Go to Property Details <span id="casaArrow">&#9654;</span></a></div>', '<div id="casaMarker"><h4 id="casaTitle">Casa Feliz </h4><p id="casaAddress">3006 W Estrella St </p><a id="casaLink" href="http://brainstorm.solutions/dahl/property/casa-feliz/">Go to Property Details <span id="casaArrow">&#9654;</span></a></div>', '<div id="casaMarker"><h4 id="casaTitle">Casa Bella </h4><p id="casaAddress">6824 S Sparkman St </p><a id="casaLink" href="http://brainstorm.solutions/dahl/property/casa-bella/">Go to Property Details <span id="casaArrow">&#9654;</span></a></div>'];

  // choose icon
  var icon = {
    url: "house-icon-black.jpeg",
    scaledSize: new google.maps.Size(50, 50),
  };

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

}