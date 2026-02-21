<?php
/**
 * Plugin Name: Agent Rotator for WA
 * Plugin URI:  https://wordpress.org/plugins/agent-rotator-for-wa/
 * Description: Simple and clean contact rotator for messaging apps. Adds a floating button to distribute
 *              incoming messages across multiple available agents based on their active schedule.
 * Version:     1.0.0
 * Author:      NRD Design
 * Author URI:  https://nrd.com.ar
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: agent-rotator-for-wa
 *
 * @package AgentRotatorForWA
 */

// ──────────────────────────────────────────────────────────────────────────────
// Security: Exit immediately if accessed directly (not through WordPress).
// ──────────────────────────────────────────────────────────────────────────────
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ──────────────────────────────────────────────────────────────────────────────
// Constants
// ──────────────────────────────────────────────────────────────────────────────

/** Maximum number of agents allowed in the Lite version. */
define( 'RWA_LITE_MAX_AGENTS', 2 );

/** Nonce action name used to validate the settings form submission. */
define( 'RWA_LITE_NONCE_ACTION', 'rwa_lite_save_settings' );

/** Nonce field name used in the settings form. */
define( 'RWA_LITE_NONCE_FIELD', 'rwa_lite_nonce' );


// ══════════════════════════════════════════════════════════════════════════════
// 1. ADMIN MENU
// ══════════════════════════════════════════════════════════════════════════════

/**
 * Registers the plugin's top-level admin menu page.
 *
 * Hooked to 'admin_menu'.
 */
add_action( 'admin_menu', 'rwa_lite_menu' );

function rwa_lite_menu() {
	add_menu_page(
		__( 'Contact Rotator', 'agent-rotator-for-wa' ), // Page <title>
		__( 'WA Rotator', 'agent-rotator-for-wa' ),      // Menu label
		'manage_options',                                       // Required capability
		'rwa_lite_settings',                                    // Menu slug
		'rwa_lite_page',                                        // Callback function
		'dashicons-whatsapp',                                   // Dashicon
		100                                                     // Menu position
	);
}


// ══════════════════════════════════════════════════════════════════════════════
// 2. ADMIN SETTINGS PAGE
// ══════════════════════════════════════════════════════════════════════════════

/**
 * Renders the plugin's settings page HTML.
 *
 * Includes:
 *  - Capability check.
 *  - Scoped CSS for the admin UI.
 *  - Settings form with wp_nonce_field() for CSRF protection.
 *  - Agent table populated from saved options.
 *  - Inline JS for dynamic row management (add / remove agents).
 */
function rwa_lite_page() {

	// ── Capability check ──────────────────────────────────────────────────────
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// ── Retrieve saved options ────────────────────────────────────────────────
	$agents     = get_option( 'rwa_lite_agents', array() );
	$global_msg = get_option(
		'rwa_lite_global_msg',
		__( 'Hello, I need more information.', 'agent-rotator-for-wa' )
	);

	// ── Translated day labels (Sun–Sat) ───────────────────────────────────────
	$week_days = array(
		__( 'Sun', 'agent-rotator-for-wa' ),
		__( 'Mon', 'agent-rotator-for-wa' ),
		__( 'Tue', 'agent-rotator-for-wa' ),
		__( 'Wed', 'agent-rotator-for-wa' ),
		__( 'Thu', 'agent-rotator-for-wa' ),
		__( 'Fri', 'agent-rotator-for-wa' ),
		__( 'Sat', 'agent-rotator-for-wa' ),
	);

	// Display order: Mon(1) … Sat(6), Sun(0).
	$days_order = array( 1, 2, 3, 4, 5, 6, 0 );

	// ── Build a JSON-safe list of translated day labels for JS ────────────────
	// wp_json_encode with JSON_HEX_TAG | JSON_HEX_AMP prevents XSS in inline JS.
	$day_labels_json = wp_json_encode( $week_days, JSON_HEX_TAG | JSON_HEX_AMP );

	?>
	<style>
		/* ── Field group wrapper for name / phone / time inputs ── */
		.rwa-lite-field-group {
			position:      relative;
			display:       inline-block;
			margin-right:  8px;
			margin-bottom: 4px;
			vertical-align: top;
		}

		.rwa-lite-field-group input {
			display: block;
		}

		/* ── Tooltip shown below an invalid field ── */
		.rwa-lite-error-tooltip {
			display:       flex;
			align-items:   center;
			gap:           8px;
			position:      absolute;
			left:          0;
			top:           100%;
			margin-top:    4px;
			z-index:       10;
			max-width:     280px;
			padding:       10px 12px;
			background:    #fff;
			border-radius: 6px;
			box-shadow:    0 4px 12px rgba(0,0,0,.15);
			border:        1px solid #e0e0e0;
			font-size:     13px;
			color:         #1a1a1a;
			white-space:   nowrap;
		}

		/* Caret pointing upward from the tooltip */
		.rwa-lite-error-tooltip::before {
			content:      '';
			position:     absolute;
			left:         12px;
			top:          -6px;
			border-left:  6px solid transparent;
			border-right: 6px solid transparent;
			border-bottom:6px solid #fff;
			filter:       drop-shadow(0 -1px 1px rgba(0,0,0,.08));
		}

		/* Orange exclamation icon inside tooltip */
		.rwa-lite-error-tooltip .rwa-lite-error-icon {
			flex-shrink:     0;
			width:           20px;
			height:          20px;
			background:      #f57c00;
			border-radius:   3px;
			display:         flex;
			align-items:     center;
			justify-content: center;
			color:           #fff;
			font-weight:     bold;
			font-size:       14px;
			line-height:     1;
		}

		.rwa-lite-error-tooltip .rwa-lite-error-text {
			flex:        1;
			white-space: normal;
		}

		/* Red border highlight on the invalid input */
		.rwa-lite-field-group.has-error input {
			border-color: #d63638;
			box-shadow:   0 0 0 1px #d63638;
		}

		/* ── Days checkbox row ── */
		.rwa-lite-field-group-days {
			display:    flex;
			flex-wrap:  wrap;
			flex-direction: row;
			align-items: center;
			gap:        6px 10px;
		}

		.rwa-lite-field-group-days label {
			display:     inline-flex;
			align-items: center;
			margin:      0;
			white-space: nowrap;
			cursor:      pointer;
		}

		.rwa-lite-field-group-days label input {
			margin: 0 4px 0 0;
		}

		/* ── Upgrade notice badge ── */
		.rwa-lite-upgrade-notice {
			display:      inline-flex;
			align-items:  center;
			gap:          6px;
			margin-left:  10px;
			padding:      6px 12px;
			background:   #fff8e1;
			border:       1px solid #ffe082;
			border-radius:4px;
			font-size:    13px;
			color:        #795548;
			vertical-align: middle;
		}

		.rwa-lite-upgrade-notice a {
			color:           #e65100;
			font-weight:     600;
			text-decoration: none;
		}

		.rwa-lite-upgrade-notice a:hover {
			text-decoration: underline;
		}
	</style>

	<div class="wrap">
		<h1><?php esc_html_e( 'Agent Rotator for WA', 'agent-rotator-for-wa' ); ?></h1>

		<?php
		// Display any admin notices (validation errors, success messages).
		settings_errors( 'rwa_lite_messages' );
		?>

		<form method="post" action="options.php" id="rwa-lite-form">

			<?php
			/**
			 * settings_fields() outputs:
			 *  – Hidden 'option_page' field  (value: 'rwa-lite-group')
			 *  – Hidden '_wpnonce' field      (WordPress's own options nonce)
			 *  – Hidden '_wp_http_referer'    field
			 *
			 * wp_nonce_field() adds an *additional* plugin-specific nonce so
			 * that rwa_lite_sanitize_agents() can verify the request originated
			 * from our own form, independently of the Options API nonce.
			 */
			settings_fields( 'rwa-lite-group' );
			wp_nonce_field( RWA_LITE_NONCE_ACTION, RWA_LITE_NONCE_FIELD );
			?>

			<table class="form-table">
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Global Pre-filled Message:', 'agent-rotator-for-wa' ); ?>
					</th>
					<td>
						<input
							type="text"
							name="rwa_lite_global_msg"
							value="<?php echo esc_attr( $global_msg ); ?>"
							class="regular-text"
						>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Sales Agents', 'agent-rotator-for-wa' ); ?></h2>

			<table class="wp-list-table widefat fixed striped" id="agents-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name / Phone', 'agent-rotator-for-wa' ); ?></th>
						<th><?php esc_html_e( 'Schedule (From/To)', 'agent-rotator-for-wa' ); ?></th>
						<th><?php esc_html_e( 'Active Days', 'agent-rotator-for-wa' ); ?></th>
						<th><?php esc_html_e( 'Action', 'agent-rotator-for-wa' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					// Render one row per saved agent.
					if ( $agents && is_array( $agents ) ) :
						foreach ( $agents as $i => $agent ) :
							// Ensure $i is a safe integer for use in input names.
							$i = (int) $i;
					?>
					<tr class="rwa-lite-agent-row">
						<td>
							<div class="rwa-lite-field-group">
								<input
									type="text"
									name="rwa_lite_agents[<?php echo esc_attr( $i ); ?>][name]"
									value="<?php echo esc_attr( $agent['name'] ?? '' ); ?>"
									placeholder="<?php esc_attr_e( 'Name', 'agent-rotator-for-wa' ); ?>"
									required
								>
							</div>
							<div class="rwa-lite-field-group">
								<input
									type="text"
									name="rwa_lite_agents[<?php echo esc_attr( $i ); ?>][phone]"
									value="<?php echo esc_attr( $agent['phone'] ?? '' ); ?>"
									placeholder="<?php esc_attr_e( 'Phone Number (e.g. 1234567890)', 'agent-rotator-for-wa' ); ?>"
									inputmode="numeric"
									required
								>
							</div>
						</td>
						<td>
							<div class="rwa-lite-field-group">
								<input
									type="time"
									name="rwa_lite_agents[<?php echo esc_attr( $i ); ?>][start]"
									value="<?php echo esc_attr( $agent['start'] ?? '' ); ?>"
									required
								>
							</div>
							<div class="rwa-lite-field-group">
								<input
									type="time"
									name="rwa_lite_agents[<?php echo esc_attr( $i ); ?>][end]"
									value="<?php echo esc_attr( $agent['end'] ?? '' ); ?>"
									required
								>
							</div>
						</td>
						<td>
							<div class="rwa-lite-field-group rwa-lite-field-group-days">
								<?php
								foreach ( $days_order as $idx ) {
									// Retrieve translated label for this day index.
									$day = $week_days[ $idx ];

									// Build the array of already-saved day strings.
									$saved_days = ( isset( $agent['days'] ) && is_array( $agent['days'] ) )
										? array_map( 'strval', $agent['days'] )
										: array();

									// Determine whether this day checkbox should be pre-checked.
									$is_checked = in_array( (string) $idx, $saved_days, true );
									?>
									<label>
										<input
											type="checkbox"
											name="rwa_lite_agents[<?php echo esc_attr( $i ); ?>][days][]"
											value="<?php echo esc_attr( $idx ); ?>"
											<?php checked( $is_checked ); ?>
										>
										<?php echo esc_html( $day ); ?>
									</label>
									<?php
								}
								?>
							</div>
						</td>
						<td>
							<button type="button" class="button remove-row">
								<?php esc_html_e( 'Remove', 'agent-rotator-for-wa' ); ?>
							</button>
						</td>
					</tr>
					<?php
						endforeach;
					endif;
					?>
				</tbody>
			</table>

			<p>
				<button type="button" class="button button-primary" id="add-agent">
					<?php esc_html_e( 'Add Agent', 'agent-rotator-for-wa' ); ?>
				</button>

				<span class="rwa-lite-upgrade-notice" id="rwa-lite-limit-notice" style="display:none;">
					⭐
					<?php esc_html_e( 'Lite version supports up to 2 agents.', 'agent-rotator-for-wa' ); ?>
					<a href="https://nrd.com.ar" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Upgrade to Premium', 'agent-rotator-for-wa' ); ?> →
					</a>
				</span>
			</p>

			<?php submit_button(); ?>

		</form>
	</div>

	<script>
	/* ── Admin JS: dynamic agent rows ── */
	(function () {
		'use strict';

		// ── Constants passed securely from PHP ──────────────────────────────
		// (int) cast in PHP ensures this is always a safe integer.
		var RWA_LITE_MAX = <?php echo (int) RWA_LITE_MAX_AGENTS; ?>;

		// Running index used for new row field names (prevents collisions).
		var rwaLiteRowIdx = <?php echo (int) count( (array) $agents ); ?>;

		/**
		 * Day labels are passed via wp_json_encode (JSON_HEX_TAG | JSON_HEX_AMP)
		 * so special characters are entity-encoded before reaching the browser.
		 * This prevents XSS, even if a translation contains HTML characters.
		 *
		 * Index 0 = Sunday, 1 = Monday, … 6 = Saturday.
		 */
		var rwaLiteDayLabels = <?php echo $day_labels_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- value is produced by wp_json_encode with HEX flags. ?>;

		// Display order: Mon(1)…Sat(6), Sun(0).
		var rwaLiteDaysOrder = [1, 2, 3, 4, 5, 6, 0];

		// Translated strings for UI text — injected via esc_js() to be safe inside a JS string literal.
		var rwaLiteI18n = {
			placeholderName:  '<?php echo esc_js( __( 'Name', 'agent-rotator-for-wa' ) ); ?>',
			placeholderPhone: '<?php echo esc_js( __( 'Phone Number', 'agent-rotator-for-wa' ) ); ?>',
			removeLabel:      '<?php echo esc_js( __( 'Remove', 'agent-rotator-for-wa' ) ); ?>',
			errRequired:      '<?php echo esc_js( __( 'Field required', 'agent-rotator-for-wa' ) ); ?>',
			errInvalidChars:  '<?php echo esc_js( __( 'Invalid characters', 'agent-rotator-for-wa' ) ); ?>',
			errPhoneRange:    '<?php echo esc_js( __( 'Between 6 and 15 digits', 'agent-rotator-for-wa' ) ); ?>',
			errSelectDay:     '<?php echo esc_js( __( 'Select at least one day', 'agent-rotator-for-wa' ) ); ?>',
		};

		// ── Helpers ─────────────────────────────────────────────────────────

		/**
		 * Updates the "Add Agent" button state based on the current row count.
		 * Disables the button and shows the upgrade notice when the limit is reached.
		 */
		function rwaLiteUpdateAddButton() {
			var rows   = document.querySelectorAll( '#agents-table tbody tr.rwa-lite-agent-row' );
			var btn    = document.getElementById( 'add-agent' );
			var notice = document.getElementById( 'rwa-lite-limit-notice' );

			if ( rows.length >= RWA_LITE_MAX ) {
				btn.disabled          = true;
				btn.style.opacity     = '0.5';
				btn.style.cursor      = 'not-allowed';
				notice.style.display  = 'inline-flex';
			} else {
				btn.disabled          = false;
				btn.style.opacity     = '';
				btn.style.cursor      = '';
				notice.style.display  = 'none';
			}
		}

		/**
		 * Attaches an error tooltip beneath the given field-group element.
		 *
		 * NOTE: textContent is used (never innerHTML) so the message string
		 * is not parsed as HTML, preventing any potential XSS.
		 *
		 * @param {Element} fieldGroup  The .rwa-lite-field-group wrapper element.
		 * @param {string}  message     Translated, plain-text error message.
		 */
		function rwaLiteShowFieldError( fieldGroup, message ) {
			if ( ! fieldGroup ) return;

			fieldGroup.classList.add( 'has-error' );

			// Remove any previously appended tooltip.
			var existing = fieldGroup.querySelector( '.rwa-lite-error-tooltip' );
			if ( existing ) existing.remove();

			// Build tooltip using DOM methods — no innerHTML with user data.
			var tip  = document.createElement( 'div' );
			tip.className = 'rwa-lite-error-tooltip';

			var icon = document.createElement( 'span' );
			icon.className   = 'rwa-lite-error-icon';
			icon.textContent = '!';

			var text = document.createElement( 'span' );
			text.className   = 'rwa-lite-error-text';
			text.textContent = message; // Safe: assigned as plain text.

			tip.appendChild( icon );
			tip.appendChild( text );
			fieldGroup.appendChild( tip );
		}

		/**
		 * Clears all validation error highlights and tooltips from the form.
		 */
		function rwaLiteClearErrors() {
			document.querySelectorAll( '.rwa-lite-field-group' ).forEach( function ( g ) {
				g.classList.remove( 'has-error' );
				g.querySelectorAll( '.rwa-lite-error-tooltip' ).forEach( function ( t ) { t.remove(); } );
			} );
		}

		/**
		 * Builds the days-checkboxes HTML string for a new agent row.
		 * Values come from the PHP-injected, JSON-encoded rwaLiteDayLabels array,
		 * so they are already safe; day index is always an integer literal.
		 *
		 * @param  {number} rowIdx  The index to use in field name attributes.
		 * @return {string}         HTML string for the checkboxes.
		 */
		function rwaLiteBuildDaysHtml( rowIdx ) {
			var html = '';
			rwaLiteDaysOrder.forEach( function ( idx ) {
				// idx is always an integer from the literal array above — safe to interpolate.
				// rwaLiteDayLabels[idx] comes from wp_json_encode with HEX flags — already safe.
				html += '<label>'
					+ '<input type="checkbox"'
					+ ' name="rwa_lite_agents[' + rowIdx + '][days][]"'
					+ ' value="' + idx + '">'
					+ ' ' + rwaLiteDayLabels[ idx ]
					+ '</label>';
			} );
			return html;
		}

		// ── Event: Add Agent button ──────────────────────────────────────────

		document.getElementById( 'add-agent' ).addEventListener( 'click', function () {
			var rows  = document.querySelectorAll( '#agents-table tbody tr.rwa-lite-agent-row' );
			if ( rows.length >= RWA_LITE_MAX ) return;

			var tbody    = document.querySelector( '#agents-table tbody' );
			var rowCount = rwaLiteRowIdx++;

			// Build the new row using DOM APIs to avoid innerHTML XSS risks.
			var row = document.createElement( 'tr' );
			row.className = 'rwa-lite-agent-row';

			// ── Cell 1: Name + Phone ────────────────────────────────────────
			var tdNamePhone = document.createElement( 'td' );

			// Name input.
			var nameGroup       = document.createElement( 'div' );
			nameGroup.className = 'rwa-lite-field-group';
			var nameInput       = document.createElement( 'input' );
			nameInput.type        = 'text';
			nameInput.name        = 'rwa_lite_agents[' + rowCount + '][name]';
			nameInput.placeholder = rwaLiteI18n.placeholderName;
			nameInput.required    = true;
			nameGroup.appendChild( nameInput );
			tdNamePhone.appendChild( nameGroup );

			// Phone input.
			var phoneGroup       = document.createElement( 'div' );
			phoneGroup.className = 'rwa-lite-field-group';
			var phoneInput       = document.createElement( 'input' );
			phoneInput.type        = 'text';
			phoneInput.name        = 'rwa_lite_agents[' + rowCount + '][phone]';
			phoneInput.placeholder = rwaLiteI18n.placeholderPhone;
			phoneInput.setAttribute( 'inputmode', 'numeric' );
			phoneInput.required = true;
			phoneGroup.appendChild( phoneInput );
			tdNamePhone.appendChild( phoneGroup );

			row.appendChild( tdNamePhone );

			// ── Cell 2: Start + End time ────────────────────────────────────
			var tdTime = document.createElement( 'td' );

			var startGroup       = document.createElement( 'div' );
			startGroup.className = 'rwa-lite-field-group';
			var startInput       = document.createElement( 'input' );
			startInput.type     = 'time';
			startInput.name     = 'rwa_lite_agents[' + rowCount + '][start]';
			startInput.required = true;
			startGroup.appendChild( startInput );
			tdTime.appendChild( startGroup );

			var endGroup       = document.createElement( 'div' );
			endGroup.className = 'rwa-lite-field-group';
			var endInput       = document.createElement( 'input' );
			endInput.type     = 'time';
			endInput.name     = 'rwa_lite_agents[' + rowCount + '][end]';
			endInput.required = true;
			endGroup.appendChild( endInput );
			tdTime.appendChild( endGroup );

			row.appendChild( tdTime );

			// ── Cell 3: Days checkboxes ─────────────────────────────────────
			var tdDays       = document.createElement( 'td' );
			var daysGroup    = document.createElement( 'div' );
			daysGroup.className = 'rwa-lite-field-group rwa-lite-field-group-days';
			// rwaLiteBuildDaysHtml uses only integer indices and wp_json_encode values.
			daysGroup.innerHTML = rwaLiteBuildDaysHtml( rowCount );
			tdDays.appendChild( daysGroup );
			row.appendChild( tdDays );

			// ── Cell 4: Remove button ───────────────────────────────────────
			var tdAction   = document.createElement( 'td' );
			var removeBtn  = document.createElement( 'button' );
			removeBtn.type      = 'button';
			removeBtn.className = 'button remove-row';
			removeBtn.textContent = rwaLiteI18n.removeLabel; // Plain text — safe.
			tdAction.appendChild( removeBtn );
			row.appendChild( tdAction );

			tbody.appendChild( row );
			rwaLiteUpdateAddButton();
		} );

		// ── Event: Remove row (delegated) ────────────────────────────────────

		document.addEventListener( 'click', function ( e ) {
			if ( e.target.classList.contains( 'remove-row' ) ) {
				e.target.closest( 'tr' ).remove();
				rwaLiteUpdateAddButton();
			}
		} );

		// ── Event: Form validation on submit ─────────────────────────────────

		document.getElementById( 'rwa-lite-form' ).addEventListener( 'submit', function ( e ) {
			rwaLiteClearErrors();

			// Name: Unicode letters, numbers and spaces only.
			var nameRe  = /^[\p{L}\p{N} ]+$/u;
			// Time: HH:MM in 24-hour format.
			var timeRe  = /^(?:[01]\d|2[0-3]):[0-5]\d$/;

			var rows         = document.querySelectorAll( '#agents-table tbody tr.rwa-lite-agent-row' );
			var hasError     = false;
			var firstErrorEl = null;

			rows.forEach( function ( row ) {
				var nameEl      = row.querySelector( 'input[name*="[name]"]' );
				var phoneEl     = row.querySelector( 'input[name*="[phone]"]' );
				var startEl     = row.querySelector( 'input[name*="[start]"]' );
				var endEl       = row.querySelector( 'input[name*="[end]"]' );
				var daysChecked = row.querySelectorAll( 'input[type="checkbox"][name*="[days]"]:checked' );

				var name       = ( nameEl  ? nameEl.value  : '' ).trim();
				var phoneRaw   = ( phoneEl ? phoneEl.value : '' ).trim();
				var phoneDigits= phoneRaw.replace( /\D+/g, '' );
				var start      = ( startEl ? startEl.value : '' ).trim();
				var end        = ( endEl   ? endEl.value   : '' ).trim();

				var nameGroup  = nameEl  ? nameEl.closest( '.rwa-lite-field-group' )  : null;
				var phoneGroup = phoneEl ? phoneEl.closest( '.rwa-lite-field-group' ) : null;
				var startGroup = startEl ? startEl.closest( '.rwa-lite-field-group' ) : null;
				var endGroup   = endEl   ? endEl.closest( '.rwa-lite-field-group' )   : null;
				var daysGroup  = row.querySelector( '.rwa-lite-field-group-days' );

				// Validate name.
				if ( name.length === 0 ) {
					rwaLiteShowFieldError( nameGroup, rwaLiteI18n.errRequired );
					hasError = true;
					if ( ! firstErrorEl ) firstErrorEl = nameEl;
				} else if ( ! nameRe.test( name ) || ! /[\p{L}\p{N}]/u.test( name ) ) {
					rwaLiteShowFieldError( nameGroup, rwaLiteI18n.errInvalidChars );
					hasError = true;
					if ( ! firstErrorEl ) firstErrorEl = nameEl;
				}

				// Validate phone.
				if ( phoneDigits.length === 0 ) {
					rwaLiteShowFieldError( phoneGroup, rwaLiteI18n.errRequired );
					hasError = true;
					if ( ! firstErrorEl ) firstErrorEl = phoneEl;
				} else if ( phoneDigits.length < 6 || phoneDigits.length > 15 ) {
					rwaLiteShowFieldError( phoneGroup, rwaLiteI18n.errPhoneRange );
					hasError = true;
					if ( ! firstErrorEl ) firstErrorEl = phoneEl;
				}

				// Validate start time.
				if ( ! timeRe.test( start ) ) {
					rwaLiteShowFieldError( startGroup, rwaLiteI18n.errRequired );
					hasError = true;
					if ( ! firstErrorEl ) firstErrorEl = startEl;
				}

				// Validate end time.
				if ( ! timeRe.test( end ) ) {
					rwaLiteShowFieldError( endGroup, rwaLiteI18n.errRequired );
					hasError = true;
					if ( ! firstErrorEl ) firstErrorEl = endEl;
				}

				// Validate at least one day selected.
				if ( daysChecked.length === 0 ) {
					rwaLiteShowFieldError( daysGroup, rwaLiteI18n.errSelectDay );
					hasError = true;
					if ( ! firstErrorEl ) firstErrorEl = daysGroup;
				}
			} );

			if ( hasError ) {
				e.preventDefault();
				if ( firstErrorEl ) {
					firstErrorEl.scrollIntoView( { behavior: 'smooth', block: 'center' } );
				}
			}
		} );

		// ── Init ─────────────────────────────────────────────────────────────
		rwaLiteUpdateAddButton();

	}());
	</script>
	<?php
}


// ══════════════════════════════════════════════════════════════════════════════
// 3. SETTINGS REGISTRATION
// ══════════════════════════════════════════════════════════════════════════════

/**
 * Registers the plugin settings with the WordPress Settings API.
 *
 * Hooked to 'admin_init'.
 *  - 'rwa_lite_agents'     → sanitised by rwa_lite_sanitize_agents().
 *  - 'rwa_lite_global_msg' → sanitised by the core sanitize_text_field().
 */
add_action( 'admin_init', 'rwa_lite_register_settings' );

function rwa_lite_register_settings() {
	register_setting(
		'rwa-lite-group',
		'rwa_lite_agents',
		array( 'sanitize_callback' => 'rwa_lite_sanitize_agents' )
	);

	register_setting(
		'rwa-lite-group',
		'rwa_lite_global_msg',
		array( 'sanitize_callback' => 'sanitize_text_field' )
	);
}


// ══════════════════════════════════════════════════════════════════════════════
// 4. DATA VALIDATION & SANITISATION
// ══════════════════════════════════════════════════════════════════════════════


/**
 * Validates and sanitises raw agent input from $_POST.
 *
 * Performs strict validation on every field:
 *  - name  : non-empty, Unicode letters/numbers/spaces.
 *  - phone : digits only, 6–15 characters.
 *  - start / end : HH:MM 24-hour format.
 *  - days  : array, each element 0–6, at least one item.
 *
 * @param  mixed $input      Raw value (expected: array of agent rows).
 * @param  array &$sanitized Reference that receives the clean output array.
 * @return array{0: bool, 1: array}  [valid, sanitized_agents]
 */
function rwa_lite_validate_agents_input( $input, &$sanitized = array() ) {
	$sanitized = array();

	// Treat empty / missing input as a valid "no agents" state.
	if ( null === $input || '' === $input || false === $input ) {
		return array( true, $sanitized );
	}

	if ( ! is_array( $input ) ) {
		return array( false, $sanitized );
	}

	// Enforce the Lite plan agent limit.
	$input = array_slice( $input, 0, RWA_LITE_MAX_AGENTS );

	// Closure that validates HH:MM 24-hour format.
	$time_ok = static function ( $t ) {
		return is_string( $t ) && (bool) preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $t );
	};

	foreach ( $input as $row ) {
		if ( ! is_array( $row ) ) {
			return array( false, $sanitized );
		}

		// ── Extract and cast raw field values ─────────────────────────────
		$name      = isset( $row['name'] )  ? trim( (string) $row['name'] )  : '';
		$phone_raw = isset( $row['phone'] ) ? (string) $row['phone']          : '';
		$phone     = preg_replace( '/\D+/', '', $phone_raw ); // Digits only.
		$start     = isset( $row['start'] ) ? trim( (string) $row['start'] ) : '';
		$end       = isset( $row['end'] )   ? trim( (string) $row['end'] )   : '';
		$days      = isset( $row['days'] )  ? $row['days']                   : array();

		// ── Field validation ──────────────────────────────────────────────
		$name_ok  = ( '' !== $name )
			&& (bool) preg_match( '/^[\p{L}\p{N} ]+$/u', $name )
			&& (bool) preg_match( '/[\p{L}\p{N}]/u', $name );

		$phone_ok = ( is_string( $phone ) || is_numeric( $phone ) )
			&& strlen( (string) $phone ) >= 6
			&& strlen( (string) $phone ) <= 15;

		$start_ok = $time_ok( $start );
		$end_ok   = $time_ok( $end );

		// ── Days validation ───────────────────────────────────────────────
		if ( ! is_array( $days ) || count( $days ) < 1 ) {
			return array( false, $sanitized );
		}

		$days_sanitized = array();
		foreach ( $days as $d ) {
			$d = (string) $d;
			// Only accept single-digit values 0–6.
			if ( ! preg_match( '/^[0-6]$/', $d ) ) {
				return array( false, $sanitized );
			}
			$days_sanitized[] = $d;
		}

		// Remove duplicates and re-index.
		$days_sanitized = array_values( array_unique( $days_sanitized ) );

		if ( count( $days_sanitized ) < 1 ) {
			return array( false, $sanitized );
		}

		// ── Abort if any required field is invalid ────────────────────────
		if ( ! $name_ok || ! $phone_ok || ! $start_ok || ! $end_ok ) {
			return array( false, $sanitized );
		}

		// ── Build sanitised row ───────────────────────────────────────────
		$sanitized[] = array(
			'name'  => sanitize_text_field( $name ),   // Strips tags, encodes special chars.
			'phone' => (string) $phone,                 // Already digits-only from preg_replace.
			'start' => sanitize_text_field( $start ),  // Safe HH:MM string.
			'end'   => sanitize_text_field( $end ),    // Safe HH:MM string.
			'days'  => $days_sanitized,                 // Array of '0'–'6' strings.
		);
	}

	return array( true, $sanitized );
}

/**
 * Adds a single validation error notice to the Settings API message queue.
 * The static flag prevents duplicate messages on the same request.
 */
function rwa_lite_add_validation_error_once() {
	static $added = false;

	if ( $added ) {
		return;
	}

	$added = true;

	add_settings_error(
		'rwa_lite_messages',
		'rwa_lite_validation',
		__( 'Please fill all fields correctly.', 'agent-rotator-for-wa' ),
		'error'
	);
}

/**
 * Sanitise callback for the 'rwa_lite_agents' option.
 *
 * Called automatically by the Settings API when the form is submitted.
 * Verifies the plugin-specific nonce before processing any data.
 *
 * @param  mixed $input  Raw value coming from $_POST.
 * @return array         Sanitised agents array, or the previous saved value on error.
 */
function rwa_lite_sanitize_agents( $input ) {

	// ── Nonce verification ────────────────────────────────────────────────────
	// check_admin_referer() is recognised by the WordPress PHPCS sniffer as
	// a nonce verification call, eliminating NonceVerification warnings.
	// It verifies the plugin-specific nonce added by wp_nonce_field() in the form
	// and calls wp_die() automatically on failure (CSRF protection).
	// The Settings API calls this callback only when options.php processes a POST,
	// so the check is always applicable here.
	check_admin_referer( RWA_LITE_NONCE_ACTION, RWA_LITE_NONCE_FIELD );

	// Fallback: keep the current saved value if validation fails.
	$current   = get_option( 'rwa_lite_agents', array() );
	$sanitized = array();

	list( $ok, $sanitized ) = rwa_lite_validate_agents_input( $input, $sanitized );

	if ( ! $ok ) {
		rwa_lite_add_validation_error_once();
		return $current; // Return unchanged data — nothing harmful is saved.
	}

	return $sanitized;
}


// ══════════════════════════════════════════════════════════════════════════════
// 5. FRONTEND OUTPUT
// ══════════════════════════════════════════════════════════════════════════════

/**
 * Renders the floating messaging button and the agent-selection script
 * in the page footer.
 *
 * Hooked to 'wp_footer'. Only runs when at least one agent is configured.
 * The agent data is passed to JavaScript via wp_json_encode() with HEX flags
 * so that it cannot be used for XSS even if a field contains HTML characters.
 */
add_action( 'wp_footer', 'rwa_lite_render_frontend' );

function rwa_lite_render_frontend() {

	$agents     = get_option( 'rwa_lite_agents', array() );
	$msg_global = get_option( 'rwa_lite_global_msg', '' );

	// Nothing to show if no agents are configured.
	if ( empty( $agents ) || ! is_array( $agents ) ) {
		return;
	}

	?>
	<style>
		/* ── Floating contact button ── */
		#rwa-lite-container {
			position:        fixed;
			bottom:          20px;
			right:           20px;
			z-index:         99999;
			display:         none; /* Shown by JS only when an active agent is found. */
			text-decoration: none;
		}

		.rwa-lite-icon {
			width:            60px;
			height:           60px;
			background-color: #25D366;
			border-radius:    50%;
			display:          flex;
			align-items:      center;
			justify-content:  center;
			box-shadow:       0 4px 10px rgba(0,0,0,.3);
			transition:       transform 0.2s ease-in-out;
		}

		.rwa-lite-icon:hover {
			transform: scale(1.05);
		}

		@media (max-width: 480px) {
			.rwa-lite-icon {
				width:  50px;
				height: 50px;
			}
		}
	</style>

	<a id="rwa-lite-container" href="#" target="_blank" rel="noopener noreferrer">
		<div class="rwa-lite-icon">
			<!-- Messaging app SVG icon (standard brand path) -->
			<svg style="width:35px;height:35px;fill:white" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
				<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
			</svg>
		</div>
	</a>

	<script>
	/* ── Frontend: select and display the active agent ── */
	document.addEventListener( 'DOMContentLoaded', function () {
		'use strict';

		/**
		 * Agent data serialised with wp_json_encode( …, JSON_HEX_TAG | JSON_HEX_AMP ).
		 *
		 * JSON_HEX_TAG  → encodes < and > as \u003C / \u003E
		 * JSON_HEX_AMP  → encodes & as \u0026
		 *
		 * This prevents the array values from being interpreted as HTML even if
		 * a saved field contained characters like <, >, &, or quotes.
		 */
		var agents = <?php echo wp_json_encode( $agents, JSON_HEX_TAG | JSON_HEX_AMP ); ?>; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- value is produced by wp_json_encode with HEX flags.

		/**
		 * Global pre-filled message, escaped via esc_js() so it is safe to
		 * embed inside a JavaScript string literal.
		 * encodeURIComponent() is applied later before appending to the URL.
		 */
		var msgGlobal = "<?php echo esc_js( $msg_global ); ?>";

		var now        = new Date();
		var currentMin = ( now.getHours() * 60 ) + now.getMinutes();
		var currentDay = now.getDay();

		/**
		 * Filter agents that are active right now:
		 *  – The current weekday must be in the agent's days array.
		 *  – The current time must fall within [start, end).
		 *  – Handles overnight schedules (start > end).
		 */
		var active = agents.filter( function ( agent ) {
			if ( ! Array.isArray( agent.days ) ) return false;

			var days = agent.days.map( String );
			if ( ! days.includes( currentDay.toString() ) ) return false;

			if (
				! agent.start || ! agent.end ||
				typeof agent.start !== 'string' || typeof agent.end !== 'string'
			) return false;

			var partsIn  = agent.start.split( ':' );
			var partsOut = agent.end.split( ':' );
			if ( partsIn.length !== 2 || partsOut.length !== 2 ) return false;

			var minIn  = ( Number( partsIn[0] )  * 60 ) + Number( partsIn[1] );
			var minOut = ( Number( partsOut[0] ) * 60 ) + Number( partsOut[1] );

			if ( ! Number.isFinite( minIn ) || ! Number.isFinite( minOut ) ) return false;

			// Normal schedule  (e.g. 09:00 – 18:00).
			// Overnight schedule (e.g. 22:00 – 06:00).
			return minIn <= minOut
				? ( currentMin >= minIn && currentMin < minOut )
				: ( currentMin >= minIn || currentMin  < minOut );
		} );

		var btn = document.getElementById( 'rwa-lite-container' );

		if ( active.length > 0 ) {
			// Pick a random active agent to distribute the load.
			var selected = active[ Math.floor( Math.random() * active.length ) ];

			/**
			 * agent.phone comes from wp_json_encode — it is a digits-only string
			 * validated server-side, so it is safe to interpolate in the URL.
			 * encodeURIComponent() ensures msgGlobal is URL-safe.
			 */
			var finalMsg = encodeURIComponent( msgGlobal );
			btn.style.display = 'flex';
			btn.setAttribute( 'href', 'https://wa.me/' + selected.phone + '?text=' + finalMsg );
		}
	} );
	</script>
	<?php
}