<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: downloads.php
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
require_once "../../maincore.php";
pageAccess('D');

require_once THEMES."templates/admin_header.php";
require_once INCLUDES."html_buttons_include.php";
include INFUSIONS."downloads/locale/".LOCALESET."downloads_admin.php";
include LOCALE.LOCALESET."admin/settings.php";
require_once INCLUDES."infusions_include.php";
$dl_settings = get_settings("downloads");

add_breadcrumb(array('link'=>FUSION_SELF.$aidlink, 'title'=>$locale['download_0001']));

$message = '';
if (isset($_GET['status'])) {
	switch($_GET['status']) {
		case 'sn':
			$message = $locale['download_0100'];
			$status = 'success';
			$icon = "<i class='fa fa-check-square-o fa-lg fa-fw'></i>";
			break;
		case 'su':
			$message = $locale['download_0101'];
			$status = 'info';
			$icon = "<i class='fa fa-check-square-o fa-lg fa-fw'></i>";
			break;
		case 'del':
			$message = $locale['download_0102'];
			$status = 'danger';
			$icon = "<i class='fa fa-trash fa-lg fa-fw'></i>";
			break;
	}
	if ($message) {
		addNotice($status, $icon.$message);
	}
}

$_GET['download_cat_id'] = isset($_GET['download_cat_id']) && isnum($_GET['download_cat_id']) ? $_GET['download_cat_id'] : 0;

$allowed_section = array('downloads', 'dlopts', 'sform');

$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'downloads';
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? 1 : 0;

// master template
$master_tab_title['title'][] = $locale['download_0000'];
$master_tab_title['id'][] = "downloads";
$master_tab_title['icon'][] = "";

if (dbcount("('download_cat_id')", DB_DOWNLOAD_CATS, "")) {
	$master_tab_title['title'][] = isset($_GET['action']) ? $locale['download_0003'] : $locale['download_0002'];
	$master_tab_title['id'][] = "dlopts";
	$master_tab_title['icon'][] =  $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
}

$master_tab_title['title'][] = $locale['download_settings'];
$master_tab_title['id'][] = "sform";
$master_tab_title['icon'][] = "";

$master_tab_active = tab_active($master_tab_title, $_GET['section'], 1);

opentable($locale['download_0001']);
echo opentab($master_tab_title, $master_tab_active, 'download-master', 1);
echo opentabbody($master_tab_title['title'][0], 'downloads', $master_tab_active, 1);
download_listing();
echo closetabbody();
if ($_GET['section'] == 'dlopts') {
	fusion_confirm_exit();
	add_breadcrumb(array('link'=>'', 'title'=>$edit ? $locale['download_0003']: $locale['download_0002']));
	echo opentabbody($master_tab_title['title'][1], 'dlopts', $master_tab_active, 1);
	echo "<div class='m-t-20'>\n";
	download_form();
	echo "</div>\n";
	echo closetabbody();
}

if ($_GET['section'] == 'sform') {
	add_breadcrumb(array('link'=>'', 'title'=>$locale['download_settings']));
	echo opentabbody($master_tab_title['title'][1], 'sform', $master_tab_active, 1);
	echo "<div class='m-t-20'>\n";

require_once INCLUDES."mimetypes_include.php";

if (isset($_POST['savesettings'])) {
	$admin_password = (isset($_POST['admin_password'])) ? form_sanitizer($_POST['admin_password'], '', 'admin_password') : '';
	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "") && !defined('FUSION_NULL')) {
		$download_max_b = form_sanitizer($_POST['calc_b'], 1, 'calc_b')*form_sanitizer($_POST['calc_c'], 1000000, 'calc_c');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='".(isnum($_POST['calc_b']) && isnum($_POST['calc_c']) ? $download_max_b : "150000")."' WHERE settings_name='download_max_b'") : '';
		$download_types = form_sanitizer($_POST['download_types'], '', 'download_types');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$download_types' WHERE settings_name='download_types'") : '';
		$download_screen_max_w = form_sanitizer($_POST['download_screen_max_w'], 0, 'download_screen_max_w');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$download_screen_max_w' WHERE settings_name='download_screen_max_w'") : '';
		$download_screen_max_h = form_sanitizer($_POST['download_screen_max_h'], 0, 'download_screen_max_h');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$download_screen_max_h' WHERE settings_name='download_screen_max_h'") : '';
		$download_screen_max_b = form_sanitizer($_POST['calc_bb'], 200, 'calc_bb')*form_sanitizer($_POST['calc_cc'], 1000, 'calc_cc');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$download_screen_max_b' WHERE settings_name='download_screen_max_b'") : '';
		$download_thumb_max_h = form_sanitizer($_POST['download_thumb_max_h'], 100, 'download_thumb_max_h');
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='".(isnum($_POST['download_thumb_max_h']) ? $_POST['download_thumb_max_h'] : "100")."' WHERE settings_name='download_thumb_max_h'");
		$download_thumb_max_w = form_sanitizer($_POST['download_thumb_max_w'], 100, 'download_thumb_max_w');
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='".(isnum($_POST['download_thumb_max_w']) ? $_POST['download_thumb_max_w'] : "100")."' WHERE settings_name='download_thumb_max_w'");
		$download_screenshot = form_sanitizer($_POST['download_screenshot'], 0, 'download_screenshot');
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$download_screenshot' WHERE settings_name='download_screenshot'");
		$download_pagination = form_sanitizer($_POST['download_pagination'], 12, 'download_pagination');
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='".(isnum($_POST['download_pagination']) ? $_POST['download_pagination'] : "12")."' WHERE settings_name='download_pagination'");
		if (!defined('FUSION_NULL')) {
			addNotice('success', $locale['900']);
			redirect(FUSION_SELF.$aidlink."&amp;section=sform");
		}
	}
}
$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS_INF);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}
/**
 * Options for dropdown field
 */
$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
$calc_c = calculate_byte($settings2['download_max_b']);
$calc_b = $settings2['download_max_b']/$calc_c;
$calc_cc = calculate_byte($settings2['download_screen_max_b']);
$calc_bb = $settings2['download_screen_max_b']/$calc_cc;

$choice_opts = array('1' => $locale['yes'], '0' => $locale['no']);
$mime = mimeTypes();
$mime_opts = array();
foreach ($mime as $m => $Mime) {
	$ext = ".$m";
	$mime_opts[$ext] = $ext;
}

echo "<div class='well'>".$locale['download_description']."</div>";
$formaction = FUSION_SELF.$aidlink."&amp;section=sform";
echo openform('settingsform', 'post', $formaction, array('max_tokens' => 1));
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";
openside('');
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-2'>
	<label for='download_pagination'>".$locale['939']."</label>
	</div>
	<div class='col-xs-12 col-sm-10'>
	".form_text('download_pagination', '', $settings2['download_pagination'], array('class' => 'pull-left', 'max_length' => 4, 'number'=>1, 'width'=>'150px'))."
	</div>";
echo "<div class='col-xs-12 col-sm-8'>";
echo "<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='photo_w'>".$locale['934']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('download_screen_max_w', '', $settings2['download_screen_max_w'], array('class' => 'pull-left m-r-10', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('download_screen_max_h', '', $settings2['download_screen_max_h'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
	<small class='m-l-10 mid-opacity text-uppercase pull-left m-t-10'>( ".$locale['604']." )</small>
	</div>
</div>";

echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='photo_w'>".$locale['937']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('download_thumb_max_w', '', $settings2['download_thumb_max_w'], array('class' => 'pull-left m-r-10', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('download_thumb_max_h', '', $settings2['download_thumb_max_h'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
	<small class='m-l-10 mid-opacity text-uppercase pull-left m-t-10'>( ".$locale['604']." )</small>
	</div>
</div>";
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='calc_b'>".$locale['930']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('calc_b', '', $calc_b, array('required' => 1, 'number' => 1, 'error_text' => $locale['error_rate'], 'width' => '150px', 'max_length' => 4, 'class' => 'pull-left m-r-10'))."
	".form_select('calc_c', '', $calc_opts, $calc_c, array('placeholder' => $locale['choose'], 'class' => 'pull-left', 'width' => '180px'))."
	</div>
</div>
";
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='calc_bb'>".$locale['936']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('calc_bb', '', $calc_bb, array('required' => 1, 'number' => 1, 'error_text' => $locale['error_rate'], 'width' => '150px', 'max_length' => 4, 'class' => 'pull-left m-r-10'))."
	".form_select('calc_cc', '', $calc_opts, $calc_cc, array('placeholder' => $locale['choose'], 'class' => 'pull-left', 'width' => '180px'))."
	</div>
</div>
";
closeside();
openside();
echo form_select('download_types[]', $locale['932'], $mime_opts, $settings2['download_types'], array('input_id'=>'dltype', 'error_text' => $locale['error_type'], 'placeholder' => $locale['choose'], 'multiple' => 1, 'width' => '100%', 'delimiter' => '|'));
closeside();
echo "</div><div class='col-xs-12 col-sm-4'>\n";
openside('');
echo form_select('download_screenshot', $locale['938'], $choice_opts, $settings2['download_screenshot']);
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));
echo closeform();
echo "</div>\n";
echo closetabbody();
}


echo closetab();
closetable();

add_to_jquery("
    $('#shortdesc_display').show();
    $('#calc_upload').bind('click', function() {
        if ($('#calc_upload').attr('checked')) {
            $('#download_filesize').attr('readonly', 'readonly');
            $('#download_filesize').val('');
        } else {
           $('#download_filesize').removeAttr('readonly');
        }
    });
    ");

require_once THEMES."templates/footer.php";


/* Download Form */
function download_form() {
	global $locale, $settings, $dl_settings, $userdata, $aidlink, $defender;
	$upload_directory = DOWNLOADS."files/";
	$data = array(
		'download_id' => 0,
		'download_user' => $userdata['user_id'],
		'download_homepage' => '',
		'download_title' => '',
		'download_cat' => 0,
		'download_description_short' => '',
		'download_description' => '',
		'download_keywords' => '',
		'download_image_thumb' => '',
		'download_url' => '',
		'download_file' => '',
		'download_license' => '',
		'download_copyright' => '',
		'download_os' => '',
		'download_version' => '',
		'download_filesize' => '',
		'download_visibility' => 0,
		'download_allow_comments' => 0,
		'download_allow_ratings' => 0,
		'download_datestamp' => time()
	);
	$formaction = FUSION_SELF.$aidlink."&amp;section=dlopts";

	/* delete */
	if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['download_id']) && isnum($_GET['download_id']))) {
		$result = dbquery("SELECT download_file, download_image, download_image_thumb FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			if (!empty($data['download_file']) && file_exists(DOWNLOADS.$data['download_file'])) {
				@unlink(DOWNLOADS.$data['download_file']);
			}
			if (!empty($data['download_image']) && file_exists(DOWNLOADS."images/".$data['download_image'])) {
				@unlink(DOWNLOADS."images/".$data['download_image']);
			}
			if (!empty($data['download_image_thumb']) && file_exists(DOWNLOADS."images/".$data['download_image_thumb'])) {
				@unlink(DOWNLOADS."images/".$data['download_image_thumb']);
			}
			$result = dbquery("DELETE FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
		}
		redirect(FUSION_SELF.$aidlink."&download_cat_id=".intval($_GET['download_cat_id'])."&status=del");
	}

	/* save */
	if (isset($_POST['save_download'])) {
		$data = array(
			'download_id' 	=>	form_sanitizer($_POST['download_id'], '0', 'download_id'),
			'download_user' => $userdata['user_id'],
			'download_homepage' => form_sanitizer($_POST['download_homepage'], '', 'download_homepage'),
			'download_title' => form_sanitizer($_POST['download_title'], '', 'download_title'),
			'download_cat'		=>	form_sanitizer($_POST['download_cat'], '0', 'download_cat'),
			'download_description_short' => form_sanitizer($_POST['download_description_short'], '', 'download_description_short'),
			'download_description' => form_sanitizer($_POST['download_description'], '', 'download_description'),
			'download_keywords' => form_sanitizer($_POST['download_keywords'], '', 'download_keywords'),
			'download_image'	=> isset($_POST['download_hidden_image']) ? form_sanitizer($_POST['download_hidden_image'], '', 'download_hidden_image') : '',
			'download_image_thumb'	=> isset($_POST['download_hidden_image']) ? form_sanitizer($_POST['download_hidden_image_thumb'], '', 'download_hidden_image_thumb') : '',
			'download_url' => form_sanitizer($_POST['download_url'], '', 'download_url'),
			'download_file'	=>	isset($_POST['download_hidden_image']) ? form_sanitizer($_POST['download_hidden_file'], '', 'download_hidden_file') : '',
			'download_license' => form_sanitizer($_POST['download_license'], '', 'download_license'),
			'download_copyright' => form_sanitizer($_POST['download_copyright'], '', 'download_copyright'),
			'download_os' => form_sanitizer($_POST['download_os'], '', 'download_os'),
			'download_version' => form_sanitizer($_POST['download_version'], '', 'download_version'),
			'download_filesize' => form_sanitizer($_POST['download_filesize'], '', 'download_filesize'),
			'download_visibility' => form_sanitizer($_POST['download_visibility'], '0', 'download_visibility'),
			'download_allow_comments' => isset($_POST['download_allow_comments']) ? 1 : 0,
			'download_allow_ratings' => isset($_POST['download_allow_ratings']) ? 1 : 0,
			'download_datestamp' => isset($_POST['update_datestamp']) ? time() : $data['download_datestamp'],
		);
		/**
		 * File Section
		 */
		if (isset($_POST['del_upload']) && isset($_GET['download_id']) && isnum($_GET['download_id'])) {
			$result2 = dbquery("SELECT download_file FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
			if (dbrows($result2)) {
				$data2 = dbarray($result2);
				if (!empty($data2['download_file']) && file_exists(DOWNLOADS.'files/'.$data2['download_file'])) {
					@unlink(DOWNLOADS.'files/'.$data2['download_file']);
				}
				$data['download_file'] = '';
				$data['download_filesize'] = '';
			}
		}
		if (!empty($_FILES['download_file']['name']) && is_uploaded_file($_FILES['download_file']['tmp_name'])) {
			$upload = form_sanitizer($_FILES['download_file'], '', 'download_file');
			if ($upload) {
				$data['download_file'] = $upload['target_file'];
				if ($data['download_filesize'] == "" || isset($_POST['calc_upload'])) {
					$data['download_filesize'] = parsebytesize($upload['source_size']);
				}
			}
		} elseif (isset($_POST['download_url']) && $_POST['download_url'] != "") {
			$data['download_url'] = (isset($_POST['download_url']) ? stripinput($_POST['download_url']) : "");
			$data['download_file'] = '';
		}

		/**
		 * Image Section
		 */
		if (isset($_POST['del_image']) && isset($_GET['download_id']) && isnum($_GET['download_id'])) {
			$result = dbquery("SELECT download_image, download_image_thumb FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
			if (dbrows($result)) {
				$data += dbarray($result);
				if (!empty($data['download_image']) && file_exists(DOWNLOADS."images/".$data['download_image'])) {
					@unlink(DOWNLOADS."images/".$data['download_image']);
				}
				if (!empty($data['download_image_thumb']) && file_exists(DOWNLOADS."images/".$data['download_image_thumb'])) {
					@unlink(DOWNLOADS."images/".$data['download_image_thumb']);
				}
			}
			$data['download_image'] = '';
			$data['download_image_thumb'] = '';
		} elseif (isset($_FILES['download_image'])) {
			$upload = form_sanitizer($_FILES['download_image'], '', 'download_image');
			if ($upload) {
				$data['download_image'] = $upload['image_name'];
				$data['download_image_thumb'] = $upload['thumb1_name'];
			}
		}

		// Break form and return errors
		if (!$data['download_file'] && !$data['download_url']) {
			$defender->stop();
			addNotice('info',$locale['download_0111']);
		}
		if (dbcount("(download_id)", DB_DOWNLOADS, "download_id='".$data['download_id']."'")) {
			dbquery_insert(DB_DOWNLOADS, $data, 'update');
			if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&status=su");
		} else {
			dbquery_insert(DB_DOWNLOADS, $data, 'save');
			if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&status=sn");
		}
	}

	/* edit */
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['download_id']) && isnum($_GET['download_id']))) {
		$result = dbquery("SELECT * FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;download_cat_id=".$data['download_cat']."&amp;download_id=".$_GET['download_id']."&amp;section=dlopts";
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}

	$visibility_opts = array();
	$user_groups = getusergroups();
	while (list($key, $user_group) = each($user_groups)) {
		$visibility_opts[$user_group['0']] = $user_group['1'];
	}

	echo openform('inputform', 'post', $formaction, array('max_tokens' => 1, 'enctype' => 1));
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-8'>\n";
	openside('');
	echo form_hidden('', 'download_id', 'download_id', $data['download_id']);
	echo form_hidden('', 'download_datestamp', 'download_datestamp', $data['download_datestamp']);
	echo form_text('download_title', $locale['download_0200'], $data['download_title'], array('required' => 1, 'error_text'=>$locale['download_0110']));
	echo form_textarea('download_description_short', $locale['download_0202'], $data['download_description_short'], array('required'=>1, 'error_text'=>$locale['download_0112'], 'maxlength' => '255', 'autosize' => 1));
	if ($dl_settings['download_screenshot']) {
		if (!empty($data['download_image']) && !empty($data['download_image_thumb'])) {
			echo "<div class='clearfix list-group-item m-b-20'>\n";
			echo "<div class='pull-left m-r-10'>\n";
			echo thumbnail(DOWNLOADS."images/".$data['download_image_thumb'], '80px');
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo "<span class='text-dark strong'>".$locale['download_0220']."</span>\n";
			echo form_checkbox('del_image', $locale['download_0216'], '');
			echo form_hidden('', 'download_hidden_image', 'download_hidden_image', $data['download_image']);
			echo form_hidden('', 'download_hidden_image_thumb', 'download_hidden_image_thumb', $data['download_image_thumb']);
			echo "</div>\n";
			echo "</div>\n";
		} else {
			$file_options = array(
				'max_width' => $dl_settings['download_screen_max_w'],
				'max_height' => $dl_settings['download_screen_max_w'],
				'max_byte' => $dl_settings['download_screen_max_b'],
				'type' => 'image',
				'delete_original' => 0,
				'thumbnail_folder' => '',
				'thumbnail' => 1,
				'thumbnail_suffix'=> '_thumb',
				'thumbnail_w'=> $dl_settings['download_thumb_max_w'],
				'thumbnail_h' => $dl_settings['download_thumb_max_h'],
				'thumbnail2' => 0
			);
			echo form_fileinput($locale['download_0220'], 'download_image', 'download_image', DOWNLOADS."images/", '', $file_options); // all file types.
			echo "<small>".sprintf($locale['download_0219'], parsebytesize($dl_settings['download_screen_max_b']), str_replace(',', ' ', ".jpg,.gif,.png"), $dl_settings['download_screen_max_w'], $dl_settings['download_screen_max_h'])."</small>\n";
		}
	}

	/* Download file input */
	$tab_title['title'][] = $locale['download_0214'];
	$tab_title['id'][] = 'dlf';
	$tab_title['icon'][] = 'fa fa-file-zip-o fa-fw';
	$tab_title['title'][] = $locale['download_0215'];
	$tab_title['id'][] = 'dll';
	$tab_title['icon'][] = 'fa fa-plug fa-fw';
	$tab_active = tab_active($tab_title, 0);
	echo opentab($tab_title, $tab_active, 'downloadtab');
	echo opentabbody($tab_title['title'][0], 'dlf', $tab_active);
	if (!empty($data['download_file'])) {
		echo "<div class='list-group-item m-t-10'>".$locale['download_0214']." - <a href='".DOWNLOADS.$data['download_file']."'>".DOWNLOADS.$data['download_file']."</a>\n";
		echo form_checkbox('del_upload', $locale['download_0216'], '', array('class'=>'m-b-0'));
		echo "</div>\n";
		echo form_hidden('', 'download_hidden_file', 'download_hidden_file', $data['download_file']);
	} else {
		$file_options = array(
			'max_bytes' => $dl_settings['download_max_b'],
			'valid_ext' => $dl_settings['download_types'],
		);
		echo "<div class='list-group m-t-10'><div class='list-group-item'>\n";
		echo form_fileinput($locale['download_0214'], 'download_file', 'download_file', DOWNLOADS."files/", '', $file_options);
		echo sprintf($locale['download_0218'], parsebytesize($dl_settings['download_max_b']), str_replace(',', ' ', $dl_settings['download_types']))."<br />\n";
		echo "</div>\n";
		echo "<div class='list-group-item'>\n";
		echo form_checkbox('calc_upload', $locale['download_0217'], '');
		echo "</div>\n";
		echo "</div>\n";
	}
	echo closetabbody();
	echo opentabbody($tab_title['title'][1], 'dll', $tab_active);
	if (empty($data['download_file'])) {
		echo "<div class='list-group m-t-10'><div class='list-group-item'>\n";
		echo form_text('download_url', $locale['download_0206'], $data['download_url']);
		echo "</div>\n</div>\n";
	} else {
		echo form_hidden('', 'download_url', 'download_url', $data['download_url']);
	}
	echo closetabbody();
	echo closetab();
	closeside('');
	echo form_textarea('download_description', $locale['download_0201'], $data['download_description'], array('no_resize' => '1', 'form_name' => 'inputform', 'html' => 1, 'autosize' => 1, 'preview' => 1));
	echo "</div>\n<div class='col-xs-12 col-sm-4'>\n";
	openside();
	if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
		$sys = "";
		if ($settings['comments_enabled'] == "0" && $settings['ratings_enabled'] == "0") {
			$sys = $locale['comments_ratings'];
		} elseif ($settings['comments_enabled'] == "0") {
			$sys = $locale['comments'];
		} else {
			$sys = $locale['ratings'];
		}
		echo "<div class='well'>".sprintf($locale['download_0256'], $sys)."</div>\n";
	}
	echo form_select_tree("download_cat", $locale['download_0207'], $data['download_cat'], array("no_root" => 1, "placeholder" => $locale['choose'],  'width'=>'100%', "query" => (multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")), DB_DOWNLOAD_CATS, "download_cat_name", "download_cat_id", "download_cat_parent");
	echo form_select('download_visibility', $locale['download_0205'], $visibility_opts, $data['download_visibility'], array('placeholder' => $locale['choose'], 'width'=>'100%'));
	echo form_button('save_download', $locale['download_0212'], $locale['download_0212'], array('class' => 'btn-success m-r-10', 'icon'=>'fa fa-check-square-o'));
	closeside();

	openside('');
	echo form_select('download_keywords', $locale['download_0203'], array(), $data['download_keywords'], array('max_length' => 320, 'width'=>'100%', 'tags'=>1, 'multiple' => 1));
	closeside();

	openside();
	echo form_text('download_license', $locale['download_0208'], $data['download_license'], array('inline' => 1));
	echo form_text('download_copyright', $locale['download_0222'], $data['download_copyright'], array('inline' => 1));
	echo form_text('download_os', $locale['download_0209'], $data['download_os'], array('inline' => 1));
	echo form_text('download_version', $locale['download_0210'], $data['download_version'], array('inline' => 1));
	echo form_text('download_homepage', $locale['download_0221'], $data['download_homepage'], array('inline' => 1));
	echo form_text('download_filesize', $locale['download_0211'], $data['download_filesize'], array('inline' => 1));
	closeside();

	openside('');
	echo form_checkbox('download_allow_comments', $locale['download_0223'], $data['download_allow_comments'], array('class'=>'m-b-0'));
	echo form_checkbox('download_allow_ratings', $locale['download_0224'], $data['download_allow_ratings'], array('class'=>'m-b-0'));

	if (isset($_GET['action']) && $_GET['action'] == "edit") {
		echo form_checkbox('update_datestamp', $locale['download_0213'], '', array('class'=>'m-b-0'));
	}
	closeside();
	echo "</div>\n</div>\n"; // end row.
	echo "<div class='m-t-20'>\n";
	echo form_button('save_download', $locale['download_0212'], $locale['download_0212'], array('class' => 'btn-success m-r-10', 'icon'=>'fa fa-check-square-o'));
	if (isset($_GET['action']) && $_GET['action'] == "edit") {
		echo "<button type='reset' name='reset' value='".$locale['download_0225']."' class='button btn btn-default' onclick=\"location.href='".FUSION_SELF.$aidlink."';\"/>".$locale['download_0225']."</button>";
	}
	echo "</div>\n";
	echo closeform();
}

/* Download Listing */
function download_listing() {
	global $aidlink, $locale;
	echo "<div class='m-t-20'>\n";
	$result = dbcount("(download_cat_id)", DB_DOWNLOAD_CATS." ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")."");
	if (!empty($result)) {
		$result = dbquery("SELECT dc.*,	count(d.download_id) as download_count
		 		FROM ".DB_DOWNLOAD_CATS." dc
		 		LEFT JOIN ".DB_DOWNLOADS." d on dc.download_cat_id = d.download_cat
				".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")."
				GROUP BY download_cat_id
				ORDER BY download_cat_name");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				echo "<div class='panel panel-default'>\n";
				echo "<div class='panel-heading clearfix'>\n";
				echo "<div class='btn-group pull-right m-t-5'>\n";
				echo "<a class='btn btn-default btn-sm' href='".INFUSIONS."downloads/download_cats_admin.php".$aidlink."&amp;action=edit&amp;section=dadd&amp;cat_id=".$data['download_cat_id']."'><i class='fa fa-pencil fa-fw'></i> ".$locale['edit']."</a>\n";
				echo "<a class='btn btn-default btn-sm' href='".INFUSIONS."downloads/download_cats_admin.php".$aidlink."&amp;action=delete&cat_id=".$data['download_cat_id']."' onclick=\"return confirm('".$locale['download_0350']."');\"><i class='fa fa-trash fa-fw'></i> ".$locale['delete']."</a>\n";
				echo "</div>\n";
				echo "<div class='overflow-hide p-r-10'>\n";
				echo "<h4 class='panel-title display-inline-block'><a ".collapse_header_link('download-list', $data['download_cat_id'], '0', 'm-r-10 text-bigger strong').">".$data['download_cat_name']."</a> <span class='badge'>".$data['download_count']."</h4>\n";
				echo "<br/><span class='text-smaller text-uppercase'>".$data['download_cat_language']."</span>";
				echo "</div>\n"; /// end overflow-hide
				echo "</div>\n"; // end panel heading
				echo "<div ".collapse_footer_link('download-list', $data['download_cat_id'], '0').">\n";
				echo "<ul class='list-group m-10'>\n";
				$result2 = dbquery("SELECT download_id, download_title, download_description_short, download_url, download_file, download_image, download_image_thumb FROM ".DB_DOWNLOADS." WHERE download_cat='".$data['download_cat_id']."' ORDER BY download_title");
				if (dbrows($result2) > 0) {
					while($data2 = dbarray($result2)) {
						$download_url = '';
						if (!empty($data2['download_file']) && file_exists(DOWNLOADS."files/".$data2['download_file'])) {
							$download_url = DOWNLOADS."files/".$data2['download_file'];
						} elseif (!strstr($data2['download_url'], "http://") && !strstr($data2['download_url'], "../")) {
							$download_url = BASEDIR.$data2['download_url'];
						}
						echo "<li class='list-group-item'>\n";
						echo "<div class='pull-left m-r-10'>\n";
						echo thumbnail(DOWNLOADS."images/".$data2['download_image_thumb'], '50px');
						echo "</div>\n";
						echo "<div class='overflow-hide'>\n";
						echo "<span class='strong text-dark'>".$data2['download_title']."</span><br/>\n";
						echo nl2br(parseubb($data2['download_description_short']));
						echo "<div class='pull-right'>\n";
						echo "<a class='m-r-10' href='$download_url'>".$locale['download_0214']."</a>\n";
						echo "<a class='m-r-10' href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=dlopts&amp;download_cat_id=".$data['download_cat_id']."&amp;download_id=".$data2['download_id']."'>".$locale['edit']."</a>\n";
						echo "<a  class='m-r-10' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;section=dlopts&amp;download_cat_id=".$data['download_cat_id']."&amp;download_id=".$data2['download_id']."' onclick=\"return confirm('".$locale['download_0255']."');\">".$locale['delete']."</a>\n";
						echo "</div>\n";
						echo "</div>\n";
						echo "</li>\n";
					}
				} else {
					echo "<div class='panel-body text-center'>\n";
					echo $locale['download_0250'];
					echo "</div>\n";
				}
				echo "</ul>\n";

				echo "</div>\n"; // panel default
				echo closecollapse();
			}
		} else {
			echo "<div class='well text-center'>".$locale['download_0250']."</div>\n";
		}
	} else {
		echo "<div class='well text-center'>\n";
		echo "".$locale['download_0251']."<br />\n".$locale['download_0252']."<br />\n";
		echo "<a href='".INFUSIONS."downloads/download_cats_admin.php".$aidlink."&amp;section=dadd'>".$locale['download_0253']."</a>".$locale['download_0254'];
		echo "</div>\n";
	}
	echo "</div>\n";
}

function calculate_byte($download_max_b) {
	$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
	foreach ($calc_opts as $byte => $val) {
		if ($download_max_b/$byte <= 999) {
			return $byte;
		}
	}
	return 1000000;
}

