TimeTrex Payroll and Time Management

INSTALLATION INSTRUCTIONS

1. Confirm that your system meets the TimeTrex minimum requirements.
	- PHP v5.x or greater
	- MySQL v5.0+ or PostgreSQL v8.2+ (PostgreSQL is highly recommended)

2. Locate your webroot directory on your web server. This is the directory
on your web server where publicly accessilbe files are made available by your
web server. Common locations include:

	/var/www/html/ (Linux/Apache)
	C:\Inetpub\wwwroot\ (Windows/IIS)
	C:\Program Files\Apache Group\Apache\htdocs\ (Windows/Apache)
	/Library/Web server/Documents/ (MaxOS X/Apache)

3. Unzip the TimeTrex zip file into your webroot. A directory is automatically
created within webroot. Rename this directory if desired.

4. Rename timetrex.ini.php-example_(linux|windows) to timetrex.ini.php

5. Edit timetrex.ini.php and confirm that all paths are correct.
	The installer will create and configure the database
	for you, as well as modify other non-path settings for you.

6. Point your web browser to:
	http://<web server address>/<timetrex directory>/interface/install/install.php
	ie: http://localhost/timetrex/interface/install/install.php

7. Follow instructions



UPGRADE INSTRUCTIONS

1. *IMPORTANT* Create a backup of your current installation, including your TimeTrex database.

2. *VERY IMPORTANT* No really, create a backup of all your TimeTrex data including your
   timetrex.ini.php file, as it contains a cryptographic salt that if you lose you will
   not be able to login to TimeTrex or access encrypted data ever again.
   
   **BE SURE TO BACKUP YOUR TimeTrex DATABASE AND YOUR timetrex.ini.php FILE!**

3. Unzip TimeTrex zip overtop of your current installation.

4. Edit timetrex.ini.php in your new TimeTrex directory and set:
	installer_enabled = TRUE

5. Point your web browser to:
	http://<web server address>/<timetrex directory>/interface/install/install.php
	ie: http://localhost/timetrex/interface/install/install.php

6. Follow instructions, TimeTrex will automatically upgrade
	your database tables as necessary.

