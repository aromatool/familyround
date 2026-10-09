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
	 * Date agregate, fără nicio informație personală — doar localitate, județ și număr de comenzi.
	 */
	public static function get_map_points( WP_REST_Request $request ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			"SELECT locality, county, total_count, first_delivery_at, last_delivery_at
			 FROM {$wpdb->prefix}fr_map_points
			 ORDER BY total_count DESC"
		);

		$points = array_map(
			function ( $row ) {
				return array(
					'locality'   => $row->locality,
					'county'     => $row->county,
					'count'      => (int) $row->total_count,
					'is_new'     => strtotime( $row->first_delivery_at ) >= strtotime( '-7 days' ),
				);
			},
			$rows
		);

		return new WP_REST_Response(
			array(
				'points'        => $points,
				'total_cities'  => count( $points ),
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
			"SELECT id, title, goal_amount FROM {$wpdb->prefix}fr_missions WHERE status = 'active' ORDER BY id DESC LIMIT 1"
		);

		if ( ! $mission ) {
			return new WP_REST_Response(
				array(
					'mission' => null,
					'allocated' => 0,
				),
				200
			);
		}

		$allocated = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}fr_piggybank_ledger
				 WHERE mission_id = %d AND status != 'cancelled'",
				$mission->id
			)
		);

		return new WP_REST_Response(
			array(
				'mission'   => array(
					'id'          => (int) $mission->id,
					'title'       => $mission->title,
					'goal_amount' => (float) $mission->goal_amount,
				),
				'allocated' => (float) $allocated,
			),
			200
		);
	}
}
