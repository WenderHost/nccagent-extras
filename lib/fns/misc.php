<?php

/**
 * Miscellaneous code additions
 */

/**
 * Turn off shortlinks
 */
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
