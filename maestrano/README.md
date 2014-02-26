mno-php
=======
Requires the following packages:
- php-xml

(not usable at the moment)
1 - Configure app details in app/config/1_app.php
2 - Customize migration in app/db/1_add_mno_uid_field.sql
3 - Customize app/sso/MnoSsoUser.php (along with tests)
4 - Add libraries and special init code (db_connection) in app/init/auth.php 

