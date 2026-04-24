=== rdev-calendar ===
Contributors: rdev
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional booking calendar for WordPress with availability management, temporary holds, booking requests, admin approval workflow, and email notifications.

== Description ==

rdev-calendar is a standalone WordPress booking calendar designed for service businesses (photo/video events, sessions, appointments, etc.).

PHP compatibility: 7.1 or newer.

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

## Booking status state machine

Request statuses:

* `hold`
* `approved`
* `rejected`
* `released`
* `expired`

Allowed transitions:

* `hold -> approved`
* `hold -> rejected`
* `hold -> expired`
* `approved -> released`
* `rejected -> approved`
* `expired -> approved`
* `released -> approved`

Reference:

* See `STATE_MACHINE.md` in plugin root for full details.

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

= 1.0.0 =

Initial public release.

Highlights:

* Booking workflow with temporary hold, approve/reject, release, and expiration cleanup.
* Hybrid date/time availability model:
  * default hours
  * per-date hour overrides
  * full-day mode and per-day mode overrides
* Slot-level reservation consistency (hold/booked), including server-side conflict validation.
* Frontend privacy model: public view shows only Available / Unavailable statuses.
* Frontend booking constraints:
  * past date/time blocking
  * lead time (hours)
  * time buffer (minutes)
* Admin availability manager with time preview, date locks, and safer slot remove behavior.
* Automatic historical data retention cleanup (configurable in days).
* Configurable client email templates and toggles (initial/expired/approved/rejected).
* Responsive, sectioned admin settings layout.
