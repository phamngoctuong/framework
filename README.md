# marinesTeam26
#### Refer:
- Link 1
  https://github.com/phamngoctuong/laravel-docs-vn/blob/main/readme.md
- Link 2
	https://raw.githubusercontent.com/phamngoctuong/laravel-docs-vn/main/readme.md
## You can use 1 of 2 ways to configure.
#### Guid Config 1:
- C:\Windows\System32\drivers\etc\hosts
```php
127.0.0.1 testtiah.com
```
- Create file .htaccess 👇 to your Laravel root folder
```php
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^(.*)$ /public/$1 [L]
RewriteCond %{REQUEST_URI} !(.css|.js|.png|.jpg|.gif|robots.txt|.ttf)$ [NC]
</IfModule>
```
- C:\xampp\apache\conf\extra\httpd-vhosts.conf
```php
<VirtualHost testtiah.com:80>
    DocumentRoot "C:\xampp\htdocs\marinesTeam26"
	ServerName testtiah.com
	ServerAlias *.testtiah.com
</VirtualHost>
```
Link: [testtiah.com](http://testtiah.com/)
#### Guid Config 2:
- Rename server.php in your Laravel root folder to index.php
- Create file .htaccess 👇 to your Laravel root folder
```php 
<IfModule mod_rewrite.c>
  <IfModule mod_negotiation.c>
      Options -MultiViews -Indexes
  </IfModule>
  RewriteEngine On
  # Handle Authorization Header
  RewriteCond %{HTTP:Authorization} .
  RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
  # Redirect Trailing Slashes If Not A Folder...
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_URI} (.+)/$
  RewriteRule ^ %1 [L,R=301]
  # Send Requests To Front Controller...
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^ index.php [L]
  RewriteCond %{REQUEST_URI} !(.css|.js|.png|.jpg|.gif|robots.txt|.ttf)$ [NC]
</IfModule>
```
Link: [localhost](http://localhost/marinesTeam26)
#### Guid Config 3:
- Config http to https for localhost
```php 
<VirtualHost *:80>
    RewriteEngine on
    ServerName localhost
    RewriteRule ^(.*) https://%{SERVER_NAME}$1 [R,L]
</VirtualHost>
<VirtualHost localhost:443>
     DocumentRoot "C:\xampp8\htdocs"
     ServerName localhost
     ServerAlias *.localhost
   <Directory "C:\xampp8\htdocs">
        Require all granted
   </Directory>
     SSLEngine on
     SSLCertificateFile "conf/localhost/ssl.crt/server.crt"
     SSLCertificateKeyFile "conf/localhost/ssl.key/server.key"
</VirtualHost>
```