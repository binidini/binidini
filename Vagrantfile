# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
 config.vm.provider "virtualbox" do |v|
        v.customize ["modifyvm", :id, "--memory", "2048"]
    end
  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # config.vm.network "forwarded_port", guest: 80, host: 8080

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "192.168.33.10"
  config.vm.network "public_network", bridge: 'en0: Wi-Fi (AirPort)'
 config.vm.synced_folder ".", "/vagrant", :type => "nfs"

  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  config.vm.provision "shell", inline: <<-SHELL

    sudo apt-get update
    sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
    sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
    sudo apt-get -y install mysql-server mysql-client nginx php5-cli php5-mysql php5-mongo php5-fpm mongodb php5-curl php-apc rabbitmq-server php5-intl php5-gd memcached php5-memcached

    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo cp /vagrant/vagrant/default /etc/nginx/sites-available/default
    sudo service nginx restart
    cd /vagrant/
    cp app/config/parameters.yml.dist app/config/parameters.yml
    composer install
    ./app/console assets:install
    ./app/console doctrine:database:create
    ./app/console doctrine:schema:create

  SHELL
end
