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

    marker.addListener('click', function () {
      infowindow.open(marker.get('map'), marker);
    });
  }

  // create array of addresses for each marker
  var secretMessages = ['This', 'is', 'the', 'secret', 'message'];

  // create icon and properties
  var icon = {
    url: "google map marker.png",
    scaledSize: new google.maps.Size(50, 50),
  };

  // create markers for each property listing
  var estrella = new google.maps.Marker({
    position: new google.maps.LatLng(27.928814, -82.489407),
    map: map,
    icon: icon
  });
  attachSecretMessage(estrella, secretMessages[i]);

  var juanita = new google.maps.Marker({
    position: new google.maps.LatLng(27.868068, -82.529975),
    map: map,
    icon: icon
  });
  attachSecretMessage(juanita, secretMessages[i]);

  var luna = new google.maps.Marker({
    position: new google.maps.LatLng(27.928471, -82.491106),
    map: map,
    icon: icon
  });
  attachSecretMessage(luna, secretMessages[i]);

  var feliz = new google.maps.Marker({
    position: new google.maps.LatLng(27.928510, -82.492345),
    map: map,
    icon: icon
  });
  attachSecretMessage(feliz, secretMessages[i]);
  
  var bella = new google.maps.Marker({
    position: new google.maps.LatLng(27.866954, -82.524223),
    map: map,
    icon: icon
  });
  attachSecretMessage(bella, secretMessages[i]);

}