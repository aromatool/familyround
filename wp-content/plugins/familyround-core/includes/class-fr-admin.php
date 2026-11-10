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
		add_action( 'admin_post_fr_delete_mission', array( __CLASS__, 'handle_delete_mission' ) );
		add_action( 'admin_post_fr_set_mission_image', array( __CLASS__, 'handle_set_image' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'render_game_eligibility' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_game_eligibility' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'hide_coleteonline_box_for_pickup' ), 20 );
	}

	/**
	 * Colete Online registers its AWB/shipping box on every order unconditionally.
	 * A pickup order is never shipped by courier, so the box (address, package,
	 * COD amount…) is just confusing noise there — hide it on that one case.
	 */
	public static function hide_coleteonline_box_for_pickup() {
		if ( ! class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) || ! function_exists( 'wc_get_order' ) ) {
			return;
		}
		$order_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : ( isset( $GLOBALS['post']->ID ) ? (int) $GLOBALS['post']->ID : 0 );
		$order    = $order_id ? wc_get_order( $order_id ) : false;
		if ( ! $order ) {
			return;
		}
		foreach ( $order->get_shipping_methods() as $item ) {
			if ( 'local_pickup' === $item->get_method_id() ) {
				remove_meta_box( 'coleteonline-order-shipping', \Automattic\WooCommerce\Utilities\OrderUtil::get_order_admin_screen(), 'normal' );
				return;
			}
		}
	}

	public static function render_game_eligibility() {
		$product = wc_get_product( get_the_ID() );
		$value = $product && 'no' === $product->get_meta( '_fr_piggybank_eligible', true ) ? 'no' : 'yes';
		woocommerce_wp_checkbox( array(
			'id'          => '_fr_piggybank_eligible',
			'label'       => 'Joc FamilyRound eligibil',
			'description' => 'Fiecare exemplar din comandă adaugă 5 lei în Pușculiță; o recomandare validă adaugă încă 5 lei.',
			'value'       => $value,
		) );
	}

	public static function save_game_eligibility( $product ) {
		$product->update_meta_data( '_fr_piggybank_eligible', isset( $_POST['_fr_piggybank_eligible'] ) ? 'yes' : 'no' );
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
		add_submenu_page(
			'fr-piggybank',
			'Lista de așteptare',
			'Lista de așteptare',
			'manage_woocommerce',
			'fr-prelaunch-leads',
			array( __CLASS__, 'render_prelaunch_leads' )
		);
	}

	/**
	 * Emailurile adunate de pe formularul "Anunță-mă când pot comanda", afișat
	 * în locul checkout-ului real cât timp FR_PRELAUNCH e activ (functions.php).
	 */
	public static function render_prelaunch_leads() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		global $wpdb;
		if ( isset( $_GET['fr_export'] ) && check_admin_referer( 'fr_export_leads' ) ) {
			self::export_prelaunch_leads_csv();
			exit;
		}
		$leads = $wpdb->get_results( "SELECT email, created_at FROM {$wpdb->prefix}fr_prelaunch_leads ORDER BY id DESC" );
		?>
		<div class="wrap">
			<h1>Lista de așteptare (precomandă)</h1>
			<p><?php echo count( $leads ); ?> emailuri înregistrate cât timp comanda reală e dezactivată.</p>
			<p><a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'fr_export', '1' ), 'fr_export_leads' ) ); ?>">Descarcă CSV</a></p>
			<table class="widefat striped">
				<thead><tr><th>Email</th><th>Data</th></tr></thead>
				<tbody>
					<?php if ( ! $leads ) : ?>
						<tr><td colspan="2">Niciun email încă.</td></tr>
					<?php endif; ?>
					<?php foreach ( $leads as $lead ) : ?>
						<tr><td><?php echo esc_html( $lead->email ); ?></td><td><?php echo esc_html( $lead->created_at ); ?></td></tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	private static function export_prelaunch_leads_csv() {
		global $wpdb;
		$leads = $wpdb->get_results( "SELECT email, created_at FROM {$wpdb->prefix}fr_prelaunch_leads ORDER BY id DESC" );
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=familyround-lista-asteptare.csv' );
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'email', 'created_at' ) );
		foreach ( $leads as $lead ) {
			fputcsv( $out, array( $lead->email, $lead->created_at ) );
		}
		fclose( $out );
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
					<tr>
						<th><label for="fr_image_url">URL poză (opțional)</label></th>
						<td><input type="url" id="fr_image_url" name="image_url" class="regular-text" placeholder="https://..."></td>
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
						<th>Poză</th>
						<th>Obiectiv</th>
						<th>Alocat</th>
						<th>Status</th>
						<th>Acțiuni</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! $missions ) : ?>
						<tr><td colspan="6">Nicio misiune încă.</td></tr>
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
							<td>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:6px;align-items:center;">
									<?php wp_nonce_field( 'fr_set_mission_image_' . $mission->id ); ?>
									<input type="hidden" name="action" value="fr_set_mission_image">
									<input type="hidden" name="mission_id" value="<?php echo esc_attr( $mission->id ); ?>">
									<input type="url" name="image_url" value="<?php echo esc_attr( $mission->image_url ); ?>" placeholder="https://..." style="width:220px;">
									<?php submit_button( 'Salvează', 'secondary', '', false ); ?>
								</form>
							</td>
							<td><?php echo esc_html( number_format( (float) $mission->goal_amount, 0 ) ); ?> lei</td>
							<td><?php echo esc_html( number_format( (float) $allocated, 0 ) ); ?> lei</td>
							<td><?php echo esc_html( $mission->status ); ?></td>
							<td>
								<?php if ( 'active' === $mission->status ) : ?>
									<a href="<?php echo esc_url( self::status_action_url( $mission->id, 'finalizata' ) ); ?>">Marchează finalizată</a>
								<?php else : ?>
									<a href="<?php echo esc_url( self::status_action_url( $mission->id, 'activa' ) ); ?>">Activează</a>
								<?php endif; ?>
								<?php
								$ledger_rows = $wpdb->get_var( $wpdb->prepare(
									"SELECT COUNT(*) FROM {$wpdb->prefix}fr_piggybank_ledger WHERE mission_id = %d",
									$mission->id
								) );
								?>
								<?php if ( ! $ledger_rows ) : ?>
									· <a href="<?php echo esc_url( self::delete_action_url( $mission->id ) ); ?>" onclick="return confirm('Ștergi definitiv această misiune?');" style="color:#b32d2e;">Șterge</a>
								<?php else : ?>
									· <span title="Are bani alocați din Pușculiță, nu poate fi ștearsă.">Șterge</span>
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

	private static function delete_action_url( $mission_id ) {
		return wp_nonce_url(
			add_query_arg(
				array(
					'action'     => 'fr_delete_mission',
					'mission_id' => $mission_id,
				),
				admin_url( 'admin-post.php' )
			),
			'fr_delete_mission_' . $mission_id
		);
	}

	public static function handle_delete_mission() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'Nu ai voie să faci asta.' );
		}
		$mission_id = (int) ( $_GET['mission_id'] ?? 0 );
		check_admin_referer( 'fr_delete_mission_' . $mission_id );

		global $wpdb;
		$ledger_rows = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}fr_piggybank_ledger WHERE mission_id = %d",
			$mission_id
		) );
		if ( ! $ledger_rows ) {
			$wpdb->delete( $wpdb->prefix . 'fr_missions', array( 'id' => $mission_id ) );
		}
		wp_safe_redirect( wp_get_referer() ?: admin_url( 'admin.php?page=familyround' ) );
		exit;
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
				'image_url'   => esc_url_raw( $_POST['image_url'] ?? '' ),
				'status'      => 'active',
			)
		);
		if ( $wpdb->insert_id ) {
			self::assign_unassigned_contributions( (int) $wpdb->insert_id );
		}

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
			self::assign_unassigned_contributions( $mission_id );
		} else {
			$wpdb->update( $wpdb->prefix . 'fr_missions', array( 'status' => $status, 'end_date' => current_time( 'mysql' ) ), array( 'id' => $mission_id ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=fr-piggybank' ) );
		exit;
	}

	public static function handle_set_image() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'Nu ai voie să faci asta.' );
		}
		$mission_id = (int) ( $_POST['mission_id'] ?? 0 );
		check_admin_referer( 'fr_set_mission_image_' . $mission_id );

		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'fr_missions',
			array( 'image_url' => esc_url_raw( $_POST['image_url'] ?? '' ) ),
			array( 'id' => $mission_id )
		);

		wp_safe_redirect( admin_url( 'admin.php?page=fr-piggybank' ) );
		exit;
	}

	private static function assign_unassigned_contributions( $mission_id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->prefix}fr_piggybank_ledger SET mission_id = %d WHERE mission_id IS NULL",
			$mission_id
		) );
	}
}
