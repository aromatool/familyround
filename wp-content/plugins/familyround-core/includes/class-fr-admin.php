<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Panou admin minimal pentru misiunile Pușculiței — creare, activare, arhivare.
 * O singură misiune activă la un moment dat.
 */
class FR_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_post_fr_create_mission', array( __CLASS__, 'handle_create_mission' ) );
		add_action( 'admin_post_fr_set_mission_status', array( __CLASS__, 'handle_set_status' ) );
	}

	public static function register_menu() {
		add_menu_page(
			'Pușculița FamilyRound',
			'Pușculița',
			'manage_woocommerce',
			'fr-piggybank',
			array( __CLASS__, 'render_page' ),
			'dashicons-pets',
			56
		);
	}

	public static function render_page() {
		global $wpdb;
		$missions = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fr_missions ORDER BY id DESC" );
		?>
		<div class="wrap">
			<h1>Pușculița FamilyRound</h1>

			<h2>Misiune nouă</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'fr_create_mission' ); ?>
				<input type="hidden" name="action" value="fr_create_mission">
				<table class="form-table">
					<tr>
						<th><label for="fr_title">Titlu</label></th>
						<td><input type="text" id="fr_title" name="title" class="regular-text" required></td>
					</tr>
					<tr>
						<th><label for="fr_description">Descriere</label></th>
						<td><textarea id="fr_description" name="description" class="large-text" rows="3"></textarea></td>
					</tr>
					<tr>
						<th><label for="fr_goal">Obiectiv (lei)</label></th>
						<td><input type="number" id="fr_goal" name="goal_amount" class="regular-text" step="1" min="1" required></td>
					</tr>
					<tr>
						<th><label for="fr_start">Data start</label></th>
						<td><input type="date" id="fr_start" name="start_date"></td>
					</tr>
				</table>
				<?php submit_button( 'Creează misiunea (devine activă)' ); ?>
			</form>

			<hr>

			<h2>Misiuni</h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>Titlu</th>
						<th>Obiectiv</th>
						<th>Alocat</th>
						<th>Status</th>
						<th>Acțiuni</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! $missions ) : ?>
						<tr><td colspan="5">Nicio misiune încă.</td></tr>
					<?php endif; ?>
					<?php foreach ( $missions as $mission ) : ?>
						<?php
						$allocated = $wpdb->get_var(
							$wpdb->prepare(
								"SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}fr_piggybank_ledger WHERE mission_id = %d AND status != 'cancelled'",
								$mission->id
							)
						);
						?>
						<tr>
							<td><?php echo esc_html( $mission->title ); ?></td>
							<td><?php echo esc_html( number_format( (float) $mission->goal_amount, 0 ) ); ?> lei</td>
							<td><?php echo esc_html( number_format( (float) $allocated, 0 ) ); ?> lei</td>
							<td><?php echo esc_html( $mission->status ); ?></td>
							<td>
								<?php if ( 'active' === $mission->status ) : ?>
									<a href="<?php echo esc_url( self::status_action_url( $mission->id, 'finalizata' ) ); ?>">Marchează finalizată</a>
								<?php else : ?>
									<a href="<?php echo esc_url( self::status_action_url( $mission->id, 'activa' ) ); ?>">Activează</a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	private static function status_action_url( $mission_id, $status ) {
		return wp_nonce_url(
			add_query_arg(
				array(
					'action'     => 'fr_set_mission_status',
					'mission_id' => $mission_id,
					'status'     => $status,
				),
				admin_url( 'admin-post.php' )
			),
			'fr_set_mission_status_' . $mission_id
		);
	}

	public static function handle_create_mission() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'Nu ai voie să faci asta.' );
		}
		check_admin_referer( 'fr_create_mission' );

		global $wpdb;
		$wpdb->update( $wpdb->prefix . 'fr_missions', array( 'status' => 'finalizata' ), array( 'status' => 'active' ) );

		$wpdb->insert(
			$wpdb->prefix . 'fr_missions',
			array(
				'title'       => sanitize_text_field( $_POST['title'] ?? '' ),
				'description' => sanitize_textarea_field( $_POST['description'] ?? '' ),
				'goal_amount' => (float) ( $_POST['goal_amount'] ?? 0 ),
				'start_date'  => ! empty( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : null,
				'status'      => 'active',
			)
		);

		wp_safe_redirect( admin_url( 'admin.php?page=fr-piggybank' ) );
		exit;
	}

	public static function handle_set_status() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'Nu ai voie să faci asta.' );
		}
		$mission_id = (int) ( $_GET['mission_id'] ?? 0 );
		check_admin_referer( 'fr_set_mission_status_' . $mission_id );

		$status = sanitize_text_field( $_GET['status'] ?? '' );
		global $wpdb;

		if ( 'activa' === $status ) {
			$wpdb->update( $wpdb->prefix . 'fr_missions', array( 'status' => 'finalizata' ), array( 'status' => 'active' ) );
			$wpdb->update( $wpdb->prefix . 'fr_missions', array( 'status' => 'active' ), array( 'id' => $mission_id ) );
		} else {
			$wpdb->update( $wpdb->prefix . 'fr_missions', array( 'status' => $status, 'end_date' => current_time( 'mysql' ) ), array( 'id' => $mission_id ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=fr-piggybank' ) );
		exit;
	}
}
