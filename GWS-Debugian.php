<?php
	/* 
		Plugin Name: GWS Debugian
		Description: 👉👈 Hallo ich bin Debugian, der Liebe Debughelfer von Gally Websolutions. uwu
		Version: 1.6.3
	*/
	define('GWS_DEBUGIAN_VERSION', '1.7.1');
	// MESSAGE_INFO for the Git-Commit-Message: Copilot-Anweisungen für automatische Commit-Nachrichten hinzugefügt
	// Use this format to generate Git-Commit-Message: "Vx.x.x - MESSAGE_INFO"
	// The Git-Messages must be in german
	

	if(!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}

	// GWS Dashboard einbinden
	include_once(__DIR__.'/GWS-Dashboard/GWS-Dashboard.php');

	if(file_exists(__DIR__.'/settings.php')){
		include_once(__DIR__.'/settings.php');
	}

	if(!defined('GWS_DEBUGIAN_COLOR')) 		define('GWS_DEBUGIAN_COLOR', '#FF00C3');
	if(!defined('GWS_DEBUGIAN_DEV')) 		define('GWS_DEBUGIAN_DEV', 'devdocs');
	if(!defined('SUPERADMIN_DOMAIN')) 		define('SUPERADMIN_DOMAIN', 'gally-websolutions');
	if(!defined('GWS_DEBUGIAN_AUTOUPDATE')) define('GWS_DEBUGIAN_AUTOUPDATE', false);
	if(!defined('GWS_CAPTCHA_TIME')) 		define('GWS_CAPTCHA_TIME', 18);


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
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="submit_debugian" id="submit" class="button button-primary" value="Änderungen speichern"></p>
				</form>

				<h1>
					<strong class="gws">GWS</strong> GIT
				</h1>
				<?php if(isset($_GET['rebase']) && $_GET['rebase'] == 'force'){
					exec("cd ".__DIR__."
					git config pull.rebase true
					git pull
					git status", $output, $return);
					echo "<pre>";
					print_r($output);
					echo "</pre>";

					exec("cd ".__DIR__."/dependencies/gally_access/
					git config pull.rebase true
					git pull
					git status", $output, $return);
					echo "<pre>";
					print_r($output);
					echo "</pre>";
				}
				?>
				<form action="?page=gws-debugian" method="post" class="gws-repos">
					<div>
						<h2>
							Debugian
						</h2>
						<?php
							$repo_Gian = escapeshellarg('https://github.com/GallyDev/GWS-Debugian.git');
							$dir_Gian = escapeshellarg(__DIR__);

							if(isset($_POST['perform_debugian_update']) && $_POST['perform_debugian_update'] == '1'){
								// unset all possible local changes to prevent
								// error: cannot pull with rebase: You have unstaged changes.
								// error: Please commit or stash them.
								$git = "cd $dir_Gian && git reset --hard HEAD 2>&1";
								exec($git, $output, $return_var);
								$output = implode("\n", $output);


								$git = "cd $dir_Gian && git pull origin main 2>&1";

								exec($git, $output, $return_var);
								$output = implode("\n", $output);
								echo "<pre>Update:\n<small>$git</small>\n\n$output</pre>";
								
							}
							
							
							exec("cd $dir_Gian && git fetch origin 2>&1", $output, $return);
							exec("cd $dir_Gian && git status -uno 2>&1", $output, $return);

							$output = implode("\n", $output);

							if (strpos($output, 'Changes not staged for commit') !== false) {
								echo "<h3>🚨 Achtung: Lokale Änderungen beachten</h3>";
							}
							if (strpos($output, 'behind') !== false) {
								?>
									<p>Auf Github ist eine neue Version verfügbar.</p>
									<label>
										<input type="checkbox" name="perform_debugian_update" value="1">
										«Debugian»-Version an Github angleichen
									</label>
									<!-- <a href="?page=gws-debugian&debugian_update" class="button button-primary">«Debugian»-Version an Github angleichen</a> -->
								<?php
							} elseif (strpos($output, 'up to date') !== false) {
								echo "aktuell";
							} else {
								echo "🤷 - ruf rene: ia ia rnhulhu pfthoffn";
							}

							echo "<pre>$output</pre>";
						?>
					</div>

					<div>
						<h2>
							Gally Access
						</h2>
						<?php
							$repo_GA = escapeshellarg('https://github.com/GallyDev/Gally-Access.git');
							$dir_GA = __DIR__.'/dependencies/gally_access';

							if(!is_dir($dir_GA)){
								$git = "git clone $repo_GA $dir_GA 2>&1";
								exec($git, $output, $return_var);
								$output = implode("\n", $output);
								echo "<pre>Install:\n<small>$git</small>\n\n$output</pre>";
							}
							
							$dir_GA = escapeshellarg($dir_GA);

							if(isset($_POST['perform_gally_access_update']) && $_POST['perform_gally_access_update'] == '1'){
								$git = "cd $dir_GA && git pull origin main 2>&1";

								exec($git, $output, $return_var);
								$output = implode("\n", $output);
								echo "<pre>Update:\n<small>$git</small>\n\n$output</pre>";
								
								$gally_access = __DIR__.'/../../../gally_access';
								if (is_dir($gally_access)) {
									$dirs = array_filter(scandir($gally_access), function($item) use ($gally_access) {
										return is_dir("$gally_access/$item") && !in_array($item, ['.', '..']);
									});
									$gdir = array_pop($dirs);
									
									exec("rm -rf $gally_access/* 2>&1", $output, $return);
									exec("cp -r $dir_GA/* $gally_access 2>&1", $output, $return);
									exec("mv $gally_access/to_be_randomized $gally_access/$gdir 2>&1", $output, $return);

									$users = get_users();
									$emails = array();
									foreach ($users as $user) {
										$emails[] = $user->user_email;
									}
									file_put_contents("$gally_access/$gdir/$gdir", json_encode($emails));

								}

							}
							
							
							exec("cd $dir_GA && git fetch origin 2>&1", $output, $return);
							exec("cd $dir_GA && git status -uno 2>&1", $output, $return);

							$output = implode("\n", $output);

							if (strpos($output, 'Changes not staged for commit') !== false) {
								echo "<h3>🚨 Achtung: Lokale Änderungen beachten</h3>";
							}
							if (strpos($output, 'behind') !== false) {
								?>
									<p>Auf Github ist eine neue Version verfügbar.</p>
									<label>
										<input type="checkbox" name="perform_gally_access_update" value="1">
										«Gally Access»-Version an Github angleichen
									</label>
									<!-- <a href="?page=gws-debugian&gally_access_update" class="button button-primary">«Gally Access»-Version an Github angleichen</a> -->
								<?php
							} elseif (strpos($output, 'up to date') !== false) {
								echo "aktuell";
							} else {
								echo "🤷 - ruf rene: ia ia rnhulhu pfthoffn";
							}

							echo "<pre>$output</pre>";
						?>
					</div>
					<?php

						$url = "https://api.github.com/users/GallyDev/repos";

						$options = [
							"http" => [
								"header" => [
									"User-Agent: PHP"
								]
							]
						];

						$context = stream_context_create($options);
						$response = file_get_contents($url, false, $context);

						if ($response === false) {
							echo "Fehler beim Abrufen der Repositories.";
						}

						$repos = json_decode($response);
						$themes = [];
						foreach ($repos as $repo) {
							if(in_array($repo->name, ['GWS-Debugian', 'Gally-Access'])) continue;

							if(strpos($repo->name, 'GWS-WPT') === 0){
								$themes[] = $repo;
								continue;
							}

							$url_repo = $repo->clone_url;
							$dir_repo = escapeshellarg(__DIR__.'/dependencies/'.$repo->name);

							$toClone = $_POST['clone']??[];
							$toDelete = $_POST['delete']??[];
							$toUpdate = $_POST['update']??[];

							?>
								<div>
									<h2>
										<?=$repo->name?>
										<a href="<?=$repo->html_url?>" class="page-title-action" target="_blank">Doku</a>
									</h2>
									<p><?=$repo->description?></p>
									<?php 
										if(!is_dir(__DIR__.'/dependencies/'.$repo->name)){ 
											if(in_array($repo->name, $toClone)){
												$git = "git clone ".escapeshellarg($url_repo)." $dir_repo 2>&1";
												exec($git, $output, $return_var);
												$output = implode("\n", $output);
												echo "<pre>Install:\n<small>$git</small>\n\n$output</pre>";
											}else{
											?>
												<label>
													<input type="checkbox" name="clone[]" value="<?=$repo->name?>">
													installieren
												</label>
											<?php
											}
										} else { 
											if(in_array($repo->name, $toDelete)){
												exec("rm -rf $dir_repo 2>&1", $output, $return);
												$output = implode("\n", $output);
												echo "<pre>gelöscht.</pre>";
											}elseif(in_array($repo->name, $toUpdate)){
												$git = "cd $dir_repo && git pull origin main 2>&1";

												exec($git, $output, $return_var);
												$output = implode("\n", $output);
												echo "<pre>Update:\n<small>$git</small>\n\n$output</pre>";
											}else{
												exec("cd $dir_repo && git fetch origin 2>&1", $output, $return);
												exec("cd $dir_repo && git status -uno 2>&1", $output, $return);
												$output = implode("\n", $output);
												if (strpos($output, 'Changes not staged for commit') !== false) {
													echo "<h3>🚨 Achtung: Lokale Änderungen beachten</h3>";
												}elseif (strpos($output, 'behind') !== false) {
													?>
													<p>Auf Github ist eine neue Version verfügbar.</p>
													<label>
														<input type="checkbox" name="update[]" value="<?=$repo->name?>">
														«<?=$repo->name?>»-Version an Github angleichen
													</label>
													<?php
												}
											?>
												<label>
													<input type="checkbox" name="delete[]" value="<?=$repo->name?>">
													löschen
												</label>
											<?php
											}
										}
									?>
								</div>
							<?php
						}
					?>
					<span>
						<input type="submit" name="submit_debugian_repos" id="submit_debugian_repos" class="button button-primary" value="GIT-Aktionen ausführen">
					</span>
				</form>
				
				<h2>
					Themes
				</h2>
				<div class="gws-repos">
				<?php
					foreach ($themes as $theme) {
						$ret = '';
						if($_GET['theme'] == $theme->name && isset($_POST['theme_install'])){
							$theme_folder = $_POST['theme_name'];
							$theme_folder = preg_replace('/[\s_]+/', '-', $theme_folder);
							$theme_folder = preg_replace('/-+/', '-', $theme_folder);
							$theme_folder = 'GWS-'.preg_replace('/[^a-zA-Z0-9\-]/', '', $theme_folder);
							$repo_theme = escapeshellarg($theme->clone_url);
							$dir_theme = escapeshellarg(__DIR__.'/../../themes/'.$theme_folder);

							$git = "git clone $repo_theme $dir_theme 2>&1";
							exec($git, $output, $return_var);
							$output = implode("\n", $output);

							$basefile = file_get_contents(__DIR__.'/../../themes/'.$theme_folder.'/style.css');
							$basefile = preg_replace('/^Theme Name: .*/m', 'Theme Name: '.$_POST['theme_name'], $basefile, 1);
							$copyright =  'Copyright: © '.date('Y').' Gally Websolutions GmbH' ;
							$basefile = preg_replace('/^License: .*/m', "$copyright\nLicense: ", $basefile, 1);
							file_put_contents(__DIR__.'/../../themes/'.$theme_folder.'/style.css', $basefile);


							$ret .= "<pre>Theme Install:\n<small>$git</small>\n\n$output</pre>";
							$ret .= '<a href="/wp-admin/themes.php" class="button button-primary">Zum den Themes</a>';
						}


						?>
							<form action="?page=gws-debugian&theme=<?=$theme->name?>" method="post">
								<strong>
									<?= str_replace('GWS-WPT-', '', $theme->name) ?>
								</strong>
								<div><?= $theme->description ?></div>
								<?php if($ret != '') {
									echo $ret;
								} else{ ?>
									<a href="<?=$theme->html_url?>" target="_blank">Repository anzeigen</a>
									<input type="text" placeholder="Theme Name" name="theme_name" required>
									<input type="submit" name="theme_install" class="button button-primary" value="Theme installieren">
								<?php } ?>
							</form>
						<?php
					}

				?>
				</div>

				<h1>
					<strong class="gws">GWS</strong> Baguette
				</h1>
				<p>Baguette ist meine Freundin (m/w/d) und freut sich total, wenn sie weiss, was bei mir grad so passiert.</p>
				<?php if (strpos(__DIR__, GWS_DEBUGIAN_DEV) !== false) { ?>
					<p>Baguette interessiert sich leider zum Glück nicht für DEV-Instanzen. Das ist ihr zu technisch, leider. Sie ist halt ein Girl (m/w/d).</p>
				<?php } else { ?>
					<?php if(isset($_GET['pingBaguette'])): ?>
						<?php

							$url = get_site_url();
							$url = str_replace(['https://', 'http://', '/'], '', $url);
							$link = str_replace(['https://', 'http://', 'www.'], '', $url);
							$obj = file_get_contents('https://www.gally-websolutions.com/?baguette='.$link.'&version='.GWS_DEBUGIAN_VERSION);
							$obj = json_decode($obj);
						?>
						<div class="notice notice-success is-dismissible">
							<p>Baguette wurde informiert und sie hat voll süss geantworetet:</p>
							<pre><?= json_encode($obj, JSON_PRETTY_PRINT) ?></pre>
						</div>
					<?php else: ?>
						<a href="/wp-admin/options-general.php?page=gws-debugian&pingBaguette" class="page-title-action">Baguette informieren</a>
					<?php endif; ?>
				<?php } ?>
				<br><br>
			<?php endif; ?>
			
			<h1>
				<strong class="gws">GWS</strong> Hosting-Zugangsdaten
				<?php if($superadmin && !isset($_GET['edit'])): ?>
					<a href="/wp-admin/options-general.php?page=gws-debugian&edit" class="page-title-action">Anpassen</a>
				<?php endif; ?>
			</h1>


			<style>
				.page-title-action{
					margin-left: 1em;
				}
				th,td{
					text-align: left;
					vertical-align: top;
					max-width: 50ch;
				}
				table span{
					cursor: pointer;
					user-select: all;
					display: inline-block;
					padding: .25em .5em;
					background: #eef;
					border: 1px solid #ccc;
					border-radius: .25em;
				}
				table span:hover{
					background: #ddf;
				}
				table span.copied{
					background: #efe;
				}
				table table td{
					padding: 0 1ch 0 0;
					vertical-align: middle;
				}
				[scope=row],[scope=row]+*{
					padding-bottom: .5em;
					padding-right: 1em;
				}
				[scope=row]:not(.first){
					padding-top: calc(.45em + 1px);
				}
			</style>
			<?php if(isset($_GET['edit']) && $superadmin): ?>
				<form method="post" action="?page=gws-debugian">
					<table>
						<tbody>
							<tr>
								<th scope="row">
									<label>
										<input type="checkbox" name="hosting[enabled]" value="1" <?=isset($settings["hosting"]) ? 'checked' : ''?>>
										Hostingverwaltung
									</label>
								</th>
								<td>
									<input type="text" name="hosting[label]" value="<?=$settings["hosting"]["label"]??''?>" placeholder="Label">
									<input type="text" name="hosting[url]" value="<?=$settings["hosting"]["url"]??''?>" placeholder="URL">
								</td>
							</tr>
							<tr>
								<th scope="row">
									Login / Passwort
								</th>
								<td>
									<input type="text" name="hosting[username]" value="<?=$settings["hosting"]["username"]??''?>" placeholder="Benutzername">
									<input type="text" name="hosting[password]" value="<?=$settings["hosting"]["password"]??''?>" placeholder="Passwort">
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label>
										<input type="checkbox" name="email[enabled]" value="1" <?=isset($settings["email"]) ? 'checked' : ''?>>
										E-Mail
									</label>
								</th>
								<td>
									<table>
										<tr>
											<td>POP-Server</td>
											<td><input type="text" name="email[pop]" value="<?=$settings["email"]["pop"]??''?>" placeholder="Adresse"></td>
										</tr>
										<tr>
											<td>SMTP-Server</td>
											<td><input type="text" name="email[smtp]" value="<?=$settings["email"]["smtp"]??''?>" placeholder="Adresse"></td>
										</tr>
										<tr>
											<td>Webmail</td>
											<td><input type="text" name="email[webmail]" value="<?=$settings["email"]["webmail"]??''?>" placeholder="Link"></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label>
										<input type="checkbox" name="ftp[enabled]" value="1" <?=isset($settings["ftp"]) ? 'checked' : ''?>>
										FTP
									</label>
								</th>
								<td>
									<table>
										<tr>
											<td>Server</td>
											<td><input type="text" name="ftp[server]" value="<?=$settings["ftp"]["server"]??''?>" placeholder="Adresse"></td>
										</tr>
										<tr>
											<td>Login</td>
											<td><input type="text" name="ftp[login]" value="<?=$settings["ftp"]["login"]??''?>" placeholder="Benutzername"></td>
										</tr>
										<tr>
											<td>Passwort</td>
											<td><input type="text" name="ftp[password]" value="<?=$settings["ftp"]["password"]??''?>" placeholder="Passwort"></td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="submit_debugian_hosting" id="submit" class="button button-primary" value="Änderungen speichern"></p>
				</form>
			
			<?php else: ?>
				<table>
					<tbody>
						<?php if(isset($settings["hosting"])): ?>
							<tr>
								<th scope="row" class="first">
									Hostingverwaltung
								</th>
								<td>
									<a href="<?=$settings["hosting"]["url"]?>" target="_blank"><?=$settings["hosting"]["label"]?></a><br>
								</td>
							</tr>
							<tr>
								<th scope="row">
									Login / Passwort
								</th>
								<td>
									<span><?=$settings["hosting"]["username"]?></span> / 
									<span><?=$settings["hosting"]["password"]?></span>
									<br>
									<small>Hier können Sie E-Mailadressen verwalten. Wenn Sie Ihr E-Mailpasswort ändern oder einen Autoresponder einrichten möchten, können Sie sich hier alternativ mit Ihrer E-Mailadresse und Passwort einloggen.</small>
								</td>
							</tr>
						<?php endif; ?>
						<?php if(isset($settings["email"])): ?>
							<tr>
								<th scope="row">
									E-Mail
								</th>
								<td>
									<table>
										<tr>
											<td>POP-Server</td>
											<td><span><?=$settings["email"]["pop"]?></span></td>
										</tr>
										<tr>
											<td>SMTP-Server</td>
											<td><span><?=$settings["email"]["smtp"]?></span></td>
										</tr>
									</table>
									<small>Server erfordert Authentifizierung: E-Mail & Passwort</small><br>
									Webmail: <a href="<?=$settings["email"]["webmail"]?>" target="_blank"><?=$settings["email"]["webmail"]?></a>
								</td>
							</tr>
						<?php endif; ?>
						<?php if(isset($settings["ftp"])): ?>
							<tr>
								<th scope="row">
									FTP
								</th>
								<td>
									<table>
										<tr>
											<td>Server</td>
											<td><span><?=$settings["ftp"]["server"]?></span></td>
										</tr>
										<tr>
											<td>Login</td>
											<td><span><?=$settings["ftp"]["login"]?></span></td>
										</tr>
										<tr>
											<td>Passwort</td>
											<td><span><?=$settings["ftp"]["password"]?></span></td>
										</tr>
									</table>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
				<script>
					document.querySelectorAll('span').forEach(span => {
						span.addEventListener('click', () => {
							navigator.clipboard.writeText(span.innerText);
							span.classList.add('copied');
							setTimeout(() => {
								span.classList.remove('copied');
							}, 1000);
						});
					});
				</script>
			<?php endif; ?>
		</div>
		<?php
	}
?>