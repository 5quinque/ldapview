LdapView
========

Installation
------------

Create mysql user and grant privileges

```mysql
CREATE USER 'ldapview'@'localhost' IDENTIFIED BY 'somerandompassword';
GRANT ALL PRIVILEGES ON ldapview.* TO 'ldapview'@'localhost';
```

Clone repository and install dependencies [[0](#footnote-0)]

```bash
git clone git@oegit.bskyb.com:RLI14/ldapview.git
cd ldapview
composer install
```

Configure environment variables

```bash
cp .env .env.local
chmod 600 .env.local
```

Edit `.env.local` and update database and ldap variables

Create database and table structure

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Everything should now be installed. To load data into the database, read below.

Load netgroups/host into datbase
--------------------------------

### Ansible

Use the [getnetgroups playbook](https://oegit.bskyb.com/RLI14/getnetgroups) and transfer all accessfiles to `./ldapview/accessfiles` (or modify .env.local `ACCESS_DIR` variable to wherever the access files are stored)

Run the following command

```bash
php bin/console app:load-netgroups
```

### Pull from LDAP

The `app:load-entity` command can be used to pull all people/netgroup/sudo objects from ldap.

```bash
$ php bin/console app:load-entity --help
Usage:
  app:load-entity [options]

Options:
  -p, --people
  -g, --netgroup
  -s, --sudo
```

Example usage to pull all objects

```bash
php bin/console app:load-entity -pgs
```

This could be put into cron to run every 30 minutes (Replace the directory with the installation location)

```
# Possible need to replace php with /opt/rh/rh-php72/root/usr/bin/php if installing via SCL
*/30 * * * *    php /var/www/html/ldapview/bin/console app:load-entity -pgs
```


If a user doesn't exist in the database, but does exist in LDAP, if you access their UID via the URL `/people/<UID>` they should get pulled in automatically. E.g. `/people/rli14` will get my user from LDAP and populate the database. This is the same for netgroups and sudo groups.

PHP 7 installation on RedHat 7
------------------------------

(Requires rhel-server-rhscl-7-rpms repository)

```bash
yum install rh-php72 rh-php72-php-common rh-php72-php-mysqlnd rh-php72-php-mbstring rh-php72-php-fpm rh-php72-php-ldap

systemctl enable rh-php72-php-fpm.service
systemctl start rh-php72-php-fpm.service
```

/etc/opt/rh/rh-php72/php-fpm.d/ldapview.conf  
(I also had to `mkdir /run/php-fpm/`)

```
[ldapview]
user = rli14
group = users
listen = /run/php-fpm/ldapview.sock
listen.owner = apache
listen.group = apache
listen.mode = 0666
pm = dynamic
pm.max_children = 10
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
chdir = /
php_admin_value[memory_limit] = 512M
```

The `.htaccess` file then references the socket file `/run/php-fpm/ldapview.sock`

Apache Config
-------------

/etc/httpd/conf.d/ldapview.conf

```
Alias /ldapView /var/www/html/ldapview/public
Alias /ldapview /var/www/html/ldapview/public

<Directory /var/www/html/ldapview/public>
AllowOverride All
</Directory>
```

Notes
-----
All PHP commands require PHP version 7. There are instructions above for installing PHP 7 using SCL. Installing via this route would mean the PHP cli is located at `/opt/rh/rh-php72/root/usr/bin/php`

I have added `PATH=/opt/rh/rh-php72/root/usr/bin:$PATH:$HOME/.local/bin:$HOME/bin` to `~/.bash_profile` so I don't have to provide the full path every time.

###### footnote-0
[Install Composer](https://getcomposer.org/download/)