# LAMP skeleton

1. Install [Vagrant](http://docs.vagrantup.com/v2/installation/index.html), [Virtualbox](https://www.virtualbox.org/wiki/Downloads) and [Ansible](http://docs.ansible.com/intro_installation.html).
2. Clone the repository on your computer, change into the base directory.
3. Bring virtual machine up `vagrant up`. 
4. Connect to it `vagrant ssh`.
5. Create domain in directory **/vagrant/domains/**:
 - with suffix **.local**
 - with **web** directory inside, eg. **/vagrant/domains/example.com.local/web/**
 - it could be an symlink to another directory, eg. **/vagrant/domains/example.com.local** is symlink to **/vagrant/projects/com/example/** directory.
6. Configure *dnsmasq* or add domain to local hosts file, eg.
        192.168.33.99 example.com.local
7. Create **config.php** file in **/vagrant/domains/default/** directory.
8. Open [web browser](http://192.168.33.99).


**Check docker version https://github.com/Flower7C3/docker-lamp**
