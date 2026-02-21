=== Agent Rotator for WhatsApp ===
Contributors: nrddesign
Tags: whatsapp, chat, rotator, agent, contact, support
Requires at least: 5.0
Tested up to: 6.4.3
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Simple and clean WhatsApp agent rotator. Adds a floating WhatsApp icon to distribute incoming messages across multiple available agents based on their active schedule.

== Description ==

Agent Rotator for WhatsApp is a lightweight and clean plugin designed to help you distribute your customer support or sales inquiries through WhatsApp.

Instead of sending all WhatsApp messages to a single phone number, you can add multiple agents with their working hours and working days. The plugin will automatically show a floating WhatsApp button to your visitors. When they click on it, the plugin will randomly redirect them to one of the agents that is currently available based on the time and day schedules you defined.

This is the free (Lite) edition and supports up to **2 agents**. For unlimited agents, scheduled guard features and a customizable floating bubble, check out **Agent Rotator for WhatsApp Premium**.

### Features
* Beautiful and lightweight floating WhatsApp button
* Assign working schedules for agents (open/close times per agent)
* Assign specific working days for each agent
* Auto-forwards only to agents that are actively on their shift
* Configurable pre-filled default message
* Supports up to 2 agents (upgrade to Premium for unlimited)
* 100% GDPR compliant (no invasive tracking, no external API calls)
* Accessible and responsive design
* Clean interface within WordPress dashboard

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Access the settings by navigating to 'WA Rotator' in your WordPress dashboard.
4. Add your sales or support agents (up to 2), selecting their working days and hours.
5. Provide a global pre-filled message (optional).
6. Save changes and test the button on the frontend!

== Frequently Asked Questions ==

= Does this plugin slow down my website? =

No. The plugin is designed to be completely lightweight. It doesn't load heavy frameworks and uses vanilla JavaScript. It simply drops a small floating icon in the bottom right corner of the screen.

= How does the rotation work? =

If more than one agent is available at the exact moment a user clicks the button (their schedules are currently open), the plugin selects one randomly. This guarantees an even distribution of incoming messages between available agents.

= What happens if no agents are available? =

If no agent is currently in their scheduled work time, the WhatsApp floating icon simply won't appear on the frontend.

= Can I add more than 2 agents? =

The free Lite version is limited to a maximum of 2 agents. To add unlimited agents, enable the Guard Agent (out-of-hours fallback) feature, and get a customizable floating bubble text, please upgrade to **Agent Rotator for WhatsApp Premium** available at [https://nrd.com.ar](https://nrd.com.ar).

== Screenshots ==

1. The clean and organized admin panel to manage agents.

== Changelog ==

= 1.0.0 =
* Initial release.
