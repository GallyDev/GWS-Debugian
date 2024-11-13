<?php
	/* 
		Plugin Name: GWS Debugian
		Description: 👉👈 Hallo ich bin Debugian, der Liebe Debughelfer von Gally Websolutions. uwu
		Version: 1.0
	*/

	// if loggedin add something to the admin bar

	define('GWS_DEBUGIAN_COLOR', '#f0f');

	add_action('admin_bar_menu', 'gws_debugian_admin_bar_menu', 50);
	function gws_debugian_admin_bar_menu($admin_bar) {
		// if loggedin but in frontend
		if (is_user_logged_in() && !is_admin()) {
			
			$admin_bar->add_menu(array(
				'id' => 'gws-debugian',
				'title' => 'Debugian',
				'href' => '#',
				'meta' => array(
					'class' => 'gws-debugian',
				),
			));

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
