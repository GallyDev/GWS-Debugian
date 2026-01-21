<?php if(!$superadmin || isset($_GET['edit'])) exit; ?>

<h1>
	<strong class="gws">GWS</strong> GIT
</h1>
<p><strong>Mantra:</strong> Wir installieren Module erst, wenn wir sie auch tatsächlich nutzen. </p>
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
		try {
			$response = file_get_contents($url, false, $context);
			if ($response === false) {
				echo "Fehler beim Abrufen der Repositories.";
			}
		} catch (Exception $e) {
			echo "Fehler beim Abrufen der Repositories: " . $e->getMessage();
		}

		$repos = json_decode($response);
		$themes = [];
		if(!$repos){
			$repos = [];
		}
		foreach ($repos as $repo) {
			if(in_array($repo->name, ['GWS-Debugian', 'Gally-Access'])) continue;

			if(strpos($repo->name, 'GWS-WPTModule') === 0) continue;
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
			if(isset($_GET['theme']) && $_GET['theme'] == $theme->name && isset($_POST['theme_install'])){
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