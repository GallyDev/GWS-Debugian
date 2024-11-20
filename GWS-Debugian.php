<?php
	/* 
		Plugin Name: GWS Debugian
		Description: 👉👈 Hallo ich bin Debugian, der Liebe Debughelfer von Gally Websolutions. uwu
		Version: 1.1.0
	*/

	// if loggedin add something to the admin bar

	define('GWS_DEBUGIAN_COLOR', '#f0f');
	
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
		add_options_page('GWS Debugian', '<strong class="gws">GWS</strong> Debugian', 'manage_options', 'gws-debugian', 'gws_debugian_settings_page');
	}

	function gws_debugian_settings_page() {
		global $settings;
		?>
		<div class="wrap">
			<h1><strong class="gws">GWS</strong> Debugian</h1>
			<form method="post">
				
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
		</div>
		<?php
	}