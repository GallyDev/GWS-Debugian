<?php
	// if you can find the directory «to_be_randomized» in this directory, set install mode
	if(is_dir('to_be_randomized') && !isset($_GET['install'])){
		$_GET['install'] = 'later';
	}

	if(isset($_GET['install'])){
		if($_GET['install'] == 'now'){
			$idir = array_pop(array_filter(glob('*'), 'is_dir'));
			// create strong base64 string
			$strong = true;
			$bytes = openssl_random_pseudo_bytes(16, $strong);
			$gdir = base64_encode($bytes);
			$gdir = str_replace('=', '', $gdir);
			$gdir = str_replace('+', '', $gdir);
			$gdir = str_replace('/', '', $gdir);
			$gdir = "acc$gdir";

			// rename directory
			rename($idir, $gdir);
			$base_htaccess = file_get_contents('../.htaccess');
			
			// check if htaccess contains # BEGIN GALLY ACCESS
			if(strpos($base_htaccess, '# BEGIN GALLY ACCESS') === false){
				$htaccess = file_get_contents('root.htaccess');
				$base_htaccess = $base_htaccess."\n\n".$htaccess;
				file_put_contents('../.htaccess', $base_htaccess);
			}

			$admin_htaccess = file_get_contents('wp-admin.htaccess');
			file_put_contents('../wp-admin/.htaccess', $admin_htaccess);
			
			header('Location: /wp-admin/');

			die();
		}
		
	}else{

		$mailMode = 'local'; // local: mail() | remote: external mailer
		

		
		if( isset($_POST['email']) ) {
			$gdir = array_pop(array_filter(glob('*'), 'is_dir'));;

			if($_SERVER['REMOTE_ADDR'] == '80.74.142.130'){
				$emails = json_decode(file_get_contents($gdir.'/'.$gdir));
				$email = $_POST['email'];
				if( in_array($email, $emails)
				|| strpos($email, '@gally-websolutions.com') !== false){
					echo $gdir;
				}
				die();
			}

			require_once __DIR__.'/../wp-load.php';
			$users = get_users();
			$emails = array();
			foreach ($users as $user) {
				$emails[] = $user->user_email;
			}
			file_put_contents($gdir.'/'.$gdir, json_encode($emails));

			$email = $_POST['email'];

			if( in_array($email, $emails) 
			|| strpos($email, '@gally-websolutions.com') !== false){
				
				if($mailMode == 'remote') {
					$curlString = get_site_url().';'.$email;
					$curlUrl = 'https://www.gally-websolutions.com/gaw/remoteAccessMailer.php';
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $curlUrl);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, 'data='.$curlString);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$response = curl_exec($ch);
					curl_close($ch);
				}else{

					$domain = $_SERVER['HTTP_HOST'];
					$domain = str_replace('www.', '', $domain);
					$noreply = 'noreply@'.$domain;

					$to = $email;
					$subject = "Gally Access ($domain)";
					$message = "Sie können via folgendem Link den Adminbereich freischalten:\n".get_site_url().'/gally_access/'.$gdir;
					$headers = "From: $noreply" . "\r\n" .
						"Reply-To: $noreply" . "\r\n" .
						"Content-Type: text/plain; charset=UTF-8" . "\r\n" .
						"X-Mailer: PHP/" . phpversion();
					
					mail($to, $subject, $message, $headers);
				}
			}
		}
	}
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Gally Access</title>
	<link rel="stylesheet" href="style.css?<?=date('YmdHis')?>">
</head>
<body>
	<div class="modal">
	<svg version="1.1" id="logo" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="511.692px" height="163px" viewBox="0 0 511.692 163" enable-background="new 0 0 511.692 163" xml:space="preserve">
		<rect width="162.999" height="163"/>
		<g>
			<path fill="#FFFFFF" d="M52.656,94.25c0,8.047-3.627,14.621-13.543,14.621c-3.796,0-7.254-1.078-8.726-1.533l0.284-3.568
				c2.209,1.133,5.382,2.039,8.5,2.039c9.179,0,9.973-6.685,9.973-14.844h-0.115c-1.926,4.191-5.099,5.665-8.783,5.665
				c-9.235,0-12.182-8.046-12.182-14.448c0-8.785,3.401-15.13,11.899-15.13c3.854,0,6.346,0.51,9.01,3.967h0.113v-3.287h3.57V94.25z
				M40.304,93.572c6.46,0,8.782-6.121,8.782-11.391c0-6.971-2.04-12.07-8.556-12.07c-6.799,0-8.555,6.458-8.555,12.07
				C31.975,87.848,34.241,93.572,40.304,93.572z"/>
			<path fill="#FFFFFF" d="M77.874,91.984h-0.113c-1.587,3.457-5.61,5.326-9.18,5.326c-8.216,0-9.519-5.552-9.519-8.159
				c0-9.69,10.313-10.145,17.792-10.145h0.681v-1.473c0-4.928-1.758-7.422-6.575-7.422c-3.002,0-5.836,0.681-8.498,2.378v-3.455
				c2.208-1.078,5.949-1.984,8.498-1.984c7.14,0,10.144,3.23,10.144,10.765v12.752c0,2.323,0,4.078,0.284,6.062h-3.514V91.984z
				M77.535,82.066h-1.021c-6.176,0-13.542,0.625-13.542,6.973c0,3.795,2.72,5.211,6.005,5.211c8.387,0,8.558-7.309,8.558-10.426
				V82.066z"/>
			<path fill="#FFFFFF" d="M89.606,54.131h3.569V96.63h-3.569V54.131z"/>
			<path fill="#FFFFFF" d="M102.185,54.131h3.571V96.63h-3.571V54.131z"/>
			<path fill="#FFFFFF" d="M122.811,91.814h0.058l8.329-24.083h3.74l-11.559,33.206c-1.36,3.965-3.401,7.934-7.423,7.934
				c-1.302,0-2.664-0.171-3.854-0.455l0.342-3.059c0.678,0.226,1.358,0.451,2.662,0.451c3.059,0,4.08-2.549,5.213-6.287l0.737-2.551
				l-10.258-29.239h3.911L122.811,91.814z"/>
		</g>
		<g>
			<path d="M214.433,96.63h-4.305l-7.878-24.989h-0.113l-7.875,24.989h-4.308l-9.292-28.898h3.911l7.591,24.989h0.113l8.046-24.989
				h4.308l7.649,24.989h0.113l7.99-24.989h3.569L214.433,96.63z"/>
			<path d="M248.208,95.725c-2.494,1.019-5.724,1.586-8.387,1.586c-9.577,0-13.146-6.459-13.146-15.129
				c0-8.84,4.873-15.13,12.183-15.13c8.159,0,11.501,6.574,11.501,14.336v1.813h-19.774c0,6.119,3.285,11.049,9.52,11.049
				c2.607,0,6.402-1.074,8.103-2.152V95.725z M246.453,80.139c0-5.042-2.042-10.027-7.367-10.027c-5.27,0-8.5,5.27-8.5,10.027H246.453
				z"/>
			<path d="M256.824,54.131h3.568v18.475h0.113c0.51-1.418,3.343-5.555,9.01-5.555c8.499,0,11.897,6.346,11.897,15.13
				c0,8.558-3.963,15.129-11.897,15.129c-4.137,0-7.083-1.586-9.01-5.271h-0.113v4.59h-3.568V54.131z M268.95,70.112
				c-6.518,0-8.558,6.799-8.558,12.07c0,5.27,2.211,12.068,8.558,12.068c6.799,0,8.555-6.46,8.555-12.068
				C277.504,76.57,275.749,70.112,268.95,70.112z"/>
			<path d="M286.458,92.437c2.155,1.077,4.76,1.813,7.538,1.813c3.398,0,6.402-1.868,6.402-5.156c0-6.857-13.881-5.779-13.881-14.166
				c0-5.722,4.644-7.876,9.406-7.876c1.529,0,4.588,0.341,7.137,1.303l-0.338,3.116c-1.869-0.849-4.421-1.358-6.403-1.358
				c-3.685,0-6.234,1.132-6.234,4.816c0,5.382,14.221,4.702,14.221,14.166c0,6.121-5.721,8.217-10.084,8.217
				c-2.777,0-5.553-0.34-8.103-1.359L286.458,92.437z"/>
			<path d="M321.99,67.051c9.348,0,13.484,7.252,13.484,15.13c0,7.877-4.137,15.129-13.484,15.129
				c-9.352,0-13.488-7.252-13.488-15.129C308.502,74.304,312.639,67.051,321.99,67.051z M321.99,94.25
				c6.23,0,9.574-5.383,9.574-12.068c0-6.688-3.344-12.07-9.574-12.07c-6.234,0-9.578,5.382-9.578,12.07
				C312.412,88.867,315.756,94.25,321.99,94.25z"/>
			<path d="M342.219,54.131h3.572V96.63h-3.572V54.131z"/>
			<path d="M377.352,89.83c0,2.21,0,4.479,0.227,6.8h-3.457v-5.155h-0.113c-1.189,2.607-3.342,5.836-9.352,5.836
				c-7.137,0-9.857-4.758-9.857-11.107V67.732h3.57v17.567c0,5.44,1.926,8.951,6.855,8.951c6.516,0,8.559-5.721,8.559-10.539V67.732
				h3.568V89.83z"/>
			<path d="M398.771,70.792h-6.574V89.49c0,2.607,0.965,4.76,3.854,4.76c1.361,0,2.268-0.281,3.289-0.678l0.225,2.945
				c-0.848,0.34-2.607,0.793-4.363,0.793c-6.346,0-6.572-4.363-6.572-9.633V70.792h-5.666v-3.061h5.666v-6.97l3.568-1.246v8.216h6.574
				V70.792z"/>
			<path d="M408.746,60.026h-3.57v-4.762h3.57V60.026z M405.176,67.732h3.57V96.63h-3.57V67.732z"/>
			<path d="M429.031,67.051c9.35,0,13.486,7.252,13.486,15.13c0,7.877-4.137,15.129-13.486,15.129s-13.486-7.252-13.486-15.129
				C415.545,74.304,419.682,67.051,429.031,67.051z M429.031,94.25c6.232,0,9.576-5.383,9.576-12.068c0-6.688-3.344-12.07-9.576-12.07
				s-9.576,5.382-9.576,12.07C419.455,88.867,422.799,94.25,429.031,94.25z"/>
			<path d="M449.262,74.532c0-2.21,0-4.477-0.227-6.8h3.457v5.155h0.111c1.191-2.605,3.348-5.835,9.352-5.835
				c7.141,0,9.859,4.761,9.859,11.106V96.63h-3.57V79.064c0-5.439-1.926-8.953-6.855-8.953c-6.516,0-8.555,5.723-8.555,10.538v15.98
				h-3.572V74.532z"/>
			<path d="M478.561,92.437c2.152,1.077,4.756,1.813,7.535,1.813c3.4,0,6.404-1.868,6.404-5.156c0-6.857-13.885-5.779-13.885-14.166
				c0-5.722,4.646-7.876,9.406-7.876c1.529,0,4.592,0.341,7.141,1.303l-0.342,3.116c-1.867-0.849-4.418-1.358-6.402-1.358
				c-3.682,0-6.232,1.132-6.232,4.816c0,5.382,14.223,4.702,14.223,14.166c0,6.121-5.721,8.217-10.086,8.217
				c-2.775,0-5.551-0.34-8.104-1.359L478.561,92.437z"/>
		</g>
	</svg>
	<hr>
		<small>
			Ihr sicherer Zugang
			<a href="https://www.gally-websolutions.com/gaw/">zum Gally Access Webspeicher</a>
		</small>
		<h1>Gally Access</h1>
		<?php if(isset($_GET['install'])){ ?>
			<p>
				Hoi Gally-Member. Möchtest du Gally-Access wirklich aktivieren? <br>
				<b>ACHTUNG:</b> Dieser Schritt kann nicht rückgängig gemacht werden und du und ALLE werden sofort ausgeloggt.
			</p>
			<form action="?install=now" method="post" onsubmit="return confirm('Weisst du wirklich, was du tust?????')">
				<input type="submit" value="Ja, ich weiss was ich tue!" style="width:fit-content;border-radius:5px;padding:5px 20px;">
			</form>
		<?php }elseif( isset($_POST['email']) ){ ?>
			<p>Der Zugriff wurde beantragt. Sollte sich die angegebene Adresse <br>
			<b>(<?=$_POST['email']?>)</b> in unserer Datenbank befinden,<br>
			erhalten Sie in Kürze eine E-Mail mit dem Access-Link.</p>
		<?php }else{ ?>
			<p>Der Adminbereich ist für externe Zugriffe via dem Gally-Access-System geschützt.<br>Geben Sie nachfolgend Ihre <b>E-Mail-Adresse</b> ein, um Zugriff zu erhalten.</p>
			<form action="." method="post">
				<div class="input-field">
					<input type="email" name="email" placeholder="" required>
					<label for="email">E-Mail-Adresse:</label>
				</div>

				<input type="submit" value="&#8618">
			</form>
			<p class="support">
				Bei Fragen, wenden Sie sich einfach an unseren Support: <a href="mailto:support@gally-websolutions.com" alt="Supportmail Gally Websolutions" title="Supportmail Gally Websolutions">support@gally-websolutions.com</a>.
			</p>
		<?php } ?>
	</div>
	
</body>
</html>
