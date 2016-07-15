# carlbennett-tools

Carl's Tools (carlbennett-tools) is a website dedicated to housing a collection
of tools/utilities authored by [@carlbennett](https://github.com/carlbennett).

## Installation

### Clone this repository
```sh
git clone git@github.com:carlbennett/carlbennett-tools.git ~/carlbennett-tools
```

### Install nginx
Follow the guide available over at
[carlbennett/nginx-conf](https://github.com/carlbennett/nginx-conf).

After successfully installing **carlbennett/nginx-conf**, you will need to
create and configure a new virtual host / `server` block in nginx. Use whatever
domain or subdomain name you'd like for this project, it will adapt itself to
it automatically.

### Install php-fpm
#### CentOS 7.x / Fedora 24
```sh
sudo yum install php-fpm
```

#### Debian / Ubuntu
```sh
sudo apt-get update && sudo apt-get install php-fpm
```

### Satisfy composer
Run `composer install` at the root of the repository.

### Run nginx and php-fpm
#### CentOS 7.x / Fedora 24
```sh
sudo systemctl start nginx php-fpm
```

#### Debian / Ubuntu
```sh
sudo /etc/init.d/nginx start && sudo /etc/init.d/php-fpm start
```

### Configure the site
```sh
cp ./etc/config.sample.json ./etc/config.json
```

\* Open `config.json` in your favorite text editor and modify it to your
   liking.

### Test
Try accessing your nginx server using the name you configured for this project.
