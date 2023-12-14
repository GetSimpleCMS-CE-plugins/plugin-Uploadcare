<?php
/*
Plugin Name: Uploadcare Widget for GetSimple
Description: Uploadcare Widget plugin Integrated to Getsimple
Version: 1.0
Author: Andrejus Semionovas + Olivier Franchi
Author URI: https://jvcms.fr
*/

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");
$uc_plugin_path = GSPLUGINPATH."uploadcare/";

# language support
i18n_merge($thisfile, substr($LANG,0,2)) || i18n_merge($thisfile, $LANG) || i18n_merge($thisfile, 'en') || i18n_merge($thisfile, 'en_US');

# register plugin
register_plugin(
	$thisfile, 													# ID of plugin, should be filename minus php
	'Uploadcare',			 									# Title of plugin
	'1.0', 														# Version of plugin
	'Andrejus Semionovas + Olivier Franchi',					# Author of plugin
	'https://jvcms.fr',			# Author URL
	i18n_r($thisfile.'/UC_PLUGIN_DESC'),					 	# Plugin Description
	'files', 													# Page type of plugin
	'uploadcare_settings'  										# Function that displays content
);

# activate hooks
add_action('header','uploadcare_init',array()); 
add_action('html-editor-init','load_tab_function',array());
add_action('files-sidebar','createSideMenu',array($thisfile,'Uploadcare'));
add_action('edit-extras', 'editorChangeParamsUploadcare', array());

$uc_settings = get_uc_settings($uc_plugin_path);

function uploadcare_settings() {
	global $uc_settings, $uc_plugin_path;
	$all_uc_tabs = array('file' => 'Local Files', 'localhistory' => 'Hystory', 'camera' => 'Camera', 'url' => 'Any URL', 'facebook' => 'Facebook', 'gdrive' => 'Google Drive', 'gphotos' => 'Google Photos', 'dropbox' => 'Dropbox', 'instagram' => 'Instagram', 'evernote' => 'Evernote', 'flickr' => 'Flickr', 'skydrive' => 'OneDrive', 'box' => 'Box', 'vk' => 'VK', 'huddle' => 'Huddle');
	if(isset($_POST['uc_save']) && $_POST['uc_save']) {
		if(trim($_POST['tabsOrder']) == '') { ?>
		<div class="fancy-message error"><p><?php i18n('uploadcare/UC_SAVE_ERROR'); ?></p></div> <?php
		} else {
			$uc_xml = getXML($uc_plugin_path.'uc_config.xml');
			$uc_xml->pubkey = $_POST['uc_pubkey_set'];
			$uc_xml->tabs = trim($_POST['tabsOrder']);
			$uc_xml->disabledtabs = trim($_POST['disabledTabs']);
			if(isset($_POST['uc_effects'])) {
				$uc_xml->effects = $_POST['uc_effects'];
			} else {
				$uc_xml->effects = 0;
			}
			XMLsave($uc_xml, $uc_plugin_path.'uc_config.xml');
			$uc_settings = get_uc_settings($uc_plugin_path);
		?> <div class="fancy-message seccess"><p><?php i18n('uploadcare/UC_SAVE_SECC'); ?></p></div> <?php
		}
	}
	?>
	<style>
	.iframe-buttn {
	line-height: 14px !important;
	background-color: #182227;
	color: #CCC;
	font-weight: bold;
	text-decoration: none;
	text-shadow: 1px 1px 0px rgba(0, 0, 0, 0.2);
	transition: all 0.1s ease-in-out 0s;
    font-size: 10px;
    text-transform: uppercase;
    display: block;
    padding: 3px 10px;
    float: right;
    margin: 0px 0px 0px 5px;
    border-radius: 3px;
    background-repeat: no-repeat;
    background-position: 94% center;
	cursor: pointer;
	border: none;
	}
	.iframe-buttn:hover {
	background-color: #CF3805;
	color: #FFF;
	font-weight: bold;
	text-decoration: none;
	line-height: 14px !important;
	text-shadow: 1px 1px 0px rgba(0, 0, 0, 0.2); 
	}
	.inner-divs {
	clear: both;
	padding-bottom: 20px;
	}
	.uc-input {
	float: right;
	color: #333;
	border: 1px solid #AAA;
	padding: 5px;
	font-family: Arial,Helvetica Neue,Helvetica,sans-serif;
	font-size: 12px;
	border-radius: 2px;
	width: 300px;
	}
	input[type=text]:focus, textarea:focus {
	box-shadow: 1px 0 1px rgba(81, 203, 238, 1);
	border: 1px solid rgba(81, 203, 238, 1);
	}
	ul#uc_tabs_order {
    list-style: none;
    margin: 0 0 25px 0;
	}
	#uc_tabs_order li {
    text-shadow: 1px 1px solid rgba(255,255,255,.3);
    cursor: move;
    display: block;
    margin: 2px 0;
    border: 1px solid #eee;
    background: #fbfbfb;
    padding: 5px 10px;
	}
	#uc_tabs_order li:hover {
    border: 1px solid #ccc;
    background: #f6f6f6;
	}
	#uc_tabs_order li.placeholder-menu {
	height: 18px;
	background: #FFB164;
	border: 1px solid #FF9933;
	}
	.tab-enabled {
    background: #DFF0C8 !important;
	}
	.fancy-message {
	border: 1px solid;
    border-radius: 4px;
    margin-bottom: 20px;
	}
	.fancy-message.error {
	background: #F2DEDE;
	color: #A94442;
	padding: 12px 10px 10px 10px;
	}
	.fancy-message.seccess {
    background: #DFF0D8;
    color: #3C8C8F;
	padding: 16px 10px 0 10px;
	}
	</style>
	<h3><?php i18n('uploadcare/UC_SETTINGS'); ?></h3>
	<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" class="uc-postform">
		<div class="edit-settings">
			<div class="inner-divs">
			    <a style="display:block;margin:30px 0" href="http://bit.ly/uploadcarehome">Create your free account to get your API KEY on Uploadcare</a>
				<span class="uc-activate"><?php i18n('uploadcare/UC_PUBKEY'); ?></span>
				<input type="text" name="uc_pubkey_set" class="uc-input" value="<?php echo isset($uc_settings['pubkey']) ? $uc_settings['pubkey'] : ''; ?>"  required />
			</div>
			<div class="inner-divs" style="margin-top: 14px;">
				<span class="uc-activate"><?php i18n('uploadcare/UC_EFFECTS'); ?></span>
				<input type="checkbox" name="uc_effects" class="uc-input" value=1 <?php echo (isset($uc_settings['effects']) && $uc_settings['effects'] == '1') ? "checked" : "" ?> />
			</div>
			<div class="inner-divs" style="margin: 10px 0 0 30px;padding-bottom: 10px;text-align: center;">
				<div style="float: left;width: 500px;border: 1px solid #eee;padding: 10px 0;"><?php i18n('uploadcare/UC_TAB_NAME'); ?></div>
				<div style="float: left;width: 114px;border: 1px solid #eee;padding: 10px 0;"><?php i18n('uploadcare/UC_TAB_ENABLE'); ?></div>
			</div>
			<div class="inner-divs">
				<ul id="uc_tabs_order" class="ui-sortable"> <?php
				if(!empty($uc_settings['tabs']) && $uc_settings['tabs'] != 'all') {
					$selected_tabs = explode(" ",$uc_settings['tabs']);
					$diselected_tabs = explode(" ",$uc_settings['disabledtabs']);
					foreach ($selected_tabs as $key=>$value) { ?>
					<li class="clearfix tab-enabled" rel="<?php echo $value; ?>"><?php echo trim($all_uc_tabs[$value]); ?>
						<input type="checkbox" name="<?php echo $value; ?>" class="uc-input" value=1 checked style="width: 100px;cursor: pointer;margin-top: 3px;" />
					</li>
				<?php }
					foreach ($diselected_tabs as $key=>$value) { ?>
					<li class="clearfix" rel="<?php echo $value; ?>"><?php echo trim($all_uc_tabs[$value]); ?>
						<input type="checkbox" name="<?php echo $value; ?>" class="uc-input" value=1 style="width: 100px;cursor: pointer;margin-top: 3px;" />
					</li>
				<?php }
				} else {
				foreach ($all_uc_tabs as $key=>$value) { ?>
					<li class="clearfix<?php echo (isset($uc_settings['tabs']) && strpos($uc_settings['tabs'], $key) !== false) ? " tab-enabled" : "" ?>" rel="<?php echo $key; ?>"><?php echo $value; ?>
						<input type="checkbox" name="<?php echo $key; ?>" class="uc-input" value=1 <?php echo (isset($uc_settings['tabs']) && strpos($uc_settings['tabs'], $key) !== false) ? "checked" : "" ?> style="width: 100px;cursor: pointer;margin-top: 3px;" />
					</li>

				<?php }
				} ?>
				</ul>
				<input type="hidden" name="tabsOrder" value="">
				<input type="hidden" name="disabledTabs" value="">
			</div>
			<div class="inner-divs">
				<input type="submit" name="uc_save" class="iframe-buttn" value="<?php i18n('uploadcare/UC_BTN_SAVE'); ?>" style="float:left; margin-bottom:20px;" />
				<a style="display:block;margin:20px 0;float:left;width:100%" href="http://bit.ly/uploadcarehome">Tool Powered by Uploadcare</a>
			</div>
		</div>
	</form>
	<script>
		var tabs_order = '<?php echo $uc_settings['tabs']; ?>';
		if(tabs_order != '') {
			$('[name=tabsOrder]').val(tabs_order);
		}
		var distabs_order = '<?php echo $uc_settings['disabledtabs']; ?>';
		if(distabs_order != '') {
			$('[name=disabledTabs]').val(distabs_order);
		}
		$("#uc_tabs_order").sortable({
			cursor: 'move',
			placeholder: "placeholder-menu",
			update: function() {
				var order = '';
				var disorder = '';
				$('#uc_tabs_order li').each(function(index) {
					if( $(this).find('input:checkbox:first').attr('checked') ) {
						var cat = $(this).attr('rel');
						order = order+' '+cat;
					} else {
						var dcat = $(this).attr('rel');
						disorder = disorder+' '+dcat;
					}
				});
				$('[name=tabsOrder]').val(order);
				$('[name=disabledTabs]').val(disorder);
			}
		});
		$("#uc_tabs_order").disableSelection();
		$('#uc_tabs_order li').click( function(e) {
			var order = '';
			var disorder = '';
			$('#uc_tabs_order li').each(function(index) {
				if( $(this).find('input:checkbox:first').attr('checked') ) {
					var cat = $(this).attr('rel');
					order = order+' '+cat;
					$(this).addClass( "tab-enabled" );
				} else {
					var dcat = $(this).attr('rel');
					disorder = disorder+' '+dcat;
					$(this).removeClass( "tab-enabled" );
				}
				$('[name=tabsOrder]').val(order);
				$('[name=disabledTabs]').val(disorder);
			});

		});
		jQuery(function(){
			setTimeout(function() {
				jQuery(".fancy-message.seccess").hide('slow');
			}, 10000);
		});
	</script>
	<?php
}

function get_uc_settings($uc_plugin_path) {
	$uc_sett = array();
	if (!file_exists($uc_plugin_path.'uc_config.xml')) {
		$uc_xml = @new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><uc_config></uc_config>');
		$uc_xml->addChild('pubkey', isset($uc_xml->pubkey) ? $uc_xml->pubkey : 'demopublickey');
		XMLsave($uc_xml, $uc_plugin_path.'uc_config.xml');
		$uc_sett['pubkey'] = (string) $uc_xml->pubkey;
	}
	else {
		$uc_xml = getXML($uc_plugin_path.'uc_config.xml');
		$uc_sett['pubkey'] = (string) $uc_xml->pubkey;
		$uc_sett['tabs'] = isset($uc_xml->tabs) ? (string) $uc_xml->tabs : 'all';
		$uc_sett['disabledtabs'] = isset($uc_xml->disabledtabs) ? (string) $uc_xml->disabledtabs : '';
		$uc_sett['effects'] = isset($uc_xml->effects) ? (string) $uc_xml->effects : 'false';
	}
	return $uc_sett;
}

function uploadcare_init() {
	if(basename($_SERVER['PHP_SELF']) != "edit.php") return;
	global $uc_settings, $LANG;
	if(strlen($LANG) > 2) $locale = substr($LANG,0,2);
	else $locale = $LANG;
	$uc_effect = (isset($uc_settings['effects']) && !empty($uc_settings['effects']) && $uc_settings['effects'] == '1') ? true : false;
	?>
	<script src="https://ucarecdn.com/widget/3.x/uploadcare/uploadcare.full.min.js" charset="utf-8"></script>
	<?php if($uc_effect) { ?>
	<script src="https://ucarecdn.com/libs/widget-tab-effects/1.x/uploadcare.tab-effects.min.js" charset="utf-8"></script>
	<?php } ?>
	<script>
		UPLOADCARE_PUBLIC_KEY = '<?php echo $uc_settings['pubkey']; ?>';
		UPLOADCARE_LOCALE = '<?php echo $locale; ?>';
	</script>
	<?php
}

function load_tab_function() {
	global $uc_settings, $LANG;
	$uc_tabs = (isset($uc_settings['tabs']) && !empty($uc_settings['tabs'])) ? $uc_settings['tabs'] : 'all';
	$uc_histr = (isset($uc_settings['tabs']) && strpos($uc_settings['tabs'], 'localhistory') !== false) ? true : false;
	$uc_effect = (isset($uc_settings['effects']) && !empty($uc_settings['effects']) && $uc_settings['effects'] == '1') ? true : false;
	?>
	<script>
	CKEDITOR.plugins.addExternal('uploadcare', 'https://uploadcare.github.io/uploadcare-ckeditor/dist/uploadcare/plugin.js');
	<?php
	if($uc_effect) { ?>
		uploadcare.registerTab('preview', uploadcareTabEffects);
<?php }
if($uc_histr) { ?>
	function loadTabNew() {
		var history_name = '<?php i18n('uploadcare/UC_HST_NAME'); ?>';
		var history_desc = '<?php i18n('uploadcare/UC_HST_DESC'); ?>';
		uploadcare.registerTab('localhistory', function(container, button, dialogApi, settings, name) {
			function loadItems() {
				// Format: UUID isImage size filename
				var items = localStorage.getItem(localKey);
				if (!items) {
					return [];
				}
				items = items.split("\n");
				for (var i = 0; i < items.length; i++) {
					var v = items[i].split(' ');
					v.splice(3, v.length, v.slice(3, v.length).join(' '));
					v[1] = !!parseInt(v[1]);
					v[2] = parseInt(v[2]);
					items[i] = v;
				}
				return items;
			}
			function saveItems(items) {
				items = items.slice(0, 100);
				for (var i = 0; i < items.length; i++) {
					var v = items[i].slice();
					v[1] = 0 + v[1]; // bool ? int
					items[i] = v.join(" ");
				}
				localStorage.setItem(localKey, items.join("\n"));
			}
			function addItem(fileInfo) {
				var items = loadItems();
				items = $.grep(items, function(v) {
					return v[0] !== fileInfo.uuid;
				});
				var item = [fileInfo.uuid, fileInfo.isImage, fileInfo.size, fileInfo.name];
				items.unshift(item);
				saveItems(items);
			}
			function removeItem(item) {
				var items = loadItems();
				items = $.grep(items, function(v) {
					return v[0] !== item[0];
				});
				saveItems(items);
			}
			function makeItem(data) {
				var html = $("<div/>").addClass("uploadcare--file uploadcare--files__item").append($("<div/>").addClass("uploadcare--file__description").append($("<div/>").addClass("uploadcare--file__preview").append(
                data[1] ? $('<img/>', {
                    src: settings.cdnBase + "/" + data[0] + "/-/quality/lightest/-/preview/54x54/"
                }) :
                $('<svg width="32" height="32" role="presentation" class="uploadcare--icon uploadcare--file__icon"><use xlink:href="#uploadcare--icon-file"/></svg>')
				)).append($("<div/>").addClass("uploadcare--file__name").text(data[3])).append($("<div/>").addClass("uploadcare--file__size").text(
					uc.utils.readableFileSize(data[2]))).on('click', function(e) {
					dialogApi.addFiles('uploaded', [data[0]]);
					e.preventDefault();
				})).append($("<button/>").addClass("uploadcare--button uploadcare--button_icon uploadcare--button_muted uploadcare--file__remove").append($('\<svg role="presentation" width="32" height="32" class="uploadcare--icon">\<use xlink:href="#uploadcare--icon-remove"></use>\</svg>\
'				)).on('click', function() {
					html.remove();
					removeItem(data);
				}));
				return html;
			}
			function populate(container) {
				var items = loadItems();
				for (var i = items.length - 1; i >= 0; i--) {
					var item = items[i];
					if (settings.imagesOnly && !item[1]) {
						continue;
					}
					container.prepend(makeItem(item));
				}
			}
			var localStorage = window.localStorage;
			var localKey = "uploadcare_" + settings.publicKey;
			var uc;
			if (!localStorage) {
				button.hide();
				return;
			}
			uploadcare.plugin(function(uploadcare) {
				uc = uploadcare;
			});
			dialogApi.fileColl.onAdd.add(function(file) {
				file.done(function(fileInfo) {
					addItem(fileInfo);
				})
			});
			$('<div class="uploadcare--tab__header">\<div class="uploadcare--text uploadcare--text_size_large uploadcare--tab__title">'+history_desc+'</div>\
</div>').appendTo(container);
			populate($('<div class="uploadcare--files"></div>')
				.toggleClass('uploadcare--files_type_table', !settings.imagesOnly)
				.toggleClass('uploadcare--files_type_tiles', settings.imagesOnly)
				.appendTo($('<div class="uploadcare--tab__content"></div>')
                .appendTo(container)));
		});
		UPLOADCARE_LOCALE_TRANSLATIONS = {
			dialog: {
				tabs: {
					names: {
						localhistory: history_name
					}
				}
			}
		};
	} <?php
} ?>
	UPLOADCARE_TABS = '<?php echo $uc_tabs; ?>';
	UPLOADCARE_CROP = "free, 16:9, 4:3, 5:4, 1:1";
	<?php
	if($uc_effect) { ?>
		UPLOADCARE_EFFECTS = "crop,rotate,enhance,sharp,grayscale,mirror,flip,blur,invert";
	<?php }
	if($uc_histr) { ?>
		loadTabNew();
	<?php } ?>
	</script>
<?php if($uc_histr) { ?>
	<svg width="0" height="0" style="position:absolute">
		<symbol id="uploadcare--icon-localhistory" veiwBox="0 0 32 32">
			<g>
				<path d="M21.3,5c-2.9-1.3-6.2-1.4-9.2-0.2C10.1,5.6,8.3,6.9,7,8.6L6.5,6.8C6.4,6.2,5.9,5.9,5.3,6C4.8,6.2,4.5,6.7,4.6,7.2l1,4.1 c0.1,0.5,0.5,0.8,1,0.8c0.1,0,0.2,0,0.2,0l4.1-1c0.5-0.1,0.9-0.7,0.7-1.2C11.6,9.3,11,9,10.5,9.2L8.8,9.6c1.1-1.3,2.4-2.3,4-2.9 c2.5-1,5.2-0.9,7.7,0.2c2.4,1.1,4.3,3,5.3,5.5c1,2.5,0.9,5.2-0.2,7.7s-3,4.3-5.5,5.3c-3.5,1.3-7.4,0.7-10.2-1.8 c-1.2-1.1-2.2-2.4-2.8-3.9c-0.2-0.5-0.8-0.8-1.3-0.6c-0.5,0.2-0.8,0.8-0.6,1.3c0.7,1.8,1.9,3.5,3.3,4.7c2.2,1.9,5,2.9,7.9,2.9 c1.5,0,2.9-0.3,4.3-0.8c3-1.2,5.3-3.4,6.6-6.3c1.3-2.9,1.4-6.2,0.2-9.2S24.3,6.3,21.3,5z"></path>
				<path d="M16.5,10c-0.6,0-1,0.4-1,1v6c0,0.3,0.1,0.6,0.4,0.8l4,3c0.2,0.1,0.4,0.2,0.6,0.2c0.3,0,0.6-0.1,0.8-0.4 c0.3-0.4,0.2-1.1-0.2-1.4l-3.6-2.7V11C17.5,10.4,17.1,10,16.5,10z"></path>
			</g>
		</symbol>
	</svg>
	<style>
	.uploadcare--menu__item_tab_localhistory.uploadcare--menu__item_current svg {
		color: #f0cb3c;
	}
	</style>
<?php }
}

function editorChangeParamsUploadcare() {
	GLOBAL $GSEDITORBROWSER, $EDOPTIONS, $EDTOOL;
	if(strpos($EDOPTIONS,'extraPlugins:"') !== false) {
		if(strpos($EDOPTIONS,'uploadcare') === false) {
			$EDOPTIONS = str_replace('extraPlugins:"', 'extraPlugins:"uploadcare,', $EDOPTIONS);
		}
	}
	else $EDOPTIONS .= ',extraPlugins:"uploadcare"';
	if(strpos($EDTOOL,'advanced') !== false) {
		$EDTOOL='[["Bold", "Italic", "Underline", "NumberedList", "BulletedList", "JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock", "Table", "TextColor", "BGColor", "Link", "Unlink", "Image", "RemoveFormat", "Source"],"/",["Styles","Format","Font","FontSize"]]';
	}
	if(strpos($EDTOOL,'basic') !== false) {
		$EDTOOL='[["Bold", "Italic", "Underline", "NumberedList", "BulletedList", "JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock", "Link", "Unlink", "Image", "Youtube", "RemoveFormat", "Source"]]';
	}
	if(strpos($EDTOOL,'Uploadcare') === false) {
		$EDTOOL=str_replace(']]', ',"Uploadcare"]]', $EDTOOL);
	}
}