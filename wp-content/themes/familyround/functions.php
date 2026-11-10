<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FR_THEME_VERSION', '1.59.29' );

// Site is live, but real checkout stays off until the business has a CUI and
// NETOPIA/SmartBill/Colete Online are configured with real credentials.
// Flip to false once that's done — every "Comandă acum" link reverts to the
// real add-to-cart flow on its own, no other changes needed.
// TEMPORARILY false for local testing (shipping/AWB flow) — put back to true
// before this reaches anywhere real customers can open it. Real checkout means
// real orders, and real orders need a CUI to invoice, testing-mode Colete
// Online or not.
define( 'FR_PRELAUNCH', false );

function fr_is_prelaunch() {
	return defined( 'FR_PRELAUNCH' ) && FR_PRELAUNCH;
}

// Real checkout (cart, address, courier quote) stays on, but with no card
// at checkout at all — "Transfer bancar" is relabeled to "te contactăm pentru
// plată și factură" and is the only payment option for delivery orders. No
// money moves through the site; the order lands on-hold and FamilyRound
// follows up by phone/email to actually collect payment once there's a CUI
// to invoice against. Flip to false once NETOPIA + SmartBill are live — the
// real card gateway and the real bank-transfer copy come back on their own.
define( 'FR_PAYMENT_AWAITING_CUI', true );

function fr_payment_awaiting_cui() {
	return defined( 'FR_PAYMENT_AWAITING_CUI' ) && FR_PAYMENT_AWAITING_CUI;
}

// Defense in depth: block add-to-cart at the source too, in case someone
// reaches a stale/bookmarked ?add-to-cart= link while prelaunch is active.
add_filter( 'woocommerce_add_to_cart_validation', function ( $passed ) {
	if ( fr_is_prelaunch() ) {
		wc_add_notice( 'Comenzile pornesc în curând. Lasă-ți emailul pe pagina jocului și te anunțăm.', 'error' );
		return false;
	}
	return $passed;
} );

function fr_challenge_cards() {
	return array(
		1  => 'Drumeție',
		2  => 'Picnic',
		3  => 'Comoara din Natură',
		4  => 'Planta care Crește cu Voi',
		5  => 'Cu Cortul în Sufragerie',
		6  => 'Povești la Lumina Lanternei',
		7  => 'Inversați Rolul pentru o Zi',
		8  => 'Coșul cu Surprize',
		9  => 'Expediția cu Monedă',
		10 => 'Refaceți o Fotografie Veche',
		11 => 'Scrisoarea de Peste un An',
		12 => 'Amprenta Familiei',
		13 => 'Călătorie în Copilăria Părinților',
		14 => 'Misiunea Face Lumea mai Frumoasă',
		15 => 'O Excursie cu Trenul',
		16 => 'Surprindeți Răsăritul Împreună',
		17 => 'Construim un Vehicul',
		18 => 'Zbor cu Zmeie',
		19 => 'Arborele Genealogic',
		20 => 'O Zi la Planetariu sau Observator',
		21 => 'Ziua în Care Construiți Jucării',
		22 => 'Din Natură în Borcan',
		23 => 'Zi de Magie',
		24 => 'Construiți o Plută',
		25 => 'Restaurant Familie',
		26 => 'Lăsați Ceva în Urmă',
		27 => 'Vânătoare de Comori',
		28 => 'Faceți o Surpriză Bunicilor',
		29 => 'Karaoke',
		30 => 'Blazonul Familiei',
	);
}

/**
 * The FamilyRound gold "thread" — an organic line echoing the poster & card
 * line-work and the idea of the family circle. Used as a section opener motif;
 * it draws itself in on reveal (see .fr-thread in style.css).
 */
function fr_thread( $class = '' ) {
	$cls = trim( 'fr-thread ' . $class );
	return '<svg class="' . esc_attr( $cls ) . '" viewBox="0 0 160 40" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M2 20 C 28 3, 52 37, 78 20 C 104 3, 128 37, 158 19" /></svg>';
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );

	register_nav_menus( array(
		'primary' => 'Meniu principal',
		'footer'  => 'Meniu footer',
	) );
} );

/**
 * Discreet line icons for the primary menu.
 * Matched by the item's label/URL (diacritic-insensitive), so renaming is tolerated.
 */
function fr_nav_icon_svg( $key ) {
	$icons = array(
		'map'   => '<path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z"/><circle cx="12" cy="10" r="2.4"/>',
		'cards' => '<rect x="8" y="4" width="11" height="15" rx="2"/><path d="M5 7v10a3 3 0 0 0 3 3h7"/>',
		'heart' => '<path d="M12 20s-6.6-4.2-9-8.4C1.4 8.3 3.1 5 6.3 5 8.2 5 9.6 6.2 12 8.6 14.4 6.2 15.8 5 17.7 5c3.2 0 4.9 3.3 3.3 6.6C18.6 15.8 12 20 12 20Z"/>',
		'book'  => '<path d="M4 5a2 2 0 0 1 2-2h6v16H6a2 2 0 0 0-2 2V5Z"/><path d="M20 5a2 2 0 0 0-2-2h-6v16h6a2 2 0 0 1 2 2V5Z"/>',
		'mail'  => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
	);
	if ( empty( $icons[ $key ] ) ) {
		return '';
	}
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . $icons[ $key ] . '</svg>';
}

function fr_nav_icon_key( $item ) {
	$t = remove_accents( strtolower( (string) $item->title ) );
	$u = strtolower( (string) $item->url );
	if ( false !== strpos( $t, 'hart' ) || false !== strpos( $u, 'harta' ) ) return 'map';
	if ( false !== strpos( $t, 'pusculi' ) || false !== strpos( $u, 'pusculita' ) ) return 'heart';
	if ( false !== strpos( $t, 'poveste' ) || false !== strpos( $u, 'povestea' ) ) return 'book';
	if ( false !== strpos( $t, 'contact' ) || false !== strpos( $u, 'contact' ) ) return 'mail';
	if ( false !== strpos( $t, 'joc' ) || false !== strpos( $u, 'shop' ) || false !== strpos( $u, 'product' ) || false !== strpos( $u, 'magazin' ) ) return 'cards';
	return '';
}

add_filter( 'nav_menu_item_title', function ( $title, $item, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $title;
	}
	$icon_key = fr_nav_icon_key( $item );
	$label    = 'cards' === $icon_key ? 'Experiențe' : $title;
	$svg      = fr_nav_icon_svg( $icon_key );
	return $svg ? '<span class="fr-nav-ico" aria-hidden="true">' . $svg . '</span>' . $label : $label;
}, 10, 3 );

/**
 * Display the product name without the "FamilyRound –" brand prefix on the
 * front end (single product, cart, mini-cart) — matching the homepage.
 */
add_filter( 'the_title', function ( $title, $id = 0 ) {
	if ( is_admin() || ! $id ) {
		return $title;
	}
	if ( 'product' === get_post_type( $id ) ) {
		$title = preg_replace( '/^\s*FamilyRound\s*[–—\-]\s*/u', '', $title );
	}
	return $title;
}, 10, 2 );

// Product add-to-cart button copy (Romanian) — also overrides WooCommerce's English default.
add_filter( 'woocommerce_product_single_add_to_cart_text', function () {
	return 'Comandă acum';
} );

// Skip the cart page entirely — redirect both "add to cart" and direct /cart/ visits to checkout.
add_filter( 'woocommerce_add_to_cart_redirect', function ( $url ) {
	return function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : $url;
}, 20 );

add_action( 'template_redirect', function () {
	if ( function_exists( 'is_cart' ) && is_cart() && ! empty( WC()->cart->get_cart() ) ) {
		wp_safe_redirect( wc_get_checkout_url() );
		exit;
	}
} );

/**
 * A wa.me link tapped straight from inside an app's own in-app browser (Gmail's
 * being the one reported) doesn't always hand off to the native WhatsApp app —
 * it can fall back to the web client instead. Routing through a same-origin
 * redirect first forces a real page navigation, which the OS intercepts more
 * reliably than a link click handled entirely inside that embedded webview.
 */
add_action( 'template_redirect', function () {
	if ( ! isset( $_GET['fr_wa_redirect'] ) ) {
		return;
	}
	$text = isset( $_GET['text'] ) ? sanitize_textarea_field( wp_unslash( $_GET['text'] ) ) : '';
	wp_redirect( 'https://wa.me/?text=' . rawurlencode( $text ), 302 );
	exit;
}, 1 );

function fr_whatsapp_link( $text ) {
	return add_query_arg( array( 'fr_wa_redirect' => 1, 'text' => rawurlencode( $text ) ), home_url( '/' ) );
}

add_filter( 'wc_add_to_cart_message_html', function ( $message ) {
	return esc_html__( 'Jocul a fost adăugat. Verifică datele și totalul înainte de a plasa comanda.', 'familyround' );
} );

// WordPress's default sender ("wordpress@" + site host) is "wordpress@localhost" on this
// local setup — not a valid address, so PHPMailer rejects every email before it's even
// sent. A real transactional sender still needs configuring before launch (see PRODUCT.md),
// but a syntactically valid address is required for mail to go out at all, local or live.
add_filter( 'wp_mail_from', function ( $email ) {
	return false !== strpos( $email, '@localhost' ) ? 'no-reply@familyround.ro' : $email;
} );
add_filter( 'wp_mail_from_name', function () {
	return 'FamilyRound';
} );

// Colete Online owns address entry and shipping quotes at checkout. The cart
// shows merchandise only, without a stale or incomplete shipping estimate.
add_filter( 'woocommerce_cart_ready_to_calc_shipping', function ( $ready ) {
	return ! wp_doing_ajax() && function_exists( 'is_cart' ) && is_cart() ? false : $ready;
} );

// Replace WooCommerce's English cart action with the next FamilyRound step.
add_action( 'wp', function () {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return;
	}
	remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
	add_action( 'woocommerce_proceed_to_checkout', function () {
		echo '<a href="' . esc_url( wc_get_checkout_url() ) . '" class="checkout-button button alt">Finalizează comanda <span aria-hidden="true">→</span></a>';
	}, 20 );
} );

// The current local WooCommerce language pack leaves key cart and checkout
// labels in English. Keep the purchase journey readable in Romanian.
add_filter( 'gettext', function ( $translation, $text, $domain ) {
	if ( ! function_exists( 'is_cart' ) || ! function_exists( 'is_checkout' ) || ( ! is_cart() && ! is_checkout() ) ) {
		return $translation;
	}
	if ( 'coleteonline' === $domain && is_checkout() ) {
		$colete_labels = array(
			'Choose locker on map' => 'Alege lockerul pe hartă',
			'Click to choose shipping point' => 'Alege punctul de ridicare',
			'Choose shipping point' => 'Alege punctul de ridicare',
		);
		return $colete_labels[ $text ] ?? $translation;
	}
	if ( 'woocommerce' !== $domain ) {
		return $translation;
	}
	$labels = array(
		'Product'                      => 'Produs',
		'Price'                        => 'Preț',
		'Quantity'                     => 'Cantitate',
		'Subtotal'                     => 'Subtotal',
		'Cart totals'                  => 'Sumarul coșului',
		'Shipping'                     => 'Livrare',
		'Shipment'                     => 'Livrare',
		'Shipping to %s.'              => 'Estimare pentru %s.',
		'Change address'               => 'Schimbă destinația',
		'Calculate shipping'           => 'Calculează livrarea',
		'Coupon code'                  => 'Cod de reducere',
		'Apply coupon'                 => 'Aplică reducerea',
		'Update cart'                  => 'Actualizează coșul',
		'Your order'                   => 'Sumarul comenzii',
		'Billing details'              => 'Detaliile comenzii',
		'Billing address'              => 'Adresa de livrare',
		'Billing &amp; Shipping'       => 'Detaliile comenzii',
		'Ship to a different address?' => 'Trimite jocul la o adresă diferită de cea completată mai sus',
		'Order notes'                  => 'Mesaj pentru colet',
		'Additional information'       => 'Mai vrei să ne spui ceva?',
		'Have a coupon?'               => 'Ai un cupon?',
		'Click here to enter your code' => 'Introdu codul aici',
		'Place order'                  => 'Plasează comanda',
	);
	return isset( $labels[ $text ] ) ? $labels[ $text ] : $translation;
}, 10, 3 );

add_filter( 'woocommerce_get_privacy_policy_text', function ( $text, $type ) {
	if ( 'checkout' !== $type ) {
		return $text;
	}
	$url = get_privacy_policy_url();
	return 'Folosim datele tale pentru a procesa comanda și a comunica despre livrare.' . ( $url ? ' Detalii în <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">Politica de confidențialitate</a>.' : '' );
}, 10, 2 );

add_filter( 'woocommerce_cart_item_thumbnail', function ( $thumbnail, $cart_item ) {
	$product = $cart_item['data'] ?? null;
	if ( $product && false !== stripos( $product->get_name(), '30 de provocări' ) ) {
		return '<img src="' . esc_url( get_theme_file_uri( 'assets/images/product/complete_product.webp' ) ) . '" width="80" height="80" alt="Cutia 30 de provocări în familie">';
	}
	return $thumbnail;
}, 10, 2 );

add_filter( 'woocommerce_cart_item_name', function ( $name, $cart_item ) {
	$product = $cart_item['data'] ?? null;
	if ( $product && false !== stripos( $product->get_name(), '30 de provocări' ) ) {
		$name = preg_replace( '/FamilyRound\s*[–—-]\s*/u', '', $name, 1 );
	}
	if ( $product && function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
		$image = $product->get_image( 'woocommerce_thumbnail' );
		if ( false !== stripos( $product->get_name(), '30 de provocări' ) ) {
			$image = '<img src="' . esc_url( get_theme_file_uri( 'assets/images/product/complete_product.webp' ) ) . '" alt="" width="64" height="64">';
		}
		return '<span class="fr-checkout-line-image">' . $image . '</span><span class="fr-checkout-line-name">' . $name . '</span>';
	}
	return $name;
}, 10, 2 );

// Google Analytics 4 — skipped for admins so our own testing never pollutes real traffic data.
add_action( 'wp_head', function () {
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-GXSS0XGV57"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', 'G-GXSS0XGV57');
	</script>
	<?php
}, 1 );

// Share previews for the FamilyRound brand home and individual experiences.
add_action( 'wp_head', function () {
	if ( is_front_page() ) {
		$has_referral = isset( $_GET['ref'] ) && is_string( $_GET['ref'] ) && '' !== preg_replace( '/[^A-Za-z0-9]/', '', wp_unslash( $_GET['ref'] ) );
		$title = $has_referral
			? 'Dă bucuria mai departe cu FamilyRound!'
			: 'FamilyRound — 30 de provocări pentru mai mult timp în familie';
		$description = $has_referral
			? 'Descoperă 30 de provocări în familie! Pentru fiecare joc cumpărat prin acest link, 10 lei ajung în Pușculița FamilyRound, fără niciun cost suplimentar pentru tine.'
			: 'FamilyRound creează jocuri și experiențe pentru familii cu copii, gândite să vă aducă mai aproape. Descoperă prima experiență, Pușculița și poveștile care merg mai departe.';
		$image = $has_referral
			? get_theme_file_uri( 'assets/images/pusculita-recomandare-10lei.jpg' )
			: get_theme_file_uri( 'assets/images/product/complete_product.png' );
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
		echo '<meta property="og:type" content="website">' . "\n";
		echo '<meta property="og:locale" content="ro_RO">' . "\n";
		echo '<meta property="og:site_name" content="FamilyRound">' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		return;
	}
	// A handful of standalone pages had no description/OG tags at all — each
	// gets its own real copy (taken from or matching what's already on the
	// page), not a generic fallback.
	$page_meta = array(
		'pusculita-familyround'  => array(
			'title' => 'Un joc pentru familia ta. Bucurie pentru alte familii.',
			'desc' => 'Din fiecare joc FamilyRound cumpărat, punem 5 lei în Pușculiță. Descoperă cum ducem bucuria mai departe.',
			'img'  => 'assets/images/pusculita-share.jpg',
		),
		'harta-familyround'      => array(
			'title' => 'Harta FamilyRound. Dintr-o familie, în tot mai multe case.',
			'desc' => 'Fiecare joc FamilyRound cumpărat adaugă un nou loc pe harta noastră. Descoperă orașele în care au ajuns provocările și urmărește cum crește povestea FamilyRound.',
			'img'  => 'assets/images/harta-share.jpg',
		),
		'povestea-noastra'       => array(
			'title' => 'Povestea FamilyRound',
			'desc' => 'Descoperă povestea FamilyRound, un brand creat de Alexandru și Alexandra, părinții a trei copii, din dorința de a aduce mai mult timp, bucurie și amintiri în familie.',
			'img'  => 'assets/images/story-family.jpg',
		),
		'da-timpul-mai-departe'  => array(
			'title' => 'Recomandă FamilyRound. Dă bucuria mai departe!',
			'desc' => 'Creează gratuit propriul link FamilyRound și distribuie-l mai departe. Pentru fiecare joc cumpărat prin linkul tău, 10 lei ajung în Pușculița FamilyRound. Nu trebuie să cumperi un joc ca să participi!',
			'img'  => 'assets/images/da-timpul-mai-departe-share.jpg',
		),
	);
	foreach ( $page_meta as $slug => $meta ) {
		if ( ! is_page( $slug ) ) {
			continue;
		}
		$title = $meta['title'];
		$img   = get_theme_file_uri( $meta['img'] );
		echo '<meta name="description" content="' . esc_attr( $meta['desc'] ) . '">' . "\n";
		echo '<meta property="og:type" content="website">' . "\n";
		echo '<meta property="og:locale" content="ro_RO">' . "\n";
		echo '<meta property="og:site_name" content="FamilyRound">' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $meta['desc'] ) . '">' . "\n";
		echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		return;
	}

	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$pid   = get_queried_object_id();
	$title = wp_strip_all_tags( get_the_title( $pid ) );
	$is_first_experience = false !== stripos( $title, '30 de provocări' );
	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
	$desc  = $is_first_experience
		? '30 de idei pentru timp petrecut împreună, pentru familii cu copii. Alegeți un cartonaș, faceți provocarea și păstrați amintirile pe posterul FamilyRound.'
		: ( $product ? wp_strip_all_tags( $product->get_short_description() ) : '' );
	$desc = $desc ? $desc : 'O experiență FamilyRound pentru timpul petrecut împreună.';
	$img = $is_first_experience
		? get_theme_file_uri( 'assets/images/product/complete_product.png' )
		: ( $product && $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'large' ) : get_theme_file_uri( 'assets/images/logo-icon.png' ) );
	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:type" content="product">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title . ' | FamilyRound' ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
}, 5 );

// WordPress's default author/user sitemap exposes "/author/admin/" — pure SEO
// noise for a store with no author archives, and it leaks the admin username.
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

add_filter( 'wp_resource_hints', function ( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'familyround-fonts',
		'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400;1,6..72,500;1,6..72,600&family=Hanken+Grotesk:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'familyround-style', get_stylesheet_uri(), array( 'familyround-fonts' ), FR_THEME_VERSION );

	wp_enqueue_script( 'familyround-interactions', get_theme_file_uri( 'assets/interactions.js' ), array(), FR_THEME_VERSION, true );
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
		wp_enqueue_script( 'familyround-checkout', get_theme_file_uri( 'assets/checkout.js' ), array( 'jquery' ), FR_THEME_VERSION, true );
		wp_localize_script( 'familyround-checkout', 'frCheckout', array(
			'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
			'referralNonce'      => wp_create_nonce( 'fr_referral_check' ),
			'quantityNonce'      => wp_create_nonce( 'fr_checkout_quantity' ),
			'paymentAwaitingCui' => fr_payment_awaiting_cui(),
		) );
	}

	// Single product ("pagina jocului") — Apple-style page assets, loaded only there.
	if ( function_exists( 'is_product' ) && is_product() && false !== stripos( get_the_title( get_queried_object_id() ), '30 de provocări' ) ) {
		wp_enqueue_style( 'familyround-game', get_theme_file_uri( 'assets/game.css' ), array( 'familyround-style' ), FR_THEME_VERSION );
		wp_enqueue_script( 'familyround-game', get_theme_file_uri( 'assets/game.js' ), array(), FR_THEME_VERSION, true );
	}

	$asset_path = get_theme_file_path( 'assets/dist/main.js' );
	if ( file_exists( $asset_path ) ) {
		wp_enqueue_script( 'familyround-islands', get_theme_file_uri( 'assets/dist/main.js' ), array(), FR_THEME_VERSION, true );
		wp_localize_script( 'familyround-islands', 'frData', array(
			'restUrl'      => esc_url_raw( rest_url( 'familyround/v1' ) ),
			'assetsUrl'    => get_theme_file_uri( 'assets/images/' ),
			'pusculitaUrl' => esc_url_raw( home_url( '/pusculita-familyround/' ) ),
			'referralUrl'  => esc_url_raw( home_url( '/da-timpul-mai-departe/' ) ),
			'mapUrl'       => esc_url_raw( home_url( '/harta-familyround/' ) ),
		) );
	}

	$css_path = get_theme_file_path( 'assets/dist/main.css' );
	if ( file_exists( $css_path ) ) {
		wp_enqueue_style( 'familyround-islands', get_theme_file_uri( 'assets/dist/main.css' ), array(), FR_THEME_VERSION );
	}
} );

// Nu arătăm clienților câte bucăți mai avem pe stoc, doar dacă e disponibil.
add_filter( 'woocommerce_get_availability_text', function ( $text, $product ) {
	return $product->is_in_stock() ? __( 'În stoc', 'familyround' ) : __( 'Stoc epuizat', 'familyround' );
}, 10, 2 );
