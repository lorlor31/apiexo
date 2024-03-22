# MISE EN PROD D UN PROJET SYMFONY

## Se connecter en ssh
`ssh student@laure-seng-server.eddi.cloud`
Virer l'empreinte avec la commande proposée ds le terminal. Puis relancer la connexion ssh.

## Installer les logiciels

### Script de Ben à mettre dans un fichier installation-serveur.sh 
le lancer en tapant `sh installation-serveur.sh` 
et fournir ses username et mdp

===================================================
```

#!/bin/sh
echo "*********************************************"
echo "*  Bienvenue dans le script d'installation  *"
echo "*********************************************"
echo " "
read -p "appuyez sur entrée pour lancer l'installation"

USER_NAME=$USER

installUpdate()
{
  # mise à jour de la base logicielle
  sudo apt-get update

  # mise à jour des logiciels préinstallés
  sudo apt-get -y upgrade
}

installEssentials()
{
  #installation de nano
  sudo apt-get install -y nano

  # installation zip
  sudo apt-get install -y unzip
}

reinitMySQL()
{
  sudo mysql_install_db --user=mysql
}

installApache()
{
  # installation apache
  sudo apt-get install -y apache2

  # activation du mod rewrite
  sudo a2enmod rewrite
  sudo service apache2 restart
}

configureApache()
{
  # modification du AllowOverride
  sudo sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride all/' /etc/apache2/apache2.conf

  # configuration des droits
  sudo chown -R $USER_NAME:www-data /var/www/html
  sudo chmod -R 775 /var/www/html
  sudo chmod g+s /var/www/html

  # suppression du fichier index.html par défaut pour avoir le listing des répertoires
  sudo rm -rf /var/www/html/index.html

  # redémarrage d'Apache
  sudo service apache2 restart
}

installPHP()
{
  # installation php 8.2
  sudo add-apt-repository ppa:ondrej/php
  sudo apt update
  sudo apt-get install -y php8.2
  sudo apt-get install -y php8.2-intl
  sudo apt-get install -y php8.2-common
  sudo apt-get install -y php8.2-cli
  sudo apt-get install -y php8.2-mysql
  sudo apt-get install -y libapache2-mod-php8.2
  sudo apt-get install -y php8.2-mbstring
  sudo apt-get install -y php8.2-json
  sudo apt-get install -y php8.2-xml
  sudo apt-get install -y php8.2-xdebug
  sudo service apache2 restart
}

configurePHP()
{
  sudo php -r "\$ini=glob('/etc/php/*/apache2/php.ini')[0]; \$buffer=file_get_contents(\$ini); \$buffer=str_replace('display_errors = Off', 'display_errors = On',\$buffer); file_put_contents(\$ini, \$buffer);";
  sudo service apache2 restart
}

installComposer()
{
  # composer
  cd /tmp
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php composer-setup.php --quiet
  php -r "unlink('composer-setup.php');"
  sudo mv composer.phar /usr/local/bin/composer
}

installMySQL()
{
  # installation mariadb-server
  sudo apt-get install -y mariadb-server
  sudo service mysql start
}

configureMysql()
{
  # configuration mysql
  read -p "BDD User name ? " BDD_USER_NAME
  echo $BDD_USER_NAME;
  read -p "BDD password ? " BDD_USER_PASSWORD
  echo $BDD_USER_PASSWORD
  
  sudo  mysql  <<EOF 
CREATE USER ${BDD_USER_NAME}@localhost IDENTIFIED BY '${BDD_USER_PASSWORD}'; 
GRANT ALL PRIVILEGES ON *.* TO \`${BDD_USER_NAME}\`@'localhost' WITH GRANT OPTION; 
EOF

  # modify bind-address
  sudo php -r "\$cnf=glob('/etc/mysql/mariadb.conf.d/50-server.cnf')[0]; \$buffer=file_get_contents(\$cnf); \$buffer=str_replace('127.0.0.1', '0.0.0.0',\$buffer); file_put_contents(\$cnf, \$buffer);";
  sudo service mysql restart
}


installGit()
{
  # installation de git
  sudo apt-get install -y git
}

#==========================================================================================
#==========================================================================================
installUpdate
installEssentials

installApache
configureApache

installPHP
configurePHP

installMySQL
configureMysql

installComposer

installGit

exit 0

```
==========================================================

## Cloner le repo du projet
1. Créer une clé ssh pour Github 
`ssh-keygen -t ed25519 -C "vm_serveur"`
2. Copier la clé publique dans github-->settings-->SSH 
`cat /home/student/.ssh/id_ed25519.pub` 
3. cloner le repo dans /var/www/html
4. Créer le .env.local avec les bons id et bdd, env de prod,

## Installer Composer
`sudo apt install composer` puis
`composer install --no-dev`
et composant apache
`composer require symfony/apache-pack`(pour l'htaccess)

## Créer la BDD
- `php bin/console do:da:cr`
- `php bin/console do:mi:mi`
- si pas de données, on peut 
1. créer un fichier bdd.sql avec l'export sql de la bdd dedans
2. se connecter en CLI : 
`mysql -u VOTRE_UTILISATEUR -p `
3. puis `USE votre_base_de_donnees;`
4. puis exécuter la requête avec `source /chemin/vers/votre/script.sql;`

## Installer Adminer 
`sudo apt install adminer`

`sudo a2enconf adminer`

`sudo systemctl reload apache2`

## Ou PHPMyAdmin
`sudo apt install phpmyadmin`

puis choisir apache2
Ben utilise Bcrypt pour chiffrer le mdp

## Configuration des virtual hosts
1. `cd /etc/apache2/ `
2. `sudo chown student sites-available/ `
3. `cd sites available/`
4. `touch monSite.conf`
5. `nano monSite.conf `
6. et copier
```
    <VirtualHost *:80>
	ServerName laure-seng-server.eddi.cloud
	DocumentRoot /var/www/html/monSite/public/
	
	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
	
	<Directory /var/www/html/monSite/>
		Options indexes
		AllowOverride all
		Allow from all
		Require all granted
	</Directory>	
	RewriteEngine on
    </VirtualHost>
```
6. `sudo a2ensite monSite.conf`

`sudo service apache2 restart`


# Problèmes possibles /Astuces
- Utiliser **Remote SSH** extension vsCode
- version php à upgrader
moi g la php8.3.4
https://php.watch/articles/php-8.3-install-upgrade-on-debian-ubuntu
- php-XML manquant `sudo apt install php8.2-xml`
- ou sudo apt-get update -y
sudo apt-get install -y php-xml

- mysql manquant `sudo apt install php8.2-mysql`
- pdo `sudo apt-get install php-pdo-mysql`
- Les erreurs sont dans var/log/apache2  error.log
- Redémarrer `Apache sudo apachectl restart`

- ne pas oublier de rendre la VM publique