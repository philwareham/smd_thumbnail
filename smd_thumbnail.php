<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'smd_thumbnail';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.31';
$plugin['author'] = 'Stef Dawson';
$plugin['author_uri'] = 'http://stefdawson.com/';
$plugin['description'] = 'Multiple image thumbnails of arbitrary dimensions';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public              : only on the public side of the website (default)
// 1 = public+admin        : on both the public and admin side
// 2 = library             : only when include_plugin() or require_plugin() is called
// 3 = admin               : only on the admin side (no AJAX)
// 4 = admin+ajax          : only on the admin side (AJAX supported)
// 5 = public+admin+ajax   : on both the public and admin side (AJAX supported)
$plugin['type'] = '5';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '2';

// Plugin 'textpack' is optional. It provides i18n strings to be used in conjunction with gTxt().
// Syntax:
// ## arbitrary comment
// #@event
// #@language ISO-LANGUAGE-CODE
// abc_string_name => Localized String

$plugin['textpack'] = <<<EOT
#@smd_thumb
smd_thumb_actions => Actions
smd_thumb_all_sizes => All sizes
smd_thumb_all_thumbs => Create
smd_thumb_batch_legend => Create / recreate thumbnails
smd_thumb_batch_preamble => For all active profiles:
smd_thumb_btn_pnl => Profiles
smd_thumb_btn_tools => Tools
smd_thumb_btn_tools_prefs => Tools / Prefs
smd_thumb_byall => All
smd_thumb_bycat => By category
smd_thumb_bysel => With selected
smd_thumb_byusr => By author
smd_thumb_create => Creation
smd_thumb_create_group_confirm => Really create thumbnails for ALL active profiles? Any existing thumbs will be overwritten.
smd_thumb_delete => Deletion
smd_thumb_delete_confirm => Really delete profile {name}? It will delete ALL thumbnails of this type.
smd_thumb_image => Image=
smd_thumb_new => New profile
smd_thumb_prefs => Preferences
smd_thumb_profile => Profile=
smd_thumb_profile_deleted => Profile "{name}" deleted
smd_thumb_profile_exists => Profile "{name}" already exists
smd_thumb_profile_heading => Thumbnail profiles
smd_thumb_profile_preftool_heading => Thumbnail tools and prefs
smd_thumb_profile_tool_heading => Thumbnail tools
smd_thumb_quality => Quality (%)
smd_thumb_sharpen => Sharpen
smd_thumb_tables_not_installed => Tables not installed: try reinstalling the plugin
smd_thumb_thumbnail_heading => Thumbnails
smd_thumb_txp_auto_replace => Recreate thumbnails on re-upload of main image:
smd_thumb_txp_create_from => Create thumbnails from:
smd_thumb_txp_create_from_full => Full size image
smd_thumb_txp_create_from_thumb => Thumbnail
smd_thumb_txp_default_sync => Keep Txp thumbnails in sync with default profile on:
smd_thumb_upload => Replace selected thumbnail
#@smd_thumb
#@language fr-fr
smd_thumb_actions => Actions
smd_thumb_all_sizes => Toutes les tailles
smd_thumb_all_thumbs => Créer
smd_thumb_batch_legend => Créer/Recréer des vignettes
smd_thumb_batch_preamble => Pour les profils actifs :
smd_thumb_btn_pnl => Profils
smd_thumb_btn_tools => Outils
smd_thumb_btn_tools_prefs => Outils/Préf.
smd_thumb_byAll => Tous
smd_thumb_byCat => Par catégorie
smd_thumb_bySel => Sélectionnées
smd_thumb_byUsr => Par auteur
smd_thumb_create => Création
smd_thumb_create_group_confirm => Créer les vignettes pour TOUS les profils existant ? Les précédentes vignettes seront écrasées.
smd_thumb_delete => Suppression
smd_thumb_delete_confirm => Voulez-vous vraiment supprimer le profil {name} ? Les vignettes de ce type seront TOUTES supprimées.
smd_thumb_image => Image=
smd_thumb_new => Nouveau profil
smd_thumb_prefs => Préférences
smd_thumb_profile => Profil=
smd_thumb_profile_deleted => Le profil "{name}" a été supprimé.
smd_thumb_profile_exists => Le profil "{name}" existe déjà.
smd_thumb_profile_heading => Profils de vignettes
smd_thumb_profile_preftool_heading => Outils et préférences du vignettage
smd_thumb_profile_tool_heading => Outils de vignettage
smd_thumb_quality => Qualité (%)
smd_thumb_sharpen => Rendre net
smd_thumb_tables_not_installed => Tables non installées : essayez de réinstaller le plugin.
smd_thumb_thumbnail_heading => Vignettes
smd_thumb_txp_auto_replace => Recréer les vignettes au téléchargement des images :
smd_thumb_txp_create => Créer
smd_thumb_txp_create_from => Créer les vignettes à partir de :
smd_thumb_txp_create_from_full => l'image originale
smd_thumb_txp_create_from_thumb => la vignette
smd_thumb_txp_delete => Supprimer
smd_thumb_upload => Remplacer les vignettes sélectionnées
EOT;

if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
/**
 * smd_thumbnail
 *
 * A Textpattern CMS plugin for managing multiple image thumbnails:
 *  -> Create unlimited thumbnail profiles of differing sizes for various site uses
 *  -> Thumbnail files are created in your image directory -- no realtime scaling
 *  -> Batch create / alter thumbnails
 *  -> Choose to sync the thumbnails with Textpattern's own thumbs
 *
 * @author Stef Dawson
 * @link   http://stefdawson.com/
 * TODO: Make Selections (lines 912-916) optional via prefs or something
 * TODO: Simplify AJAX response packets to speed things up
 * TODO: Interface elements for Profile creation don't get rendered if there are no images in pool
 */
if (@txpinterface == 'admin') {
	global $smd_thumb_event, $smd_thumb_prevs;

	$smd_thumb_event = 'smd_thumbnail';
	$smd_thumb_privs = '1,2,3'; // Plugin privs
	$smd_thumb_prevs = array('1'); // Privs for prefs

	add_privs($smd_thumb_event, $smd_thumb_privs);
	add_privs('smd_thumb_profiles', $smd_thumb_privs);
	register_callback('smd_thumb_welcome', 'plugin_lifecycle.'.$smd_thumb_event);
	register_callback('smd_thumb_profiles', 'image_ui', 'extend_controls');
	register_callback('smd_thumbs', 'image_ui', 'thumbnail');
	register_callback('smd_thumb_edit', 'image_ui', 'thumbnail_edit');
	register_callback('smd_thumb_empty', 'image_ui', 'thumbnail_image');
	register_callback('smd_thumb_empty', 'image_ui', 'thumbnail_create');
	register_callback('smd_thumb_generate', 'image_uploaded', 'image');
	register_callback('smd_thumb_generate', 'image_uploaded', 'moderate');
	register_callback('smd_thumb_delete', 'image_deleted', 'image');
	register_callback('smd_thumb_create_one', 'image', 'smd_thumb_create_one');
	register_callback('smd_thumb_switch_active', 'image', 'smd_thumb_switch_active');
	register_callback('smd_thumb_switch_pref', 'image', 'smd_thumb_switch_pref');
	register_callback('smd_thumb_insert', 'image', 'smd_thumbnail_insert', 1);
	register_callback('smd_thumb_inject_css', 'admin_side', 'head_end');
} else {
	smd_thumb_set_impath();
}
if (!defined('SMD_THUMB')) {
	define("SMD_THUMB", 'smd_thumbnail');
}
if (!defined('SMD_THUMB_ACTIVE')) {
	define("SMD_THUMB_ACTIVE", 1);
}
if (!defined('SMD_THUMB_CROP')) {
	define("SMD_THUMB_CROP", 2);
}
if (!defined('SMD_THUMB_SHARP')) {
	define("SMD_THUMB_SHARP", 4);
}

// -------------------------------------------------------------
// CSS definitions: hopefully kind to themers
function smd_thumb_get_style_rules() {
	$smd_thumb_styles = array(
		'smd_thumb' =>'
.smd_selected { border:1px solid red; background:red; }
#smd_thumbs img { padding:2px; margin:1px; border:1px solid black; }
.thumbnail-alter { display:none; }
.smd_hidden { display:none; }
#smd_thumb_profiles { max-width:700px; margin:0 auto 18px; }
#smd_thumb_profiles .txp-summary { text-align:left; }
#smd_thumb_batch { float:right; }
.smd_inactive { opacity:.3; }
.smd_thumb_heading_active { cursor:pointer; }
#smd_thumb_profile_form table { width:100% }
.pref-label { text-align: right!important; }
'
);

	return $smd_thumb_styles;
}

// -------------------------------------------------------------
function smd_thumb_inject_css($evt, $stp) {
	global $event;

	if ($event == 'image') {
		$smd_thumb_styles = smd_thumb_get_style_rules();
		echo '<style type="text/css">', $smd_thumb_styles['smd_thumb'], '</style>';
	}

	return;
}

// -------------------------------------------------------------
function smd_thumb_welcome($evt, $stp) {
	switch ($stp) {
		case 'installed':
			smd_thumb_table_install();
			// Remove per-user prefs on upgrade from v0.1x to v0.20
			safe_delete ('txp_prefs', "name IN ('smd_thumb_txp_create', 'smd_thumb_txp_delete', 'smd_thumb_auto_replace') AND user_name != ''");
			break;
		case 'deleted':
			smd_thumb_table_remove();
			break;
	}
	return;
}

// ------------------------
// Display the designated default thumbnail on the list page
function smd_thumbs($evt, $stp, $dflt, $currimg) {
	extract(gpsa(array('page', 'sort', 'dir', 'crit', 'search_method')));

	if (smd_thumb_table_exist()) {
		$default = get_pref('smd_thumb_default_profile', '', 1);
		if ($default) {
			$row = safe_row('*', SMD_THUMB, "name='".sanitizeForUrl($default)."'");
			if ($row) {
				$edit_url = '?event=image'.a.'step=image_edit'.a.'id='.$currimg['id'].a.'sort='.$sort.
						a.'dir='.$dir.a.'page='.$page.a.'search_method='.$search_method.a.'crit='.$crit;
				$out = smd_thumb_img($row, $currimg);
				return ($out) ? href($out, $edit_url) : gTxt('no');
			} else {
				return gTxt('no');
			}
		} else {
			return gTxt('no');
		}
	}
}

// ------------------------
// Don't want the 'create' controls or thumbnail image as they're now both handled in the edit portion of the screen
function smd_thumb_empty($evt, $stp, $dflt, $currimg) {
	return ' ';
}

// ------------------------
// With a lot of images in the database, doing it all in one hit would time out.
// So it's done via ajax; one request per image
function smd_thumb_create_group($type, $lst='') {
	switch ($type) {
		case 'all':
			$where = '1=1';
			break;
		case 'sel':
			$where = ($lst) ? 'id IN ('.doSlash($lst).')' : '1=0';
			break;
		case 'cat':
			$where = "category in ('".doSlash($lst)."')";
			break;
		case 'usr':
			$where = "author in ('".doSlash($lst)."')";
			break;
	}

	$images = safe_column('id', 'txp_image', $where);
	$count = count($images);

	echo '<script type="text/javascript">';
	echo "var bctr = 0; var btot = {$count};";
	echo 'jQuery(function() {';
	echo 'jQuery("#smd_thumb_btot").text("/'.$count.'");';
	foreach ($images as $img) {
		echo <<<EOJS
		sendAsyncEvent(
		{
			event: textpattern.event,
			step: 'smd_thumb_create_one',
			smd_thumb_imgid: {$img}
		}, smd_create_group_feedback);
EOJS;
	}
	echo '});';
	echo <<<EOJS
	function smd_create_group_feedback() {
		bctr++;
		jQuery('#smd_thumb_bcurr').text(bctr);
//		if (bctr >= btot) {
//			jQuery('#smd_thumb_bcurr,#smd_thumb_btot').empty();
//		}
	}
EOJS;
	echo '</script>';
}

// ------------------------
function smd_thumb_create_one() {
	$currimg = gps('smd_thumb_imgid');
	assert_int($currimg);

	$rs = safe_rows('*', SMD_THUMB, '1=1 AND flags & '.SMD_THUMB_ACTIVE);
	$curr = safe_row('*', 'txp_image', "id=".doSlash($currimg));
	if ($rs) {
		smd_thumb_make($rs, $curr, 1);
		send_xml_response();
	}
}

// ------------------------
function smd_thumb_switch_active() {
	$name = doSlash(gps('smd_thumb_profile'));
	if ($name) {
		safe_update(SMD_THUMB, 'flags = flags ^ '.SMD_THUMB_ACTIVE, "name='$name'");
		send_xml_response(array('smd_thumb_profile' => $name));
	}
}

// ------------------------
function smd_thumb_switch_pref() {
	$name = doSlash(gps('smd_thumb_txptype'));
	$state = doSlash(gps('smd_thumb_state'));
	if ($name) {
		set_pref('smd_thumb_'.$name, $state, 'smd_thumb', PREF_HIDDEN, 'text_input');
		send_xml_response();
	}
}

// ------------------------
function smd_thumb_generate($evt, $stp, $id='') {
	$id = ($id) ? $id : gps('id');
	$id = ($id) ? $id : $GLOBALS['ID'];
	$rs = safe_rows('*', SMD_THUMB, '1=1 AND flags & '.SMD_THUMB_ACTIVE);

	smd_thumb_make($rs, $id);
}

// ------------------------
function smd_thumb_delete($evt, $stp) {
	$ids = gps('selected');
	$rs = safe_rows('*', SMD_THUMB, '1=1');

	$images = safe_rows('*', 'txp_image', 'id IN (' . join(',',quote_list($ids)) . ')');
	foreach ($images as $img) {
		smd_thumb_unmake($rs, $img);
	}
}

// ------------------------
function smd_thumb_make($rs, $currimg, $force=0) {
	// Wrapper for wet_thumb to allow multiple thumbnails
	$msg = '';
	smd_thumb_set_impath();

	if (!class_exists('smd_thumb')) {
		class smd_thumb extends wet_thumb {
			var $m_ext;
			var $m_id;
			var $m_dir;
			var $m_dflt;
			var $width;
			var $height;
			var $force;

			// Constructor
			public function __construct ($id, $dir, $img_row, $is_default=false, $pro_w='', $pro_h='', $force=0) {
				$id = assert_int($id);
				if ($img_row) {
					extract($img_row);
					$this->m_id = $id;
					$this->m_ext = $ext;
					$this->m_dir = $dir;
					$this->force = $force;
					$this->width = $pro_w;
					$this->height = $pro_h;
					$this->m_dflt = $is_default;
				}
				parent::__construct();
			}

			public function write_image() {
				if ( !isset($this->m_ext) ) return false;

				$autor = get_pref('smd_thumb_auto_replace', '0');
				$recfrom = get_pref('smd_thumb_create_from', 'full');
				$src_sz = ($recfrom == 'full') ? '' : 't';
				$infile = IMPATH.$this->m_id.$src_sz.$this->m_ext;
				$outfile = IMPATH.$this->m_dir.DS.$this->m_id.$this->m_ext;

				if (!file_exists($outfile) || $autor || $this->force) {
					// If this is the default profile and the pref indicates, write a Txp thumb too
					if (($this->m_dflt == true) && (get_pref('smd_thumb_txp_create', '0'))) {
						$txp_thumb = IMPATH.$this->m_id.'t'.$this->m_ext;
						if ( parent::write ( $infile, $txp_thumb ) ) {
							safe_update('txp_image', "thumbnail = 1, thumb_w = $this->width, thumb_h = $this->height", 'id = '.$this->m_id);
							@chmod($outfile, 0644);
						}
					}
	
					if ( parent::write ( $infile, $outfile ) ) {
						@chmod($outfile, 0644);
						return true;
					}
				}
				return false;
			}
		}
	}

	// If passed only an ID, look up the rest of the image data
	if (!is_array($currimg)) {
		assert_int($currimg);
		$currimg = safe_row('*', 'txp_image', 'id='.$currimg);
	}

	// Create each thumbnail
	$pro_dflt = get_pref('smd_thumb_default_profile', '');
	foreach ($rs as $row) {
		// Sanitize a little
		$width = (int) $row['width'];
		$height = (int) $row['height'];
		if ($width == 0) $width = '';
		if ($height == 0) $height = '';
		if ($width === '' && $height === '') continue;
		$crop = ($row['flags'] & SMD_THUMB_CROP) ? 1 : 0;
		$sharpen = ($row['flags'] & SMD_THUMB_SHARP) ? 1 : 0;
		$id = $currimg['id'];
		$is_dflt = ($row['name'] == $pro_dflt) ? true : false;

		$t = new smd_thumb($id, sanitizeForUrl($row['name']), $currimg, $is_dflt, $width, $height, $force);
		$t->extrapolate = true; // Allow bigger thumbs than original image
		$t->crop = ($crop == '1');
		$t->sharpen = ($sharpen == '1');
		$t->hint = '0';
		$t->width = $width;
		$t->height = $height;
		$t->quality = $row['quality'];

		if ($t->write_image()) {
			$msg = gTxt('thumbnail_saved', array('{id}' => $id));
		} else {
			$msg = array(gTxt('thumbnail_not_saved', array('{id}' => $id)), E_ERROR);
		}
	}
	return $msg;
}

// ------------------------
function smd_thumb_unmake($rs, $currimg) {
	$id = $currimg['id'];
	$ext = $currimg['ext'];
	$pro_dflt = get_pref('smd_thumb_default_profile', '');
	$txp_del = get_pref('smd_thumb_txp_delete', '0');
	smd_thumb_set_impath();

	foreach ($rs as $row) {
		$path = IMPATH.sanitizeForUrl($row['name']).DS.$id.$ext;
		if (file_exists($path)) {
			unlink($path);
		}

		// Also remove Txp's built-in thumb?
		if (($row['name'] == $pro_dflt) && ($txp_del == '1')) {
			$path = IMPATH.$id.'t'.$ext;
			if (file_exists($path)) {
				safe_update('txp_image', "thumbnail = 0, thumb_w = 0, thumb_h = 0", 'id = '.$id);
				unlink($path);
			}
		}
	}
	return '';
}

// ------------------------
function smd_thumb_insert() {
	global $txpcfg, $txp_user, $page, $sort, $dir, $crit, $search_method;

	smd_thumb_set_impath();
	extract(gpsa(array('page', 'sort', 'dir', 'crit', 'search_method')));

	include_once txpath.'/lib/txplib_misc.php';

	extract($txpcfg);
	$id = assert_int(gps('id'));
	$profile = gps('smd_thumb_profile');
	$thumb_ext = gps('smd_thumb_ext');

	$author = fetch('author', 'txp_image', 'id', $id);
	if (!has_privs('image.edit') && !($author == $txp_user && has_privs('image.edit.own'))) {
		return;
	}

	$file = $_FILES['thefile']['tmp_name'];
	$name = $_FILES['thefile']['name'];

	$file = get_uploaded_file($file);

	if (empty($file))
	{
		return;
	}

	list($w, $h, $extension) = getimagesize($file);
	$valid_exts = array(
		IMAGETYPE_GIF  => '.gif',
		IMAGETYPE_JPEG => '.jpg',
		IMAGETYPE_PNG  => '.png',
		IMAGETYPE_SWF  => '.swf',
	);
	$ext = isset($valid_exts[$extension]) ? $valid_exts[$extension] : '';

	if (($file !== false) && $profile && $ext) {
		$newpath = IMPATH.sanitizeForUrl($profile).DS.$id.$ext;
		if (shift_uploaded_file($file, $newpath) == false) {
		} else {
			@chmod($newpath, 0644);

			// If the pref indicates, duplicate as a Txp thumb too
			if (get_pref('smd_thumb_txp_create', '0') == 1) {
				$txp_thumb = IMPATH.$id.'t'.$ext;
				if ( copy ( $newpath, $txp_thumb ) ) {
					safe_update('txp_image', "thumbnail = 1, thumb_w = $w, thumb_h = $h", 'id = '.$id);
					@chmod($txp_thumb, 0644);
				}
			}
			$message = gTxt('image_uploaded', array('{name}' => $name));
		}
	}

	// Since the headers have been sent, resort to js to refresh the page
	$url = '?event=image'.a.'step=image_edit'.a.'id='.$id.a.'sort='.$sort.a.'dir='.$dir.a.'page='.$page.a.'search_method='.$search_method.a.'crit='.$crit;
	echo <<<EOS
<script type="text/javascript">
window.location.href="{$url}";
</script>
<noscript>
<meta http-equiv="refresh" content="0;url={$url}" />
</noscript>
EOS;
	exit;
}

// ------------------------
function smd_thumb_edit($evt, $stp, $dflt, $currimg) {
	global $step, $file_max_upload_size, $txp_user;

	extract(gpsa(array(
		'id',
		'page',
		'sort',
		'dir',
		'crit',
		'search_method',
		'smd_thumbnail_size',
		'smd_thumbnail_chosen_size',
		'smd_thumbnail_delete',
		'smd_step'
	)));

	$id = ($id) ? $id : $GLOBALS['ID'];

	// Toggle profile panel
	if ($step == 'save_pane_state') {
		smd_thumbnail_save_pane_state();
		return;
	}

	// Create/delete the selected thumbs depending on the button pressed
	if ($smd_step == 'smd_thumbnail_manage') {
		// Validate user
		$author = fetch('author', 'txp_image', 'id', $id);
		if (!has_privs('image.edit') && !($author == $txp_user && has_privs('image.edit.own')))
		{
			image_list(gTxt('restricted_area'));
			return;
		}

		// Grab the thumbnails to work on
		$where = ($smd_thumbnail_size=='all') ? '1=1 AND flags & '.SMD_THUMB_ACTIVE : "name='".doSlash($smd_thumbnail_chosen_size)."'";
		$rs = safe_rows('*', SMD_THUMB, $where);

		// Do it
		if($smd_thumbnail_delete) {
			$msg = smd_thumb_unmake($rs, $currimg);
		} else {
			$msg = smd_thumb_make($rs, $currimg, 1);
		}
	}

	$ext = $currimg['ext'];
	echo script_js(<<<EOC
function smd_thumb_selector(sel) {
	var idx = 0;
	jQuery("#smd_thumbs img").each(function() {
		if (jQuery(this).hasClass(sel)) {
			jQuery(this).toggleClass('smd_selected');
			if (jQuery(this).hasClass('smd_selected')) {
				jQuery("#smd_upload_thumbnail").attr('disabled', false);
				jQuery("#smd_thumb_profile").val(sel);
				idx = jQuery("#smd_thumbnail_size option[value='"+sel+"']").index();
				jQuery("#smd_thumbnail_chosen_size").val(sel);
			} else {
				jQuery("#smd_upload_thumbnail").attr('disabled', true);
				jQuery("#smd_thumb_profile").val('');
				jQuery("#smd_thumbnail_chosen_size").val('');
			}
		} else {
			jQuery(this).removeClass('smd_selected');
		}
	});
	jQuery("#smd_thumbnail_size").prop("selectedIndex", idx);
}
function smd_thumb_select_changed() {
	obj = jQuery("#smd_thumbnail_size");
	if (obj.attr("selectedIndex") == 0) {
		jQuery("#smd_upload_thumbnail").attr('disabled', true);
		jQuery("#smd_thumb_profile").val('');
		jQuery("#smd_thumbnail_chosen_size").val('');
	} else {
		jQuery("#smd_upload_thumbnail").attr('disabled', false);
		jQuery("#smd_thumb_profile").val(obj.val());
		jQuery("#smd_thumbnail_chosen_size").val(obj.val());
	}
	jQuery("#smd_thumbs img").each(function() {
		if (jQuery(this).hasClass(obj.val())) {
			jQuery(this).addClass('smd_selected');
		} else {
			jQuery(this).removeClass('smd_selected');
		}
	});
}
jQuery(function() {
	jQuery("#smd_thumbs img").each(function() {
		var cls = jQuery(this).attr('class');
		jQuery(this).click(function() {
			smd_thumb_selector(cls);
		});
	});
	jQuery("#smd_upload_thumbnail").attr('disabled', true);
	jQuery(".thumbnail-upload").prepend('<input type="hidden" name="smd_thumb_imgid" value="{$id}" /><input type="hidden" id="smd_thumb_profile" name="smd_thumb_profile" value="" /><input type="hidden" id="smd_thumb_ext" name="smd_thumb_ext" value="{$ext}" />');
});
EOC
	);

	// Add thumbnails and creation controls
	if (smd_thumb_table_exist()) {
		$rs = safe_rows('*', SMD_THUMB, '1=1 ORDER BY name');
		if ($rs) {
			$profiles = array('all' => gTxt('smd_thumb_all_sizes'));
			$thumbs[] = '<div id="smd_thumbs">';
			foreach ($rs as $row) {
				if ($row['flags'] & SMD_THUMB_ACTIVE) {
					$profiles[$row['name']] = $row['name'];
				}
				$thumbs[] = smd_thumb_img($row, $currimg);
			}
			$thumbs[] = '</div>';
			$qs = array(
				"event" => 'image',
				"step" => 'image_edit',
				"id" => $id,
				"page" => $page,
				"sort" => $sort,
				"dir" => $dir,
				"crit" => $crit,
				"search_method" => $search_method,
			);

			$out[] = '<div id="smd_thumbnail_control"><span class="txp-summary lever'.(get_pref('pane_smd_thumbnails_visible') ? ' expanded' : '').'"><a href="#smd_thumbnails">'.gTxt('smd_thumb_thumbnail_heading').'</a></span><div id="smd_thumbnails" class="toggle" style="display:'.(get_pref('pane_smd_thumbnails_visible') ? 'block' : 'none').'">';
			$out[] = '<form name="smd_thumbnail_create" method="post" action="'.join_qs($qs).'">';
			$out[] = fInput('hidden', 'smd_step', 'smd_thumbnail_manage');
			$out[] = fInput('hidden', 'smd_thumbnail_chosen_size', '', '', '', '', '', '', 'smd_thumbnail_chosen_size');
			$out[] = selectInput('smd_thumbnail_size', $profiles, '', '', ' onchange="return smd_thumb_select_changed()";', 'smd_thumbnail_size');
			$out[] = fInput('submit', '', gTxt('create'));
			$out[] = fInput('submit', 'smd_thumbnail_delete', gTxt('delete'));
			$out[] = join('', $thumbs);
			$out[] = '</form>';
			$out[] = upload_form(gTxt('smd_thumb_upload'), 'upload_thumbnail', 'smd_thumbnail_insert', 'image', $id, $file_max_upload_size, 'smd_upload_thumbnail', 'thumbnail-upload');
			$out[] = '</div>';
			return join('', $out);
		}
	}
	return ' ';
}

// ------------------------
function smd_thumb_img($row, $currimg, $meta=array(), $dsp='') {
	global $img_dir, $smd_thumb_data;

	smd_thumb_set_impath();

	$dir = sanitizeForUrl($row['name']);
	$id = $currimg['id'];
	$ext = $currimg['ext'];
	// alt is a mandatory attribute so make sure it exists (even if it ="")
	if (!isset($meta['alt'])) {
		$meta['alt'] = $currimg['alt'];
	}

	$path = IMPATH.$dir.DS.$id.$ext;

	if (file_exists($path)) {
		$extras = '';
		if (isset($meta['forcew']) || isset($meta['forceh'])) {
			$dims = getimagesize($path);
			if (isset($meta['forcew']) && !$row['width']) {
				$row['width'] = $dims[0];
			}
			if (isset($meta['forceh']) && !$row['height']) {
				$row['height'] = $dims[1];
			}
			unset($meta['forcew']);
			unset($meta['forceh']);
		}

		if (isset($meta['width']) && $meta['width'] !== '') {
			$row['width'] = $meta['width'];
		}
		if (isset($meta['height']) && $meta['height'] !== '') {
			$row['height'] = $meta['height'];
		}
		unset($meta['width']);
		unset($meta['height']);

		// 'empty' includes zero dimensions which omits the attribute and allows the browser to scale
		$w = (!empty($row['width'])) ? ' width="'.$row['width'].'"': '';
		$h = (!empty($row['height'])) ? ' height="'.$row['height'].'"': '';

		$uDate = '';
		if (!isset($meta['stamp'])) {
			$uDate = '?'.filemtime($path);
		}
		unset($meta['stamp']);

		if (!isset($meta['class'])) {
			$meta['class'] = $dir;
		}

		foreach ($meta as $key => $val) {
			// We need all atts for container tags, but only valid HTML atts should appear in the default <img> tag
			if (in_array($key, array('alt', 'class', 'title'))) {
				$extras .= ' '.$key.'="'.$val.'"';
			}
		}

		if ($dsp) {
			// Hand off to the form/container for formatting advice
			$smd_thumb_data = $meta;
			$smd_thumb_data['id'] = $id;
			$smd_thumb_data['ext'] = $ext;
			$smd_thumb_data['alt'] = $alt;
			$smd_thumb_data['w'] = $row['width'];
			$smd_thumb_data['h'] = $row['height'];
			$smd_thumb_data['html_w'] = $w;
			$smd_thumb_data['html_h'] = $h;
			return parse($dsp);
		} else {
			return '<img src="'.ihu.$img_dir.'/'.$dir.'/'.$id.$ext.$uDate.'"'.$w.$h.$extras.' />';
		}
	}

	return '';
}

// ------------------------
function smd_thumb_profiles($evt, $stp, $dflt, $imglist) {
	global $smd_thumb_event, $step, $smd_thumb_prevs, $txp_user;

	if (!has_privs(__FUNCTION__)) {
		return;
	}

	extract(gpsa(array(
		'page',
		'sort',
		'dir',
		'crit',
		'search_method',
		'smd_thumb_add',
		'smd_thumb_cancel',
		'smd_thumb_save',
		'smd_thumb_name',
		'smd_thumb_newname',
		'smd_thumb_width',
		'smd_thumb_height',
		'smd_thumb_quality',
		'smd_thumb_active',
		'smd_thumb_active_new',
		'smd_thumb_crop',
		'smd_thumb_sharpen',
		'smd_thumb_default',
		'smd_thumb_selected',
		'smd_thumb_cat_selected',
		'smd_thumb_usr_selected',
		'smd_thumb_group_type',
	)));

	// Sanitize
	$quality = (is_numeric($smd_thumb_quality)) ? (($smd_thumb_quality < 0) ? 75 : (($smd_thumb_quality > 100) ? 75 : $smd_thumb_quality) ) : 75;
	$width = (is_numeric($smd_thumb_width)) ? $smd_thumb_width : 0;
	$height = (is_numeric($smd_thumb_height)) ? $smd_thumb_height : 0;
	$newname = sanitizeForUrl($smd_thumb_newname);
	$msg = '';

	smd_thumb_set_impath();

	// Table control
	if (gps('remove')) {
		smd_thumb_table_remove();
	} else {
		if (!smd_thumb_table_exist()) {
			smd_thumb_table_install();
		}
	}

	// Toggle profile panel
	if ($step == 'save_pane_state') {
		smd_thumbnail_save_pane_state();
		return;
	}

	if ($step == 'smd_thumb_create_group') {
		switch ($smd_thumb_group_type) {
			case 'all':
				smd_thumb_create_group('all');
				break;
			case 'sel':
				smd_thumb_create_group('sel', rtrim($smd_thumb_selected, ','));
				break;
			case 'cat':
				smd_thumb_create_group('cat', $smd_thumb_cat_selected);
				break;
			case 'usr':
				smd_thumb_create_group('usr', $smd_thumb_usr_selected);
				break;
		}
		set_pref('pane_smd_thumb_group_type', $smd_thumb_group_type, 'smd_thumb', PREF_HIDDEN, 'text_input', 0, PREF_PRIVATE);
		$step = 'smd_thumb_prefs';
	}

	// Save profile
	if ($step == 'smd_thumb_profile_save') {
		if (smd_thumb_table_exist()) {
			if ($smd_thumb_cancel) {
				// Do nothing
			} else if ($smd_thumb_add) {
				// Create new profile
				$exists = safe_row('*', SMD_THUMB, "name='".doSlash($newname)."'");
				if ($exists) {
					$msg = gTxt('smd_thumb_profile_exists', array('{name}' => doSlash($newname)));
				} else {
					if ($newname) {
						$flags = 0;
						$flags = ($smd_thumb_active_new) ? $flags | SMD_THUMB_ACTIVE : $flags;
						$flags = ($smd_thumb_crop) ? $flags | SMD_THUMB_CROP : $flags;
						$flags = ($smd_thumb_sharpen) ? $flags | SMD_THUMB_SHARP : $flags;

						safe_insert(SMD_THUMB,
							"name='".doSlash($newname)."'".
							", width='".doSlash($width)."'".
							", height='".doSlash($height)."'".
							", quality='".doSlash($quality)."'".
							", flags='".doSlash($flags)."'"
						);

						// Alter the default thumbnail
						if ($smd_thumb_default) {
							set_pref('smd_thumb_default_profile', $newname, 'smd_thumb', PREF_HIDDEN);
						}

						// Create the thumbnail directory
						@mkdir(IMPATH.$newname);
					}
				}
			} else {
				// Update existing profile
				$name = sanitizeForUrl($smd_thumb_name);
				$exists = safe_row('*', SMD_THUMB, "name='".doSlash($newname)."'");
				if ($exists && strtolower($newname) != strtolower($name)) {
					$msg = gTxt('smd_thumb_profile_exists', array('{name}' => doSlash($newname)));
				} else {
					if ($newname) {
						$flags = 0;
						$flags = ($smd_thumb_active) ? $flags | SMD_THUMB_ACTIVE : $flags;
						$flags = ($smd_thumb_crop) ? $flags | SMD_THUMB_CROP : $flags;
						$flags = ($smd_thumb_sharpen) ? $flags | SMD_THUMB_SHARP : $flags;

						safe_update(
							SMD_THUMB,
							"name='".doSlash($newname)."'"
								.", width='".doSlash($width)."'"
								.", height='".doSlash($height)."'"
								.", quality='".doSlash($quality)."'"
								.", flags='".doSlash($flags)."'"
							, "name='".doSlash($name)."'"
						);

						// Alter the default thumbnail
						if ($smd_thumb_default) {
							set_pref('smd_thumb_default_profile', $newname, 'smd_thumb', PREF_HIDDEN);
						} else {
							// Remove the default flag if this used to be the default
							if (get_pref('smd_thumb_default_profile') == doSlash($name)) {
								set_pref('smd_thumb_default_profile', '', 'smd_thumb', PREF_HIDDEN);
							}
						}

						// Adjust the thumbnail directory if the name has changed
						if ($newname != $name) {
							rename(IMPATH.$name, IMPATH.$newname);
						}
					}
				}
			}
		} else {
			$msg = gTxt('smd_thumb_tables_not_installed');
		}
		$step = 'smd_thumb_profile';
	}

	// Delete profile
	if ($step == 'smd_thumb_profile_delete') {
		if (smd_thumb_table_exist()) {
			$txp_del = get_pref('smd_thumb_txp_delete');
			$the_dflt = get_pref('smd_thumb_default_profile', '', 1);

			if (!empty($smd_thumb_name)) {
				$name = sanitizeForUrl($smd_thumb_name);
				$ret = safe_delete(SMD_THUMB, "name='".doSlash($name)."'");
				if ($ret) {
					smd_thumb_rmdir(IMPATH.$name);

					// Delete all Txp thumbs too?
					if (($name == $the_dflt) && ($txp_del == '1')) {
						$rs = safe_rows('id, ext', 'txp_image', 'thumbnail = 1');
						foreach ($rs as $row) {
							$path = IMPATH.$row['id'].'t'.$row['ext'];
							if (file_exists($path)) {
								unlink($path);
							}
						}
						safe_update('txp_image', 'thumbnail = 0, thumb_w = 0, thumb_h = 0', 'thumbnail = 1');
					}

					// Remove the default flag if this used to be the default
					if (get_pref('smd_thumb_default_profile') == doSlash($name)) {
						set_pref('smd_thumb_default_profile', '', 'smd_thumb', PREF_HIDDEN);
					}

					$msg = gTxt('smd_thumb_profile_deleted', array('{name}' => doSlash($name)));
				}
			}
		}
		$step = 'smd_thumb_profile';
	}

	$qs = array(
		"event" => 'image',
		"page" => $page,
		"sort" => $sort,
		"dir" => $dir,
		"crit" => $crit,
		"search_method" => $search_method,
	);

	$privs = safe_field('privs', 'txp_users', "name = '".doSlash($txp_user)."'");
	$rights = in_array($privs, $smd_thumb_prevs) ? true : false;

	if ($step == 'smd_thumb_prefs') {

		// ** THUMBNAIL PREFS **

		if (smd_thumb_table_exist()) {
			$pro_dflt = get_pref('smd_thumb_default_profile', '', 1);

			// Subselect lists for type 'cat' and 'usr'
			$rs = getTree('root', 'image');
			$allCats = $rs ? treeSelectInput('smd_thumb_cat_selected', $rs, '') : '';
			$allCats = "<span>".str_replace(array("\n", '-'), array('', '&#45;'), str_replace('</', '<\/', addslashes($allCats)))."<\/span>";
			$rs = safe_column('name', 'txp_users', "privs not in(0,6) order by name asc");
			$allUsrs = $rs ? selectInput('smd_thumb_usr_selected', $rs, '', true) : '';
			$allUsrs = "<span>".str_replace(array("\n", '-'), array('', '&#45;'), str_replace('</', '<\/', addslashes($allUsrs)))."<\/span>";

			echo script_js(<<<EOC
function smd_thumb_switch_pref(obj, name) {
	if (name == 'create_from') {
		state = jQuery(obj).val();
	} else {
		state = (jQuery(obj).prop('checked')) ? 1 : 0;
	}
	sendAsyncEvent(
	{
		event: textpattern.event,
		step: 'smd_thumb_switch_pref',
		smd_thumb_txptype: name,
		smd_thumb_state: state
	});
}
function smd_thumb_copy_selected() {
	jQuery("#images_form tbody .multi-edit input:checked, #images_form tbody .txp-list-col-multi-edit input:checked").each(function() {
		jQuery("#smd_thumb_selected").val(jQuery("#smd_thumb_selected").val() + jQuery(this).val() + ',');
	});
}
function smd_thumb_subsel(obj) {
	obj = jQuery(obj);
	dest = jQuery('#smd_thumb_subsel');
	item = obj.val();
	switch (item) {
		case 'all':
		case 'sel':
			dest.empty();
			break;
		case 'cat':
			dest.empty().prepend('{$allCats}');
			break;
		case 'usr':
			dest.empty().prepend('{$allUsrs}');
			break;
	}
}
jQuery(function() {
	jQuery('#smd_thumb_group_type').change();
});
EOC
		);

			$pnlbtn = '<a class="navlink" href="?event=image'.a.'sort='.$sort.a.'dir='.$dir.a.'page='.$page.a.'search_method='.$search_method.a.'crit='.$crit.'">'.gTxt('smd_thumb_btn_pnl').'</a>';
			$grpbtn = '<button class="navlink" type="submit" onclick="smd_thumb_copy_selected(); return confirm(\''.gTxt('smd_thumb_create_group_confirm').'\');">'.gTxt('smd_thumb_all_thumbs').'</button>';
			$grpTypes = array(
				'sel' => gTxt('smd_thumb_bysel'),
				'cat' => gTxt('smd_thumb_bycat'),
				'usr' => gTxt('smd_thumb_byusr'),
				'all' => gTxt('smd_thumb_byall'),
			);

			$grpOpts = selectInput('smd_thumb_group_type', $grpTypes, get_pref('pane_smd_thumb_group_type', 'all', 1), '', ' onchange="smd_thumb_subsel(this);"', 'smd_thumb_group_type').'<span id="smd_thumb_subsel"></span>';

			$out[] = '<div id="smd_thumb_profiles"><div class="txp-summary lever'.(get_pref('pane_smd_thumbnail_profiles_visible') ? ' expanded' : '').'"><a href="#smd_thumbnail_profiles">'.(($rights) ? gTxt('smd_thumb_profile_preftool_heading') : gTxt('smd_thumb_profile_tool_heading')).'</a></div><div id="smd_thumbnail_profiles" class="toggle" style="display:'.(get_pref('pane_smd_thumbnail_profiles_visible') ? 'block' : 'none').'">';
			$out[] = '<div class="txp-buttons">'.$pnlbtn.'</div>';

			$out[] = '<div><div>'.gTxt('smd_thumb_batch_legend').'</div>';
			$out[] = '<div id="smd_thumb_batch"><span id="smd_thumb_bcurr"></span><span id="smd_thumb_btot"></span></div>';
			$out[] = '<form method="post" name="smd_thumb_multi_edit" id="smd_thumb_multi_edit" action="'.join_qs($qs).'">';
			$out[] = startTable();
			$out[] = tr(td(gTxt('smd_thumb_batch_preamble').$grpbtn.$grpOpts));
			$out[] = endTable();
			$out[] = fInput('hidden', 'smd_thumb_selected', '', '', '', '', '', '', 'smd_thumb_selected');
			$out[] = eInput($smd_thumb_event);
			$out[] = sInput('smd_thumb_create_group');
			$out[] = '</form>';
			$out[] = '</div>';

			if ($rights) {
				$out[] = '<div><div>'.gTxt('smd_thumb_prefs').'</div>';
				$out[] = '<form method="post" name="smd_thumb_prefs" id="smd_thumb_prefs_form" action="'.join_qs($qs).'">';
				$out[] = startTable();

				$txp_rf = get_pref('smd_thumb_create_from', 'full');
				$out[] = tr(
					tda(gTxt('smd_thumb_txp_create_from'), ' class="pref-label"') .
					tda(
						'<input type="radio" name="smd_thumb_create_from" value="full"'.(($txp_rf=='full') ? ' checked="checked"' : ''). ' class="radio" onchange="smd_thumb_switch_pref(this, \'create_from\');" />'.gTxt('smd_thumb_txp_create_from_full')
						.'<input type="radio" name="smd_thumb_create_from" value="thumb"'.(($txp_rf=='thumb') ? ' checked="checked"' : ''). ' class="radio" onchange="smd_thumb_switch_pref(this, \'create_from\');" />'.gTxt('smd_thumb_txp_create_from_thumb')
					));

				if ($pro_dflt) {
					$txp_c = get_pref('smd_thumb_txp_create');
					$txp_d = get_pref('smd_thumb_txp_delete');
					$out[] = tr(
						tda(gTxt('smd_thumb_txp_default_sync'), ' class="pref-label"') .
						tda(
							'<input type="checkbox" name="smd_thumb_txp_create" value="1"'.(($txp_c) ? ' checked="checked"' : ''). ' class="checkbox" onchange="smd_thumb_switch_pref(this, \'txp_create\');" />'.
							gTxt('smd_thumb_create').
							'<input type="checkbox" name="smd_thumb_txp_delete" value="1"'.(($txp_d) ? ' checked="checked"' : ''). ' class="checkbox" onchange="smd_thumb_switch_pref(this, \'txp_delete\');" />'.
							gTxt('smd_thumb_delete')
						));
				}

				$txp_ar = get_pref('smd_thumb_auto_replace');
				$out[] = tr(
					tda(gTxt('smd_thumb_txp_auto_replace'), ' class="pref-label noline"') .
					tda(
						'<input type="checkbox" name="smd_thumb_auto_replace" value="1"'.(($txp_ar) ? ' checked="checked"' : ''). ' class="checkbox" onchange="smd_thumb_switch_pref(this, \'auto_replace\');" />'
					, ' class="noline"'));

				$out[] = endTable();
				$out[] = '</form></div>';
			}
			$out[] = '</div></div>';
		}
	} else {

		// ** THUMBNAIL PROFILES **

		$editFocus = ($step == 'smd_thumb_profile_edit') ? 'jQuery("#smd_thumb_profile_edited input[name=\'smd_thumb_newname\']").focus();' : '';

		echo script_js(<<<EOC
function smd_thumb_togglenew() {
	box = jQuery("#smd_thumb_profile_create");
	if (box.css("display") == "none") {
		box.show();
	} else {
		box.hide();
	}
	jQuery("input.smd_focus").focus();
	return false;
}
function smd_thumb_switch_active(name) {
	sendAsyncEvent(
	{
		event: textpattern.event,
		step: 'smd_thumb_switch_active',
		smd_thumb_profile: name
	}, smd_thumb_switch_active_done);
}
function smd_thumb_switch_active_done(data) {
	var smd_thumb_profile = jQuery('#smd_thumb_profile_'+jQuery(data).find('smd_thumb_profile').attr('value'));
	smd_thumb_profile.toggleClass('smd_inactive');
	if (smd_thumb_profile.hasClass('smd_inactive')) {
		smd_thumb_profile.find('input:checkbox').removeAttr('checked');
	} else {
		smd_thumb_profile.find('input:checkbox').attr('checked', 'checked');
	}
}

jQuery(function() {
	jQuery('.smd_thumb_heading_active').click(function() {
		jQuery('.smd_thumb_row input:checkbox').each(function() {
			smd_thumb_switch_active(jQuery(this).parent().attr('class'));
		});
	});
	{$editFocus}
});
EOC
		);

		$newbtn = '<a class="navlink" href="#" onclick="return smd_thumb_togglenew();">'.gTxt('smd_thumb_new').'</a>';
		$prefbtn = '<a class="navlink" href="?event=image'.a.'step=smd_thumb_prefs'.a.'sort='.$sort.a.'dir='.$dir.a.'page='.$page.a.'search_method='.$search_method.a.'crit='.$crit.'">'.(($rights) ? gTxt('smd_thumb_btn_tools_prefs') : gTxt('smd_thumb_btn_tools')).'</a>';

		$headings = n.'<thead>'.tr(
			n.column_head('name', 'name', 'image', false, 'asc').
			column_head('thumb_width', 'width', 'image', false).
			column_head('thumb_height', 'height', 'image', false).
			column_head(gTxt('smd_thumb_quality'), 'quality', 'image', false).
			column_head('keep_square_pixels', 'crop', 'image', false).
			column_head(gTxt('smd_thumb_sharpen'), 'sharpen', 'image', false).
			column_head('active', 'active', 'image', false, '', '', '', 'smd_thumb_heading_active').
			column_head('default', 'default', 'image', false).
			column_head(gTxt('smd_thumb_actions'), 'actions', 'image', false)
		).'</thead>';

		$out[] = '<div id="smd_thumb_profiles"><div class="txp-summary lever'.(get_pref('pane_smd_thumbnail_profiles_visible') ? ' expanded' : '').'"><a href="#smd_thumbnail_profiles">'.gTxt('smd_thumb_profile_heading').'</a></div><div id="smd_thumbnail_profiles" class="toggle" style="display:'.(get_pref('pane_smd_thumbnail_profiles_visible') ? 'block' : 'none').'">';
		$out[] = '<div class="txp-buttons">'.$newbtn.n.$prefbtn.'</div>';

		// Main list of profiles
		$out[] = '<form method="post" name="smd_thumb_profile_form" id="smd_thumb_profile_form" action="'.join_qs($qs).'">';
		$out[] = startTable();
		$out[] = $headings;

		if (smd_thumb_table_exist()) {
			$rs = safe_rows('*', SMD_THUMB, '1=1 ORDER BY name');
			$pro_dflt = get_pref('smd_thumb_default_profile', '', 1);

			// New Profile row
			$out[]= '<tr id="smd_thumb_profile_create" class="smd_hidden">';
			$out[] = td(sInput('smd_thumb_profile_save').fInput('text', 'smd_thumb_newname', (($step=='smd_thumb_profile_save') ? $smd_thumb_name : ''), 'smd_focus'))
				.td(fInput('text', 'smd_thumb_width', (($step=='smd_thumb_profile_save') ? $width : ''), '', '', '', '4'))
				.td(fInput('text', 'smd_thumb_height', (($step=='smd_thumb_profile_save') ? $height : ''), '', '', '', '4'))
				.td(fInput('text', 'smd_thumb_quality', (($step=='smd_thumb_profile_save') ? $quality : ''), '', '', '', '4'))
				.td(checkbox('smd_thumb_crop', '1', (($step=='smd_thumb_profile_save') ? $smd_thumb_crop : 0)))
				.td(checkbox('smd_thumb_sharpen', '1', (($step=='smd_thumb_profile_save') ? $smd_thumb_sharpen : 0)))
				.td(checkbox('smd_thumb_active_new', '1', 1))
				.td(checkbox('smd_thumb_default', '1', 0))
				.td(fInput('submit', 'smd_thumb_add', gTxt('add')));
			$out[]= '</tr>';

			// Remaining rows
			foreach ($rs as $row) {
				$link_edt = join_qs($qs).a.'step=smd_thumb_profile_edit'.a.'smd_thumb_name='.$row['name'];
				$link_del = join_qs($qs).a.'step=smd_thumb_profile_delete'.a.'smd_thumb_name='.$row['name'];
				$btnEdt = '<a href="'.$link_edt.'">[' . gTxt('edit') . ']</a>';
				$btnDel = '<a href="'.$link_del.'" onclick="return confirm(\''.gTxt('smd_thumb_delete_confirm', array("{name}" => $row['name'])).'\');">[' . gTxt('delete') . ']</a>';
				$active = ($row['flags'] & SMD_THUMB_ACTIVE) ? 1 : 0;
				$crop = ($row['flags'] & SMD_THUMB_CROP) ? 1 : 0;
				$sharpen = ($row['flags'] & SMD_THUMB_SHARP) ? 1 : 0;

				if ($step == 'smd_thumb_profile_edit' && $row['name'] == $smd_thumb_name) {
					$btnSave = fInput('submit', 'smd_thumb_save', gTxt('Save'));
					$btnCancel = fInput('submit', 'smd_thumb_cancel', gTxt('Cancel'));
					$out[] = tr(
						td(hInput('smd_thumb_name', $row['name']).fInput('text', 'smd_thumb_newname', $row['name']))
						.td(fInput('text', 'smd_thumb_width', $row['width'], '', '', '', '4'))
						.td(fInput('text', 'smd_thumb_height', $row['height'], '', '', '', '4'))
						.td(fInput('text', 'smd_thumb_quality', $row['quality'], '', '', '', '4'))
						.td(checkbox('smd_thumb_crop', '1', $crop))
						.td(checkbox('smd_thumb_sharpen', '1', $sharpen))
						.td(checkbox('smd_thumb_active', '1', $active), '', $row['name'])
						.td(checkbox('smd_thumb_default', '1', (($row['name'] == $pro_dflt) ? 1 : 0)))
						.td($btnSave.$btnCancel)
					, ' id="smd_thumb_profile_edited"');
				} else {
					$out[] = tr(
						td(href($row['name'], $link_edt)).
						td($row['width']).
						td($row['height']).
						td($row['quality']).
						td(($crop) ? gTxt('yes') : gTxt('no')).
						td(($sharpen) ? gTxt('yes') : gTxt('no')).
						td('<input type="checkbox" name="smd_thumb_active_'.$row['name'].'" value="1"'.(($active) ? ' checked="checked"' : ''). ' class="checkbox" onchange="smd_thumb_switch_active(\''.$row['name'].'\');" />', '', $row['name']).
						td(($row['name'] == $pro_dflt) ? gTxt('yes') : gTxt('no')).
						td($btnDel)
					, ' id="smd_thumb_profile_'.$row['name'].'" class="smd_thumb_row' . (($active) ? '' : ' smd_inactive').'"');
				}
			}
		}

		$out[] = endTable();
		$out[] = '</form></div></div>';
	}

	return join('', $out);
}

// ------------------------
// Useful function for other plugins to use to retrieve a list of current profiles
function smd_thumb_get_profiles($active=1, $order='name') {
	$allowedOrders = array('name', 'width', 'height','quality');
	$order = in_array($order, $allowedOrders) ? $order : 'name';

	$where[] = '1=1';
	if ($active) {
		$where[] = 'flags & '.SMD_THUMB_ACTIVE;
	}

	$rs = safe_rows('*', SMD_THUMB, join(' AND ', $where) . ' ORDER BY '.$order);

	return $rs;
}

// ------------------------
// Delete a thumbnail directory and its contents
function smd_thumb_rmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir.DS.$object) == "dir") smd_thumb_rmdir($dir.DS.$object); else unlink($dir.DS.$object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

// ------------------------
function smd_thumb_set_impath() {
	global $path_to_site, $img_dir;
	if (!defined('IMPATH')) {
		define('IMPATH', $path_to_site.DS.$img_dir.DS);
	}
}

// ------------------------
// Add thumbnail table if not already installed
// Flags: Bit 0=Active / 1=Crop / 2 = Sharpen
function smd_thumb_table_install() {
	$GLOBALS['txp_err_count'] = 0;
	$ret = '';
	$sql = array();
	$sql[] = "CREATE TABLE IF NOT EXISTS `".PFX.SMD_THUMB."` (
		`name` varchar(32) NOT NULL default '',
		`width` int(4) NOT NULL default '0',
		`height` int(4) NOT NULL default '0',
		`quality` tinyint(2) NULL default '75',
		`flags` tinyint(2) NOT NULL default '0',
		PRIMARY KEY (`name`)
	) ENGINE=MyISAM";

	if(gps('debug')) {
		dmp($sql);
	}
	foreach ($sql as $qry) {
		$ret = safe_query($qry);
		if ($ret===false) {
			$GLOBALS['txp_err_count']++;
			echo "<b>".$GLOBALS['txp_err_count'].".</b> ".mysql_error()."<br />\n";
			echo "<!--\n $qry \n-->\n";
		}
	}

	// Backup the current thumb prefs
	$exists = safe_row('*', 'txp_prefs', "name='smd_thumb_backup_w'");
	if (!$exists) {
		set_pref('smd_thumb_backup_w', get_pref('thumb_w'), 'smd_thumb', PREF_HIDDEN, 'text_input', 0);
		set_pref('smd_thumb_backup_h', get_pref('thumb_h'), 'smd_thumb', PREF_HIDDEN, 'text_input', 1);
		set_pref('smd_thumb_backup_c', get_pref('thumb_crop'), 'smd_thumb', PREF_HIDDEN, 'text_input', 2);

		// Erase the current thumb prefs
		set_pref('thumb_w', '', 'image', PREF_HIDDEN);
		set_pref('thumb_h', '', 'image', PREF_HIDDEN);
		set_pref('thumb_crop', '', 'image', PREF_HIDDEN);
	}
}

// ------------------------
// Drop table if in database
function smd_thumb_table_remove() {
	$ret = '';
	$sql = array();
	$GLOBALS['txp_err_count'] = 0;
	if (smd_thumb_table_exist()) {
		$sql[] = "DROP TABLE IF EXISTS " .PFX.SMD_THUMB. "; ";
		if(gps('debug')) {
			dmp($sql);
		}
		foreach ($sql as $qry) {
			$ret = safe_query($qry);
			if ($ret===false) {
				$GLOBALS['txp_err_count']++;
				echo "<b>".$GLOBALS['txp_err_count'].".</b> ".mysql_error()."<br />\n";
				echo "<!--\n $qry \n-->\n";
			}
		}
	}

	// Restore the original thumb prefs
	$exists = safe_row('*', 'txp_prefs', "name='smd_thumb_backup_w'");
	if ($exists) {
		set_pref('thumb_w', get_pref('smd_thumb_backup_w'), 'image', PREF_HIDDEN);
		set_pref('thumb_h', get_pref('smd_thumb_backup_h'), 'image', PREF_HIDDEN);
		set_pref('thumb_crop', get_pref('smd_thumb_backup_c'), 'image', PREF_HIDDEN);

	}
	// Erase the backup thumb prefs
	safe_delete('txp_prefs', "name IN('smd_thumb_backup_w', 'smd_thumb_backup_h', 'smd_thumb_backup_c', 'smd_thumb_default_profile', 'pane_smd_thumbnail_profiles_visible', 'pane_smd_thumbnails_visible')");
}

// ------------------------
function smd_thumb_table_exist($all='') {
	if ($all) {
		$tbls = array(SMD_THUMB => 5);
		$out = count($tbls);
		foreach ($tbls as $tbl => $cols) {
			if (gps('debug')) {
				echo "++ TABLE ".$tbl." HAS ".count(@safe_show('columns', $tbl))." COLUMNS; REQUIRES ".$cols." ++".br;
			}
			if (count(@safe_show('columns', $tbl)) == $cols) {
				$out--;
			}
		}
		return ($out===0) ? 1 : 0;
	} else {
		if (gps('debug')) {
			echo "++ TABLE ".SMD_THUMB." HAS ".count(@safe_show('columns', SMD_THUMB))." COLUMNS;";
		}
		return(@safe_show('columns', SMD_THUMB));
	}
}

// -------------------------------------------------------------
function smd_thumbnail_save_pane_state() {
	$panes = array('smd_thumbnail_profiles', 'smd_thumbnails');
	$pane = gps('pane');
	if (in_array($pane, $panes))
	{
		set_pref("pane_{$pane}_visible", (gps('visible') == 'true' ? '1' : '0'), 'smd_thumb', PREF_HIDDEN, 'yesnoradio', 0, PREF_PRIVATE);
		send_xml_response();
	} else {
		send_xml_response(array('http-status' => '400 Bad Request'));
	}
}

//*****************
// PUBLIC SIDE TAGS
//*****************
// ------------------------
function smd_thumbnail($atts, $thing=NULL) {
	global $thisimage, $img_dir;

	extract(lAtts(array(
		'type'       => get_pref('smd_thumb_default_profile', ''),
		'id'         => '',
		'name'       => '',
		'escape'     => 'html',
		'wraptag'    => '',
		'class'      => '',
		'html_id'    => '',
		'style'      => '',
		'link'       => '',
		'link_rel'   => '',
		'poplink'    => 0,
		'add_stamp'  => 0,
		'width'      => '',
		'height'     => '',
		'force_size' => '',
		'display'    => 'thumbnail', // thumbnail (full img tag) or url
		'form'       => '',
	),$atts));

	$thing = (empty($form)) ? $thing : fetch_form($form);

	if ($name) {
		$name = doSlash($name);
		$rs = safe_row('*', 'txp_image', "name = '$name' limit 1");
	} elseif ($id) {
		$id = (int) $id;
		$rs = safe_row('*', 'txp_image', "id = $id limit 1");
	} elseif ($thisimage) {
		$id = (int) $thisimage['id'];
		$rs = $thisimage;
	} else {
		trigger_error(gTxt('unknown_image'));
		return;
	}

	smd_thumb_set_impath();

	if ($rs) {
		extract($rs);
		$thumbinfo = safe_row('*', SMD_THUMB, "name='".doSlash($type)."'");
		if ($thumbinfo) {
			$dir = sanitizeForUrl($thumbinfo['name']);
			$path = IMPATH.$dir.DS.$id.$ext;
			if (file_exists($path)) {
				// Drop out if all we need to display is the thumb's URL
				if ($display == 'url') {
					return ihu.$img_dir.'/'.$dir.'/'.$id.$ext;
				}
				if ($escape == 'html') {
					$alt = htmlspecialchars($alt);
					$caption = htmlspecialchars($caption);
				}

				$meta['url'] = ihu.$img_dir.'/'.$dir.'/'.$id.$ext;
				$meta['alt'] = $alt;
				$meta['type'] = $type;
				$meta['width'] = $width;
				$meta['height'] = $height;
				$force_size = do_list($force_size);
				if (in_array('width', $force_size)) {
					$meta['forcew'] = 1;
				}
				if (in_array('height', $force_size)) {
					$meta['forceh'] = 1;
				}
				if ($add_stamp == 0) { $meta['stamp'] = 1; } // Negative logic since we want the stamp on by default for the admin side
				if ($name) { $meta['name'] = $name; }
				if ($caption) { $meta['title'] = $caption; }
// $meta['title'] = gTxt('smd_thumb_image') . $rs['w'] . " &#215; " . $rs['h'] . sp .  gTxt('smd_thumb_profile') . $thumbinfo['width'] . " &#215; " . $thumbinfo['height'];

				if ($category) {
					$meta['category'] = $category;
					$meta['category_title'] = fetch_category_title($category, 'image');
				}
				if ($author) { $meta['author'] = $author; }
				if ($date) { $meta['date'] = $date; }
				if ($html_id && !$wraptag) { $meta['id'] = $html_id; }
				if ($class && !$wraptag) { $meta['class'] = $class; } else { $class=$type; }
				if ($style) { $meta['style'] = $style; }

				$out = smd_thumb_img($thumbinfo, $rs, $meta, $thing);

				if ($link) {
					$out = href($out, imagesrcurl($id, $ext), (!empty($link_rel) ? ' rel="'.$link_rel.'"' : '').' title="'.$caption.'"');
				} elseif ($poplink) {
					$out = '<a href="'.imagesrcurl($id, $ext).'"'.
						' onclick="window.open(this.href, \'popupwindow\', '.
						'\'width='.$w.', height='.$h.', scrollbars, resizable\'); return false;">'.$out.'</a>';
				}

				return ($wraptag) ? doTag($out, $wraptag, $class, '', $html_id) : $out;
			}
		}
	}

	trigger_error(gTxt('unknown_image'));
}

// ------------------------
function smd_if_thumbnail($atts, $thing) {
	global $thisimage;
	assert_image();

	smd_thumb_set_impath();

	extract(lAtts(array(
		'type' => @get_pref('smd_thumb_default_profile'),
	),$atts));

	$thumbinfo = safe_row('*', SMD_THUMB, "name='".doSlash($type)."'");
	$ret = false;
	if ($thumbinfo) {
		$path = IMPATH.sanitizeForUrl($thumbinfo['name']).DS.$thisimage['id'].$thisimage['ext'];
		$ret = file_exists($path);
	}
	return parse(EvalElse($thing, $ret));
}

// Display data for form/container usage
function smd_thumbnail_info($atts, $thing=NULL) {
	global $smd_thumb_data;

	extract(lAtts(array(
		'item'    => '',
		'wraptag' => '',
		'break'   => '',
		'class'   => '',
		'debug'   => 0,
	), $atts));

	$tdata = is_array($smd_thumb_data) ? $smd_thumb_data : array();

	if ($debug) {
		echo '++ AVAILABLE INFO ++';
		dmp($tdata);
	}

	$items = do_list($item);
	$out = array();
	foreach ($items as $it) {
		if (isset($tdata[$it])) {
			$out[] = $tdata[$it];
		}
	}

	return doWrap($out, $wraptag, $break, $class);
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
h1. smd_thumbnail

h2. Features

* Assign any number of thumbnails to Txp's images
* Batch assign thumbnails to existing images
* Set one thumbnail size as default
* Display any of the thumbnail sizes on your site
* Keep thumbs in sync with Txp's thumbs if you wish
* Conditional thumbnail check available

h2. Installation / upgrading / uninstallation

p(warning). Requires Txp 4.5.0+

Download the plugin from either "textpattern.org":http://textpattern.org/plugins/1186/smd_thumbnail, or the "software page":http://stefdawson.com/sw, paste the code into the Txp Admin -> Plugins pane, install and enable the plugin. Visit the "forum thread":http://forum.textpattern.com/viewtopic.php?id=34367 for more info or to report on the success or otherwise of the plugin.

p(warning). When upgrading froim v0.1x to v0.20, your preferences for _Keep Txp thumbnails in sync with default profile_ will be removed. Please visit the _Tools/Prefs_ area to reinstate the behaviour you desire.

To uninstall, delete from the Admin -> Plugins page.

h2. Usage -- admin side

Visit the Content->Images tab. Above the list of images is an area labelled _Thumbnail Profiles_. Click it to reveal the control panel. You can toggle this panel on and off by clicking the heading.

You must define at least one profile to begin with, so click the _New profile_ button. This will reveal edit boxes where you can enter:

* *Name*: some descriptive name for this thumbnail dimension. You are limited to usual non-weird characters in the name
* *Width*: thumbnail width, in pixels. If left blank (or set to 0) and height is specified, the width will be computed in proportion to the height
* *Height*: thumbnail height, in pixels. If left blank (or set to 0) and width is specified, the height will be computed in proportion to the width
* *Quality (%)*: The quality of the resulting thumbnail. Generally only of use for jpg or png(24) images. The higher the value the better the quality and the bigger the file size. Default: 75
* *Crop*: whether to crop the image when the thumbnail is created, or scale it to fit
* *Sharpen*: whether to apply a sharpening filter to the thumbnail when it is created. Useful for very small images
* *Active*: Enable/disable this thumbnail size so it will/won't be automatically generated when the next image is uploaded. Click the checkbox to instantly switch the profile on/off. Click the 'Active' word in the heading row to toggle the status of all profiles. Note that this setting governs how the "All sizes":#smd_thumb_create dropdown entry interacts with the _Create_ and _Delete_ buttons
* *Default*: set this thumbnail profile as the default. The default image will be displayed in the Images list and will also be used as the default size to be displayed using @<txp:smd_thumbnail/>@. If the option to "sync with Txp's thumbs":#smd_thumb_prefs is on, it will also become Txp's standard (built-in) thumbnail size from this point forward

Once you've configured your profile, click Save to store it. You can always edit it again by clicking its name. Go ahead and create some more profiles for thumbnails of different sizes/parameters. If you wish to delete a profile, hit the _[Delete]_ button and confirm. *All thumbnails associated with that profile will be deleted*.

At this point, no thumbnails have been created. Let's rectify that.

h3(#smd_thumb_create). Creating thumbnails

Pick one of your images and click to edit it.

Beneath the main picture you will see an area labelled _Thumbnails_. You can toggle this panel on and off by clicking the heading.

Inside the panel is a simple select list containing one entry for each thumbnail profile you have configured, plus the special entry _All sizes_. You can select one of these entries and click _Create_ to create thumbnail(s) in the selected size or hit _Delete_ to remove the selected thumbnail(s).

Once created, the thumbnails appear in the panel; remember they can be tucked away if you don't want to look at them.

If you select _All sizes_ and hit _Create_, you will get one thumbnail for each active profile. Similarly, if you hit _Delete_ any thumbnails stored against an active profile for the current image will be removed. By toggling certain thumbnail profiles on/off you can control which ones are created or deleted.

By default:

* when you upload a new image, thumbnails will be automatically created for any active profiles
* when you replace an existing image, thumbnails will only be automatically created for active profiles if thumbs _do not_ already exist. This option can be altered with the "prefs":#smd_thumb_prefs

Note that if you use Txp's multi-edit tool to delete images, *all* corresponding thumbnails for the selected images (including inactive ones) will be removed.

h3(#smd_thumb_custom). Custom thumbnails

If you prefer to make your own thumbs in an external program, you can still set up the profiles as normal but instead of clicking _Create_ in the Image Edit screen, you can pick a profile size from the dropdown and use the file picker facility (Browse... / Choose...) to select a thumbnail from your hard drive. When you click _Upload_ the image will be inserted against the selected profile.

At any time you can click one of the thumbnails (or select a size from the dropdown) and choose for another image to replace the existing thumbnail -- even ones that have been auto-created. Some things to note about this feature:

* the thumbnail must be of the same file type as the original image or the upload will be ignored
* you can click a highlighted image to deselect it
* you cannot Browse for an image (the file upload box is greyed out) unless you have chosen one profile

h3(#smd_thumb_batch). Batch thumbnail creation

You can also create thumbnails en-masse. Click the _Tools_ button (labelled '_Tools / Prefs_' if you have sufficient rights) in the top-right hand corner of the smd_thumbnail control panel. From there you will see an area that allows you to create thumbnails for:

* Only the selected images (the checkboxes next to each image)
* All images of a particular category
* All images uploaded by a particular user
* All images

After selecting the desired option (and if you choose category or author, select one of the entries from the secondary list that apears), hit _Create_. Once you confirm your action, the plugin will go away and create thumbnails for every active profile for all images that match your criteria. This is a quick way to generate thumbs for your existing images. Note that if you choose the first (empty) category entry, then all images that have not been assigned a category are manipulated.

Because of the nature of this tool -- especially with large image pools -- the process is done as a background task via AJAX. After clicking the _Create_ button (and confirming you are sure) a counter will appear in the top-right of the prefs panel showing how many images it has processed out of the total number of images in the set. When it's done, if you then refresh your Images tab (e.g. click to the _Profiles_ pane) you will see your chosen default thumbnails in the list -- assuming you've set one as default.

h3(#smd_thumb_prefs). Preferences

If you have sufficient privileges, in the lower portion of the _Tools_ panel are some options that govern how thumbnails are created for all users. There is no 'Save' action here: the checkmarks are stored in real-time and always reflect the current state.

h4. Create smd_thumbnails from

Governs whether the smd_thumbnails are created from the full size image or its (Txp) thumbnail. If you have elected not to have Txp thumbnails created then it'll probably not work very well :-)

This preference applies:

* when you use any of the batch tools to mass-produce smd_thumbnails
* if you upload a new image
* if you reupload a main image and _Recreate thumbnails on re-upload of main image_ is checked

h4. Keep Txp thumbnails in sync with default profile

(This option only appears if you have set a profile as default)

Ordinarily, smd_thumbnail works independently of Txp's thumbs. If you disable the plugin you'll see that everything goes back to how it was before you installed it. This is great because your hard-graft isn't lost if you have uploaded your own thumbs.

But if you start creating thumbs with the plugin you will notice that Txp's built in tags such as @<txp:article_image thumbnail="1" />@ and @<txp:thumbnail />@ won't work for newer thumbnails. This might be confusing so you can opt to have smd_thumbnail update your Txp thumbnails at the same time.

By ticking either of the checkboxes, the plugin will track any changes *to the default profile* and mimic them with Txp's thumbnails. This has a few effects worth highlighting.

When the _Creation_ checkbox is ticked:

# if you batch create thumbs, upload a new image, or replace an existing image, a new Txp thumbnail will be created at the default size. Any existing thumbs will be overwritten
# if you change default profile, any Txp thumbnails that have been created will remain at the previous size. Only when you start creating, uploading or replacing images will the new thumbnail sizes be created

When the _Deletion_ checkbox is ticked:

# if you delete a single thumb that corresponds to the default profile, both the profile thumb and Txp's thumb will be deleted
# if you delete an entire profile, all its thumbnails *and all Txp's thumbs* will be removed

Further:

# if you have no default profile, the checkboxes disappear and the settings have no effect. Choose a default profile to reinstate the behaviour
# if you deactivate the default profile the checkboxes have no effect *unless* you delete the profile (since you're deleting it, its status is 'lost' and therefore the checkboxes function as normal). If there's any doubt, uncheck the _Deletion_ checkbox first!

h4. Recreate thumbnails on re-upload of main image

When this checkbox is cleared and you *replace* a main image (from the Image Edit screen) any thumbnails that have already been assigned to the image are left as they are -- only missing thumbnails will be created from the main image. Conversely, if you set this checkbox, when you upload the replacement image *ALL smd_thumbnails for active profiles will be (re)created from the main image*.

Note one important exception: Txp thumbnails are *NOT* covered by this option -- they are governed by the _Keep Txp thumbs in sync_'s 'Creation' checkbox. Thus, if you upload a replacement image and the 'Creation' checkbox is on, you will replace Txp's thumb regardless of the setting of the _Recreate thumbnails..._ option.

h2(#smd_thumbnail). Tag: @<txp:smd_thumbnail />@

A direct replacement for the built in "txp:thumbnail":http://textpattern.net/wiki/index.php?title=thumbnail tag with exactly the same functionality and attributes apart from these exceptions:

* %(atnm)type% : use this attribute to display thumbnails of the given profile name. e.g. @type="large"@. If you do not specify this attribute, the default profile will be used. If there is no default profile you'll see warning messages
* %(atnm)class% : if omitted, the name of the profile will be used as a class name for the img tag. If you specify a wraptag and omit the class, the profile name will be used as a class on both the container and the img tag
* %(atnm)align% : this attribute has been removed
* %(atnm)add_stamp% : adds the image file modification time to the end of the thumbnail's URL. Use @add_stamp="1"@ to switch this feature on. This helps prevent stale images, but may prevent browsers from cacheing the thumbs properly thus increasing bandwidth usage. Default: 0
* %(atnm)force_size% : usually when you set one or other width/height to 0 in a profile, the browser scales the missing dimension automatically. It does this by omitting the @width=@ or @height=@ attribute in the @<img>@ tag. This may cause visual artefacts as the page is rendered and the browser calculates the sizes. If you wish the plugin to add the actual dimension to the @<img>@ tag (the size at the time the thumbnail was created), tell the plugin with this attribute. Choose one or both of @width@ or @height@. Comma-separate as required. Default: unset
* %(atnm)display% : by default, this tag outputs a full @<img>@ tag. If you just require the image URL so you can make your own image tags, set @display="url"@. Default: @thumbnail@
* %(atnm)form% : you can construct your own @<img>@ tags using the given form. If not specified, you may use the tag as a container.

The tag works inside @<txp:images>@ or can be used standalone by specifying the @id@ or @name@ attribute. If using it as a container or with the @form@ attribute you display the various pieces of thumbnail information using the @<txp:smd_thumbnail_info>@ tag.

h2(#smd_thumbnail_info). Tag: @<txp:smd_thumbnail_info />@

Display various information from the current @<txp:smd_thumbnail>@ tag (in either its form or container). Attributes:

* %(atnm)item% : list of things you wish to display. Choose from:
** @url@ : full image URL of the thumbnail
** @type@ : thumbnail profile name
** @name@ : image name
** @id@ : thumbnail ID
** @ext@ : thumbnail file extension
** @category@ : image category name
** @category_title@ : image category title
** @alt@ : image alt text
** @title@ : image title / caption
** @author@ : image author
** @class@ : class applied to thumbnail (if not supplied, is same as type)
** @date@ : raw datestamp of when the image was uploaded
** @w@ : thumbnail width (pixels)
** @h@ : thumbnail height (pixels)
** @html_w@ : HTML-formatted width attribute
** @html_h@ : HTML-formatted height attribute
* %(atnm)wraptag% : HTML tag, without angle brackets, to wrap all the items with. e.g. @wraptag="ul"@
* %(atnm)class% : CSS class name to apply to the wraptag
* %(atnm)break% : HTML tag, without angle brackets, to wrap each item with. e.g. @break="li"@

h2(#smd_if_thumbnail). Tag: @<txp:smd_if_thumbnail>@

A direct replacement for the built in @<txp:if_thumbnail />@ tag with exactly the same functionality. It has one attribute:

* %(atnm)type% : use this attribute to check for thumbnails of the given profile name. e.g. @type="Medium"@. If you do not specify this attribute, the default profile will be tested. If there is no default profile the tag will always render the @<txp:else />@ portion

h2(#smd_thumb_how_it_works). How it works

For reference, when you create a profile a directory is created with that name inside your Txp @images@ folder. Inside this folder you will find images of the format @id.ext@: where @id@ and @ext@ match the corresponding image IDs in the Txp database. That's pretty much it.

You can delete thumbnail files manually from any of these directories and the plugin will figure everything out. But it's probably not advisable to delete the directories themselves -- use the _[Delete]_ buttons in the control panel for that.

h2. Author / credits

Written by "Stef Dawson":http://stefdawson.com/contact. Many thanks to the beta test crew who offered feature and workflow advice, especially thebombsite, jakob, jstubbs and maniqui.

h2(#smd_thumb_changelog). Changelog

* 26 Sep 2014 | 0.31 | Selected thumbnail creation for 4.6.x (thanks jpdupont)
* 14 Jan 2013 | 0.30 | For Txp 4.5.0 ; added mem_moderation_image support ; smd_thumbnail tag has form/container support ; added smd_thumbnail_info tag ; fixed image <-> select list interaction (damn you attr/prop!) ; added better tooltip on thumb hover ; fixed invalid markup ; added width & height attributes (thanks jstubbs)
* 12 Oct 2011 | 0.22 | Added @display@ attribute ; added 'create from' preference (thanks the blue dragon/maniqui) ; fixed plugin for dashboard use ; fixed jQuery attr/prop in prefs ; fixed Txp menu interference (thanks maniqui)
* 12 May 2011 | 0.21 | Added @smd_thumb_get_profiles()@ for other plugins to find which profiles are available
* 26 Jan 2011 | 0.20 | Tools / prefs separated from profile list ; permitted batch creation via 1) selected images 2) category 3) author (thanks jpdupont) ; added smd_thumb_auto_replace option and altered default behaviour (thanks maniqui) ; fixed Txp thumbnail creation on thumbnail replace and made prefs global -- and only available to site admins (all thanks jstubbs) ; fixed New Profile button wrapping (thanks jpdupont)
* 21 Nov 2010 | 0.14 | Permitted thumbs to be larger than the original images (thanks maniqui) ; fixed callbacks for image uploading and deleting
* 10 Nov 2010 | 0.13 | Added confirmation on _Create all_ ; relabelled upload box (thank uli) ; tamed styles ; silenced chmod warnings (thanks zero)
* 10 Nov 2010 | 0.12 | Added ability to sync default profile thumbs with the corresponding Txp thumbnails (thanks zero)
* 09 Nov 2010 | 0.11 | Added @force_size@ and @add_stamp@ (both thanks zero) ; removed unnecessary DB call
* 04 Oct 2010 | 0.10 | Official public release
* 06 Aug 2010 | 0.10beta | Initial beta release
# --- END PLUGIN HELP ---
-->
<?php
}
?>