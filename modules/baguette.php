<?php if(!$superadmin || isset($_GET['edit'])) exit; ?>

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
		<?php

			if (strpos($host, 'www.') !== 0) {
				?>
					<p><strong>Achtung:</strong> Deine Domain beginnt nicht mit WWW und das könnte ein Indiz dafür sein, dass du auf einer DEV-Domain bist. Baguette interessiert sich leider zum Glück nicht für DEV-Instanzen. Wenn das eine DEV-Instanz ist und du Baguette trotzdem informierst, wird sie dich finden und verhauen. 😡 Ja, so ist Baguette.</p>
				<?php
			}
		?>
	<?php endif; ?>
<?php } ?>
<?php 
	global $blacklist;
	global $iplist;
	global $allowlist;
	$blacklist = 'https://raw.githubusercontent.com/splorp/wordpress-comment-blocklist/refs/heads/master/blacklist.txt';
	$iplist = 'https://cdn.uptimerobot.com/api/IPv4andIPv6.txt';
	$allowlist = ABSPATH . 'wp-content/uploads/aios/firewall-rules/allowlist.php';
	

	function checkKeywords () {
		global $blacklist;
		$keywords = file_get_contents($blacklist);
		$keywordCount = substr_count($keywords, "\n");

		// Wordpress Setting disallowed_keys
		$wp_keywords = get_option('disallowed_keys', '');
		$wp_count = substr_count($wp_keywords, "\n")+1;
		if(trim($wp_keywords) === '') $wp_count = 0;

		return [
			'count' => $keywordCount,
			'wp_count' => $wp_count
		];
	}

	function checkIPs () {
		global $iplist;
		global $allowlist;

		$ips = file_get_contents($iplist);
		$ipCount = substr_count($ips, "\n");

		$aios_allowlist = explode('*/',file_get_contents($allowlist));
		$aios_ips = trim($aios_allowlist[1] ?? '');
		$aios_count = substr_count($aios_ips, "\n")+1;
		if(trim($aios_ips) === '') $aios_count = 0;

		return [
			'count' => $ipCount,
			'wp_count' => $aios_count
		];
	}

	$info = '';
	if (isset($_POST['submit_spaguetti'])) {
		$toDo = $_POST['spaguetti']??[];
		$info .= '<h3>Baguette hat Folgendes erledigt:</h3>';
		if (in_array('keywords', $toDo)) {
			$keywords = file_get_contents($blacklist);
			update_option('disallowed_keys', $keywords);
			$info .= '<p>- Spam-Keywords aktualisiert.</p>';
		}
		if (in_array('ips', $toDo)) {
			$ips = file_get_contents($iplist);
			$aios_allowlist = explode('*/',file_get_contents($allowlist));
			$aios_allowlist[1] = "\n".$ips;
			file_put_contents($allowlist, implode('*/', $aios_allowlist));
			if (function_exists('aio_wp_security')) {
				$aio = aio_wp_security();
				if (method_exists($aio->firewall, 'clear_firewall_rules_cache')) {
					$aio->firewall->clear_firewall_rules_cache();
				}
			}
			
			$info .= '<p>- UptimeRobot IPs in AIOS aktualisiert.</p>';
		}
	}
	if ($info !== '') {
		?>
		<div class="notice notice-success is-dismissible">
			<?= $info ?>
		</div>
		<?php
	}
?>

<h2>Baguettes Wachposten</h2>
<p>
	Als kleine Familie im Jahr <?= date('Y') ?> muss Baguette leider nebenbei etwas Geld verdienen. 
	Darum arbeitet sie nebenher auf dem Wachposten. Sie hat darum tiefes Sicherheitswissen über Spam und UptimeRobot.
</p>
<p>
	<a href="/wp-admin/options-general.php?page=gws-debugian&checkKeywords" class="button">Spam-Keywords prüfen</a>
	<a href="/wp-admin/options-general.php?page=gws-debugian&checkIPs" class="button">UptimeRobot IPs prüfen</a>
	<a href="/wp-admin/options-general.php?page=gws-debugian&checkBoth" class="button">Beides prüfen</a>
</p>
<form action="?page=gws-debugian" method="post" class="gws-repos">
<?php
if (isset($_GET['checkKeywords']) || isset($_GET['checkBoth'])) {
	$data = checkKeywords();
	?>
		<div>
			<p>Die Spam-Blacklist enthält <strong><?= $data['count'] ?></strong> Einträge. In WordPress sind aktuell <strong><?= $data['wp_count'] ?></strong> Keywords hinterlegt.</p>
			<p>Das ist ein Unterschied von <strong><?= $data['count'] - $data['wp_count'] ?></strong> Keywords.</p>

			<label>
				<input type="checkbox" name="spaguetti[]" value="keywords">
				Liste angleichen
			</label>
		</div>
	<?php
}

if (isset($_GET['checkIPs']) || isset($_GET['checkBoth'])) {
	$data = checkIPs();
	?>
		<div>
			<p>Die UptimeRobot-Liste umfasst <strong><?= $data['count'] ?></strong> IPs. Im AIOWPS-Plugin sind <strong><?= $data['wp_count'] ?></strong> IPs auf der Whitelist.</p>
			<p>Das ist ein Unterschied von <strong><?= $data['count'] - $data['wp_count'] ?></strong> IPs.</p>
			<label>
				<input type="checkbox" name="spaguetti[]" value="ips">
				Liste angleichen
			</label>
		</div>
	<?php
}
?>
	<span>
		<input type="submit" name="submit_spaguetti" id="submit_spaguetti" class="button button-primary" value="Baguette beauftragen">
	</span>
</form>
