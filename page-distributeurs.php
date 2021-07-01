<?php

use Mediapilote\Wordpress\Theme;


/**
 * Template Name: [NOM_SITE] Page distributeurs
 */

$context = Theme::get()->initContext();
$post = new TimberPost();
$context['post'] = $post;


/**
 * @controleur code
 */
if (!empty($_POST['cp'])) {
	$context['getValuePost'] 	= $_POST['cp'];
	$allDistributor = getAllDistributor();
	$valuePost 		= getLatAndLng($_POST['cp']);
	if (is_null($valuePost) || empty($valuePost)) {
		$context['notResult']   = "Désolé, aucun résultat n'a été trouvé";
	}
	if (!is_null($valuePost)) {
		$arrayDistance = [];
		foreach ($allDistributor as $key => $distance) {
			$distancePoint = getDistance($allDistributor[$key], $valuePost );
			if ($distancePoint <= 30.00) {
				$arrayDistance[] = $distance;
			}
		}
		if (!empty($arrayDistance)) {
			$context['mapMarker'] 	= 'post';
			$context['dataPost']    = json_encode($valuePost);
			$context['getData'] 	= json_encode($arrayDistance);
		} else {
			$context['notResult']   = "Désolé, aucun résultat n'a été trouvé";
		} 
		
	}
}

function getAllDistributor() {
	$args = array(
	  'numberposts' => -1,
	  'post_type'   => 'distributeurs',
	  'post_status' => 'publish'
	);
	$distributeurs = get_posts( $args );
	$allElement = [];
	foreach ($distributeurs as $key => $map) {
		$id 	= $map->ID;
		$local  = get_field('geolocalisation_distributeur', $id);
		$title  = $map->post_title;
		$address = get_field('adresse_distributeur', $id);
		if (!is_null($local)) {
			if (!empty($local['lat']) || !empty($local['lng'])) {
				$allElement[] = array(
					"lat" => $local['lat'], 
					"lng" => $local['lng'], 
					"entry" => $title, 
					"id" => $id,
					"address" => $address,
					"city" => $local['city'],
					"postCode" => $local['post_code'] 
				);
			}
			
		}
	}
	return $allElement;

}
function getDistance($coord1, $coord2)
    {
        return rad2deg(
            acos((
                sin(deg2rad($coord1['lat'])) * sin(deg2rad($coord2['lat'])))
                 + (cos(deg2rad($coord1['lat'])) * cos(deg2rad($coord2['lat'])) * cos(deg2rad($coord1['lng'] - ($coord2['lng']))))
            )
        ) * 111.13384;
    }

function getPostLatLong($postData) {
 	$url     = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($postData) . ",france&key=".get_google_map_api()."";
 	$details = file_get_contents($url);
 	return json_decode($details, true); 
}

function getLatAndLng($postData) {
	$result = getPostLatLong($postData);
	if ($result['status'] != 'ZERO_RESULTS') {
		$lat     = $result['results'][0]['geometry']['location']['lat'];
		$lng     = $result['results'][0]['geometry']['location']['lng'];
	}
	if (!empty($lat) && !empty($lng)) {
		$coord 	 	 = array("lat" => $lat, "lng" => $lng);
		return $coord;
	}
}

//rendu
$context['api_key_google_map'] = get_google_map_api();
Theme::get()->render(array(
	"distributeurs/page-distributeurs.html.twig"
), $context);