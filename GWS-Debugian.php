<?php
	/* 
		Plugin Name: GWS Debugian
		Description: 👉👈 Hallo ich bin Debugian, der Liebe Debughelfer von Gally Websolutions. uwu
		Version: 1.8.0
	*/
	define('GWS_DEBUGIAN_VERSION', '1.8.0.alpha');
	// MESSAGE_INFO for the Git-Commit-Message: Copilot-Anweisungen für automatische Commit-Nachrichten hinzugefügt
	// Use this format to generate Git-Commit-Message: "Vx.x.x - MESSAGE_INFO"
	// The Git-Messages must be in german / don't mention in MESSAGE_INFO that the version has changed, that is implied
	

	if(!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}

	if(file_exists(__DIR__.'/settings.php')){
		include_once(__DIR__.'/settings.php');
	}

	if(!defined('GWS_DEBUGIAN_COLOR')) 		define('GWS_DEBUGIAN_COLOR', '#FF00C3');
	if(!defined('GWS_DEBUGIAN_DEV')) 		define('GWS_DEBUGIAN_DEV', 'devdocs');
	if(!defined('SUPERADMIN_DOMAIN')) 		define('SUPERADMIN_DOMAIN', 'gally-websolutions');
	if(!defined('GWS_DEBUGIAN_AUTOUPDATE')) define('GWS_DEBUGIAN_AUTOUPDATE', false);
	if(!defined('GWS_CAPTCHA_TIME')) 		define('GWS_CAPTCHA_TIME', 18);

	// GWS Dashboard einbinden
	include_once(__DIR__.'/GWS-Dashboard/GWS-Dashboard.php');


	$deps = glob(__DIR__.'/dependencies/*/functions.php');
	foreach ($deps as $dep) {
		include_once($dep);
	}
	
	

	if (strpos(__DIR__, GWS_DEBUGIAN_DEV) !== false) {
		
		add_action('wp_head', 'gws_debugian_header_style');
		add_action('admin_head', 'gws_debugian_header_style');

		function gws_debugian_header_style() {
			if (is_user_logged_in()) {
				?>
				<style>
					body:after {
						content:"DEV";
						position:fixed;
						left: .5em;
						bottom: .5em;
						background: <?=GWS_DEBUGIAN_COLOR?>;
						color: #fff;
						text-shadow: 0 0 1px #000, 
									 0 0 3px #000,
									 0 0 1px #000,
									 0 0 2px #000;
						/*               !   ;) */
						box-shadow:  0 0 1px #000, 
									 0 0 3px #000,
									 0 0 1px #000,
									 0 0 2px #000;
						/*               !   ;) */
						z-index: 999999;
						aspect-ratio:1;
						display: flex;
						justify-content: center;
						align-items: center;
						padding: 0 .5em;
						font-size:14px;
						border-radius: 50%;
						box-sizing: border-box;
						pointer-events: none;
						font-family: system-ui, sans-serif;
						font-weight: 600;
					}
				</style>
				<?php
			}
		}
	}
	
	add_action('wp_enqueue_scripts', function() {
		wp_enqueue_style('gws-captcha', plugin_dir_url(__FILE__) . 'captcha.css', [], GWS_DEBUGIAN_VERSION);
	});
	add_action('admin_enqueue_scripts', function ($hook) {
		wp_enqueue_style('gws-debugian', plugin_dir_url(__FILE__) . 'debugian.css', [], GWS_DEBUGIAN_VERSION);
	});
	

	add_filter('gform_pre_render', 'gws_debugian_pre_render');

	function gws_debugian_pre_render($form) {
		session_start();
		if(count($_POST) == 0) {
			$_SESSION['gws_captcha'] = base64_encode(rand(111111,999999));
			$_SESSION['gws_captcha_time'] = time();
		}
		session_write_close();


		foreach ($form['fields'] as &$field) {
			if($field->allowsPrepopulate && $field->inputName == 'gws-captcha') {

				$field->cssClass = 'gws-captcha'; 
			}
		}

		return $form;
	}

	add_filter('gform_pre_validation', 'gws_debugian_pre_validation');
	function gws_debugian_pre_validation($form) {
		session_start();
		foreach ($form['fields'] as &$field) {
			if($field->allowsPrepopulate && $field->inputName == 'gws-captcha') {
				$retmsg = '';
				if(rgpost('input_'.$field->id) != $_SESSION['gws_captcha']) {
					$field->failed_validation = true;
					$retmsg = __('Bitte den Code eingeben.', 'gws-debugian');
				}
				if(time() - $_SESSION['gws_captcha_time'] < GWS_CAPTCHA_TIME) {
					$field->failed_validation = true;
					if($retmsg == '') {
						$retmsg = __('Die Eingabe ist zu schnell.', 'gws-debugian')  .  ' - Fehler Code: ' . time() - $_SESSION['gws_captcha_time'];
					}
				}
				if($retmsg != '') {
					$field->validation_message = $retmsg;
					$field->is_value_submission = false;
				}
			}
		}
		session_write_close();
		return $form;
	}


	if(!file_exists(__DIR__.'/../../../.htaccess')){
		file_put_contents(__DIR__.'/../../../.htaccess', '');
	}
	$htaccess = file_get_contents(__DIR__.'/../../../.htaccess');
	$htaccess = str_replace("\r\n", "\n", $htaccess);
	$htaccess = str_replace("\r", "\n", $htaccess);

	if(isset($_GET['switch_gally_access'])){
		global $htaccess;
		if(strpos($htaccess, "# BEGIN GALLY ACCESS\n#<Files") === false){
			$htaccess = str_replace("# BEGIN GALLY ACCESS\n<Files", "# BEGIN GALLY ACCESS\n#<Files", $htaccess);
			$htaccess = str_replace("</Files>\n# END GALLY ACCESS", "#</Files>\n# END GALLY ACCESS", $htaccess);
			file_put_contents(__DIR__.'/../../../.htaccess', $htaccess);
			?>
			<div class="notice notice-success is-dismissible">
				<p>Gally Access schützt nun die komplette Website</p>
			</div>
			<?php
		}else{
			$htaccess = str_replace("# BEGIN GALLY ACCESS\n#<Files", "# BEGIN GALLY ACCESS\n<Files", $htaccess);
			$htaccess = str_replace("#</Files>\n# END GALLY ACCESS", "</Files>\n# END GALLY ACCESS", $htaccess);
			file_put_contents(__DIR__.'/../../../.htaccess', $htaccess);
			?>
			<div class="notice notice-success is-dismissible">
				<p>Gally Access schützt nur WordPress</p>
			</div>
			<?php
		}
	}

	
	if(isset($_POST['submit_debugian'])){
		$settings = file_get_contents(__DIR__.'/base_settings.json');
		if(file_exists(__DIR__.'/settings.json')){
			$settings = file_get_contents(__DIR__.'/settings.json');
		}
		$settings = json_decode($settings, true);

		$settings['post_types'] = $_POST['gws_debugian_post_types']??[];

		file_put_contents(__DIR__.'/settings.json', json_encode($settings));

		// show success message
		add_action('admin_notices', 'gws_debugian_settings_saved');

		function gws_debugian_settings_saved() {
			?>
			<div class="notice notice-success is-dismissible">
				<p>Debugian hat sich das gemerkt. 👉👈</p>
			</div>
			<?php
		}
	}

	if(isset($_POST['submit_debugian_hosting'])){
		$settings = file_get_contents(__DIR__.'/base_settings.json');
		if(file_exists(__DIR__.'/settings.json')){
			$settings = file_get_contents(__DIR__.'/settings.json');
		}
		$settings = json_decode($settings, true);

		$settings['hosting'] = 	isset($_POST['hosting']['enabled']) ? $_POST['hosting']	: null;
		$settings['email'] = 	isset($_POST['email']['enabled'])	? $_POST['email']	: null;
		$settings['ftp'] = 		isset($_POST['ftp']['enabled'])	 	? $_POST['ftp']		: null;

		file_put_contents(__DIR__.'/settings.json', json_encode($settings));

		// show success message
		add_action('admin_notices', 'gws_debugian_settings_saved');

		function gws_debugian_settings_saved() {
			?>
			<div class="notice notice-success is-dismissible">
				<p>Debugian hat sich das für das Kundi gemerkt. 👉👈</p>
			</div>
			<?php
		}
	}

	$settings = file_get_contents(__DIR__.'/base_settings.json');
	if(file_exists(__DIR__.'/settings.json')){
		$settings = file_get_contents(__DIR__.'/settings.json');
	}
	$settings = json_decode($settings, true);

	if(!isset($settings['post_types'])) $settings['post_types'] = ['page'];
	
	if(isset($_GET['gally_access_install'])){
		
		$gally_access = __DIR__.'/dependencies/gally_access';
		$gally_access_install = __DIR__.'/../../../gally_access';

		ob_start();
			echo "<pre>";
			echo "Gally Access installieren...\n";
			var_export($gally_access);
			echo "\n";
			var_export(is_dir($gally_access));
			echo "\n";
			

			if(is_dir($gally_access)) {
				echo "Gally Access vorhanden und wird kopiert.\n";
				// copy($gally_access, __DIR__.'/../../../gally_access');
				// copy folders
				$gally_access = escapeshellarg($gally_access);
				if (!is_dir($gally_access_install)) {
					mkdir($gally_access_install, 0755, true);
				}
				$gally_access_install = escapeshellarg($gally_access_install);
				exec("cp -r $gally_access/. $gally_access_install 2>&1", $output, $return);
				$output = implode("\n", $output);
				echo $output;
				echo "\nGally Access wurde kopiert.\n";
			};
			echo "</pre>";
		$output = ob_get_clean();
		//echo $output;

		header('Location: /gally_access/?install');
	}

	add_action('admin_bar_menu', 'gws_debugian_admin_bar_menu', 50);
	function gws_debugian_admin_bar_menu($admin_bar) {
		global $htaccess;
		// if loggedin but in frontend
		$doDebugian = false;

		if (is_user_logged_in() && !is_admin()) {

			$doDebugian = true;

			$admin_bar->add_menu(array(
				'id' => 'gws-debugian-visual-css-log',
				'title' => 'Visual CSS Log',
				'href' => '#',
				'parent' => 'gws-debugian',
				'meta' => array(
					'class' => 'gws-debugian-visual-css-log',
					'onclick' => 'return dbg_toggle("visual_css_log")',
				),
			));
		}elseif (is_user_logged_in()){
			// check if folder ../../../gally_access exists
			$dir_GA = __DIR__.'/dependencies/gally_access';
			$gally_access = __DIR__.'/../../../gally_access';

			$doDebugian = true;

			if(!is_dir($dir_GA)){
				$doDebugian = true;
				$admin_bar->add_menu(array(
					'id' => 'gws-debugian-gally-access',
					'title' => 'Gally Access Status prüfen',
					'href' => '/wp-admin/options-general.php?page=gws-debugian',
					'parent' => 'gws-debugian'
				));
			}elseif (!is_dir($gally_access)) {
				$doDebugian = true;
				$admin_bar->add_menu(array(
					'id' => 'gws-debugian-gally-access',
					'title' => 'Gally Access installieren',
					'href' => '/wp-admin/options-general.php?page=gws-debugian&gally_access_install',
					'parent' => 'gws-debugian'
				));
			}else{
				$doDebugian = true;
				$admin_bar->add_menu(array(
					'id' => 'gws-debugian-gally-access',
					'title' => 'Gally Access neu verschlüsseln',
					'href' => '/gally_access/?install',
					'parent' => 'gws-debugian'
				));
				
				$info = '🔒 Website freigeben und nur Admin mit Gally Access schützen';
				if(strpos($htaccess, "# BEGIN GALLY ACCESS\n#<Files") === false){
					$info = '🔓 Ganze Website hinter Gally Access stellen';
				}

				$admin_bar->add_menu(array(
					'id' => 'gws-debugian-gally-access-DEV',
					'title' => $info,
					'href' => '/wp-admin/options-general.php?page=gws-debugian&switch_gally_access',
					'parent' => 'gws-debugian'
				));
			}
		}

		if($doDebugian){
			$admin_bar->add_menu(array(
				'id' => 'gws-debugian',
				'title' => 'Debugian',
				'href' => '/wp-admin/options-general.php?page=gws-debugian',
				'meta' => array(
					'class' => 'gws-debugian',
				),
			));
		}
	}

	// add javascript to the footer if frontend and logged in
	add_action('wp_footer', 'gws_debugian_footer');
	function gws_debugian_footer() {
		if (is_user_logged_in()) {
			?>
			<script>
				const dbg_style = document.createElement('style');
				document.head.appendChild(dbg_style);

				const dbg_options = {
					visual_css_log: false,
				};

				const dbg_toggle = (key) => {
					dbg_options[key] = !dbg_options[key];
					dbg_update();
					return false;
				};

				const dbg_update = () => {
					dbg_style.innerHTML = '';
					if (dbg_options.visual_css_log) {
						dbg_style.innerHTML += `
							* { outline: 4px solid <?=GWS_DEBUGIAN_COLOR?>; }
							#wpadminbar * { outline: none; }

							#wp-admin-bar-gws-debugian .ab-submenu .gws-debugian-visual-css-log .ab-item:before {
								content: '👉';
								filter: grayscale(0);
							}
						`;
					}
				};
			</script>
			<style>
				#wp-admin-bar-gws-debugian .ab-submenu .ab-item{
					display:flex;
					align-items:center;
					gap: 5px;
				}
				#wp-admin-bar-gws-debugian .ab-submenu .ab-item:before {
					content: '👆';
					font-size: .88em;
					filter: grayscale(1);
					display:block;
					margin: 0;
					padding: 0;
				}
				@media screen and (max-width: 782px) {
					#wpadminbar li#wp-admin-bar-gws-debugian{
						display: block!important;
					}
					#wpadminbar li#wp-admin-bar-gws-debugian:before{
						content: '👉👈';
						padding: 0 10px;
						line-height: calc(3.28571428 / 2);
						font-size:2em;

						filter: grayscale(1);
					}
					#wpadminbar li#wp-admin-bar-gws-debugian>a{
						display:none !important;
					}
				}
			</style>
			<?php
		}
	}

	// add CSS to footer in backend
	add_action('admin_footer', 'gws_debugian_admin_footer');
	function gws_debugian_admin_footer() {
		global $settings;
		$types = $settings['post_types'] ?? ['none'];
		$types = '.post-type-'.implode(', .post-type-', $types);

		?>
		<style>
			:is(<?=$types?>) h1.wp-block-post-title{
				/* all: unset; */
				font-family: monospace !important;
				font-weight: normal !important;
				text-transform: none !important;
				font-size: 2em;
				background: #eee;
				padding: .5em;
				border: 1px solid #ccc;	
				border-radius: .1em;
				position: relative;

			}
			:is(<?=$types?>) h1.wp-block-post-title:before{
				content: 'Dokumentname';
				font-size: .7em;
				position: absolute;
				bottom: 100%;
				background: #eee;
				color: #999;
				border: 1px solid #ccc;
				border-bottom: none;
				padding: .5em 1em .25em;
				border-radius:.5em .5em 0 0;
				left: -1px;
				cursor:pointer;
			}

			body:is(<?=$types?>) .is-root-container h1 ~ h1,
			body:is(<?=$types?>) .is-root-container :has(h1) ~ h1,
			body:is(<?=$types?>) .is-root-container :has(h1) ~ :has(h1) h1,
			body:is(<?=$types?>) .is-root-container h1 ~ :has(h1) h1,
			body:not(<?=$types?>) .is-root-container h1
			{
				background: #800 !important;
				color: #fff !important;
				font-family: monospace !important;
				font-weight: normal !important;
				text-transform: none !important;
				color: #f00 !important;
				font-size: 2em;
				padding: .5em;
				border: 5px solid #f00;	
				border-radius: .1em;
				position: relative;

				&:after{
					content: 'Es darf nur ein H1 geben pro Dokument';
					font-size: .5em;
					color: #fff;
					display:block;
					padding-top:1em;
				}
			}

			strong.gws{
				font-family: monospace;
			}
		</style>
		<?php
	}

	// settings page
	add_action('admin_menu', 'gws_debugian_settings_menu');
	function gws_debugian_settings_menu() {
		if(strpos(wp_get_current_user()->user_email, SUPERADMIN_DOMAIN)){
			add_options_page('GWS Debugian', '<strong class="gws">GWS</strong> Debugian', 'manage_options', 'gws-debugian', 'gws_debugian_settings_page');
		}else{
			add_options_page('GWS Zugangsdaten', '<strong class="gws">GWS</strong> Zugangsdaten', 'manage_options', 'gws-debugian', 'gws_debugian_settings_page');
		}
	}

	function gws_debugian_settings_page() {
		global $settings;
		
		$superadmin = strpos(wp_get_current_user()->user_email, SUPERADMIN_DOMAIN);

		?>
		<div class="wrap">

			<?php if($superadmin && !isset($_GET['edit'])): ?>
				<h1><strong class="gws">GWS</strong> Debugian</h1>

				<?php
					$url = get_site_url();
					$parsed_url = parse_url($url);
					$host = $parsed_url['host'] ?? '';

					if(strpos(__DIR__, GWS_DEBUGIAN_DEV) === false){
						if (strpos($host, 'dev.') === 0
						|| strpos($host, 'sui-inter') > 0) {
							?>
								<p>YO. SORRY 🛑 aber du bist mega eindeutig auf einer DEV-Instanz unterwegs ohne mich vorher informiert zu haben. Was denkst du dir dabei? Du kannst mich doch nicht einfach auf einer DEV-Instanz nutzen ohne mich zu informieren. So denke ich doch, dass ich live bin und bin voll aufgeregt. Aber nun bin ich schlicht und einfach nicht mal echt. Ich bin nicht wütend, nur enttäuscht. 😢 Finde hier heraus, wie du es mir sagen kannst: <a href="https://app.clickup.com/9015213390/v/dc/8cnjfae-4035/8cnjfae-1215?block=block-02053606-fbe0-41ee-b7f4-b3de58217b91" target="_blank">ClickUp-Wissensdatenbanklink</a></p>
							<?php
						}elseif (strpos($host, 'www.') !== 0) {
							?>
								<p><strong>Achtung:</strong> Deine Domain beginnt nicht mit WWW und das könnte ein Indiz dafür sein, dass du auf einer DEV-Domain bist. Falls das tatsächlich eine DEV-Instanz ist: WARUM hast du mir das nicht vorher gesagt? 😢 Finde hier heraus, wie du es mir sagen kannst: <a href="https://app.clickup.com/9015213390/v/dc/8cnjfae-4035/8cnjfae-1215?block=block-02053606-fbe0-41ee-b7f4-b3de58217b91" target="_blank">ClickUp-Wissensdatenbanklink</a></p>
							<?php
						}
					}

				?>


				<form method="post" action="?page=gws-debugian">
					
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row">
									Post-Types mit Dokumentname<br>
									<small>ohne automatisches H1</small>
								</th>
								<td><?php
									// get all post types
									$post_types = get_post_types(array('public' => true), 'objects');
									
									foreach ($post_types as $post_type) {
										if($post_type->name == 'attachment') continue;
										?>
										<label>
											<input type="checkbox" name="gws_debugian_post_types[]" value="<?=$post_type->name?>" <?=in_array($post_type->name, $settings['post_types']) ? 'checked' : ''?>>
											<?=$post_type->label?>
										</label><br>
										<?php
									}
								?></td>
							</tr>
							<tr>
								<th scope="row">
									Debugian Module
								</th>
								<td>
									<ol>
									<?php
										$modules = glob(__DIR__.'/modules/*.php');
										foreach ($modules as $module) {
											?>
												<li><?=basename($module)?></li>
											<?php
										}
									?>
									</ol>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="submit_debugian" id="submit" class="button button-primary" value="Änderungen speichern"></p>
				</form>
			<?php endif; ?>
			
			
			<?php include(__DIR__.'/modules/git.php'); ?><br><br>
			<?php include(__DIR__.'/modules/baguette.php'); ?><br><br>		
			<?php include(__DIR__.'/modules/hosting-info.php'); ?><br><br>
		</div>
		<?php
	}
?>