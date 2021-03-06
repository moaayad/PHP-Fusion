<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles.php
| Author: Nick Jones (Digitanium)
| Co-Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."/../../maincore.php";
if (!db_exists(DB_ARTICLES)) {
	$_GET['code'] = 404;
	require_once BASEDIR."error.php";
	exit;
}

require_once THEMES."templates/header.php";
include INFUSIONS."articles/locale/".LOCALESET."articles.php";
include INFUSIONS."articles/templates/articles.php";

$info = array();
add_to_title($locale['global_200'].$locale['400']);
add_breadcrumb(array('link'=>INFUSIONS.'articles/articles.php', 'title'=>$locale['400']));

/* Render Articles */
if (isset($_GET['article_id']) && isnum($_GET['article_id'])) {

	$result = dbquery("SELECT ta.article_subject, ta.article_article, ta.article_keywords, ta.article_breaks,
		ta.article_datestamp, ta.article_reads, ta.article_allow_comments, ta.article_allow_ratings,
		tac.article_cat_id, tac.article_cat_name,
		tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, tu.user_joined, tu.user_level
		FROM ".DB_ARTICLES." ta
		INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
		LEFT JOIN ".DB_USERS." tu ON ta.article_name=tu.user_id
		".(multilang_table("AR") ?  "WHERE tac.article_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('article_visibility')." AND article_id='".$_GET['article_id']."' AND article_draft='0'");

	if (dbrows($result)>0) {
		$data = dbarray($result);
		require_once INCLUDES."comments_include.php";
		require_once INCLUDES."ratings_include.php";
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;
		if ($_GET['rowstart'] == 0) dbquery("UPDATE ".DB_ARTICLES." SET article_reads=article_reads+1 WHERE article_id='".$_GET['article_id']."'");
		$article = preg_split("/<!?--\s*pagebreak\s*-->/i", stripslashes($data['article_article']));
		$pagecount = count($article);
		$article_subject = stripslashes($data['article_subject']);

		add_breadcrumb(array('link'=>INFUSIONS.'articles/articles.php?cat_id='.$data['article_cat_id'], 'title'=>$data['article_cat_name']));
		add_breadcrumb(array('link'=>INFUSIONS.'articles/articles.php?article_id='.$_GET['article_id'], 'title'=>$data['article_subject']));

		if ($data['article_keywords'] !=="") { set_meta("keywords", $data['article_keywords']); }

		$article_info = array(
			"article_id" => $_GET['article_id'],
			"cat_id" => $data['article_cat_id'],
			"cat_name" => $data['article_cat_name'],
			"user_id" => $data['user_id'],
			"user_name" => $data['user_name'],
			"user_status" => $data['user_status'],
			"user_avatar" => $data['user_avatar'],
			"user_joined" => $data['user_joined'],
			"user_level" => $data['user_level'],
			"article_date" => $data['article_datestamp'],
			"article_breaks" => $data['article_breaks'],
			"article_comments" => dbcount("(comment_id)", DB_COMMENTS, "comment_type='A' AND comment_item_id='".$_GET['article_id']."'"),
			"article_reads" => $data['article_reads'],
			"article_allow_comments" => $data['article_allow_comments'],
			"article_allow_ratings" => $data['article_allow_ratings'],
			"page_nav" =>  $pagecount > 1 ? makepagenav($_GET['rowstart'], 1, $pagecount, 3, INFUSIONS."articles/articles.php?article_id=".$_GET['article_id']."&amp;") : ''
		);

		add_to_title($locale['global_201'].$article_subject);
		render_article($article_subject, $article[$_GET['rowstart']], $article_info);
	} else {
		redirect(INFUSIONS."articles/articles.php");
	}
}

/* Main Index View */
elseif (!isset($_GET['cat_id']) || !isnum($_GET['cat_id'])) {
	$result = dbquery("SELECT ac.article_cat_id, ac.article_cat_name, ac.article_cat_description, COUNT(a.article_cat) AS article_count FROM ".DB_ARTICLES." a
		LEFT JOIN ".DB_ARTICLE_CATS." ac ON a.article_cat=ac.article_cat_id
		".(multilang_table("AR") ? "WHERE ac.article_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('a.article_visibility')."
		GROUP BY ac.article_cat_id
		ORDER BY ac.article_cat_name");
	$info['articles_rows'] = dbrows($result);
	if ($info['articles_rows']>0) {
		while ($data = dbarray($result)){
			$info['articles']['item'][] = $data;
		}
	}
	render_articles_main($info);
} else {
// Category view
	$result = dbquery("SELECT article_cat_name, article_cat_sorting FROM ".DB_ARTICLE_CATS." ".(multilang_table("AR") ?  "WHERE article_cat_language='".LANGUAGE."' AND" : "WHERE")." article_cat_id='".$_GET['cat_id']."'");
	if (dbrows($result) != 0) {
		$cdata = dbarray($result);
		add_to_title($locale['global_201'].$cdata['article_cat_name']);
		add_breadcrumb(array('link'=>INFUSIONS.'articles/articles.php?cat_id='.$_GET['cat_id'], 'title'=>$cdata['article_cat_name']));
		$info['articles']['category'] = $cdata;
		$info['articles_max_rows'] = dbcount("(article_id)", DB_ARTICLES, "article_cat='".$_GET['cat_id']."' AND article_draft='0'");
		$_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['articles_max_rows']) ? $_GET['rowstart'] : 0;
		if ($info['articles_max_rows'] > 0) {
			$result = dbquery("SELECT article_id, article_subject, article_snippet, article_datestamp FROM ".DB_ARTICLES."
						WHERE article_cat='".$_GET['cat_id']."' AND article_draft='0' AND ".groupaccess('article_visibility')." ORDER BY ".$cdata['article_cat_sorting']."
						LIMIT ".$_GET['rowstart'].",".$settings['articles_per_page']);
			$info['articles_rows'] = dbrows($result);
			while ($data = dbarray($result)) {
				$data['new'] = ($data['article_datestamp']+604800 > time()+($settings['timeoffset']*3600)) ? $locale['402'] : '';
				$info['articles']['item'][] = $data;
			}
			$info['page_nav'] = ($info['articles_rows'] > $settings['articles_per_page']) ? makepagenav($_GET['rowstart'], $settings['articles_per_page'], $info['articles_rows'], 3, FUSION_SELF."?cat_id=".$_GET['cat_id']."&amp;") : '';
		}
	} else {
		redirect(INFUSIONS.'articles/articles.php');
	}
	render_articles_category($info);
}
require_once THEMES."templates/footer.php";
