<?php
 /*
  Plugin Name: Google Maps - Simple Pins
  Plugin URI: http://bunte-giraffe.de/google-maps-simple-pins-wordpress-plugin
  Description: Easy way to manage maps and locations on maps in WordPress.
  Version: 1.3
  Author: Bunte Giraffe
  Author URI: http://bunte-giraffe.de
  License: GPLv2
  Domain Path: /languages
  Text Domain: google-maps-simple-pins
  */
 
/* Make sure we don't expose any info if called directly */
if ( !function_exists( 'add_action' ) ) {
	die("This application is not meant to be called directly!");
}

require_once('SavedMaps.php');
require_once('ErrorLogging.php');

/* Tabs-related includes */
require_once('PluginContext.php');
require_once('TabView.php');
require_once('TabManager.php');
require_once('TabMarkers.php');
require_once('TabMaps.php');
require_once('TabIconManager.php');
require_once('TabMapStyles.php');
require_once('TabExportImport.php');
require_once('TabSettings.php');

/* PRO-related includes */
if (file_exists(plugin_dir_path( __FILE__ ) . 'EditMarker.php') && 
	file_exists(plugin_dir_path( __FILE__ ) . 'CloneMap.php') && 
	file_exists(plugin_dir_path( __FILE__ ) . 'AddWidget.php') && 
	file_exists(plugin_dir_path( __FILE__ ) . 'IconManager.php') &&
	file_exists(plugin_dir_path( __FILE__ ) . 'MapStylesManager.php')  && 
	file_exists(plugin_dir_path( __FILE__ ) . 'ExportImport.php') ) {
		require_once('EditMarker.php');
		require_once('CloneMap.php');
		require_once('AddWidget.php');
		require_once('IconManager.php');
		require_once('MapStylesManager.php');
		require_once('ExportImport.php');
		define("GMSP_PRO_VERSION", true);
		$pro_icons = new gmsp_IconManager();	
}


register_uninstall_hook( __FILE__, 'gmsp_plugin_uninstall' );
register_activation_hook( __FILE__, 'gmsp_plugin_activate' );
register_deactivation_hook( __FILE__, 'gmsp_plugin_deactivate' );


/* Adding a custom "Admin Action" to hook to requests sent from plugin's backend
 */
add_action( "admin_post_gmsp_save_prepared_marker",
	"gmsp_save_prepared_marker" );
add_action( "admin_post_gmsp_delete_selected_marker",
	"gmsp_delete_selected_marker" );
add_action( "admin_post_gmsp_add_new_map",
	"gmsp_add_new_map" );
add_action( "admin_post_gmsp_save_map_with_selected_markers",
	"gmsp_save_map_with_selected_markers" );
add_action( "admin_post_gmsp_save_api_key", "gmsp_save_api_key" );
add_action( "admin_post_gmsp_submit_feedback", "gmsp_submit_feedback" );
add_action( "admin_post_gmsp_add_new_map_type", "gmsp_add_new_map_type");
add_action( "admin_post_gmsp_remove_map_type", "gmsp_remove_map_type");
add_action( "admin_menu", "gmsp_add_plugin_mgmgt_menu" );
add_action( "init", "gmsp_plugin_register_shortcodes" );
add_action( "init", "gmsp_register_icons_post_type" );
add_action( "admin_enqueue_scripts", "gmsp_plugin_add_google_maps_API" );
add_action( "admin_init", "gmsp_plugin_register_marker_icons");
add_filter('widget_text', 'do_shortcode');

function gmsp_plugin_activate()  { 
}

function gmsp_plugin_deactivate(){
}

function gmsp_plugin_uninstall() { 
	delete_option( "gmsp_Markers" );
	delete_option( "gmsp-Maps" );
	delete_option( "gmsp-apiKey" );
	delete_option( "gmsp_Icons" );
	delete_option( "gmsp_ProIcons" );
	delete_option("gmsp_MapTypes" );
	gmsp_plugin_remove_marker_icons();
}


/* Add Google Maps API to plugin options page */
function gmsp_plugin_add_google_maps_API() {
	global $pagenow;
	
	wp_enqueue_style( 'styleBE-gmsp.css', plugins_url( "assets/css/styleBE.css", __FILE__ ) );
		
	if ($pagenow == 'tools.php') {
		wp_enqueue_script( "google-maps-api",
			"https://maps.googleapis.com/maps/api/js?v=3&libraries=places&key="
			. get_option("gmsp-apiKey")
		);
        wp_enqueue_script( 'postbox' );
		wp_enqueue_script( "gmsp-custom-map-styles",
			plugins_url( "assets/js/gmsp-custom-map-styles.js", __FILE__ ), '', '', true );
		wp_enqueue_script( "gmsp-admin-script",
			plugins_url( "assets/js/gmsp-admin-script.js", __FILE__ ), array('jquery', 'postbox') );
		wp_enqueue_script( 'gmsp-marker-clustering',
			plugins_url( 'assets/js/markerclusterer.js', __FILE__ ) );
		wp_localize_script('gmsp-admin-script', 'GMSP_URL',
			array( 'siteurl' => plugins_url( "", __FILE__ ) ));
		wp_localize_script('gmsp-marker-clustering', 'GMSP_BACKEND_CLUSTERING', '1');
		if (defined('GMSP_PRO_VERSION')) {
			wp_enqueue_script( "gmsp-edit-marker",
				plugins_url( "assets/js/gmsp-edit-marker.js", __FILE__ ) );
		}
	}
}


/**
 * Register the Plugin options menu under "Tools" menu
 */
function gmsp_add_plugin_mgmgt_menu() {
	if (defined('GMSP_PRO_VERSION')) {
		add_management_page( /* Add submenu to Tools menu */
			"Google Maps - Simple Pins PRO Options:",
			"Google Maps - Simple Pins PRO",
			"edit_posts",
			"google_maps_simple_pins",
			"gmsp_plugin_options_page"
		);
	}
	else {
		add_management_page( /* Add submenu to Tools menu */
			"Google Maps - Simple Pins Options:",
			"Google Maps - Simple Pins",
			"edit_posts",
			"google_maps_simple_pins",
			"gmsp_plugin_options_page"
		);
	}
}

 /**
 * Insert Settings link under the plugin name
 */
function gmsp_settings_link($links, $file) {

	if (defined('GMSP_PRO_VERSION')) {
		if ( $file == plugin_basename( __FILE__  ) ) {
			$links['settings'] = sprintf( '<a href="%s"> %s </a>',
				admin_url( 'tools.php?page=google_maps_simple_pins' ),
				__( 'Create Maps', 'plugin_domain' ) 
			);
		}
	}
	else {
		if ( $file == 'simple-pins-for-google-maps/GoogleMapsSimplePins.php' ) {
			$links['settings'] =
				sprintf( '<a href="%s"> %s </a>',
				admin_url( 'tools.php?page=google_maps_simple_pins' ),
				__( 'Create Maps', 'plugin_domain' )
			);
		}
	}

    return $links;
}
add_filter('plugin_action_links', 'gmsp_settings_link', 10, 2);

function gmsp_plugin_options_page() {

	$gmspProVersionUsage = defined('GMSP_PRO_VERSION') 
		? "gmsp_pro"
		: "gmsp_free";
		
	gmsp_setDefaultOptions( $gmspProVersionUsage);
	
	/* Prepare Plugin Context */
	$pluginContext = new gmsp_PluginContext( );
	$pluginContext->setDefaultTabName( "add_marker");
	$pluginContext->setAdminPostUrl( admin_url( 'admin-post.php' ) );
	$pluginContext->setIconForInfo(
		plugins_url( 'assets/img/info.png', __FILE__) );
	$pluginContext->setIconForMapPin( 
		plugins_url( 'assets/img/map-pin.png', __FILE__) );
	$pluginContext->setRedirectValue( $_SERVER['REQUEST_URI'] );
	$pluginContext->setAllTumbs( gmsp_plugin_get_all_thumbs());
	$pluginContext->setStoredMarkers( get_option( "gmsp_Markers", false));
	$pluginContext->setApiKeyObtainUrl(
		"https://console.developers.google.com/apis/credentials" );
	
	$tmpApiKeyOption = get_option( "gmsp-apiKey", false);
	$pluginContext->setApiKey( ( $tmpApiKeyOption ) ? 
			"value=" . $tmpApiKeyOption :
			"placeholder=\"API key not set\"" );

	/* Check if Goole Maps API key has been entered */
	$pluginContext->setActiveTabName(
		isset( $_GET[ "tab"] )
			? $_GET[ "tab"]
			: $pluginContext->getDefaultTabName()
	);

	$pluginContext->setProVersionUsage( $gmspProVersionUsage);
	$pluginContext->setForceApiLoad( get_option( "gmsp_ForceApiLoad", false) );
	
	/* Init. Tab Manager and populate it with tabs */
	$tabManager = new gmsp_TabManager( new gmsp_TabView( $pluginContext) );

	$tmpTabMarkers = new gmsp_TabMarkers( $pluginContext);
	$tabManager->addTab( $tmpTabMarkers);	
	$tabManager->addTab( new gmsp_TabMaps( $pluginContext) );
	$tabManager->addTab( new gmsp_TabIconManager( $pluginContext) );
	$tabManager->addTab( new gmsp_TabMapStylesManager( $pluginContext) );
	$tabManager->addTab( new gmsp_TabExportImport( $pluginContext) );
	
	$tmpTabSettings = new gmsp_TabSettings( $pluginContext);
	$tabManager->addTab( $tmpTabSettings);	
	
	if( false === $tmpApiKeyOption ) {
		$tabManager->setActiveTab( "settings");
	}
	else {
		$tabManager->setActiveTab( $pluginContext->getActiveTabName() );
	}

	if( !$tabManager->displayActiveTab() ) {
		gmsp_LogFile::getInstance()->Error( __FUNCTION__ 
			. ": Failed to disply active tab"
		);
	}
}

/**
 * \brief If don't exist, set default option values
 * 
 * \param $gmspProVersionUsage[in] - holds the version designator (free/pro)
 */
function gmsp_setDefaultOptions( $gmspProVersionUsage = 'gmsp_free') {
	if (!get_option( "gmsp_Markers", false )) {
		add_option( "gmsp_Markers", 
			array( "gmsp_marker_57cdd5be79d7f" => array("name" => "wordpress.com", 
                "lat" => "37.783991", 
                "ln" => "-122.39736299999998", 
                "info" => "<b>Wordpress CMS</b><br/>My favourite CMS for blogging and more. <a href='http://wordpress.org'>wordpress.org</a>", 
                "img" => plugins_url( "assets/img/wp.png", __FILE__ ), 
                "crop" => "cover",
				"icon" => plugins_url( "assets/img/markers/flag-export.png", __FILE__ ) ?  plugins_url( "assets/img/markers/flag-export.png", __FILE__ ) : ''
				),
			) 
		);	
	}

	if( !get_option( "gmsp-Maps", false) ) {
		add_option( "gmsp-Maps", 
			array( "gmsp-map-57cdd5f9017f8" => array (
					"name" => "wordpress.com",
					"markers-on-map" => array( 0 => "gmsp_marker_57cdd5be79d7f" ),
					"center-coords" => "37.78388182918197, -122.39818308540629",
					"zoom-factor" => "16"
				)
			)
		);
	}

	if ($gmspProVersionUsage == "gmsp_pro") {

		if ( !get_option( "gmsp_ProIcons", false) ) {
			add_option( "gmsp_ProIcons", array("airport.png", "bank.png", "fastfood.png") );
		}
	}
	
	if (!get_option("gmsp_ForceApiLoad", false)) {
		add_option("gmsp_ForceApiLoad","0");
	}

	if (!get_option("gmsp_MapTypes", false)) {
		add_option( "gmsp_MapTypes", 
			array( 
			"Roadmap" => '[]',
			"Terrain" => '[]',
			"Satellite" => '[]',
			"Hybrid" => '[]',
			"Retro" => '[{\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#ebe3cd\"}]},{\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#523735\"}]},{\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#f5f1e6\"}]},{\"featureType\":\"administrative\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#c9b2a6\"}]},{\"featureType\":\"administrative.land_parcel\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#dcd2be\"}]},{\"featureType\":\"administrative.land_parcel\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#ae9e90\"}]},{\"featureType\":\"landscape.natural\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#dfd2ae\"}]},{\"featureType\":\"poi\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#dfd2ae\"}]},{\"featureType\":\"poi\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#93817c\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"geometry.fill\",\"stylers\":[{\"color\":\"#a5b076\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#447530\"}]},{\"featureType\":\"road\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#f5f1e6\"}]},{\"featureType\":\"road.arterial\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#fdfcf8\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#f8c967\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#e9bc62\"}]},{\"featureType\":\"road.highway.controlled_access\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#e98d58\"}]},{\"featureType\":\"road.highway.controlled_access\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#db8555\"}]},{\"featureType\":\"road.local\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#806b63\"}]},{\"featureType\":\"transit.line\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#dfd2ae\"}]},{\"featureType\":\"transit.line\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#8f7d77\"}]},{\"featureType\":\"transit.line\",\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#ebe3cd\"}]},{\"featureType\":\"transit.station\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#dfd2ae\"}]},{\"featureType\":\"water\",\"elementType\":\"geometry.fill\",\"stylers\":[{\"color\":\"#b9d3c2\"}]},{\"featureType\":\"water\",\"elementType\":\"labels.text.fill\", \"stylers\":[{\"color\": \"#92998d\"}]} ]',
			"Grayscale" => 
			'[{"elementType": "geometry","stylers": [{"color": "#f5f5f5"}]},{"elementType": "labels.icon","stylers": [{"visibility": "off"}]},{"elementType": "labels.text.fill","stylers": [{"color": "#616161"}]},{"elementType": "labels.text.stroke","stylers": [{"color": "#f5f5f5"}]},{"featureType": "administrative.land_parcel","elementType": "labels.text.fill","stylers": [{"color": "#bdbdbd"}]},{"featureType": "poi","elementType": "geometry","stylers": [{"color": "#eeeeee"}]},{"featureType": "poi","elementType": "labels.text.fill","stylers": [{"color": "#757575"}]},{"featureType": "poi.park","elementType": "geometry","stylers": [{"color": "#e5e5e5"}]},{"featureType": "poi.park","elementType": "labels.text.fill","stylers": [{"color": "#9e9e9e"}]},{"featureType": "road","elementType": "geometry","stylers": [{"color": "#ffffff"}]},{"featureType": "road.arterial","elementType": "labels.text.fill","stylers": [{"color": "#757575"}]},{"featureType": "road.highway","elementType": "geometry","stylers": [{"color": "#dadada"}]},{"featureType": "road.highway","elementType": "labels.text.fill","stylers": [{"color": "#616161"}]},{"featureType": "road.local","elementType": "labels.text.fill","stylers": [{"color": "#9e9e9e"}]},{"featureType": "transit.line","elementType": "geometry","stylers": [{"color": "#e5e5e5"}]},{"featureType": "transit.station","elementType": "geometry","stylers": [{"color": "#eeeeee"}]},{"featureType": "water","elementType": "geometry","stylers": [{"color": "#c9c9c9"}]},{"featureType": "water","elementType": "labels.text.fill","stylers": [{"color": "#9e9e9e"}]}]',
			"Pale-Dawn" => '[{\"featureType\":\"administrative\",\"elementType\":\"all\",\"stylers\":[{\"visibility\":\"on\"},{\"lightness\":33}]},{\"featureType\":\"landscape\",\"elementType\":\"all\",\"stylers\":[{\"color\":\"#f2e5d4\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#c5dac6\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"labels\",\"stylers\":[{\"visibility\":\"on\"},{\"lightness\":20}]},{\"featureType\":\"road\",\"elementType\":\"all\",\"stylers\":[{\"lightness\":20}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#c5c6c6\"}]},{\"featureType\":\"road.arterial\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#e4d7c6\"}]},{\"featureType\":\"road.local\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#fbfaf7\"}]},{\"featureType\":\"water\",\"elementType\":\"all\",\"stylers\":[{\"visibility\":\"on\"},{\"color\":\"#acbcc9\"}]}]',
			"Nature" => '[{\"featureType\":\"landscape\",\"stylers\":[{\"hue\":\"#FFA800\"},{\"saturation\":0},{\"lightness\":0},{\"gamma\":1}]},{\"featureType\":\"road.highway\",\"stylers\":[{\"hue\":\"#53FF00\"},{\"saturation\":-73},{\"lightness\":40},{\"gamma\":1}]},{\"featureType\":\"road.arterial\",\"stylers\":[{\"hue\":\"#FBFF00\"},{\"saturation\":0},{\"lightness\":0},{\"gamma\":1}]},{\"featureType\":\"road.local\",\"stylers\":[{\"hue\":\"#00FFFD\"},{\"saturation\":0},{\"lightness\":30},{\"gamma\":1}]},{\"featureType\":\"water\",\"stylers\":[{\"hue\":\"#00BFFF\"},{\"saturation\":6},{\"lightness\":8},{\"gamma\":1}]},{\"featureType\":\"poi\",\"stylers\":[{\"hue\":\"#679714\"},{\"saturation\":33.4},{\"lightness\":-25.4},{\"gamma\":1}]}]',
			"Hopper" => '[{\"featureType\":\"water\",\"elementType\":\"geometry\",\"stylers\":[{\"hue\":\"#165c64\"},{\"saturation\":34},{\"lightness\":-69},{\"visibility\":\"on\"}]},{\"featureType\":\"landscape\",\"elementType\":\"geometry\",\"stylers\":[{\"hue\":\"#b7caaa\"},{\"saturation\":-14},{\"lightness\":-18},{\"visibility\":\"on\"}]},{\"featureType\":\"landscape.man_made\",\"elementType\":\"all\",\"stylers\":[{\"hue\":\"#cbdac1\"},{\"saturation\":-6},{\"lightness\":-9},{\"visibility\":\"on\"}]},{\"featureType\":\"road\",\"elementType\":\"geometry\",\"stylers\":[{\"hue\":\"#8d9b83\"},{\"saturation\":-89},{\"lightness\":-12},{\"visibility\":\"on\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry\",\"stylers\":[{\"hue\":\"#d4dad0\"},{\"saturation\":-88},{\"lightness\":54},{\"visibility\":\"simplified\"}]},{\"featureType\":\"road.arterial\",\"elementType\":\"geometry\",\"stylers\":[{\"hue\":\"#bdc5b6\"},{\"saturation\":-89},{\"lightness\":-3},{\"visibility\":\"simplified\"}]},{\"featureType\":\"road.local\",\"elementType\":\"geometry\",\"stylers\":[{\"hue\":\"#bdc5b6\"},{\"saturation\":-89},{\"lightness\":-26},{\"visibility\":\"on\"}]},{\"featureType\":\"poi\",\"elementType\":\"geometry\",\"stylers\":[{\"hue\":\"#c17118\"},{\"saturation\":61},{\"lightness\":-45},{\"visibility\":\"on\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"all\",\"stylers\":[{\"hue\":\"#8ba975\"},{\"saturation\":-46},{\"lightness\":-28},{\"visibility\":\"on\"}]},{\"featureType\":\"transit\",\"elementType\":\"geometry\",\"stylers\":[{\"hue\":\"#a43218\"},{\"saturation\":74},{\"lightness\":-51},{\"visibility\":\"simplified\"}]},{\"featureType\":\"administrative.province\",\"elementType\":\"all\",\"stylers\":[{\"hue\":\"#ffffff\"},{\"saturation\":0},{\"lightness\":100},{\"visibility\":\"simplified\"}]},{\"featureType\":\"administrative.neighborhood\",\"elementType\":\"all\",\"stylers\":[{\"hue\":\"#ffffff\"},{\"saturation\":0},{\"lightness\":100},{\"visibility\":\"off\"}]},{\"featureType\":\"administrative.locality\",\"elementType\":\"labels\",\"stylers\":[{\"hue\":\"#ffffff\"},{\"saturation\":0},{\"lightness\":100},{\"visibility\":\"off\"}]},{\"featureType\":\"administrative.land_parcel\",\"elementType\":\"all\",\"stylers\":[{\"hue\":\"#ffffff\"},{\"saturation\":0},{\"lightness\":100},{\"visibility\":\"off\"}]},{\"featureType\":\"administrative\",\"elementType\":\"all\",\"stylers\":[{\"hue\":\"#3a3935\"},{\"saturation\":5},{\"lightness\":-57},{\"visibility\":\"off\"}]},{\"featureType\":\"poi.medical\",\"elementType\":\"geometry\",\"stylers\":[{\"hue\":\"#cba923\"},{\"saturation\":50},{\"lightness\":-46},{\"visibility\":\"on\"}]}]',
			"flat-green" =>	'[{\"stylers\":[{\"hue\":\"#bbff00\"},{\"weight\":0.5},{\"gamma\":0.5}]},{\"elementType\":\"labels\",\"stylers\":[{\"visibility\":\"off\"}]},{\"featureType\":\"landscape.natural\",\"stylers\":[{\"color\":\"#a4cc48\"}]},{\"featureType\":\"road\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#ffffff\"},{\"visibility\":\"on\"},{\"weight\":1}]},{\"featureType\":\"administrative\",\"elementType\":\"labels\",\"stylers\":[{\"visibility\":\"on\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"labels\",\"stylers\":[{\"visibility\":\"simplified\"},{\"gamma\":1.14},{\"saturation\":-18}]},{\"featureType\":\"road.highway.controlled_access\",\"elementType\":\"labels\",\"stylers\":[{\"saturation\":30},{\"gamma\":0.76}]},{\"featureType\":\"road.local\",\"stylers\":[{\"visibility\":\"simplified\"},{\"weight\":0.4},{\"lightness\":-8}]},{\"featureType\":\"water\",\"stylers\":[{\"color\":\"#4aaecc\"}]},{\"featureType\":\"landscape.man_made\",\"stylers\":[{\"color\":\"#718e32\"}]},{\"featureType\":\"poi.business\",\"stylers\":[{\"saturation\":68},{\"lightness\":-61}]},{\"featureType\":\"administrative.locality\",\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"weight\":2.7},{\"color\":\"#f4f9e8\"}]},{\"featureType\":\"road.highway.controlled_access\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"weight\":1.5},{\"color\":\"#e53013\"},{\"saturation\":-42},{\"lightness\":28}]}]'
			) 
		);	
	}
}

function gmsp_save_api_key() {
	if ( isset( $_POST["apiKey"] ) ) {
				
		if (!get_option( "gmsp-apiKey", false )) {
			add_option( "gmsp-apiKey", $_POST["apiKey"] );
		}
		else {
			update_option( "gmsp-apiKey", $_POST["apiKey"] );
			
		}
		
		if ( isset( $_POST["gmsp_force_api_load"] ) ) {
			update_option( "gmsp_ForceApiLoad", "1" );
		}
		else {
			update_option( "gmsp_ForceApiLoad", "0" );
		}
		
		$urlBack = urldecode( admin_url( 'tools.php' ) );
		$urlBack = add_query_arg( "page", "google_maps_simple_pins", $urlBack );
		$urlBack = add_query_arg( "tab", "settings", $urlBack);

		wp_safe_redirect( $urlBack );

	}
	else {
		gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": \"apiKey\" is not set in POST request");
	}

}

function gmsp_save_map_with_selected_markers() {
	if( isset( $_POST["delete-edited-map"]) ) {
		if( isset( $_POST["map-id"])) {
			$savedMaps = get_option( "gmsp-Maps", false );
		
			if( array_key_exists( $_POST["map-id"], $savedMaps) ) {
				unset( $savedMaps[ $_POST["map-id"] ]);
				update_option( "gmsp-Maps", $savedMaps);

				/* TODO: Move the "Redirect Back" to separate function, remove duplicated code */
				$urlBack = urldecode( admin_url( 'tools.php' ) );
				$urlBack = add_query_arg( "page", "google_maps_simple_pins", $urlBack );
				$urlBack = add_query_arg( "tab", "add_map", $urlBack);

				wp_safe_redirect( $urlBack );
			}
		}
		else {
			echo "Warning: Map ID has not been set";
			gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": Map ID has not been set");
			return;
		}
	}
	else if( isset($_POST["save-edited-map"]) || isset($_POST['clone-edited-map'])  ) {
		$markersArray = array();
		
		if ( isset ( $_POST[ "map_id"] ) ) {
			$mapId = esc_html( $_POST["map_id"] );
			if( isset( $_POST[ $mapId . "-list-of-selected-tags" ] ) ) {
				$markersArray = $_POST[ $mapId . "-list-of-selected-tags"];
			}
		}
					
		$mapName = false;
		if ( isset ( $_POST['user_selected_map_name'] ) ) {
			$mapName = esc_html( $_POST['user_selected_map_name'] );
		}
		
		if ( isset ( $_POST['mapZoom'] ) && is_numeric( $_POST['mapZoom'] + 0 ) ) {
			$mapZoom = esc_html( $_POST['mapZoom'] );
		}
		
		if (isset($_POST['orig_zoom'])) {
			$mapOrigZoom = esc_html( $_POST['orig_zoom'] );
		}
		
		if ( isset ( $_POST['mapCenter'] ) ) { 
			$centerCoords = explode(',', $_POST['mapCenter']);
			if ($centerCoords && isset($centerCoords[0]) && isset($centerCoords[1])) {
				$centerLat = $centerCoords[0];
				$centerLng = $centerCoords[1];		
				if (is_numeric( $centerLat + 0 ) && is_numeric( $centerLng + 0 ) ) {
					$mapCenter = esc_html( $_POST['mapCenter'] );
				}
			}
		}
		
		if (isset($_POST['orig_center'])) {
			$mapOrigCenter = esc_html( $_POST['orig_center'] );
		}

		if ( isset ( $_POST['autocenter_autozoom'.$mapId] ) ) {
			$mapAutofit = esc_html( $_POST['autocenter_autozoom'.$mapId] );
		}
		
		if ( isset ( $_POST['map_type'.$mapId] ) ) {
			$mapType = esc_html( $_POST['map_type'.$mapId] );
		}
		
		if ( defined('GMSP_PRO_VERSION') && isset($_POST['clone-edited-map']) ) {
			$mapToClone = new gmsp_CloneMap();
			$mapId = $mapToClone->gmsp_cloneEditedMap($mapId);
			$mapName = $mapName.' - copy';
		}		


		$savedMaps = get_option( "gmsp-Maps", false );
		
		if( "gmsp-map-in-progress" == $mapId) {
			
			foreach ($markersArray as $key => $value) {
				$value = str_replace('gmsp-map-in-progress', '', $value);
				$markersArray[$key] = $value;
			}
			
			$savedMaps[ "gmsp-map-in-progress" ][ "name" ] = $mapName;
			$savedMaps[ "gmsp-map-in-progress" ][ "markers-on-map" ] = $markersArray;
			if (isset($mapZoom) && isset($mapCenter)) {
				$savedMaps[ "gmsp-map-in-progress" ][ "zoom-factor" ] = $mapZoom;
				$savedMaps[ "gmsp-map-in-progress" ][ "center-coords" ] = $mapCenter;
			}
			else if (isset($mapOrigCenter) && isset($mapOrigZoom)) {
				$savedMaps[ "gmsp-map-in-progress" ][ "zoom-factor" ] = $mapOrigZoom;
				$savedMaps[ "gmsp-map-in-progress" ][ "center-coords" ] = $mapOrigCenter;
			}
			if (isset($mapAutofit) && isset($mapType)) {
				$savedMaps[ "gmsp-map-in-progress" ][ "auto" ] = $mapAutofit;
				$savedMaps[  "gmsp-map-in-progress" ][ "map-type" ] = $mapType;
			}
			$savedMaps[uniqid( "gmsp-map-")] = $savedMaps[ "gmsp-map-in-progress" ];
			unset( $savedMaps[ "gmsp-map-in-progress" ] );
		}
		else {
			$savedMaps[ $mapId ][ "name" ] = $mapName;
			$savedMaps[ $mapId ][ "markers-on-map" ] = $markersArray;
			if (isset($mapZoom) && isset($mapCenter)) {
				$savedMaps[ $mapId ][ "zoom-factor" ] = $mapZoom;
				$savedMaps[ $mapId ][ "center-coords" ] = $mapCenter;
			}
			if (isset($mapAutofit) && isset($mapType)) {
				$savedMaps[ $mapId ][ "auto" ] = $mapAutofit;
				$savedMaps[ $mapId ][ "map-type" ] = $mapType;
			}
		}
		
		update_option( "gmsp-Maps", $savedMaps);	
		
		$urlBack = urldecode( admin_url( 'tools.php' ) );
		$urlBack = add_query_arg( "page", "google_maps_simple_pins", $urlBack );
		$urlBack = add_query_arg( "tab", "add_map", $urlBack);

		wp_safe_redirect( $urlBack );		
	}
	else {
		echo "Error: Unknown request - no actions will be taken";
		gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": Unknown request");
		return;
	}
}
function gmsp_delete_selected_marker() {
	ob_start();

	if ( isset ( $_POST['checked_markers'] ) ) {
		foreach ($_POST['checked_markers'] as $marker) {
			echo esc_html( $marker ) . "<br>";
		}
	}

	if( isset( $_POST["create_map_with_selected_marker"]) ) {
		echo "Received POST: create_map_with_selected_marker <br>";

		$urlToMapsMgmtPage = urldecode( admin_url( 'tools.php' ) );
		$urlToMapsMgmtPage = add_query_arg( "page",
			"google_maps_simple_pins", $urlToMapsMgmtPage );
		$urlToMapsMgmtPage = add_query_arg( "tab", "add_map",
			$urlToMapsMgmtPage);
		$mapCenter = isset( $_POST["mapCenter"] ) ? $_POST["mapCenter"] : '51,40' ; 
		$mapZoom = isset( $_POST["mapZoom"] ) ? $_POST["mapZoom"] : 0;

		$storedMaps = get_option( "gmsp-Maps", array() );

		$storedMaps["gmsp-map-in-progress"] = array(
			"name" => "Name of your map",
			"markers-on-map" => array(),
			"center-coords" => "",
			"zoom-factor" => "",
		);

		foreach( $_POST['checked_markers'] as $pin) {
			array_push( $storedMaps["gmsp-map-in-progress"]["markers-on-map"], ( $pin) );
		}
		
		$storedMaps["gmsp-map-in-progress"]["center-coords"] = $mapCenter;
		$storedMaps["gmsp-map-in-progress"]["zoom-factor"] = $mapZoom;
		
		echo "<pre>";
		print_r($storedMaps);
		echo "</pre>";
		
		update_option("gmsp-Maps", $storedMaps);
		
		wp_safe_redirect( $urlToMapsMgmtPage );
		
		ob_end_flush();
		
		return;	
	}

	foreach ($_POST['checked_markers'] as $marker) {

		$idMarkerToDelete = $marker;

		$storedMarkers = get_option( "gmsp_Markers", false);

		if( array_key_exists( $idMarkerToDelete, $storedMarkers)) {
			unset( $storedMarkers[ $idMarkerToDelete ] );
		}
		else {
			echo "ERROR: Marker ID=" . $idMarkerToDelete . "has not been found <br>";
			gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": Marker ID not found");
			return;
		}

		update_option( "gmsp_Markers", $storedMarkers);
	}
	
	
	if ( isset ( $_POST['_wp_http_referer'] ) ) {
		$url = urldecode( $_POST['_wp_http_referer'] );
		echo "_wp_http_referer=".$_POST["_wp_http_referer"]."<br>";
		echo "redirect_URL=".$url."<br>";
		wp_safe_redirect( $url );
	}
	else {
		echo "Incorrect POST Request";
		gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": Incorrect POST request");
	}
}


function gmsp_add_new_map() {
	if( isset( $_POST["action"])) {
		if( $_POST["action"] != "gmsp_add_new_map") {
			echo "ERROR: Unexpected action!";
			gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": \"action\" in POST request is not \"gmsp_add_new_map\"");
			return;
		}
	}
	
	$storedMaps = get_option( "gmsp-Maps", array() );

	$storedMaps["gmsp-map-in-progress"] = array(
		"name" => "Name of your map",
		"markers-on-map" => array(),
		"center-coords" => "15.1234567, -15.1234567",
		"zoom-factor" => "1",
	);	

	update_option("gmsp-Maps", $storedMaps);

	if ( isset ( $_POST['_wp_http_referer'] ) ) {
		$url = urldecode( $_POST['_wp_http_referer'] );
		wp_safe_redirect( $url );
	}
	else {
		echo "ERROR: Incorrect POST Request - Can't return back to the source page";
		gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": Incorrect POST request");
	}
}


/* gmsp_save_prepared_marker()
 * Process GET requests from plugin's backend forms
 */
function gmsp_save_prepared_marker() {
	ob_start();
	
	if (defined('GMSP_PRO_VERSION')) {
		global $pro_icons;
	}
	
	if ( isset ( $_POST['marker_name'] ) ) {
		//echo esc_html( $_POST['marker_name'] );
	}
	else {
		gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": \"marker_name\" not set in POST request");
		ob_end_flush();
		exit;
	}
	
	$storedMarkers = get_option( "gmsp_Markers", false);
	
	if( ! $storedMarkers) {
		$storedMarkers = array();
	}
	
	/* Getting info for InfoWindow */
	
	$marker_info = '';
	
	if (isset($_POST["marker_info_title"]) && !empty($_POST["marker_info_title"])) {
		$marker_info .= '<b>'.$_POST["marker_info_title"].'</b><br>';
	}
	
	if (isset($_POST["marker_info_body"]) && !empty($_POST["marker_info_body"])) {
		$marker_info .= $_POST["marker_info_body"];
	}
	
	if (isset($_POST["marker_info_body_mce"]) && !empty($_POST["marker_info_body_mce"])) {
		$marker_info = $_POST["marker_info_body_mce"];
		$marker_info_rte = 1;
	}

	
	if (isset($_POST["marker_info_img"]) && !empty($_POST["marker_info_img"])) {
		$marker_img = $_POST["marker_info_img"];
	}
	
	if (isset($_POST["marker_info_img_crop"]) && !empty($_POST["marker_info_img_crop"])) {
		$marker_img_crop = $_POST["marker_info_img_crop"];
	}
	
	if (isset($_POST["info_win_width"]) && !empty($_POST["info_win_width"]) ) {
		$marker_info_width = $_POST["info_win_width"];
	}
	
	/* Getting custom marker icon */
	
	if (isset($_POST["custom_icon_marker"]) && !empty($_POST["custom_icon_marker"]) ) {
		$marker_icon_id = $_POST["custom_icon_marker"];
		$marker_icon = defined('GMSP_PRO_VERSION') ? $pro_icons->gmsp_getIconUrl($marker_icon_id) : wp_get_attachment_url($marker_icon_id);
	}

	$updatedMarkers = array(
		"name" => isset($_POST["marker_name"]) ? $_POST["marker_name"] : 'Unnamed',
		"lat" => ( isset( $_POST["marker_latitude"] ) && is_numeric( $_POST["marker_latitude"] )) ? $_POST["marker_latitude"] : 51.496,
		"ln" => ( isset( $_POST["marker_longitude"] ) && is_numeric( $_POST["marker_longitude"] )) ? $_POST["marker_longitude"] : -0.014,
		"info" => (!empty($marker_info)) ? $marker_info : '',
		"img" => (!empty($marker_img)) ? $marker_img : '',
		"crop" => (!empty($marker_img_crop)) ? $marker_img_crop : 'cover',
		"icon" => (!empty($marker_icon)) ? $marker_icon : '',
		"rte" => (!empty($marker_info_rte)) ? $marker_info_rte : 0,
		"info_width" => (!empty($marker_info_width)) ? $marker_info_width : ''
	);

	if ( defined('GMSP_PRO_VERSION') && !empty($_POST['marker_id']) ) {
		$editMarker = new gmsp_EditMarker();
		if (isset($_POST['clone_selected_marker'])) {
			$editMarker->gmsp_cloneMarker($_POST['marker_id'], $updatedMarkers);
		}
		else {
			$editMarker->gmsp_saveEditedMarker($_POST['marker_id'], $updatedMarkers);
		}
	}
	else {
		/* Add new Marker to the top of the list of saved Markers */
		$storedMarkers = array( 
			uniqid("gmsp_marker_") => $updatedMarkers
		) + $storedMarkers;

		/* Write marker to settings */
		update_option( "gmsp_Markers", $storedMarkers);
	}
	
	//echo "<pre>";
	//print_r( $storedMarkers );
	//echo "</pre>";

	if ( isset ( $_POST['_wp_http_referer'] ) ) {
		if ($_POST['_wp_http_referer'] === "continue") {
			ob_end_flush();
			return;
		}
		else {
			$url = add_query_arg( 'msg', '', urldecode( $_POST['_wp_http_referer'] ) );
			echo "_wp_http_referer=".$_POST["_wp_http_referer"]."<br>";
			echo "redirect_URL=".$url."<br>";
			wp_safe_redirect( $url );
		}
	}
	else {
		echo "Incorrect POST Request";
		gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": Incorrect POST request");
	}
	
	ob_end_flush();

	exit;
}

if (get_option("gmsp_ForceApiLoad", false) !== false) {
	if (get_option("gmsp_ForceApiLoad") === '1') {
		add_action('wp_footer','gmsp_deregister_other_google_maps_scripts');
		add_action('wp_print_scripts','gmsp_deregister_other_google_maps_scripts');
	}
	else if (get_option("gmsp_ForceApiLoad") === '0') {
		add_action('wp_footer','gmsp_google_maps_script_loader');
	}
}

function gmsp_google_maps_script_loader() {
    global $wp_scripts; $gmapsenqueued = false;
    foreach ($wp_scripts->registered as $key => $script) {
        if (preg_match('#maps\.google(?:\w+)?\.com/maps/api/js#', $script->src)) {
			if( wp_script_is( $key, 'enqueued') === true) { 
				gmsp_LogFile::getInstance()->Warning( __FUNCTION__
					. ": Already enqueued with handle "
					.$script->handle 
				);

				$gmapsenqueued = true;
			}
        }
    }

    if( !$gmapsenqueued) {
        wp_enqueue_script( "google-maps-api-fe",
		"https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=" . 
			get_option("gmsp-apiKey")
		);
    }
}

function gmsp_deregister_other_google_maps_scripts() {
	if ( ! is_admin() ) {
		global $wp_scripts; $gmapsenqueued = false;
		foreach ($wp_scripts->registered as $key => $script) {
			if (preg_match('#maps\.google(?:\w+)?\.com/maps/api/js#', $script->src)) {
				if (wp_script_is($key, 'enqueued') === true){ 
									
					wp_enqueue_script("google-maps-api-fe",
					"https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=" . 
						get_option("gmsp-apiKey"), ''
					);
					
					gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": Already enqueued with handle ".$script->handle);
					
					// Dequeue script to fix api errors
					wp_deregister_script($script->handle);
					wp_dequeue_script($script->handle);

					$gmapsenqueued = true;		
				}
			}
			else {
				//gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": Handle ".$script->handle." Src ".$script->src);
			}
		}

		if (!$gmapsenqueued) {
			wp_enqueue_script("google-maps-api-fe",
			"https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=" . 
				get_option("gmsp-apiKey"), ''
			);
		}
	}
}


/* Add Shortcode Map */
function gmsp_shortcode_map( $attr, $content = null) {

	wp_enqueue_script( "gmsp-custom-map-styles", plugins_url( "assets/js/gmsp-custom-map-styles.js", __FILE__ ), '', '', true );
	
	wp_enqueue_script( 'gmsp-marker-clustering', plugins_url( 'assets/js/markerclusterer.js', __FILE__ ) );
			
	wp_enqueue_script( "insert-map-instead-of-short-code",
		plugins_url( "assets/js/short-code.js", __FILE__ ), array('jquery'), false, true
	);
		
	wp_localize_script('insert-map-instead-of-short-code', 'GMSP_URL', array( 'siteurl' => plugins_url( "", __FILE__ ) ));
	
	wp_enqueue_style( 'style.css', plugins_url( "assets/css/style.css", __FILE__ ) );
		
	$a = shortcode_atts( 
		array( 	'id' => 'not-set',
				'w' => '100%',
				'h' => '250px',
				'nocontrols' => '0',
				'noscroll' => '0',
				'cluster' => '0',
				'open' => '' ), 	
		$attr
	);
	
	$mapId = $a["id"];
	$mapWidth = $a["w"];
	$mapHeight = $a["h"];	
	$mapControls = $a["nocontrols"];
	$mapScroll = $a["noscroll"];
	$markerClustering = $a["cluster"];
	$mapOpenInfoOnMarker = $a["open"];
	$mapMarkers = array();
	$tmpShortCode = '';
	
	/* Random number in container id allows for multiple containers (and maps) on the page */
	$mapContainerId = "map-in-post-" . $mapId . "-" . rand(111, 999);
	
	/* Get maps and markers from DB */
	$allMaps = get_option( "gmsp-Maps", false );
	$allMarkers = get_option( "gmsp_Markers", false );
	
	if (!isset($allMaps[$mapId])) {
		gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": Deleted map request");
		return "<div> This map has been deleted </div>";
	}
	else {
		/* Get map options by id */
		$mapParams = $allMaps[$mapId];
	}
	
	$marker_icons = [];
	
	foreach ($mapParams["markers-on-map"] as $marker) {
		if (!isset($allMarkers[$marker])) {
			gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": Deleted marker request");
		}
		else {
			if ($allMarkers[$marker]['icon']) {
				array_push($marker_icons, $allMarkers[$marker]['icon']);
			}
		}
	}
	
	$marker_icons = array_unique($marker_icons);
	
	foreach ($marker_icons as $marker_icon) {
		$headers = wp_get_http_headers($marker_icon);
				
		if ($headers === false) {
			if(($key = array_search($marker_icon, $marker_icons)) !== false) {
				unset($marker_icons[$key]);
			}
		}
		else {
			if (isset($headers->getAll()["content-type"])) {
				if ($headers->getAll()["content-type"] !== "image/png") {
					if(($key = array_search($marker_icon, $marker_icons)) !== false) {
						unset($marker_icons[$key]);
					}
				}
			}
		}
	}
	
	foreach ($mapParams["markers-on-map"] as $marker) {
		if (!isset($allMarkers[$marker])) {
			//gmsp_LogFile::getInstance()->Warning(__FUNCTION__ . ": Deleted marker request");
		}
		else {
			/* Check marker icon */
			if ($allMarkers[$marker]['icon']) {		
				if ( !in_array($allMarkers[$marker]['icon'], $marker_icons) ) {

					if (current_user_can('manage_options') && !strpos($allMarkers[$marker]['icon'],"gmspNoneSelected")) {
						$tmpShortCode .= "<div style='background-color:beige;padding:10px;font-size:75%;margin:10px 0px;'>Missing marker icon ".$allMarkers[$marker]['icon']." for marker ".$allMarkers[$marker]['name']."</br></div>";
					}
					unset($allMarkers[$marker]['icon']);
				}
			}
			
			/* Add markers to map */
			$allMarkers[$marker]['id'] = $marker;
			array_push($mapMarkers, $allMarkers[$marker]);				
		}
	}
	
	$storedMapTypes = get_option( "gmsp_MapTypes", false);
	$mapTypesArray = $mapTypesJSA = array();
	if (!$storedMapTypes) { 
		gmsp_setDefaultOptions();
		$storedMapTypes = get_option( "gmsp_MapTypes");
	}
	foreach( $storedMapTypes as $name => $mapType) {
		$mapTypesArray[strtoupper($name)] = "gmsp_".str_replace('-','_',$name)."StyledMapType";
		$mapTypesJSA[strtoupper($name)] = $mapType;
	}
	
	wp_localize_script ( 'gmsp-custom-map-styles', 'gmsp_MapTypes', $mapTypesArray);
	wp_localize_script ( 'gmsp-custom-map-styles', 'gmsp_MapTypesJSA', $mapTypesJSA);

	
	
		
	$tmpShortCode .="
		<div id=\"" . $mapContainerId . "\" \" style=\"width:$mapWidth; height:$mapHeight\">
			Loading map ... 
		</div>
		<div style=\"clear:both;\"></div>		
		<script type=\"text/javascript\">
		//<![CDATA[
		jQuery(document).ready(function() {
			showMapByShortCode( { id:'" . $mapContainerId . "', 
					center:'" . $mapParams['center-coords'] . "',
					zoom: '" . $mapParams['zoom-factor'] . "',
					markers:" . json_encode($mapMarkers) . ",
					auto: '". (isset($mapParams['auto']) ? $mapParams['auto'] : 0) ."',
					mapType: '". (isset($mapParams['map-type']) ? $mapParams['map-type'] : 'roadmap') ."',
					mapControls: '". $mapControls ."',
					mapScroll: '". $mapScroll ."',
					markerClustering: '". $markerClustering ."',
					openInfo: '".$mapOpenInfoOnMarker ."'
			});
				
		});
		//]]>
		</script>";
		
	return $tmpShortCode;
}


function gmsp_plugin_register_shortcodes(){
	add_shortcode("gmsp_map", "gmsp_shortcode_map");
}


/* gmsp_plugin_get_all_thumbs - Displays thumbnails of all
 * icons stored fro GMSP as a post attachment
 * 
 * TODO: Remove settings, 
 *	like thumbnails dimensions out of the function, and pass as a parameter
 *
 * TODO: Re-format HTML - use templates instead of string concatenations
*/
function gmsp_plugin_get_all_thumbs() {
	
	$imageWidthPx = 32;
	$imageHeightPx = 37;
	$thumbnailsInRow = 9;
	
	if (defined('GMSP_PRO_VERSION')) {
		global $pro_icons;
		$attachments = $pro_icons->gmsp_getAllMarkerIcons();
	}
	else {
		$tmpExistingPosts = get_posts(
			array( "post_type" => "gmsp-marker-icons") );
		
		if( ! array_key_exists( 0, $tmpExistingPosts) ) {
			/* TODO: Add a warning to error log */
			return;
		}

		$post_parent = $tmpExistingPosts[ 0 ]->ID;

		add_image_size( 'gmsp-icon', $imageWidthPx, $imageHeightPx	);

		$attachments = get_posts( array (
			'post_type'   => 'attachment',
			'post_parent' => $post_parent,
			'posts_per_page' => -1,
		) );

		$retValThumb = "";
	}
	

	$retValThumb =  "<div class='marker-icons'>";

	$i = 0;
	if ($attachments) {
		foreach ( $attachments as $attachment ) {
			$retValThumb .= ( $i % $thumbnailsInRow === 0 ) ?
				"</div> <div class='clear'> </div> <div class='marker-icons' style='padding-bottom: 15px; height:62px'>" : '';
			$retValThumb .= '<div style="float:left; width:36px; height: 62px; position: relative;">' .
				(defined('GMSP_PRO_VERSION') ? $pro_icons->gmsp_getIcon($attachment) : wp_get_attachment_image( $attachment->ID, 'gmsp-icon', true )) . 
				'<input type="radio" name="custom_icon_marker" value="'.(defined('GMSP_PRO_VERSION') ? $attachment : $attachment->ID) . 
				'" style="position:absolute;bottom:0;left:10px"></input></div>';
			$i++;
		}
	}
	if ( defined('GMSP_PRO_VERSION') ) {
		$retValThumb .= '<div class="clear"><div>Please go to <a href="?page=google_maps_simple_pins&tab=icon_manager">Manage Marker Icons</a> tab to add icons</div>';
	}
	
	$retValThumb .= "</div><div class='clear'></div>";
	
	return $retValThumb;
}


function gmsp_plugin_register_marker_icons() {
	if (!defined('GMSP_PRO_VERSION')) {
		if (!get_option( "gmsp_Icons")) {
			gmsp_register_icons_post_type();
			gmsp_plugin_add_marker_icons();
			add_option( "gmsp_Icons", "true"); 
		}
	}
}


function gmsp_register_icons_post_type() {
	register_post_type( 'gmsp-marker-icons' );
}


function gmsp_plugin_add_marker_icons() {
	if (post_type_exists( 'gmsp-marker-icons' )) {
		if (get_posts( array( "post_type" => "gmsp-marker-icons") )) {
			$uploaded_media = array();
			$posts_of_gmsp_type = get_posts( array( "post_type" => "gmsp-marker-icons") );
			$parent_post = $posts_of_gmsp_type[0];
			$parent_post_id = $parent_post->ID;	
			$media = get_attached_media( 'image', $parent_post_id);
			if ($media) {
				foreach($media as $medium) {
					$uploaded_media[] = $medium->post_title;
				}
			}
		}
		else {
			$postarr = array(
				'post_type' => 'gmsp-marker-icons',
				'post_status' => 'publish'
			);
			$parent_post_id = wp_insert_post( $postarr );
		}
	
	
		$gmsp_img_folder = plugin_dir_path( __FILE__ )."assets/img/markers/";
			
		if ($dir = opendir($gmsp_img_folder)) {
			$images = array();
			while (false !== ($file = readdir($dir))) {
				if ($file != "." && $file != "..") {
					$images[] = $file; 
				}
			}
			closedir($dir);
		}
		
		if (isset($uploaded_media)) {
			$i = 0;
			foreach($images as $image) {
				foreach($uploaded_media as $medium_title) {
					if (strpos($image, $medium_title) !== false) {
						unset($images[$i]);
					}
				}
				$i++;
			}
		}
		foreach($images as $image) {	
			media_sideload_image(plugins_url( "assets/img/markers/".$image, __FILE__ ), $parent_post_id);
		}
	}
}

function gmsp_plugin_wp_editor() {
	
	$content = '';
	ob_start();
		
	$settings = array('textarea_name' => 'marker_info_body_mce',
				 'wpautop' => false,
                 'quicktags' => true,
                 'media_buttons' => true,
                 'teeny' => true,
                 'tinymce'=> array(
                 'toolbar1'=> 'formatselect,bold,italic,underline,strikethrough,bullist,numlist,link,unlink,alignleft,aligncenter,alignright,fullscreen') 
				 );

	wp_editor( $content, "marker_info_body_mce", $settings);

	$editor_contents = ob_get_clean();
	
	return $editor_contents;
}


function gmsp_plugin_remove_marker_icons() {
	if (get_posts( array( "post_type" => "gmsp-marker-icons") )) {
		$posts_of_gmsp_type = get_posts( array( "post_type" => "gmsp-marker-icons") );
		$parent_post = $posts_of_gmsp_type[0];
		$parent_post_id = $parent_post->ID;	
		$post_attachments = get_attached_media( 'image', $parent_post_id);
		if($post_attachments) {
			foreach ($post_attachments as $attachment) {
				//gmsp_TRACE( "Found attachment with id=".$attachment->ID, true);
				wp_delete_attachment($attachment->ID, true);
			}
		 }
		wp_delete_post($parent_post_id, true);
	}
}


function gmsp_submit_feedback() {

    if ( isset( $_POST['submit_feedback'] ) ) {

        $subject = 'New feedback for GMSP submitted from wp-admin';
        $message = esc_textarea( $_POST["gmsp_feedback_text"] );

        $to = 'it@bunte-giraffe.de';

        if ( wp_mail( $to, $subject, $message	) ) {
			if ( isset ( $_POST['_wp_http_referer'] ) ) {
				$url = urldecode( $_POST['_wp_http_referer'] );
				wp_safe_redirect( $url );					
			}        
		} 
    }
}

function gmsp_add_new_map_type() {
	$newMapTypeName = $_POST["gmspMapTypeName"];
	$newMapType = $_POST["gmspMapType"];
	if ($newMapTypeName && $newMapType) {
		$storedMapTypes = get_option( "gmsp_MapTypes", false);
		if (null !== json_decode(str_replace('\"', '"', $_POST["gmspMapType"]), true)) {
			$storedMapTypes[preg_replace("/[^a-zA-Z0-9]+/", "_", $newMapTypeName)] = $newMapType;
		}
		else {
			$storedMapTypes[preg_replace("/[^a-zA-Z0-9]+/", "_", $newMapTypeName)] = 'Invalid format';
		}
		update_option( "gmsp_MapTypes", $storedMapTypes );
		
	}
	
	$urlBack = urldecode( admin_url( 'tools.php' ) );
	$urlBack = add_query_arg( "page", "google_maps_simple_pins", $urlBack );
	$urlBack = add_query_arg( "tab", "map_styles_manager", $urlBack);

	wp_safe_redirect( $urlBack );

}

function gmsp_remove_map_type() {
	$mapTypeToRemove = $_POST["gmspMapTypeToRemove"];
	$removeFormSubmitted = $_POST['action'];
	if (isset($removeFormSubmitted) && $removeFormSubmitted ==="gmsp_remove_map_type" && isset($mapTypeToRemove)) {
		$storedMapTypes = get_option( "gmsp_MapTypes", false);
		unset($storedMapTypes[$mapTypeToRemove]);
		update_option( "gmsp_MapTypes", $storedMapTypes );
	}
	
	$urlBack = urldecode( admin_url( 'tools.php' ) );
	$urlBack = add_query_arg( "page", "google_maps_simple_pins", $urlBack );
	$urlBack = add_query_arg( "tab", "map_styles_manager", $urlBack);

	wp_safe_redirect( $urlBack );

}


/* Add TinyMCE button */
add_action('admin_head', 'gmsp_add_tc_button');
add_action( "admin_enqueue_scripts", "gmsp_add_mce_style" );

function gmsp_add_tc_button() {
    global $typenow;
    // check user permissions
    if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
		return;
    }
    // verify the post type
    if( !in_array( $typenow, array( 'post', 'page' ) ) )
        return;
    // check if WYSIWYG is enabled
    if ( get_user_option('rich_editing') == 'true') {
        add_filter("mce_external_plugins", "gmsp_add_tinymce_plugin");
        add_filter('mce_buttons', 'gmsp_register_tc_button');
    }
}


function gmsp_add_tinymce_plugin($plugin_array) {
    $plugin_array['gmsp_tc_button'] = plugins_url( 'assets/js/gmsp-mce-button.js', __FILE__ ); 
    return $plugin_array;
}


function gmsp_register_tc_button($buttons) {
   array_push($buttons, "gmsp_tc_button");
   return $buttons;
}


function gmsp_add_mce_style() {
	global $typenow;
		
	if( in_array( $typenow, array( 'post', 'page' ) ) ) {		
		wp_enqueue_style( 'styleBE-tmce-gmsp.css', plugins_url( "assets/css/styleBE-tmce.css", __FILE__ ) );	
		
		$maps_raw = get_option( "gmsp-Maps", false );
		$markers_raw = get_option( "gmsp_Markers", false );
		
		$map_names = array();
		$marker_names = array();
		
		foreach ($maps_raw as $map => $value) {
			array_push($map_names, array($map, $value['name']));
		}
		
		foreach ($markers_raw as $marker => $value) {
			array_push($marker_names, array($marker, $value['name']));
		}

		wp_localize_script( 'jquery', 'GMSP_MAPS', $map_names);
		wp_localize_script( 'jquery', 'GMSP_MARKERS', $marker_names);
	}
}
/* end TinyMCE button*/


/* Check for PRO version updates */

/* add_filter ('pre_set_site_transient_update_plugins', 'gmsp_get_pro_url');

function gmsp_check_username_api_key( $send_to_api ) {
	if ( $this->options && isset( $this->options['username'] ) && isset( $this->options['api_key'] ) ) {
		$send_to_api['username'] = urlencode( sanitize_text_field( $this->options['username'] ) );
		$send_to_api['api_key'] = sanitize_text_field( $this->options['api_key'] );
	}

	return $send_to_api;
}


function gmsp_get_pro_url($transient) { 

	global $wp_version;
	
	$pro_key = get_option('gmspPROkey', false);
	
	if ( false !== $pro_key ) {
	
		if ( !is_object( $transient ) ) {
			$transient = new stdClass();
		}
		$pro_version = new stdClass();
		$pro_version->slug = plugin_basename( __FILE__  );

		$send_to_api = array(
			'action' => 'check_pro_eligibility',
		);

		$send_to_api = $this->gmsp_check_username_api_key( $send_to_api );
		$options = array(
			'body'			=> $send_to_api,
			'user-agent'	=> 'WordPress/' . $wp_version . '; ' . home_url()
		);

		$pro_url_request = wp_remote_post( 'http://bunte-giraffe.de/api/api.php', $options );
		
		if ( !is_wp_error( $pro_url_request ) && wp_remote_retrieve_response_code( $pro_url_request ) == 200 ){
			$pro_url_response = unserialize( wp_remote_retrieve_body( $pro_url_request ) );
		
			if ( ! empty( $pro_url_response ) ) {
				foreach ( $pro_url_response as $response ) {
					if ( array_key_exists( 'pro_url', $response ) ) {
						$pro_version->url = $response['pro_url'];
					}
					elseif ( array_key_exists ( 'pro_version', $response ) ) {
						$pro_version->new_version = $response['pro_version'];
					} 
				}
			}
			
			$transient->response[plugin_basename( __FILE__  )] = $pro_version;
			return $transient;
		}
	}
} */

/* end check for PRO version updates */

add_action( 'admin_print_footer_scripts', function () {
    ?>
    <script type="text/javascript">
    jQuery(function ($) {
        if (typeof tinymce !== 'undefined') {
            tinymce.on('SetupEditor', function (editor) {
                if (editor.id == 'marker_info_body_mce') {
                    editor.on('change keyup paste', function (event) {
						showInfoWindowOnChangeBE(this, this.getContent());
                    });
                }
            });
        }
    });
    </script>
    <?php
} );

?>