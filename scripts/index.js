function initMap() {
  var bella = {lat: 27.866954, lng: -82.524223};
  var estrella = {lat: 27.928814, lng: -82.489407};
  var juanita = {lat: 27.868068, lng: -82.529975};
  var luna = {lat: 27.928471, lng: -82.491106};
  var bella = {lat: 27.866954, lng: -82.524223};
  var bella = {lat: 27.866954, lng: -82.524223};

  var map = new google.maps.Map(document.getElementById('map'), {
    center: { lat: 27.899, lng: -82.515 },
    zoom: 13
  });
  var marker = new google.maps.Marker({
    position: uluru,
    map: map
  });
}