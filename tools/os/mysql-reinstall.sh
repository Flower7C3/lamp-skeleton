sudo apt-get --purge remove mysql-server
sudo apt-get --purge remove mysql-client
sudo apt-get --purge remove mysql-common
sudo apt-get autoremove
sudo apt-get autoclean
sudo rm -rf /etc/mysql
sudo cp -R /var/lib/mysql/ ~/mysql
sudo rm -rf /var/lib/mysql/

which mysql
mysql --version

sudo dpkg --configure -a
sudo apt-get update

sudo apt-get install mysql-server phpmyadmin

mysqlcheck --repair --all-databases -p
