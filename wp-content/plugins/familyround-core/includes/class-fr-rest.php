<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API propriu, sub namespace-ul familyround/v1 — consumat de insulele React
 * și, mai târziu, de orice alt frontend (fără legătură cu tema activă).
 */
class FR_REST {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'prevent_caching' ), 10, 3 );
	}

	/**
	 * These endpoints report live order/piggybank data. LiteSpeed's own REST
	 * caching (and browsers obeying it) was serving a week-old empty response
	 * long after a mission existed — override it explicitly on every request.
	 */
	public static function prevent_caching( $response, $server, $request ) {
		if ( 0 === strpos( $request->get_route(), '/familyround/v1/' ) ) {
			header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		}
		return $response;
	}

	public static function register_routes() {
		register_rest_route(
			'familyround/v1',
			'/piggybank/summary',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_piggybank_summary' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'familyround/v1',
			'/map/points',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_map_points' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Date agregate, fără informații personale: jocuri din comenzi confirmate.
	 */
	public static function get_map_points( WP_REST_Request $request ) {
		global $wpdb;
		$raw_code = $request->get_param( 'code' );
		$code = is_string( $raw_code ) ? FR_Referrals::normalize_code( $raw_code ) : '';
		if ( null !== $raw_code && ! $code ) {
			return new WP_Error( 'fr_missing_code', 'Introdu codul de recomandare.', array( 'status' => 400 ) );
		}
		if ( $code ) {
			$referrer_id = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}fr_referrers WHERE referral_code = %s",
				$code
			) );
			if ( ! $referrer_id ) {
				return new WP_Error( 'fr_unknown_code', 'Nu am găsit acest cod. Verifică-l și încearcă din nou.', array( 'status' => 404 ) );
			}
			$rows = $wpdb->get_results( $wpdb->prepare(
				"SELECT m.locality, m.county, SUM(m.game_units) AS total_count, MIN(m.confirmed_at) AS first_confirmed_at
				 FROM {$wpdb->prefix}fr_map_orders m
				 INNER JOIN {$wpdb->prefix}fr_referral_orders r ON r.order_id = m.order_id
				 WHERE r.referrer_id = %d AND r.status = 'valid'
				 GROUP BY m.locality, m.county
				 ORDER BY total_count DESC",
				$referrer_id
			) );
		} else {
			$rows = $wpdb->get_results(
				"SELECT locality, county, SUM(game_units) AS total_count, MIN(confirmed_at) AS first_confirmed_at
				 FROM {$wpdb->prefix}fr_map_orders
				 GROUP BY locality, county
				 ORDER BY total_count DESC"
			);
		}

		$points = array_map(
			function ( $row ) {
				return array(
					'locality'   => $row->locality,
					'county'     => $row->county,
					'count'      => (int) $row->total_count,
					'is_new'     => strtotime( $row->first_confirmed_at ) >= strtotime( '-7 days' ),
				);
			},
			$rows
		);

		return new WP_REST_Response(
			array(
				'points'        => $points,
				'total_cities'  => count( $points ),
				'filtered_code' => $code,
			),
			200
		);
	}

	/**
	 * Suma alocată pentru misiunea activă — ceea ce afișează contorul public.
	 * Pe producție se adaugă un cache scurt (transient) peste acest query.
	 */
	public static function get_piggybank_summary( WP_REST_Request $request ) {
		global $wpdb;

		$mission = $wpdb->get_row(
			"SELECT id, title, description, goal_amount, image_url FROM {$wpdb->prefix}fr_missions WHERE status = 'active' ORDER BY id DESC LIMIT 1"
		);

		$past_missions = $wpdb->get_results(
			"SELECT m.id, m.title, m.goal_amount,
			        COALESCE(SUM(l.amount), 0) AS allocated
			 FROM {$wpdb->prefix}fr_missions m
			 LEFT JOIN {$wpdb->prefix}fr_piggybank_ledger l
			        ON l.mission_id = m.id AND l.status != 'cancelled'
			 WHERE m.status != 'active'
			 GROUP BY m.id
			 ORDER BY m.id DESC"
		);

		$past = array_map(
			function ( $row ) {
				return array(
					'id'          => (int) $row->id,
					'title'       => $row->title,
					'goal_amount' => (float) $row->goal_amount,
					'allocated'   => (float) $row->allocated,
				);
			},
			$past_missions
		);

		if ( ! $mission ) {
			$unassigned = (float) $wpdb->get_var(
				"SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}fr_piggybank_ledger
				 WHERE mission_id IS NULL AND status != 'cancelled'"
			);
			return new WP_REST_Response(
				array(
					'mission'       => null,
					'allocated'     => $unassigned,
					'donated'       => 0,
					'cause'         => null,
					'past_missions' => $past,
				),
				200
			);
		}

		// Allocated and donated are read from the SAME active mission, so the amount
		// and the goal always describe one campaign (never the historical total).
		$allocated = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}fr_piggybank_ledger
				 WHERE mission_id = %d AND status != 'cancelled'",
				$mission->id
			)
		);

		// Funds actually transferred to the cause. No ledger entry carries this status
		// yet, so it is 0 today; it becomes real once transfers are recorded.
		$donated = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}fr_piggybank_ledger
				 WHERE mission_id = %d AND status = 'donated'",
				$mission->id
			)
		);

		return new WP_REST_Response(
			array(
				'mission'       => array(
					'id'          => (int) $mission->id,
					'title'       => $mission->title,
					'description' => $mission->description,
					'goal_amount' => (float) $mission->goal_amount,
				),
				'allocated'     => (float) $allocated,
				'donated'       => (float) $donated,
				// The active mission's title is the confirmed beneficiary — set only
				// once an admin activates a real, named cause for that mission.
				'cause'         => array( 'name' => $mission->title, 'note' => null, 'image' => $mission->image_url ?: null ),
				'past_missions' => $past,
			),
			200
		);
	}
}
