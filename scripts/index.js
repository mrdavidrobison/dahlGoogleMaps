var map;
function initMap() {
  map = new google.maps.Map(document.getElementById('map'), {
    center: {lat: 27.899, lng: -82.515},
    zoom: 13
  });
}