=== rdev-calendar ===
Contributors: rdev
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.1
Stable tag: 2.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional booking calendar for WordPress with availability management, temporary holds, booking requests, admin approval workflow, and email notifications.

== Description ==

rdev-calendar is a standalone WordPress booking calendar designed for service businesses (photo/video events, sessions, appointments, etc.).

Main capabilities:

* Visual availability calendar with statuses (`available`, `tentative`, `booked`)
* Frontend booking form for available days only
* Temporary hold logic (configurable hold duration in minutes)
* Automatic hold expiration cleanup (WP-Cron + traffic fallback)
* Booking requests stored in wp-admin as a custom post type
* Approve / Reject actions for each booking request
* Optional client emails:
  * after initial booking request
  * when hold expires
* Configurable frontend style presets:
  * theme presets
  * background styles
  * font presets
* No ACF Pro required

== Installation ==

1. Upload the `rdev-calendar` plugin folder to `/wp-content/plugins/`.
2. Activate the plugin in **Plugins > Installed Plugins**.
3. Go to **Calendars** and create a new calendar.
4. Configure settings and availability.
5. Save the calendar and copy the shortcode shown in the admin notice.

== Usage ==

Shortcode:

`[rdev_calendar id="123"]`

Alias:

`[rdev_booking_calendar id="123"]`

Replace `123` with your calendar post ID.

== Booking Workflow ==

1. Visitor clicks an `available` day and submits the booking form.
2. A booking request is created in **Booking Requests**.
3. The selected day becomes `tentative` (hold active).
4. Admin can:
   * **Approve** -> day changes to `booked`
   * **Reject** -> day returns to `available`
5. If not approved in time, hold expires automatically and day returns to `available`.

== Security ==

The plugin includes:

* Nonce validation for form submission and admin actions
* Honeypot anti-spam field
* Input sanitization/validation for all submitted fields
* Capability checks for admin-only actions

== Frequently Asked Questions ==

= Does it require ACF Pro? =

No. The plugin is fully standalone.

= Will expired holds clear automatically? =

Yes. Cleanup runs via WP-Cron and also has a fallback trigger on regular traffic.

= Can I change hold duration? =

Yes. Set it per calendar in minutes in calendar settings.

= Can I customize labels and email texts? =

Yes. Most form labels, notices, and email templates are editable in calendar settings.

== Changelog ==

= 2.0.5 =

* Cleared hold notes after approve/reject so calendar displays only standard status text.
* Added editable client email templates and toggles for approved and rejected decisions.

= 2.0.4 =

* Fixed trait method collision during plugin activation.
* Updated shortcode names to match plugin branding.
* Removed old shortcode aliases.

= 2.0.2 =

* Added safer modular loader with missing-file detection and admin notice.
* Updated plugin author metadata.

= 2.0.1 =

* Improved PHP compatibility for broader hosting support.
* Reduced risk of activation fatals on older environments.

= 2.0.0 =

* Refactored codebase into modular files.
* Added safer load guards for class/trait conflicts.
* Added frontend style presets (theme/background/font).
* Added admin approval workflow (Approve / Reject).
* Improved asset versioning with file modification timestamps.
