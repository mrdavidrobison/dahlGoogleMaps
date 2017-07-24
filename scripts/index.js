function initMap() {

  // center the map
  var map = new google.maps.Map(document.getElementById('map'), {
    center: { lat: 27.899, lng: -82.515 },
    zoom: 13
  });

  // create markers for each property listing
  var estrella = new google.maps.Marker({
    position: new google.maps.LatLng(27.928814, -82.489407),
    map: map,
    icon: "google map marker.png"
  });
  var juanita = new google.maps.Marker({
    position: new google.maps.LatLng(27.868068, -82.529975),
    map: map,
    icon: "google map marker.png"
  });
  var luna = new google.maps.Marker({
    position: new google.maps.LatLng(27.928471, -82.491106),
    map: map,
    icon: "google map marker.png"
  });
  var feliz = new google.maps.Marker({
    position: new google.maps.LatLng(27.928510, -82.492345),
    map: map,
    icon: "google map marker.png"
  });
  var bella = new google.maps.Marker({
    position: new google.maps.LatLng(27.866954, -82.524223),
    map: map,
    icon: "google map marker.png"
  });
}