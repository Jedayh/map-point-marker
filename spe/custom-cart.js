var map = new google.maps.Map(document.getElementById('map'), {
  zoom: 6,
  /* Zoom level of your map */
  center: new google.maps.LatLng(47.393245, -2.247669),
  /* coordinates for the center of your map */
  mapTypeId: google.maps.MapTypeId.ROADMAP
});

var infowindow = new google.maps.InfoWindow();
