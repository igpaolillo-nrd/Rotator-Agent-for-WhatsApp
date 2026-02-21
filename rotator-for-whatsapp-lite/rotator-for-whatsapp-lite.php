<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/*
 Plugin Name: Agent Rotator for WhatsApp
 Plugin URI: https://wordpress.org/plugins/agent-rotator-for-whatsapp/
 Description: Simple and clean WhatsApp contact rotator. Adds a floating WhatsApp icon to distribute incoming messages across multiple available agents based on their active schedule.
 Version: 1.0.0
 Author: NRD Design
 Author URI: https://nrd.com.ar
 License: GPLv2 or later
 Text Domain: rotator-for-whatsapp-lite
 */

define('RWA_LITE_MAX_AGENTS', 2);

// 1. Create Menu
add_action('admin_menu', 'rwa_lite_menu');
function rwa_lite_menu()
{
    add_menu_page(
        __('Contact Rotator', 'rotator-for-whatsapp-lite'),
        __('WA Rotator', 'rotator-for-whatsapp-lite'),
        'manage_options',
        'rwa_lite_settings',
        'rwa_lite_page',
        'dashicons-whatsapp',
        100
    );
}

// 2. Settings Page
function rwa_lite_page()
{
    if (!current_user_can('manage_options'))
        return;
?>
<style>
    .rwa-lite-field-group {
        position: relative;
        display: inline-block;
        margin-right: 8px;
        margin-bottom: 4px;
        vertical-align: top;
    }

    .rwa-lite-field-group input {
        display: block;
    }

    .rwa-lite-error-tooltip {
        display: flex;
        align-items: center;
        gap: 8px;
        position: absolute;
        left: 0;
        top: 100%;
        margin-top: 4px;
        z-index: 10;
        max-width: 280px;
        padding: 10px 12px;
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
        border: 1px solid #e0e0e0;
        font-size: 13px;
        color: #1a1a1a;
        white-space: nowrap;
    }

    .rwa-lite-error-tooltip::before {
        content: '';
        position: absolute;
        left: 12px;
        top: -6px;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-bottom: 6px solid #fff;
        filter: drop-shadow(0 -1px 1px rgba(0, 0, 0, .08));
    }

    .rwa-lite-error-tooltip .rwa-lite-error-icon {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        background: #f57c00;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: bold;
        font-size: 14px;
        line-height: 1;
    }

    .rwa-lite-error-tooltip .rwa-lite-error-text {
        flex: 1;
        white-space: normal;
    }

    .rwa-lite-field-group.has-error input {
        border-color: #d63638;
        box-shadow: 0 0 0 1px #d63638;
    }

    .rwa-lite-field-group-days {
        display: flex;
        flex-wrap: wrap;
        flex-direction: row;
        align-items: center;
        gap: 6px 10px;
    }

    .rwa-lite-field-group-days label {
        display: inline-flex;
        align-items: center;
        margin: 0;
        white-space: nowrap;
        cursor: pointer;
    }

    .rwa-lite-field-group-days label input {
        margin: 0 4px 0 0;
    }

    .rwa-lite-upgrade-notice {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: 10px;
        padding: 6px 12px;
        background: #fff8e1;
        border: 1px solid #ffe082;
        border-radius: 4px;
        font-size: 13px;
        color: #795548;
        vertical-align: middle;
    }

    .rwa-lite-upgrade-notice a {
        color: #e65100;
        font-weight: 600;
        text-decoration: none;
    }

    .rwa-lite-upgrade-notice a:hover {
        text-decoration: underline;
    }
</style>
<div class="wrap">
    <h1>
        <?php esc_html_e('Agent Rotator for WhatsApp', 'rotator-for-whatsapp-lite'); ?>
    </h1>
    <?php settings_errors('rwa_lite_messages'); ?>
    <form method="post" action="options.php" id="rwa-lite-form">
        <?php
    settings_fields('rwa-lite-group');
    $agents = get_option('rwa_lite_agents', []);
    $global_msg = get_option('rwa_lite_global_msg', __('Hello, I need more information.', 'rotator-for-whatsapp-lite'));

    $week_days = [
        __('Sun', 'rotator-for-whatsapp-lite'),
        __('Mon', 'rotator-for-whatsapp-lite'),
        __('Tue', 'rotator-for-whatsapp-lite'),
        __('Wed', 'rotator-for-whatsapp-lite'),
        __('Thu', 'rotator-for-whatsapp-lite'),
        __('Fri', 'rotator-for-whatsapp-lite'),
        __('Sat', 'rotator-for-whatsapp-lite')
    ];
    $days_order = [1, 2, 3, 4, 5, 6, 0];
?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <?php esc_html_e('Global Pre-filled Message:', 'rotator-for-whatsapp-lite'); ?>
                </th>
                <td><input type="text" name="rwa_lite_global_msg" value="<?php echo esc_attr($global_msg); ?>"
                        class="regular-text"></td>
            </tr>
        </table>

        <h2>
            <?php esc_html_e('Sales Agents', 'rotator-for-whatsapp-lite'); ?>
        </h2>
        <table class="wp-list-table widefat fixed striped" id="agents-table">
            <thead>
                <tr>
                    <th>
                        <?php esc_html_e('Name / Phone', 'rotator-for-whatsapp-lite'); ?>
                    </th>
                    <th>
                        <?php esc_html_e('Schedule (From/To)', 'rotator-for-whatsapp-lite'); ?>
                    </th>
                    <th>
                        <?php esc_html_e('Active Days', 'rotator-for-whatsapp-lite'); ?>
                    </th>
                    <th>
                        <?php esc_html_e('Action', 'rotator-for-whatsapp-lite'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if ($agents && is_array($agents)):
        foreach ($agents as $i => $agent): ?>
                <tr class="rwa-lite-agent-row">
                    <td>
                        <div class="rwa-lite-field-group">
                            <input type="text" name="rwa_lite_agents[<?php echo esc_attr($i); ?>][name]"
                                value="<?php echo esc_attr($agent['name'] ?? ''); ?>"
                                placeholder="<?php esc_attr_e('Name', 'rotator-for-whatsapp-lite'); ?>" required>
                        </div>
                        <div class="rwa-lite-field-group">
                            <input type="text" name="rwa_lite_agents[<?php echo esc_attr($i); ?>][phone]"
                                value="<?php echo esc_attr($agent['phone'] ?? ''); ?>"
                                placeholder="<?php esc_attr_e('Phone Number (e.g. 1234567890)', 'rotator-for-whatsapp-lite'); ?>"
                                inputmode="numeric" required>
                        </div>
                    </td>
                    <td>
                        <div class="rwa-lite-field-group">
                            <input type="time" name="rwa_lite_agents[<?php echo esc_attr($i); ?>][start]"
                                value="<?php echo esc_attr($agent['start'] ?? ''); ?>" required>
                        </div>
                        <div class="rwa-lite-field-group">
                            <input type="time" name="rwa_lite_agents[<?php echo esc_attr($i); ?>][end]"
                                value="<?php echo esc_attr($agent['end'] ?? ''); ?>" required>
                        </div>
                    </td>
                    <td>
                        <div class="rwa-lite-field-group rwa-lite-field-group-days">
                            <?php foreach ($days_order as $idx):
                $day = $week_days[$idx];
                $saved_days = (isset($agent['days']) && is_array($agent['days'])) ? array_map('strval', $agent['days']) : [];
                $checked = in_array((string)$idx, $saved_days, true) ? 'checked' : '';
?>
                            <label><input type="checkbox" name="rwa_lite_agents[<?php echo esc_attr($i); ?>][days][]"
                                    value="<?php echo esc_attr($idx); ?>" <?php echo esc_attr($checked); ?>>
                                <?php echo esc_html($day); ?>
                            </label>
                            <?php
            endforeach; ?>
                        </div>
                    </td>
                    <td><button type="button" class="button remove-row">
                            <?php esc_html_e('Remove', 'rotator-for-whatsapp-lite'); ?>
                        </button></td>
                </tr>
                <?php
        endforeach;
    endif; ?>
            </tbody>
        </table>
        <p>
            <button type="button" class="button button-primary" id="add-agent">
                <?php esc_html_e('Add Agent', 'rotator-for-whatsapp-lite'); ?>
            </button>
            <span class="rwa-lite-upgrade-notice" id="rwa-lite-limit-notice" style="display:none;">
                ⭐
                <?php esc_html_e('Lite version supports up to 2 agents.', 'rotator-for-whatsapp-lite'); ?>
                <a href="https://nrd.com.ar" target="_blank">
                    <?php esc_html_e('Upgrade to Premium', 'rotator-for-whatsapp-lite'); ?> →
                </a>
            </span>
        </p>

        <?php submit_button(); ?>
    </form>
</div>

<script>
    const RWA_LITE_MAX_AGENTS = <?php echo (int)RWA_LITE_MAX_AGENTS; ?>;
    let _rwaLiteRowIdx = <?php echo (int)count((array)$agents); ?>;

    function rwaLiteUpdateAddButton() {
        const rows = document.querySelectorAll('#agents-table tbody tr.rwa-lite-agent-row');
        const btn = document.getElementById('add-agent');
        const notice = document.getElementById('rwa-lite-limit-notice');
        if (rows.length >= RWA_LITE_MAX_AGENTS) {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
            notice.style.display = 'inline-flex';
        } else {
            btn.disabled = false;
            btn.style.opacity = '';
            btn.style.cursor = '';
            notice.style.display = 'none';
        }
    }

    // Run on page load
    rwaLiteUpdateAddButton();

    document.getElementById('add-agent').addEventListener('click', function () {
        const rows = document.querySelectorAll('#agents-table tbody tr.rwa-lite-agent-row');
        if (rows.length >= RWA_LITE_MAX_AGENTS) return;

        const table = document.querySelector('#agents-table tbody');
        const rowCount = _rwaLiteRowIdx++;
        const row = table.insertRow();
        const dayLabels = [
            '<?php esc_html_e('Sun', 'rotator -for-whatsapp - lite'); ?>',
                '<?php esc_html_e('Mon', 'rotator -for-whatsapp - lite'); ?>',
                    '<?php esc_html_e('Tue', 'rotator -for-whatsapp - lite'); ?>',
                        '<?php esc_html_e('Wed', 'rotator -for-whatsapp - lite'); ?>',
                            '<?php esc_html_e('Thu', 'rotator -for-whatsapp - lite'); ?>',
                                '<?php esc_html_e('Fri', 'rotator -for-whatsapp - lite'); ?>',
                                    '<?php esc_html_e('Sat', 'rotator -for-whatsapp - lite'); ?>'
        ];
        const daysOrder = [1, 2, 3, 4, 5, 6, 0];
        let daysHtml = '';
        daysOrder.forEach(idx => {
            daysHtml += `<label><input type="checkbox" name="rwa_lite_agents[${rowCount}][days][]" value="${idx}"> ${dayLabels[idx]}</label>`;
        });

        row.className = 'rwa-lite-agent-row';
        row.innerHTML = `
            <td>
                <div class="rwa-lite-field-group">
                    <input type="text" name="rwa_lite_agents[${rowCount}][name]" placeholder="<?php esc_attr_e('Name', 'rotator-for-whatsapp-lite'); ?>" required>
                </div>
                <div class="rwa-lite-field-group">
                    <input type="text" name="rwa_lite_agents[${rowCount}][phone]" placeholder="<?php esc_attr_e('Phone Number', 'rotator-for-whatsapp-lite'); ?>" inputmode="numeric" required>
                </div>
            </td>
            <td>
                <div class="rwa-lite-field-group">
                    <input type="time" name="rwa_lite_agents[${rowCount}][start]" required>
                </div>
                <div class="rwa-lite-field-group">
                    <input type="time" name="rwa_lite_agents[${rowCount}][end]" required>
                </div>
            </td>
            <td>
                <div class="rwa-lite-field-group rwa-lite-field-group-days">${daysHtml}</div>
            </td>
            <td><button type="button" class="button remove-row"><?php esc_html_e('Remove', 'rotator-for-whatsapp-lite'); ?></button></td>
        `;

        rwaLiteUpdateAddButton();
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
            rwaLiteUpdateAddButton();
        }
    });

    function rwaLiteShowFieldError(fieldGroup, message) {
        if (!fieldGroup) return;
        fieldGroup.classList.add('has-error');
        const existing = fieldGroup.querySelector('.rwa-lite-error-tooltip');
        if (existing) existing.remove();
        const tip = document.createElement('div');
        tip.className = 'rwa-lite-error-tooltip';
        tip.innerHTML = '<span class="rwa-lite-error-icon">!</span><span class="rwa-lite-error-text">' + message + '</span>';
        fieldGroup.appendChild(tip);
    }

    function rwaLiteClearErrors() {
        document.querySelectorAll('.rwa-lite-field-group').forEach(g => {
            g.classList.remove('has-error');
            g.querySelectorAll('.rwa-lite-error-tooltip').forEach(t => t.remove());
        });
    }

    document.getElementById('rwa-lite-form').onsubmit = function (e) {
        rwaLiteClearErrors();
        const nameRe = /^[\p{L}\p{N} ]+$/u;
        const timeRe = /^(?:[01]\d|2[0-3]):[0-5]\d$/;
        const rows = document.querySelectorAll('#agents-table tbody tr.rwa-lite-agent-row');
        let hasError = false;
        let firstErrorEl = null;

        for (let row of rows) {
            const nameEl = row.querySelector('input[name*="[name]"]');
            const phoneEl = row.querySelector('input[name*="[phone]"]');
            const startEl = row.querySelector('input[name*="[start]"]');
            const endEl = row.querySelector('input[name*="[end]"]');
            const daysChecked = row.querySelectorAll('input[type="checkbox"][name*="[days]"]:checked');

            const name = (nameEl?.value || '').trim();
            const phoneRaw = (phoneEl?.value || '').trim();
            const phoneDigits = phoneRaw.replace(/\D+/g, '');
            const start = (startEl?.value || '').trim();
            const end = (endEl?.value || '').trim();

            const nameGroup = nameEl?.closest('.rwa-lite-field-group');
            const phoneGroup = phoneEl?.closest('.rwa-lite-field-group');
            const startGroup = startEl?.closest('.rwa-lite-field-group');
            const endGroup = endEl?.closest('.rwa-lite-field-group');
            const daysGroup = row.querySelector('.rwa-lite-field-group-days');

            if (name.length === 0) {
                rwaLiteShowFieldError(nameGroup, '<?php esc_html_e('Field required', 'rotator -for-whatsapp - lite'); ?>');
                hasError = true; if (!firstErrorEl) firstErrorEl = nameEl;
            } else if (!nameRe.test(name) || !/[\p{L}\p{N}]/u.test(name)) {
                rwaLiteShowFieldError(nameGroup, '<?php esc_html_e('Invalid characters', 'rotator -for-whatsapp - lite'); ?>');
                hasError = true; if (!firstErrorEl) firstErrorEl = nameEl;
            }
            if (phoneDigits.length === 0) {
                rwaLiteShowFieldError(phoneGroup, '<?php esc_html_e('Field required', 'rotator -for-whatsapp - lite'); ?>');
                hasError = true; if (!firstErrorEl) firstErrorEl = phoneEl;
            } else if (phoneDigits.length < 6 || phoneDigits.length > 15) {
                rwaLiteShowFieldError(phoneGroup, '<?php esc_html_e('Between 6 and 15 digits', 'rotator -for-whatsapp - lite'); ?>');
                hasError = true; if (!firstErrorEl) firstErrorEl = phoneEl;
            }
            if (!timeRe.test(start)) {
                rwaLiteShowFieldError(startGroup, '<?php esc_html_e('Field required', 'rotator -for-whatsapp - lite'); ?>');
                hasError = true; if (!firstErrorEl) firstErrorEl = startEl;
            }
            if (!timeRe.test(end)) {
                rwaLiteShowFieldError(endGroup, '<?php esc_html_e('Field required', 'rotator -for-whatsapp - lite'); ?>');
                hasError = true; if (!firstErrorEl) firstErrorEl = endEl;
            }
            if (daysChecked.length === 0) {
                rwaLiteShowFieldError(daysGroup, '<?php esc_html_e('Select at least one day', 'rotator -for-whatsapp - lite'); ?>');
                hasError = true; if (!firstErrorEl) firstErrorEl = daysGroup;
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

add_action('admin_init', function () {
    register_setting('rwa-lite-group', 'rwa_lite_agents', ['sanitize_callback' => 'rwa_lite_sanitize_agents']);
    register_setting('rwa-lite-group', 'rwa_lite_global_msg', ['sanitize_callback' => 'sanitize_text_field']);
});

function rwa_lite_is_our_settings_post()
{
    return is_admin()
        && isset($_POST['option_page'])
        && $_POST['option_page'] === 'rwa-lite-group';
}

function rwa_lite_validate_agents_input($input, &$sanitized = null)
{
    $sanitized = [];

    if ($input === null || $input === '' || $input === false) {
        return [true, $sanitized];
    }
    if (!is_array($input)) {
        return [false, $sanitized];
    }

    // Limit to max agents allowed in Lite version
    $input = array_slice($input, 0, RWA_LITE_MAX_AGENTS);

    foreach ($input as $row) {
        if (!is_array($row))
            return [false, $sanitized];

        $name = isset($row['name']) ? trim((string)$row['name']) : '';
        $phone_raw = isset($row['phone']) ? (string)$row['phone'] : '';
        $phone = preg_replace('/\D+/', '', $phone_raw);
        $start = isset($row['start']) ? trim((string)$row['start']) : '';
        $end = isset($row['end']) ? trim((string)$row['end']) : '';
        $days = $row['days'] ?? [];

        $name_ok = ($name !== '')
            && preg_match('/^[\p{L}\p{N} ]+$/u', $name)
            && preg_match('/[\p{L}\p{N}]/u', $name);
        $phone_ok = (is_string($phone) || is_numeric($phone)) && (strlen((string)$phone) >= 6) && (strlen((string)$phone) <= 15);
        $time_ok = function ($t) {
            return is_string($t) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $t);
        };
        $start_ok = $time_ok($start);
        $end_ok = $time_ok($end);

        if (!is_array($days) || count($days) < 1)
            return [false, $sanitized];
        $days_sanitized = [];
        foreach ($days as $d) {
            $d = (string)$d;
            if (!preg_match('/^[0-6]$/', $d))
                return [false, $sanitized];
            $days_sanitized[] = $d;
        }
        $days_sanitized = array_values(array_unique($days_sanitized));
        if (count($days_sanitized) < 1)
            return [false, $sanitized];

        if (!$name_ok || !$phone_ok || !$start_ok || !$end_ok) {
            return [false, $sanitized];
        }

        $sanitized[] = [
            'name' => sanitize_text_field($name),
            'phone' => (string)$phone,
            'start' => sanitize_text_field($start),
            'end' => sanitize_text_field($end),
            'days' => $days_sanitized,
        ];
    }

    return [true, $sanitized];
}

function rwa_lite_add_validation_error_once()
{
    static $added = false;
    if ($added)
        return;
    $added = true;
    add_settings_error('rwa_lite_messages', 'rwa_lite_validation', __('Please fill all fields correctly.', 'rotator-for-whatsapp-lite'), 'error');
}

function rwa_lite_get_agents_validation()
{
    static $cache = null;
    if ($cache === null) {
        $raw = isset($_POST['rwa_lite_agents']) ? wp_unslash($_POST['rwa_lite_agents']) : null;
        $sanitized = [];
        [$ok, $sanitized] = rwa_lite_validate_agents_input($raw, $sanitized);
        $cache = ['ok' => $ok, 'sanitized' => $sanitized];
    }
    return $cache;
}

function rwa_lite_sanitize_agents($input)
{
    $current = get_option('rwa_lite_agents', []);
    $sanitized = [];
    [$ok, $sanitized] = rwa_lite_validate_agents_input($input, $sanitized);
    if (!$ok) {
        rwa_lite_add_validation_error_once();
        return $current;
    }
    return $sanitized;
}

// 3. Frontend
add_action('wp_footer', function () {
    $agents = get_option('rwa_lite_agents', []);
    $msg_global = get_option('rwa_lite_global_msg', '');

    if (empty($agents) || !is_array($agents))
        return;
?>
<style>
    #rwa-lite-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 99999;
        display: none;
        text-decoration: none;
    }

    .rwa-lite-icon {
        width: 60px;
        height: 60px;
        background-color: #25D366;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        transition: transform 0.2s ease-in-out;
    }

    .rwa-lite-icon:hover {
        transform: scale(1.05);
    }

    @media (max-width: 480px) {
        .rwa-lite-icon {
            width: 50px;
            height: 50px;
        }
    }
</style>

<a id="rwa-lite-container" href="#" target="_blank" rel="noopener noreferrer">
    <div class="rwa-lite-icon">
        <svg style="width:35px;height:35px;fill:white" viewBox="0 0 24 24">
            <path
                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
        </svg>
    </div>
</a>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const agents = <?php echo wp_json_encode($agents, JSON_HEX_TAG | JSON_HEX_AMP); ?>;
        const msgGlobal = "<?php echo esc_js($msg_global); ?>";

        const now = new Date();
        const currentMin = (now.getHours() * 60) + now.getMinutes();
        const currentDay = now.getDay();

        let active = agents.filter(agent => {
            if (!Array.isArray(agent.days)) return false;
            const days = agent.days.map(String);
            if (!days.includes(currentDay.toString())) return false;
            if (!agent.start || !agent.end || typeof agent.start !== 'string' || typeof agent.end !== 'string') return false;
            const partsIn = agent.start.split(':');
            const partsOut = agent.end.split(':');
            if (partsIn.length !== 2 || partsOut.length !== 2) return false;
            const minIn = (Number(partsIn[0]) * 60) + Number(partsIn[1]);
            const minOut = (Number(partsOut[0]) * 60) + Number(partsOut[1]);
            if (!Number.isFinite(minIn) || !Number.isFinite(minOut)) return false;
            return minIn <= minOut ? (currentMin >= minIn && currentMin < minOut) : (currentMin >= minIn || currentMin < minOut);
        });

        const btn = document.getElementById('rwa-lite-container');

        if (active.length > 0) {
            const selected = active[Math.floor(Math.random() * active.length)];
            const finalMsg = encodeURIComponent(msgGlobal);
            btn.style.display = 'flex';
            btn.setAttribute('href', `https://wa.me/${selected.phone}?text=${finalMsg}`);
        }
    });
</script>
<?php
});