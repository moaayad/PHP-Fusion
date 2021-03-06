<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: my_posts.php
| Author: Hien (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
add_to_title($locale['global_200'].$locale['global_042']);

$result = dbquery("SELECT tp.post_id FROM ".DB_FORUM_POSTS." tp
	INNER JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id = tt.thread_id
	INNER JOIN ".DB_FORUMS." tf ON tp.forum_id = tf.forum_id
	".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('forum_access')." AND post_author='".$userdata['user_id']."' AND post_hidden='0' AND thread_hidden='0'");
$rows = dbrows($result);
$info['post_rows'] = $rows;
if ($rows) {

	if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
		$_GET['rowstart'] = 0;
	}
	$result = dbquery("SELECT tp.forum_id, tp.thread_id, tp.post_id, tp.post_author, tp.post_datestamp,
		tf.forum_name, tf.forum_access, tf.forum_type, tt.thread_subject
		FROM ".DB_FORUM_POSTS." tp
		INNER JOIN ".DB_FORUMS." tf ON tp.forum_id=tf.forum_id
		INNER JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id=tt.thread_id
		".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('tf.forum_access')." AND tp.post_author='".$userdata['user_id']."' AND post_hidden='0' AND thread_hidden='0'
		ORDER BY tp.post_datestamp DESC LIMIT ".$_GET['rowstart'].",20");
	$i = 0;

	while ($data = dbarray($result)) {
		$info['item'][$data['post_id']] = $data;
	}
}


