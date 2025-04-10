# RSS Feed Importer WordPress Plugin

This WordPress plugin imports posts from a specified RSS or Atom feed into your WordPress site on a recurring schedule.

## Features

*   **Scheduled Imports:** Automatically checks a specified feed URL for new posts based on standard WordPress cron schedules (e.g., hourly, twice daily, daily).
*   **Configurable Import Quantity:** Set the maximum number of posts to attempt importing during each scheduled check.
*   **Post Status Control:** Choose the status (Draft, Published, Pending Review, Private) for newly imported posts.
*   **Category Assignment:** Assign all imported posts to a specific category selected from a dropdown list.
*   **Image Handling:**
    *   Imports images found within the feed item's content, uploads them to the WordPress Media Library, and updates the image URLs in the post content.
    *   Detects image enclosures in the feed and attempts to set them as the post's Featured Image.
*   **Duplicate Prevention:** Stores the feed item's GUID (or permalink as fallback) in post meta to prevent importing the same item multiple times.
*   **Simple Settings Page:** Configure the feed URL, schedule, quantity, status, and category under "Tools" -> "RSS Importer" in the WordPress admin area.

## How it Works

1.  **Settings:** Configure the plugin via the admin settings page. Saving the settings schedules (or reschedules) the import task using WP-Cron.
2.  **Cron Job:** On the chosen schedule, the `rss_importer_cron` function runs.
3.  **Fetch Feed:** It fetches the specified RSS/Atom feed using WordPress's built-in `fetch_feed()` function.
4.  **Check for Duplicates:** For each item in the feed (up to the specified quantity), it checks if a post with the same GUID has already been imported by checking post meta (`_rss_importer_guid`).
5.  **Process Content Images:** It parses the feed item's content (`the_content`), finds `<img>` tags, downloads the images using `download_url()`, uploads them to the Media Library using `media_handle_sideload()`, and updates the `src` attributes in the content with the new URLs.
6.  **Insert Post:** It creates a new post using `wp_insert_post()`, mapping the feed item's title, processed content, date, and selected status/category. It also stores the original GUID, feed URL, and item permalink as post meta.
7.  **Featured Image:** If the feed item has an image enclosure, it attempts to download and upload that image using `media_handle_sideload()` and sets it as the Featured Image for the newly created post using `set_post_thumbnail()`.

## Requirements

*   WordPress (tested vaguely around recent versions)
*   PHP (with standard extensions like DOM, mbstring, and an image library like GD or Imagick for thumbnail generation)
*   A functioning WP-Cron system (standard WordPress cron or a server-side cron job triggering `wp-cron.php`)

## Installation

1.  Download the plugin files (e.g., `rss_importer.php`).
2.  Place the `rss_importer.php` file (or the directory containing it, if packaged) into your `/wp-content/plugins/` directory.
3.  Activate the "RSS Feed Importer" plugin through the 'Plugins' menu in WordPress.
4.  Go to "Tools" -> "RSS Importer" to configure the settings.

## Notes

*   Import scheduling relies on WP-Cron, which depends on site traffic to trigger schedules. For guaranteed timing, consider setting up a server-level cron job to hit `wp-cron.php`.
*   Image import success depends on the remote server allowing downloads, PHP memory limits, and correct image library setup on your server.
*   The author for imported posts is currently hardcoded to user ID 1 (the initial admin user). This could be made configurable in future versions.
