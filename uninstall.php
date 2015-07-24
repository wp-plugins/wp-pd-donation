<?php

global $wpdb;

$table = $wpdb->prefix . "donors";

$wpdb->query("DROP TABLE IF EXISTS $table");

delete_option("donation_options");

