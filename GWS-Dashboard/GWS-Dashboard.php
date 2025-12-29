<?php 
	session_start();
	define('GWS_DASH_VERSION', '1.3.6');


	add_action('admin_menu', 'gws_dashboard_settings_menu');
	function gws_dashboard_settings_menu() {
		if(strpos(wp_get_current_user()->user_email, SUPERADMIN_DOMAIN)){
			add_options_page('GWS Dashboard', '<strong class="gws">GWS</strong> Dashboard', 'manage_options', 'gws-dashboard', 'gws_dashboard_settings');
		}
	}

	add_action('admin_enqueue_scripts', function ($hook) {
		wp_enqueue_style('gws-dashboard', plugin_dir_url(__FILE__) . 'gws-dashboard.css', [], GWS_DASH_VERSION);
	});

	function gws_dashboard_settings() {
		if(!current_user_can('manage_options')){
			wp_die(esc_html__('Du hast keine Berechtigung, um diese Aktion durchzuführen.', 'gws-dashboard'));
		}

		static $available_dashboard_widgets = null;

		if ($available_dashboard_widgets === null) {
			require_once ABSPATH . 'wp-admin/includes/dashboard.php';

			if (function_exists('set_current_screen')) {
				set_current_screen('dashboard');
			}

			if (function_exists('wp_dashboard_setup')) {
				wp_dashboard_setup();
			}

			global $wp_meta_boxes;
			$available_dashboard_widgets = [];

			if (!empty($wp_meta_boxes['dashboard'])) {
				foreach ($wp_meta_boxes['dashboard'] as $context) {
					foreach ($context as $priority) {
						foreach ($priority as $widget_id => $widget) {
							$available_dashboard_widgets[$widget_id] = wp_strip_all_tags($widget['title']);
						}
					}
				}
			}
		}

		if(isset($_GET['dashboard_update'])){
			check_admin_referer();

			$users = get_users(['fields' => ['ID']]);
			foreach($users as $user){
				$hidden_widgets = [];
				foreach($available_dashboard_widgets as $widget_id => $title){
					$input_name = 'gws_dashboard_user_' . $user->ID . '_widget_' . $widget_id;
					if(!isset($_POST[$input_name])){
						$hidden_widgets[] = $widget_id;
					}
				}
				update_user_meta($user->ID, 'metaboxhidden_dashboard', $hidden_widgets);
			}

			echo '<div class="updated"><p>' . esc_html__('Einstellungen gespeichert.', 'gws-dashboard') . '</p></div>';
			
		}


		echo '<h1>' . esc_html__('Dashboards pro User', 'gws-dashboard') . '</h1>';

		$users = get_users([
			'fields'  => ['ID', 'display_name'],
			'orderby' => 'display_name',
			'order'   => 'ASC',
		]);
		echo '<form method="post" action="?page=' . esc_attr($_GET['page']) . '&dashboard_update">';
		echo '<table class="widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__('User', 'gws-dashboard') . '</th>';
		echo '<th>' . esc_html__('Dashboard-Widgets', 'gws-dashboard') . '</th>';
		echo '</tr></thead><tbody>';

		foreach ($users as $user) {
			$user_has_settings = false;
			echo "<tr><td>$user->display_name</td><td>";

			$hidden_widgets = get_user_meta($user->ID, 'metaboxhidden_dashboard', true);
			if (!is_array($hidden_widgets)) {
				$hidden_widgets = [];
			}

			$active_widgets = array_diff(array_keys($available_dashboard_widgets), $hidden_widgets);

			if ($active_widgets) {
				echo '<ul class="gws-dashboard-widget-list">';
				foreach ($active_widgets as $widget_id) {
					$title = $available_dashboard_widgets[$widget_id] ?? $widget_id;
					?>
						<li class="id_<?= esc_attr($widget_id); ?> active">
							<label>
								<input type="checkbox" checked name="gws_dashboard_user_<?= esc_attr($user->ID); ?>_widget_<?= esc_attr($widget_id); ?>">
								<?= esc_html($title); ?>
							</label>
						</li>
					<?php
				}
				foreach ($hidden_widgets as $widget_id) {
					$title = $available_dashboard_widgets[$widget_id] ?? $widget_id;
					?>
						<li class="id_<?= esc_attr($widget_id); ?> inactive">
							<label>
								<input type="checkbox" name="gws_dashboard_user_<?= esc_attr($user->ID); ?>_widget_<?= esc_attr($widget_id); ?>">
								<?= esc_html($title); ?>
							</label>
						</li>
					<?php
				}
				echo '</ul>';
			} else {
				echo esc_html__('Keine Widgets aktiviert.', 'gws-dashboard');
			}
			
			echo '</td></tr>';
		}

		?>
			</tbody>
			<tfoot>
				<tr>
					<th></th>
					<th>
						<input type="button" onclick="gwsDash_justGally()" class="button" value="<?php echo esc_attr__('Nur Gally auswählen', 'gws-dashboard'); ?>">
						<input type="button" onclick="gwsDash_none()" class="button" value="<?php echo esc_attr__('Alle abwählen', 'gws-dashboard'); ?>">
					</th>
				</tr>
			</tfoot>
		</table>
		<script>
			const gwsDash_none = () => {
				document.querySelectorAll('.gws-dashboard-widget-list input[type="checkbox"]').forEach(cb => cb.checked = false);
			}
			const gwsDash_justGally = () => {
				gwsDash_none();
				document.querySelectorAll('.gws-dashboard-widget-list [class^="id_gws"] input[type="checkbox"]').forEach(cb => cb.checked = true);
			}
		</script>
		<?php
		

		echo '<div class="submit"><input type="submit" class="button button-primary" value="' . esc_attr__('Änderungen speichern', 'gws-dashboard') . '"></div>';
		wp_nonce_field();
		echo '</form>';
		echo '</div>';

	}

	add_action('wp_dashboard_setup', function () {
		wp_add_dashboard_widget('gws_dashboard_gally_support', 'Gally Support', 'gws_dashboard_render_support_widget');
		wp_add_dashboard_widget('gws_dashboard_gally_news', 'Gally News', 'gws_dashboard_render_news_widget');
	});

	
	$php_file = basename($_SERVER['PHP_SELF']);
	if($php_file == 'index.php' && is_admin()){
		if(!isset($_SESSION['gws_dashboard_data_fetched']) || (time() - $_SESSION['gws_dashboard_data_fetched']) > 300){
			$gws_dashboard_data = file_get_contents('https://www.gally-websolutions.com/gaw/dashboard/');
			$_SESSION['gws_dashboard_data'] = $gws_dashboard_data;
			$_SESSION['gws_dashboard_data_fetched'] = time();
		} else {
			$gws_dashboard_data = $_SESSION['gws_dashboard_data'];
		}
	}



	if(!isset($gws_dashboard_data)){
		$gws_dashboard_data = '{}';
	}
	try{
		$gws_dashboard_data = json_decode($gws_dashboard_data, true);
	} catch(Exception $e){
		$gws_dashboard_data = [];
	}

	function gws_dashboard_render_support_widget() { 
		global $gws_dashboard_data;

		echo '<small>'.
			(300 - (time() - $_SESSION['gws_dashboard_data_fetched'])).
			'</small>';

		if($gws_dashboard_data['spezial']){
			echo '<div class="hervorheben">';
			echo $gws_dashboard_data['spezial'];
			echo '</div>';
		}
		echo $gws_dashboard_data['support'] ?? '';

	}
	function gws_dashboard_render_news_widget() { 
		global $gws_dashboard_data;
		$posts = $gws_dashboard_data['posts'] ?? [];
		foreach($posts as $post){
			?>
				<a class="<?=implode(' ',$post['tags'])?>" href="<?= esc_url($post['link']); ?>" target="_blank" rel="noopener">
					<datetime><?= esc_html(date('d.m.Y', strtotime($post['date']))); ?></datetime>
					<strong><?= esc_html($post['title']); ?></strong>
					<span><?= esc_html($post['excerpt']); ?></span>
				</a>
			<?php
		}
	}


	session_write_close();