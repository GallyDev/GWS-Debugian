<?php
	/* 
		Plugin Name: GWS Debugian
		Description: 👉👈 Hallo ich bin Debugian, der Liebe Debughelfer von Gally Websolutions. uwu
		Version: 1.2.0
	*/

	// if loggedin add something to the admin bar

	define('GWS_DEBUGIAN_COLOR', '#f0f');
	define('SUPERADMIN_DOMAIN', 'gally-websolutions');
	
	if(isset($_POST['submit'])){
		$settings = file_get_contents(__DIR__.'/settings.json');
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

	if(isset($_POST['submit_hosting'])){
		$settings = file_get_contents(__DIR__.'/settings.json');
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

	$settings = file_get_contents(__DIR__.'/settings.json');
	$settings = json_decode($settings, true);

	if(!isset($settings['post_types'])) $settings['post_types'] = ['page'];
	
	if(isset($_GET['gally_access_install'])){
		
		$gally_access = __DIR__.'/dependencies/gally_access';
		if(is_dir($gally_access)) rename($gally_access, __DIR__.'/../../../gally_access');

		header('Location: /gally_access/?install');
	}

	add_action('admin_bar_menu', 'gws_debugian_admin_bar_menu', 50);
	function gws_debugian_admin_bar_menu($admin_bar) {
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
			$gally_access = __DIR__.'/../../../gally_access';
			if (!is_dir($gally_access)) {
				$doDebugian = true;
				$admin_bar->add_menu(array(
					'id' => 'gws-debugian-gally-access',
					'title' => 'Gally Access installieren',
					'href' => '?gally_access_install',
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
			}
		}

		if($doDebugian){
			$admin_bar->add_menu(array(
				'id' => 'gws-debugian',
				'title' => 'Debugian',
				'href' => '#',
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
		$types = $settings['post_types'];
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

		$url = get_site_url();
		$url = str_replace(['https://', 'http://', '/'], '', $url);

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
					<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Änderungen speichern"></p>
				</form>
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
					<p class="submit"><input type="submit" name="submit_hosting" id="submit" class="button button-primary" value="Änderungen speichern"></p>
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