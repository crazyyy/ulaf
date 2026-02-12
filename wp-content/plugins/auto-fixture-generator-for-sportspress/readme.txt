=== Auto Fixture Generator for SportsPress ===
Contributors: savvasha
Tags: roundrobin, fixtures, schedule, generator, events
Requires at least: 5.3
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl.html

Save hours of manual scheduling and let your SportsPress league build itself automatically.

== Description ==

Auto Fixture Generator for SportsPress helps you generate complete league match schedules automatically in just a few clicks.  
Whether you are managing a simple round-robin tournament or a professional season with advanced scheduling rules, this plugin integrates seamlessly with the SportsPress plugin and takes care of the fixture creation for you.

[youtube https://youtu.be/YF2CS6T0Cu0]

**Features included:** 
* Single Round Robin algorithm — every team plays each other once.  
* Select your League, Season and Start Date.  
* Define a naming template for gameweeks/matchdays (e.g. “Gameweek 1”, “Gameweek 2”, etc.).  
* Automatically create calendar and league table entries in SportsPress.  
* All teams in the selected season are included by default.

**Upgrade to the Premium version for advanced control:**  
* Double Round Robin algorithm — home and away matches for every team.
* Custom Gameweeks Algorithm: Let users define the exact number of gameweeks for their schedule. The generator will automatically distribute all matches as evenly as possible across the specified number of gameweeks, with flexible support for non-standard league structures and scheduling needs.  
* Multiple time slots per match day.  
* Block specific dates (e.g. holidays).  
* Select exactly which teams will participate.  
* Shuffle team order for fair fixtures.  
* Avoid consecutive away games in your schedule.  
* More algorithms coming soon.

With the premium upgrade, you’ll get full flexibility and professional scheduling capability — perfect for clubs, leagues and tournament organisers who demand complete control over their season structure.  
[Upgrade to Premium](https://savvasha.com/auto-fixture-generator-for-sportspress/)

== Installation ==

1. Upload the `auto-fixture-generator-for-sportspress` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Ensure the SportsPress plugin is installed and active.
4. Go to SportsPress → Events → Auto Fixture Generator to create new fixtures.
5. Debug / Dry Run mode available if needed.

== Frequently Asked Questions ==

= Do I need SportsPress installed? =
Yes — this plugin is an add-on for SportsPress and uses its Leagues, Seasons, Teams and Events.

= Can I pick which teams take part? =
Team selection is only available in the Premium version.

= Is Double Round Robin supported? =
Yes — but only in the Premium version.

= Will the plugin automatically create events and the table? =
Yes. The plugin creates fixtures as SportsPress events, and updates the league table accordingly.

= Where are the fixtures stored? =
Fixtures are created as SportsPress events.

== Screenshots ==

1. Generator screen with algorithm selection and options (part1)
2. Generator screen with algorithm selection and options (part2)
3. Generator screen with algorithm selection and options (part3)
4. Succesfull creation of events
5. Debug / Dry Run mode available if needed.

== Changelog ==

= 1.5 =
* NEW: Events per timeslot mode - AUTO calculates optimal distribution, MANUAL lets you set custom limits per slot (Premium version only)
* NEW: Algorithm-specific options now displayed in dry run debug log (e.g. Season Weeks for Fixed Week Season)
* FIX: Dry run mode now uses the same scheduling logic as normal mode, ensuring consistent fixture results.
* FIX: Fixed Week Season algorithm now generates correct number of gameweeks (rematches no longer incorrectly removed)
* FIX: Gameweeks no longer share dates - each gameweek properly advances to the next week period

= 1.4 =
* NEW: Debug / Dry Run mode - simulate fixture generation without database changes when WP_DEBUG and WP_DEBUG_LOG are enabled
* FIX: Teams no longer scheduled multiple times per gameweek with odd team counts
* FIX: Double Round Robin now correctly reverses home/away in second half (Premium version only)
* FIX: Improved home/away balance in Single Round Robin algorithm

= 1.3 =
* New premium algorithm allowing user-defined number of gameweeks (Premium version only)
* FIX: Skip duplicate checking for algorithms that allow rematches
* FIX: Properly advance to next week when gameweek changes
* FIX: Handle week-boundary-crossing gameweeks (Sat-Sun, Fri-Sat-Sun, etc.)
* UI: Add number input support, dynamic events description update

= 1.2 =
* Update Freemius SDK to 2.13.

= 1.1 =
* FIX: Gameweek names are not assigned correctly to generated fixtures.
* FIX: Calendar and League Table is not created.

= 1.0 =
* Initial release.