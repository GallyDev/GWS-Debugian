# Gally-Access Version 1.0.0
A simple yet powerful way to keep attacks out of the system and customers happy.

# How to install 
Upload folder to Web-Root, rename it to ```gally_access``` and open URL: website.com/gally_access


# Troubleshoot
- E-Mail does not arrive

  Set ```$mailMode``` to ```remote``` in ```index.php``` to use the webservice of gally-websolutions.com (less secure).

- Front-End-Login no longer works

  Some Front-End-Login-Methods use wp-login.php and therefore you have to add a # in the root-file to the RewriteRule so the line looks like this:
  ```
  #RewriteRule ^(.*)$ /gally_access/ [L,R=302]
  ```
