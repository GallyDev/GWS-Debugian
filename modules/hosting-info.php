<?php if(!$superadmin || isset($_GET['edit'])) exit; ?>

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