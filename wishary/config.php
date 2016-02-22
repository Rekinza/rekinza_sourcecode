<?php
	define( 'DB_HOST', 'localhost');
	define( 'DB_USERID', 'root' );
	define( 'DB_PASSWD', 'harsh' );
	define( 'DB_NAME', 'magento' );
	define( 'SERVICE_NAME', 'wishary_engine' );
	define( 'SDK_VERSION', '0.5' );
	define( 'API_URL_BASE', 'http://207.182.158.186' );


	// must change belows
	define( 'USERID', 'Rekinza' );
	define( 'APIKEY', '33a0049e527b50c19cafdd8e94e23d13' );
	define( 'SITE_URL', 'http://www.rekinza.com' );
	define( 'UID_PREFIX', 'wishary_view_table_' );
	define( 'VIEW_TABLE', 'wishary_send_data_view');
	define( 'USE_CUSTOM_CATEGORY_MAPPER', true );
	define( 'CATEGORY_WAY', "MAP" ); // MAP OR DICTIONARY

	// custom category Map
	// $this_site_category => $wishary_category
	// sample (Map)
	$categoryMap = array(
		"Clothing" => "682",
		"Shoes" => "684",
		"Bags"  => "699",
		"Dresses" => "1997",
		"Handbags" => "2039",
		"Wallets" => "2040",
		"Flats" => "2022",
		"Heels" => "2645",
		"Clutches" => "2033",

		
	);


	// custom category Map
	// $wishary_category => $this_site_category_array
	// sample (Dictionary)
	$categoryDictionary = array(
		"Beach-wears" => array("0", "1", "2")
		,"Blouse" => array("3", "etc")
	)

?>
