<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Impedir ejecución directa del archivo

/*
Plugin Name: Agent Rotator for WhatsApp Premium
Plugin URI: https://nrd.com.ar
Description: Professional WhatsApp agent rotator featuring customizable float bubbles, active shift scheduling, and a global out-of-hours guard switch.
Version: 1.0.0
Author: NRD Design
Author URI: https://nrd.com.ar
License: GPLv2 or later
Text Domain: whatsapp-rotator-premium
*/

// 1. Create Menu
add_action('admin_menu', 'wa_premium_v2_menu');
function wa_premium_v2_menu() {
    add_menu_page('WA Agent Rotator Premium', 'WA Agent Rotator', 'manage_options', 'wa_premium_v2_settings', 'wa_premium_v2_page', 'dashicons-whatsapp', 100);
}

// 2. Settings Page
function wa_premium_v2_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <style>
        .wa-p2-field-group { position: relative; display: inline-block; margin-right: 8px; margin-bottom: 4px; vertical-align: top; }
        .wa-p2-field-group input { display: block; }
        .wa-p2-error-tooltip {
            display: flex; align-items: center; gap: 8px;
            position: absolute; left: 0; top: 100%; margin-top: 4px; z-index: 10;
            max-width: 280px; padding: 10px 12px; background: #fff; border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,.15); border: 1px solid #e0e0e0;
            font-size: 13px; color: #1a1a1a; white-space: nowrap;
        }
        .wa-p2-error-tooltip::before {
            content: ''; position: absolute; left: 12px; top: -6px; border-left: 6px solid transparent; border-right: 6px solid transparent; border-bottom: 6px solid #fff; filter: drop-shadow(0 -1px 1px rgba(0,0,0,.08));
        }
        .wa-p2-error-tooltip .wa-p2-error-icon { flex-shrink: 0; width: 20px; height: 20px; background: #f57c00; border-radius: 3px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold; font-size: 14px; line-height: 1; }
        .wa-p2-error-tooltip .wa-p2-error-text { flex: 1; white-space: normal; }
        .wa-p2-field-group.has-error input { border-color: #d63638; box-shadow: 0 0 0 1px #d63638; }
        .wa-p2-field-group-dias { display: flex; flex-wrap: wrap; flex-direction: row; align-items: center; gap: 6px 10px; }
        .wa-p2-field-group-dias label { display: inline-flex; align-items: center; margin: 0; white-space: nowrap; cursor: pointer; }
        .wa-p2-field-group-dias label input { margin: 0 4px 0 0; }
    </style>
    <div class="wrap">
        <h1>Agent Rotator for WhatsApp Premium</h1>
        <?php settings_errors('wa_p2_messages'); ?>
        <form method="post" action="options.php" id="wa-p2-form">
            <?php
            settings_fields('wa-p2-group');
            $vendedores = get_option('wa_p2_vendedores', []);
            $global_msg = get_option('wa_p2_global_msg', 'Hello, I need more information!');
            $bubble_text = get_option('wa_p2_bubble_text', 'Contact Us');
            $guardia_active = get_option('wa_p2_guardia_active', 'no');
            $guardia = get_option('wa_p2_guardia', ['nombre' => 'Guard Agent', 'tel' => '', 'msj' => 'Hello, we are currently out of office hours...']);
            
            $dias_semana = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $dias_orden = [1, 2, 3, 4, 5, 6, 0];
            ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Floating Bubble Text:</th>
                    <td><input type="text" name="wa_p2_bubble_text" value="<?php echo esc_attr($bubble_text); ?>" class="regular-text" placeholder="e.g.: Contact us via WhatsApp"></td>
                </tr>
                <tr>
                    <th scope="row">Global Pre-filled Message:</th>
                    <td><input type="text" name="wa_p2_global_msg" value="<?php echo esc_attr($global_msg); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">Activate Guard Agent?</th>
                    <td>
                        <fieldset>
                            <label style="margin-right:12px;">
                                <input type="radio" name="wa_p2_guardia_active" value="no" <?php checked($guardia_active, 'no'); ?>>
                                No (Hide button out of hours)
                            </label>
                            <label>
                                <input type="radio" name="wa_p2_guardia_active" value="si" <?php checked($guardia_active, 'si'); ?>>
                                Yes (Forward to guard)
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <h2>Sales Agents</h2>
            <table class="wp-list-table widefat fixed striped" id="vendedores-table">
                <thead>
                    <tr>
                        <th>Name / Phone</th>
                        <th>Schedule (From/To)</th>
                        <th>Active Days</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($vendedores && is_array($vendedores)) : foreach ($vendedores as $i => $v) : ?>
                    <tr class="wa-p2-vendedor-row">
                        <td>
                            <div class="wa-p2-field-group">
                                <input type="text" name="wa_p2_vendedores[<?php echo esc_attr($i); ?>][nombre]" value="<?php echo esc_attr($v['nombre'] ?? ''); ?>" placeholder="Name" required>
                            </div>
                            <div class="wa-p2-field-group">
                                <input type="text" name="wa_p2_vendedores[<?php echo esc_attr($i); ?>][tel]" value="<?php echo esc_attr($v['tel'] ?? ''); ?>" placeholder="Phone Number" inputmode="numeric" required>
                            </div>
                        </td>
                        <td>
                            <div class="wa-p2-field-group">
                                <input type="time" name="wa_p2_vendedores[<?php echo esc_attr($i); ?>][inicio]" value="<?php echo esc_attr($v['inicio'] ?? ''); ?>" required>
                            </div>
                            <div class="wa-p2-field-group">
                                <input type="time" name="wa_p2_vendedores[<?php echo esc_attr($i); ?>][fin]" value="<?php echo esc_attr($v['fin'] ?? ''); ?>" required>
                            </div>
                        </td>
                        <td>
                            <div class="wa-p2-field-group wa-p2-field-group-dias">
                                <?php foreach ($dias_orden as $idx): 
                                    $dia = $dias_semana[$idx];
                                    $dias_guardados = (isset($v['dias']) && is_array($v['dias'])) ? array_map('strval', $v['dias']) : [];
                                    $checked = in_array((string)$idx, $dias_guardados, true) ? 'checked' : '';
                                ?>
                                    <label><input type="checkbox" name="wa_p2_vendedores[<?php echo esc_attr($i); ?>][dias][]" value="<?php echo esc_attr($idx); ?>" <?php echo esc_attr($checked); ?>> <?php echo esc_html($dia); ?></label>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td><button type="button" class="button remove-row">Remove</button></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
            <p><button type="button" class="button button-primary" id="add-vendedor">Add Agent</button></p>

            <hr>
            <h2>Guard Agent Configuration</h2>
            <div id="wa-guardia-fields">
                <table class="form-table">
                    <tr>
                        <th scope="row">Name/Phone:</th>
                        <td>
                            <input type="text" name="wa_p2_guardia[nombre]" value="<?php echo esc_attr($guardia['nombre'] ?? ''); ?>" placeholder="Name">
                            <input type="text" name="wa_p2_guardia[tel]" value="<?php echo esc_attr($guardia['tel'] ?? ''); ?>" placeholder="Phone Number" inputmode="numeric">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Guard Message:</th>
                        <td><input type="text" name="wa_p2_guardia[msj]" value="<?php echo esc_attr($guardia['msj'] ?? ''); ?>" class="regular-text"></td>
                    </tr>
                </table>
            </div>

            <?php submit_button(); ?>
        </form>
    </div>

    <script>
    let _waP2RowIdx = <?php echo (int) count( (array)$vendedores ); ?>;
    function waP2ToggleGuardiaFields() {
        const selected = document.querySelector('input[name="wa_p2_guardia_active"]:checked');
        const wrap = document.getElementById('wa-guardia-fields');
        if (!wrap) return;
        const isActive = (selected && selected.value === 'si');
        wrap.style.display = isActive ? '' : 'none';
        const inputs = wrap.querySelectorAll('input');
        inputs.forEach(input => {
            input.disabled = !isActive;
        });
    }

    document.querySelectorAll('input[name="wa_p2_guardia_active"]').forEach(r => {
        r.addEventListener('change', waP2ToggleGuardiaFields);
    });
    waP2ToggleGuardiaFields();

    document.getElementById('add-vendedor').addEventListener('click', function() {
        const table = document.querySelector('#vendedores-table tbody');
        const rowCount = _waP2RowIdx++; 
        const row = table.insertRow();
        const diasLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const diasOrden = [1, 2, 3, 4, 5, 6, 0];
        let diasHtml = '';
        diasOrden.forEach(idx => {
            diasHtml += `<label><input type="checkbox" name="wa_p2_vendedores[${rowCount}][dias][]" value="${idx}"> ${diasLabels[idx]}</label>`;
        });
        
        row.className = 'wa-p2-vendedor-row';
        row.innerHTML = `
            <td>
                <div class="wa-p2-field-group">
                    <input type="text" name="wa_p2_vendedores[${rowCount}][nombre]" placeholder="Name" required>
                </div>
                <div class="wa-p2-field-group">
                    <input type="text" name="wa_p2_vendedores[${rowCount}][tel]" placeholder="Phone Number" inputmode="numeric" required>
                </div>
            </td>
            <td>
                <div class="wa-p2-field-group">
                    <input type="time" name="wa_p2_vendedores[${rowCount}][inicio]" required>
                </div>
                <div class="wa-p2-field-group">
                    <input type="time" name="wa_p2_vendedores[${rowCount}][fin]" required>
                </div>
            </td>
            <td>
                <div class="wa-p2-field-group wa-p2-field-group-dias">${diasHtml}</div>
            </td>
            <td><button type="button" class="button remove-row">Remove</button></td>
        `;
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) { e.target.closest('tr').remove(); }
    });

    function waP2ShowFieldError(fieldGroup, message) {
        if (!fieldGroup) return;
        fieldGroup.classList.add('has-error');
        const existing = fieldGroup.querySelector('.wa-p2-error-tooltip');
        if (existing) existing.remove();
        const tip = document.createElement('div');
        tip.className = 'wa-p2-error-tooltip';
        tip.innerHTML = '<span class="wa-p2-error-icon">!</span><span class="wa-p2-error-text">' + message + '</span>';
        fieldGroup.appendChild(tip);
    }

    function waP2ClearErrors() {
        document.querySelectorAll('.wa-p2-field-group').forEach(g => {
            g.classList.remove('has-error');
            g.querySelectorAll('.wa-p2-error-tooltip').forEach(t => t.remove());
        });
    }

    document.getElementById('wa-p2-form').onsubmit = function(e) {
        waP2ClearErrors();
        const nameRe = /^[\p{L}\p{N} ]+$/u;
        const timeRe = /^(?:[01]\d|2[0-3]):[0-5]\d$/;
        const rows = document.querySelectorAll('#vendedores-table tbody tr.wa-p2-vendedor-row');
        let hasError = false;
        let firstErrorEl = null;

        for (let row of rows) {
            const nombreEl = row.querySelector('input[name*="[nombre]"]');
            const telEl = row.querySelector('input[name*="[tel]"]');
            const inicioEl = row.querySelector('input[name*="[inicio]"]');
            const finEl = row.querySelector('input[name*="[fin]"]');
            const diasChecked = row.querySelectorAll('input[type="checkbox"][name*="[dias]"]:checked');

            const nombre = (nombreEl?.value || '').trim();
            const telRaw = (telEl?.value || '').trim();
            const telDigits = telRaw.replace(/\D+/g, '');
            const inicio = (inicioEl?.value || '').trim();
            const fin = (finEl?.value || '').trim();

            const nombreGroup = nombreEl?.closest('.wa-p2-field-group');
            const telGroup = telEl?.closest('.wa-p2-field-group');
            const inicioGroup = inicioEl?.closest('.wa-p2-field-group');
            const finGroup = finEl?.closest('.wa-p2-field-group');
            const diasGroup = row.querySelector('.wa-p2-field-group-dias');

            if (nombre.length === 0) {
                waP2ShowFieldError(nombreGroup, 'Field required');
                hasError = true; if (!firstErrorEl) firstErrorEl = nombreEl;
            } else if (!nameRe.test(nombre) || !/[\p{L}\p{N}]/u.test(nombre)) {
                waP2ShowFieldError(nombreGroup, 'Invalid characters');
                hasError = true; if (!firstErrorEl) firstErrorEl = nombreEl;
            }
            if (telDigits.length === 0) {
                waP2ShowFieldError(telGroup, 'Field required');
                hasError = true; if (!firstErrorEl) firstErrorEl = telEl;
            } else if (telDigits.length < 6 || telDigits.length > 15) {
                waP2ShowFieldError(telGroup, 'Between 6 and 15 digits');
                hasError = true; if (!firstErrorEl) firstErrorEl = telEl;
            }
            if (!timeRe.test(inicio)) {
                waP2ShowFieldError(inicioGroup, 'Field required');
                hasError = true; if (!firstErrorEl) firstErrorEl = inicioEl;
            }
            if (!timeRe.test(fin)) {
                waP2ShowFieldError(finGroup, 'Field required');
                hasError = true; if (!firstErrorEl) firstErrorEl = finEl;
            }
            if (diasChecked.length === 0) {
                waP2ShowFieldError(diasGroup, 'Select at least one day');
                hasError = true; if (!firstErrorEl) firstErrorEl = diasGroup;
            }
        }

        if (hasError) {
            e.preventDefault();
            if (firstErrorEl) firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
    };
    </script>
    <?php
}

add_action('admin_init', function() {
    register_setting('wa-p2-group', 'wa_p2_vendedores', ['sanitize_callback' => 'wa_p2_sanitize_vendedores']);
    register_setting('wa-p2-group', 'wa_p2_global_msg', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('wa-p2-group', 'wa_p2_bubble_text', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('wa-p2-group', 'wa_p2_guardia_active', ['sanitize_callback' => 'wa_p2_sanitize_guardia_active']);
    register_setting('wa-p2-group', 'wa_p2_guardia', ['sanitize_callback' => 'wa_p2_sanitize_guardia']);
});

function wa_p2_is_our_settings_post(): bool {
    return is_admin()
        && isset($_POST['option_page'])
        && $_POST['option_page'] === 'wa-p2-group';
}

function wa_p2_validate_vendedores_input($input, &$sanitized = null): array {
    $sanitized = [];

    if ($input === null || $input === '' || $input === false) {
        return [true, $sanitized];
    }
    if (!is_array($input)) {
        return [false, $sanitized];
    }

    foreach ($input as $row) {
        if (!is_array($row)) return [false, $sanitized];

        $nombre = isset($row['nombre']) ? trim((string)$row['nombre']) : '';
        $telRaw = isset($row['tel']) ? (string)$row['tel'] : '';
        $tel = preg_replace('/\D+/', '', $telRaw);
        $inicio = isset($row['inicio']) ? trim((string)$row['inicio']) : '';
        $fin = isset($row['fin']) ? trim((string)$row['fin']) : '';
        $dias = $row['dias'] ?? [];

        $nombreOk = ($nombre !== '')
            && preg_match('/^[\p{L}\p{N} ]+$/u', $nombre)
            && preg_match('/[\p{L}\p{N}]/u', $nombre);
        $telOk = (is_string($tel) || is_numeric($tel)) && (strlen((string)$tel) >= 6) && (strlen((string)$tel) <= 15);
        $timeOk = function($t) {
            return is_string($t) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $t);
        };
        $inicioOk = $timeOk($inicio);
        $finOk = $timeOk($fin);

        if (!is_array($dias) || count($dias) < 1) return [false, $sanitized];
        $diasSan = [];
        foreach ($dias as $d) {
            $d = (string)$d;
            if (!preg_match('/^[0-6]$/', $d)) return [false, $sanitized];
            $diasSan[] = $d;
        }
        $diasSan = array_values(array_unique($diasSan));
        if (count($diasSan) < 1) return [false, $sanitized];

        if (!$nombreOk || !$telOk || !$inicioOk || !$finOk) {
            return [false, $sanitized];
        }

        $sanitized[] = [
            'nombre' => sanitize_text_field($nombre),
            'tel' => (string)$tel,
            'inicio' => sanitize_text_field($inicio),
            'fin' => sanitize_text_field($fin),
            'dias' => $diasSan,
        ];
    }

    return [true, $sanitized];
}

function wa_p2_add_validation_error_once(): void {
    static $added = false;
    if ($added) return;
    $added = true;
    add_settings_error('wa_p2_messages', 'wa_p2_validation', 'Please fill all fields correctly.', 'error');
}

function wa_p2_get_vendedores_validation(): array {
    static $cache = null;
    if ( $cache === null ) {
        $raw      = isset( $_POST['wa_p2_vendedores'] ) ? wp_unslash( $_POST['wa_p2_vendedores'] ) : null;
        $sanitized = [];
        [ $ok, $sanitized ] = wa_p2_validate_vendedores_input( $raw, $sanitized );
        $cache = [ 'ok' => $ok, 'sanitized' => $sanitized ];
    }
    return $cache;
}

function wa_p2_sanitize_vendedores($input) {
    $current = get_option('wa_p2_vendedores', []);
    $sanitized = [];
    [$ok, $sanitized] = wa_p2_validate_vendedores_input($input, $sanitized);
    if (!$ok) {
        wa_p2_add_validation_error_once();
        return $current;
    }
    return $sanitized;
}

function wa_p2_sanitize_guardia_active($input) {
    if (wa_p2_is_our_settings_post()) {
        [ 'ok' => $ok ] = wa_p2_get_vendedores_validation();
        if (!$ok) {
            wa_p2_add_validation_error_once();
            return get_option('wa_p2_guardia_active', 'no');
        }
    }
    $v = is_string($input) ? $input : '';
    return ($v === 'si') ? 'si' : 'no';
}

function wa_p2_sanitize_guardia($input) {
    if (wa_p2_is_our_settings_post()) {
        [ 'ok' => $ok ] = wa_p2_get_vendedores_validation();
        if (!$ok) {
            wa_p2_add_validation_error_once();
            return get_option('wa_p2_guardia', []);
        }
    }
    $out = [
        'nombre' => '',
        'tel' => '',
        'msj' => '',
    ];
    if (!is_array($input)) return get_option('wa_p2_guardia', $out);
    $out['nombre']  = sanitize_text_field( $input['nombre'] ?? '' );
    $tel_guardia    = preg_replace( '/\D+/', '', (string) ( $input['tel'] ?? '' ) );
    $out['tel']     = ( strlen( $tel_guardia ) >= 6 && strlen( $tel_guardia ) <= 15 ) ? $tel_guardia : '';
    $out['msj']     = sanitize_text_field( $input['msj'] ?? '' );
    return $out;
}

// 3. Frontend
add_action('wp_footer', function() {
    $vendedores = get_option('wa_p2_vendedores', []);
    $msg_global = get_option('wa_p2_global_msg', '');
    $bubble_text = get_option('wa_p2_bubble_text', 'Contact Us');
    $g_active = get_option('wa_p2_guardia_active', 'no');
    $guardia = get_option('wa_p2_guardia', []);

    if ((empty($vendedores) || !is_array($vendedores)) && $g_active === 'no') return;
    ?>
    <style>
        #wa-premium-container { position: fixed; bottom: 20px; right: 20px; z-index: 99999; display: none; align-items: center; text-decoration: none; animation: wa-p2-pulse 2s infinite; font-family: sans-serif; }
        .wa-p2-bubble { background-color: #25D366; color: white; padding: 8px 15px; border-radius: 20px; font-size: 14px; font-weight: 600; margin-right: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.15); position: relative; white-space: nowrap; }
        .wa-p2-bubble::after { content: ''; position: absolute; right: -6px; top: 50%; transform: translateY(-50%); border-top: 6px solid transparent; border-bottom: 6px solid transparent; border-left: 8px solid #25D366; }
        .wa-p2-icon-circle { width: 60px; height: 60px; background-color: #25D366; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        @keyframes wa-p2-pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
        @media (max-width: 480px) { .wa-p2-bubble { font-size: 12px; } .wa-p2-icon-circle { width: 50px; height: 50px; } }
    </style>

    <a id="wa-premium-container" href="#" target="_blank" rel="noopener noreferrer">
        <?php if (!empty($bubble_text)): ?>
            <div class="wa-p2-bubble"><?php echo esc_html($bubble_text); ?></div>
        <?php endif; ?>
        <div class="wa-p2-icon-circle">
            <svg style="width:35px;height:35px;fill:white" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        </div>
    </a>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const vendedores = <?php echo wp_json_encode( is_array($vendedores) ? $vendedores : [], JSON_HEX_TAG | JSON_HEX_AMP ); ?>;
        const msgGlobal  = "<?php echo esc_js( $msg_global ); ?>";
        const gActive    = "<?php echo esc_js( $g_active ); ?>";
        const guardia    = <?php echo wp_json_encode( is_array($guardia) ? $guardia : [], JSON_HEX_TAG | JSON_HEX_AMP ); ?>;
        
        const now = new Date();
        const currentMin = (now.getHours() * 60) + now.getMinutes();
        const currentDay = now.getDay();

        let activos = vendedores.filter(v => {
            if (!Array.isArray(v.dias)) return false;
            const dias = v.dias.map(String);
            if (!dias.includes(currentDay.toString())) return false;
            if (!v.inicio || !v.fin || typeof v.inicio !== 'string' || typeof v.fin !== 'string') return false;
            const partsIn = v.inicio.split(':');
            const partsOut = v.fin.split(':');
            if (partsIn.length !== 2 || partsOut.length !== 2) return false;
            const Math_minIn = (Number(partsIn[0]) * 60) + Number(partsIn[1]);
            const Math_minOut = (Number(partsOut[0]) * 60) + Number(partsOut[1]);
            if (!Number.isFinite(Math_minIn) || !Number.isFinite(Math_minOut)) return false;
            return Math_minIn <= Math_minOut ? (currentMin >= Math_minIn && currentMin < Math_minOut) : (currentMin >= Math_minIn || currentMin < Math_minOut);
        });

        let seleccionado, finalMsg;
        const btn = document.getElementById('wa-premium-container');

        if (activos.length > 0) {
            seleccionado = activos[Math.floor(Math.random() * activos.length)];
            finalMsg = encodeURIComponent(msgGlobal);
            btn.style.display = 'flex';
        } else if (gActive === 'si' && guardia.tel) {
            seleccionado = guardia;
            finalMsg = encodeURIComponent(guardia.msj || msgGlobal);
            btn.style.display = 'flex';
        } else {
            return; 
        }

        btn.setAttribute('href', `https://wa.me/${seleccionado.tel}?text=${finalMsg}`);
    });
    </script>
    <?php
});
