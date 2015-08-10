UseCanonicalName Off

# this log format can be split per-virtual-host based on the first field
LogFormat "%V %h %l %u %t \"%r\" %s %b" vcommon
CustomLog /var/log/apache2/access.log vcommon

<Directory "{{ doc_root }}">
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

<VirtualHost *:81>
    VirtualDocumentRoot {{ doc_root }}%0
</VirtualHost>

<VirtualHost *:80>
    VirtualDocumentRoot {{ doc_root }}%0/web
</VirtualHost>


<VirtualHost *:80>
    ServerName 192.168.33.99
    DocumentRoot {{ doc_root }}default
</VirtualHost>
