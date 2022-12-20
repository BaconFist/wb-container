## this line may only appear once in the .htaccess
RewriteEngine On
## include redirect to https
RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
RewriteRule ^(.*)$ https://%1/$1 [R=301,L]
RewriteCond %{HTTPS} !on
RewriteRule (.*) https://%{HTTP_HOST}/$1 [R=301,L]
