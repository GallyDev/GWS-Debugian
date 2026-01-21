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