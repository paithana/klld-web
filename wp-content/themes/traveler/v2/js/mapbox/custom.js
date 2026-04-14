var initRtlMapbox = null;
var markerMapBoxGolbal = [];
var mapMapboxGobal;
var ii = 0;
var mapImg;
function initHalfMapBox(mapEl, mapData, mapLat, mapLng, mapZoom, mapIcon) {
    var popupPos = mapEl.data('popup-position');
    if (mapData.length <= 0)
        mapData = mapEl.data('data_show');
    if (mapLat.length <= 0)
        mapLat = mapEl.data('lat');
    if (mapLng.length <= 0)
        mapLng = mapEl.data('lng');
    if (mapZoom.length <= 0)
        mapZoom = mapEl.data('zoom');
    if (mapIcon.length <= 0)
        mapIcon = mapEl.data('icon');
    mapboxgl.accessToken = st_params.token_mapbox;
    var icon_mapbox = st_params.st_icon_mapbox;
    if(typeof icon_mapbox !== 'underfind' ){
        icon_map = icon_mapbox;
    } else {
        icon_map = "https://i.imgur.com/MK4NUzI.png";
    }
    mapboxgl.accessToken = st_params.token_mapbox;
    if(typeof st_params.text_rtl_mapbox !== 'underfind' && initRtlMapbox == null){
        mapboxgl.setRTLTextPlugin(st_params.text_rtl_mapbox);
        initRtlMapbox = 1;
    }
    var map = new mapboxgl.Map({
      container: 'map-search-form',
      style: 'mapbox://styles/mapbox/light-v10?optimize=true',
      center: [mapLng, mapLat],
      zoom: mapZoom,
    });

	var bounds = new mapboxgl.LngLatBounds();

    var listOfObjects = [];
    jQuery.map(mapData, function (location, i) {
        var item_map = InitItemmap(location,i);
        listOfObjects.push(item_map);

		const el = document.createElement('div');
        el.innerHTML = '<div class="inner" data-marker-id="'+ location.id +'">' + jQuery(location.content_adv_html).find('.item_price_map span').text() + '</div>';
        el.className = 'stt-price-label';

		const popup = new mapboxgl.Popup({offset: [0, -30]}).setHTML(item_map.properties.description);

        markerMapBoxGolbal[i] = new mapboxgl.Marker(el).setLngLat(item_map.geometry.coordinates).setPopup(popup).addTo(map);
        bounds.extend(item_map.geometry.coordinates);
    });

}
function InitItemmap(item_map,key){
    var singleObj = {};
    singleObj['type'] = 'Feature';
    singleObj['geometry'] = {
        type: 'Point',
        coordinates: [item_map.lng, item_map.lat]
    };
    singleObj['properties'] = {
        title: item_map.name,
        description: item_map.content_html
    };
    return singleObj;

}
function clickPoup(mapLng,mapLat) {
    var map = new mapboxgl.Map({
        container: 'map-search-form',
        style: 'mapbox://styles/mapbox/light-v10?optimize=true',
        center: [mapLng, mapLat],
        zoom: 6,
    });
}
