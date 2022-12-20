### Caching enabled ###

<IfModule mod_headers.c>

<filesMatch ".(jpg|jpeg|png|gif|ico)$">
Header set Cache-Control "max-age=31536000, public"
</filesMatch>

<filesMatch ".(css|js)$">
Header set Cache-Control "max-age=2628000, public"
</filesMatch>

Header set Cache-Control "max-age=3600, public"

</IfModule>