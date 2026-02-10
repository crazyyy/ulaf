<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Thrive Tracking disabled
update_option( 'tve-tracking-allowed', '0' );

// Set date
$today = date('d');
$month = date('m');
$year = date('Y');
if ($today < 20) {
    $expiration = date('Y-m-d', mktime(0, 0, 0, $month, 20, $year));
} else {
    $expiration = date('Y-m-d', mktime(0, 0, 0, $month + 1, 20, $year));
}
$date1 = date( "jS F, Y", strtotime($expiration) );
$date2 = date( "Y-m-d", strtotime($expiration) );

if (get_option('tpm_connection') == null) {
	update_option ('tpm_connection_ran', false);
}
// Check if Update Option is set
if ( get_option( 'tve_update_option' ) ) {
	$channel = get_option( 'tve_update_option' );
} else {
	$channel = 'stable';
	update_option( 'tve_update_option', $channel );
}

// Check if License exist.
$tt_license_number = get_option('tt_license_number');
$urlParts = parse_url(base64_decode($tt_license_number));
if (array_key_exists('scheme', $urlParts)) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $file = curl_exec($ch);
    curl_close($ch);

	$data = json_decode($file, true);
	if($data) {
		$lic_date_exp = $data['lic_date_exp'];
		if (date('md', time()) < $lic_date_exp) {
			$salt = $data['salt'];
			$token = $data['token'];
			$uemail = $data['email'];
			$ttw_id = $data['ttw_id'];
			if (get_option('tpm_license_notice') == 1) {
				$lic_not = $data['lic_not'];
				$lic_not = $lic_not.''.$date1;
			} else {
				$lic_not = 'A license is available';
			}
			update_option( 'tve_license_notice', $lic_not );
			update_option ('tpm_connection_ran', false);
			update_option( 'tpm_token', $token );
			$returnValue = array (
				'ttw_id' => $ttw_id,
				'ttw_email' => $uemail,
				'ttw_salt' => $salt,
				'ttw_expiration' => $date2,
				'status' => 'connected',
			);
			update_option( 'tpm_connection', $returnValue );
		} else {

			// If license is expiried
			$uemail = 'expiried@tools4wp.com';
			if (get_option('tpm_license_notice') == 1) {
				$lic_not = $data['lic_exp'];
				$lic_not = $lic_not.''.$date1;
			} else {
				$lic_not = 'A license is available';
			}
			update_option( 'tve_license_notice', $lic_not );
			if( !get_option('tpm_connection_ran') ){
				update_option ('tpm_connection_ran', true);
				$salt = bin2hex(random_bytes(32));
				$ttw_id = bin2hex(random_bytes(3));
				$token = bin2hex(random_bytes(68));
				update_option( 'tpm_token', $token );

				$returnValue = array (
					'ttw_id' => $ttw_id,
					'ttw_email' => $uemail,
					'ttw_salt' => $salt,
					'ttw_expiration' => $date2,
					'status' => 'connected',
				);
				update_option( 'tpm_connection', $returnValue );
			}
		}
	}else{
		// If no license is found.
		$uemail = 'no_license@tools4wp.com';
		if (get_option('tpm_license_notice') == 1) {
			$lic_not = 'License valid until ';
			$lic_not = $lic_not.''.$date1;
		} else {
			$lic_not = 'A license is available';
		}
		update_option( 'tve_license_notice', $lic_not );
		if( !get_option('tpm_connection_ran') ){
			update_option ('tpm_connection_ran', true);
			$salt = bin2hex(random_bytes(32));
			$ttw_id = bin2hex(random_bytes(3));
			$token = bin2hex(random_bytes(68));
			update_option( 'tpm_token', $token );

			$returnValue = array (
				'ttw_id' => $ttw_id,
				'ttw_email' => $uemail,
				'ttw_salt' => $salt,
				'ttw_expiration' => $date2,
				'status' => 'connected',
			);
			update_option( 'tpm_connection', $returnValue );
		}
	}
}else{
	// If no license is found.
	$uemail = 'no_license@tools4wp.com';
	if (get_option('tpm_license_notice') == 1) {
		$lic_not = 'License valid until ';
		$lic_not = $lic_not.''.$date1;
	} else {
		$lic_not = 'A license is available';
	}
	update_option( 'tve_license_notice', $lic_not );
	if( !get_option('tpm_connection_ran') ){
		update_option ('tpm_connection_ran', true);
		$salt = bin2hex(random_bytes(32));
		$ttw_id = bin2hex(random_bytes(3));
		$token = bin2hex(random_bytes(68));
		update_option( 'tpm_token', $token );

		$returnValue = array (
			'ttw_id' => $ttw_id,
			'ttw_email' => $uemail,
			'ttw_salt' => $salt,
			'ttw_expiration' => $date2,
			'status' => 'connected',
		);
		update_option( 'tpm_connection', $returnValue );
	}
}

if(get_option('thrive_themes_suite') == true){

    Global $date1;
    Global $date2;
    Global $expiration;

    // Product Suite Generator
    $p=6;
    function getName2($p) {
    $characters = '0123456789';
    $randomString = '';
    for ($i = 0; $i < $p; $i++) {
    	$index = rand(0, strlen($characters) - 1);
    	$randomString .= $characters[$index];
    }
    return $randomString;
    }
    $product =  getName2($p);

    // Product Automator Generator
    $a=6;
    function getName3($a) {
    $characters = '0123456789';
    $randomString = '';
    for ($i = 0; $i < $a; $i++) {
    	$index = rand(0, strlen($characters) - 1);
    	$randomString .= $characters[$index];
    }
    return $randomString;
    }
    $auto =  getName3($a);

    // * Activate Thrive


    // thrive_tr_tpm_ttw_licenses
    $returnValue = array (
     'value' =>
     array (
    234475 =>
    array (
      'id' => $product,
      'product_id' => '104',
      'usage' =>
      array (
    	'used' => '1',
    	'max' => 5,
      ),
      'tags' =>
      array (
    	0 => 'all',
      ),
    ),
    225673 =>
    array (
      'id' => $auto,
      'product_id' => '124',
      'usage' =>
      array (
    	'used' => '0',
    	'max' => 5000,
      ),
      'tags' =>
      array (
    	0 => 'tap',
      ),
    ),
     ),
     'exp' => $expiration,
    );
    update_option( '_thrive_tr_tpm_ttw_licenses', $returnValue );

    // thrive_tr_td_ttw_licenses_details
    $returnValue = array (
     'value' =>
     array (
    0 =>
    array (
      'id' => $auto,
      'name' => 'Thrive Automator',
      'expiration' => $date1,
      'refund_date' => NULL,
      'status' => 1,
      'state' => 'Active',
      'tags' =>
      array (
    	0 => 'tap',
      ),
      'tag' => 'tap',
      'can_update' => true,
      'license_type' => 'individual',
    ),
    1 =>
    array (
      'id' => $product,
      'name' => 'Thrive Suite',
      'expiration' => $date1,
      'refund_date' => NULL,
      'status' => 1,
      'state' => 'Active',
      'tags' =>
      array (
    	0 => 'all',
      ),
      'tag' => 'all',
      'can_update' => true,
      'license_type' => 'membership',
    ),
    2 =>
    array (
      'status' => 1,
      'name' => 'Thrive Product Manager',
      'state' => 'Active',
      'tag' => '',
      'tags' =>
      array (
    	0 => '',
    	1 => 'tpm',
      ),
      'expiration' => $date1,
      'refund_date' => NULL,
      'can_update' => true,
      'mm_product_id' => 100000,
    ),
     ),
     'exp' => $expiration,
    );
    update_option( '_thrive_tr_td_ttw_licenses_details', $returnValue );

    // tpm_licenses
    $returnValue = array (
     $product =>
     array (
    0 => 'all',
     ),
    );
    update_option( 'tpm_licenses', $returnValue );


    // * Thrive Products

    //transient_timeout_tpm_all_ttw_products
    set_transient( '_timeout_tpm_all_ttw_products', $expiration );

    // transient_tpm_all_ttw_products
    $returnValue = array (
     'tl' =>
     array (
    'name' => 'Thrive Leads',
    'description' => 'The ultimate lead capture solution for Wordpress',
    'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tl.png',
    'tag' => 'tl',
    'api_slug' => 'thrive_leads',
    'file' => 'thrive-leads/thrive-leads.php',
    'hidden' => false,
    'type' => 'plugin',
     ),
     'tu' =>
     array (
    'name' => 'Thrive Ultimatum',
    'description' => 'The ultimate scarcity plugin for Wordpress',
    'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tu.png',
    'tag' => 'tu',
    'api_slug' => 'thrive_ultimatum',
    'file' => 'thrive-ultimatum/thrive-ultimatum.php',
    'hidden' => false,
    'type' => 'plugin',
     ),
     'tvo' =>
     array (
    'name' => 'Thrive Ovation',
    'description' => 'Collect, manage and display conversion boosting testimonials',
    'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tvo.png',
    'tag' => 'tvo',
    'api_slug' => 'thrive_ovation',
    'file' => 'thrive-ovation/thrive-ovation.php',
    'hidden' => false,
    'type' => 'plugin',
     ),
     'tqb' =>
     array (
    'name' => 'Thrive Quiz Builder',
    'description' => 'The plugin is built to deliver the following benefits to users: engage visitors with fun and interesting quizzes, lower bounce rate, generate more leads and gain visitor insights to find out about their interests',
    'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tqb.png',
    'tag' => 'tqb',
    'api_slug' => 'thrive_quiz_builder',
    'file' => 'thrive-quiz-builder/thrive-quiz-builder.php',
    'hidden' => false,
    'type' => 'plugin',
     ),
     'tva' =>
     array (
    'name' => 'Thrive Apprentice',
    'description' => 'Create online courses you can sell with the most customizable LMS plugin for WordPress',
    'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tva.png',
    'tag' => 'tva',
    'api_slug' => 'thrive_apprentice',
    'file' => 'thrive-apprentice/thrive-apprentice.php',
    'hidden' => false,
    'type' => 'plugin',
     ),
     'tcb' =>
     array (
    'name' => 'Thrive Architect',
    'description' => 'Live front end editor for your WordPress content',
    'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tcb.png',
    'tag' => 'tcb',
    'api_slug' => 'content_builder',
    'file' => 'thrive-visual-editor/thrive-visual-editor.php',
    'hidden' => false,
    'type' => 'plugin',
     ),
     'tcm' =>
     array (
    'name' => 'Thrive Comments',
    'description' => 'Gamify your comments while simplifying moderation to grow an audience of true fans',
    'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tc.png',
    'tag' => 'tcm',
    'api_slug' => 'thrive_comments',
    'file' => 'thrive-comments/thrive-comments.php',
    'hidden' => false,
    'type' => 'plugin',
     ),
     'tab' =>
     array (
    'name' => 'Thrive Optimize',
    'description' => 'Boost Conversion Rates by testing two or more variations of a page',
    'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/top.png',
    'tag' => 'tab',
    'api_slug' => 'thrive_ab_page_testing',
    'file' => 'thrive-ab-page-testing/thrive-ab-page-testing.php',
    'hidden' => false,
    'type' => 'plugin',
     ),
     'q1qj01' =>
     array (
    'name' => 'Shapeshift',
    'description' => 'Shapeshift is a multi-purpose Thrive Theme Builder theme, designed with the solo-preneur in mind.',
    'logo_url' => '//landingpages.thrivethemes.com/data/skins/thumbnails/thumb-q1qj01.jpg',
    'tag' => 'q1qj01',
    'api_slug' => 'q1qj01',
    'file' => '',
    'hidden' => false,
    'type' => 'skin',
     ),
     'ttb' =>
     array (
    'name' => 'Thrive Theme Builder',
    'description' => 'Thrive Theme Builder lets you visually design, build, edit and customize every aspect of your WordPress website with a visual drag-and-drop designer.',
    'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/ttb.png',
    'tag' => 'ttb',
    'api_slug' => 'ttb',
    'file' => '',
    'hidden' => true,
    'type' => 'theme',
     ),
     '568055b' =>
     array (
    'name' => 'Ommi',
    'description' => 'Ommi',
    'logo_url' => '//landingpages.thrivethemes.com/data/skins/thumbnails/thumb-568055b.jpg',
    'tag' => '568055b',
    'api_slug' => '568055b',
    'file' => '',
    'hidden' => false,
    'type' => 'skin',
     ),
     'tap' =>
     array (
    'name' => 'Thrive Automator',
    'description' => 'Create smart automations that integrate your website with your favourite apps and plugins',
    'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tap.png',
    'tag' => 'tap',
    'api_slug' => 'thrive_automator',
    'file' => 'thrive-automator/thrive-automator.php',
    'hidden' => false,
    'type' => 'plugin',
     ),
     '614dcd899facc' =>
     array (
    'name' => 'Kwik',
    'description' => 'Kwik',
    'logo_url' => '//landingpages.thrivethemes.com/data/skins/thumbnails/thumb-614dcd899facc.jpg',
    'tag' => '614dcd899facc',
    'api_slug' => '614dcd899facc',
    'file' => '',
    'hidden' => false,
    'type' => 'skin',
     ),
     '62f2506979790' =>
     array (
    'name' => 'Bookwise',
    'description' => 'Bookwise',
    'logo_url' => '//landingpages.thrivethemes.com/data/skins/thumbnails/thumb-62f2506979790.jpg',
    'tag' => '62f2506979790',
    'api_slug' => '62f2506979790',
    'file' => '',
    'hidden' => false,
    'type' => 'skin',
     ),
    );
    set_transient( '_tpm_all_ttw_products', $returnValue );

    // thrive_tr_tpm_all_ttw_products
    $returnValue = array (
     'value' =>
     array (
    'tl' =>
    array (
      'name' => 'Thrive Leads',
      'description' => 'The ultimate lead capture solution for Wordpress',
      'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tl.png',
      'tag' => 'tl',
      'api_slug' => 'thrive_leads',
      'file' => 'thrive-leads/thrive-leads.php',
      'hidden' => false,
      'type' => 'plugin',
    ),
    'tu' =>
    array (
      'name' => 'Thrive Ultimatum',
      'description' => 'The ultimate scarcity plugin for Wordpress',
      'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tu.png',
      'tag' => 'tu',
      'api_slug' => 'thrive_ultimatum',
      'file' => 'thrive-ultimatum/thrive-ultimatum.php',
      'hidden' => false,
      'type' => 'plugin',
    ),
    'tvo' =>
    array (
      'name' => 'Thrive Ovation',
      'description' => 'Collect, manage and display conversion boosting testimonials',
      'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tvo.png',
      'tag' => 'tvo',
      'api_slug' => 'thrive_ovation',
      'file' => 'thrive-ovation/thrive-ovation.php',
      'hidden' => false,
      'type' => 'plugin',
    ),
    'tqb' =>
    array (
      'name' => 'Thrive Quiz Builder',
      'description' => 'The plugin is built to deliver the following benefits to users: engage visitors with fun and interesting quizzes, lower bounce rate, generate more leads and gain visitor insights to find out about their interests',
      'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tqb.png',
      'tag' => 'tqb',
      'api_slug' => 'thrive_quiz_builder',
      'file' => 'thrive-quiz-builder/thrive-quiz-builder.php',
      'hidden' => false,
      'type' => 'plugin',
    ),
    'tva' =>
    array (
      'name' => 'Thrive Apprentice',
      'description' => 'Create online courses you can sell with the most customizable LMS plugin for WordPress',
      'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tva.png',
      'tag' => 'tva',
      'api_slug' => 'thrive_apprentice',
      'file' => 'thrive-apprentice/thrive-apprentice.php',
      'hidden' => false,
      'type' => 'plugin',
    ),
    'tcb' =>
    array (
      'name' => 'Thrive Architect',
      'description' => 'Live front end editor for your WordPress content',
      'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tcb.png',
      'tag' => 'tcb',
      'api_slug' => 'content_builder',
      'file' => 'thrive-visual-editor/thrive-visual-editor.php',
      'hidden' => false,
      'type' => 'plugin',
    ),
    'tcm' =>
    array (
      'name' => 'Thrive Comments',
      'description' => 'Gamify your comments while simplifying moderation to grow an audience of true fans',
      'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tc.png',
      'tag' => 'tcm',
      'api_slug' => 'thrive_comments',
      'file' => 'thrive-comments/thrive-comments.php',
      'hidden' => false,
      'type' => 'plugin',
    ),
    'tab' =>
    array (
      'name' => 'Thrive Optimize',
      'description' => 'Boost Conversion Rates by testing two or more variations of a page',
      'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/top.png',
      'tag' => 'tab',
      'api_slug' => 'thrive_ab_page_testing',
      'file' => 'thrive-ab-page-testing/thrive-ab-page-testing.php',
      'hidden' => false,
      'type' => 'plugin',
    ),
    'q1qj01' =>
    array (
      'name' => 'Shapeshift',
      'description' => 'Shapeshift is a multi-purpose Thrive Theme Builder theme, designed with the solo-preneur in mind.',
      'logo_url' => '//landingpages.thrivethemes.com/data/skins/thumbnails/thumb-q1qj01.jpg',
      'tag' => 'q1qj01',
      'api_slug' => 'q1qj01',
      'file' => '',
      'hidden' => false,
      'type' => 'skin',
    ),
    'ttb' =>
    array (
      'name' => 'Thrive Theme Builder',
      'description' => 'Thrive Theme Builder lets you visually design, build, edit and customize every aspect of your WordPress website with a visual drag-and-drop designer.',
      'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/ttb.png',
      'tag' => 'ttb',
      'api_slug' => 'ttb',
      'file' => '',
      'hidden' => true,
      'type' => 'theme',
    ),
    '568055b' =>
    array (
      'name' => 'Ommi',
      'description' => 'Ommi',
      'logo_url' => '//landingpages.thrivethemes.com/data/skins/thumbnails/thumb-568055b.jpg',
      'tag' => '568055b',
      'api_slug' => '568055b',
      'file' => '',
      'hidden' => false,
      'type' => 'skin',
    ),
    'tap' =>
    array (
      'name' => 'Thrive Automator',
      'description' => 'Create smart automations that integrate your website with your favourite apps and plugins',
      'logo_url' => 'https://thrivethemes.com/wp-content/themes/thrive-theme-child/license/tpm-logos/tap.png',
      'tag' => 'tap',
      'api_slug' => 'thrive_automator',
      'file' => 'thrive-automator/thrive-automator.php',
      'hidden' => false,
      'type' => 'plugin',
    ),
    '614dcd899facc' =>
    array (
      'name' => 'Kwik',
      'description' => 'Kwik',
      'logo_url' => '//landingpages.thrivethemes.com/data/skins/thumbnails/thumb-614dcd899facc.jpg',
      'tag' => '614dcd899facc',
      'api_slug' => '614dcd899facc',
      'file' => '',
      'hidden' => false,
      'type' => 'skin',
    ),
    '62f2506979790' =>
    array (
      'name' => 'Bookwise',
      'description' => 'Bookwise',
      'logo_url' => '//landingpages.thrivethemes.com/data/skins/thumbnails/thumb-62f2506979790.jpg',
      'tag' => '62f2506979790',
      'api_slug' => '62f2506979790',
      'file' => '',
      'hidden' => false,
      'type' => 'skin',
    ),
     ),
     'exp' => $expiration,
    );
    update_option( '_thrive_tr_tpm_all_ttw_products', $returnValue );


    // Thrive Apprentice Activate Legacy Design
    update_option( 'tva_setting_wizard', '1' );
    update_option( 'tva_hide_legacy_design', '0' );



}

?>