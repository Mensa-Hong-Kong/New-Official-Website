CREATE USER 'laravel'@'%' IDENTIFIED WITH 'caching_sha2_password' BY 'password';
GRANT USAGE ON *.* TO 'laravel'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `mensa`.* TO 'laravel'@'%';
