=== Agent Rotator for WA ===
Contributors: nrddesign
Tags: chat, contact, agents, rotator, schedule
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Simple and clean contact rotator. Adds a floating icon to distribute incoming messages across agents based on their active schedule.

== Description ==

**Agent Rotator for WA** is a lightweight plugin that adds a floating contact button to your WordPress site and automatically routes visitors to one of your available agents based on:

* **Working days** — choose which days each agent is active (Mon–Sun).
* **Working hours** — set a start and end time per agent. Overnight schedules (e.g. 22:00–06:00) are supported.
* **Round-robin rotation** — when more than one agent is active at the same time, a random one is selected to evenly distribute the load.

If no agent is active at the moment a visitor clicks the button, the button stays hidden — so visitors never reach an offline agent.

= Lite version limits =

* Up to **2 agents** in the free version.
* Upgrade to **Premium** for unlimited agents and extra features.

= Features =

* Zero dependencies — no jQuery, no external libraries.
* Pre-filled message configurable from the admin panel.
* Fully translatable (i18n ready, Text Domain: `agent-rotator-for-wa`).
* Nonce-protected settings form (CSRF-safe).
* Strict data sanitisation and validation on every save.

== Installation ==

1. Upload the `agent-rotator-for-wa` folder to the `/wp-content/plugins/` directory, **or** install the plugin through the WordPress Plugins screen directly.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Go to **WA Rotator** in the admin sidebar.
4. Add your agents: name, phone number (digits only, no spaces or dashes), working hours, and active days.
5. Optionally set a global pre-filled message.
6. Click **Save Changes** — the floating button will appear on your site automatically when at least one agent is active.

== Frequently Asked Questions ==

= How many agents can I add with the free version? =

The Lite version supports up to 2 agents. Upgrade to Premium to add unlimited agents.

= Does the button show when no agents are active? =

No. The button is hidden when no configured agent is currently within their working hours and days.

= Can I set overnight schedules? =

Yes. If the start time is later than the end time (e.g. 22:00–06:00), the plugin treats it as an overnight shift.

= Is the plugin compatible with page builders? =

Yes. The button is injected via `wp_footer` so it works with Elementor, Divi, Beaver Builder, and any standard WordPress theme.

= Where is the phone number format validated? =

Phone numbers are stripped down to digits only (6–15 digits). Country codes should be included without the leading `+` sign (e.g. `5491158887777`).

= Does this plugin slow down my website? =

No. The plugin is completely lightweight. It loads no heavy frameworks and uses vanilla JavaScript.

== Screenshots ==

1. The clean and organized admin panel to manage agents.
2. The floating contact button on the frontend (bottom-right corner).

== Changelog ==

= 1.0.0 =
* Initial release.
* Admin panel with agent management (name, phone, hours, days).
* Floating contact button with agent rotation logic.
* Lite plan enforces a 2-agent maximum.
* Full i18n support with text domain `agent-rotator-for-wa`.
* Nonce verification and strict data sanitisation/validation.

== Upgrade Notice ==

= 1.0.0 =
Initial release — no upgrade actions required.
