# CS-Cart

- [Multi-vendor](https://www.cs-cart.com/marketplace-builder.html) is the CS-Cart marketplace builder is tailored to fast start. We took care about the tools and features that simplify and speed up building a marketplace.

## Getting started

- [Documents](https://docs.cs-cart.com/4.15.x/install/index.html).

## Requirements

- System requirement in this project:

    - PHP version 7.4-fpm (with mysqli, gd, curl, sockets, soap, json, openssl, zip, imap, mbstring extensions)
    - MySQL version 8.0.29
    - nginx version 1.23.0

- For more information about server configuration see [this article](https://docs.cs-cart.com/4.15.x/install/system_requirements.html).

## Configue Nginx

- Edit files in folder /etc

```bash
< PROJECT ROOT >
   |
   |-- cs-cart
   |-- etc
   |    |
   |    |-- default.conf
   |    |-- nginx.conf
   |
   |-- .gitignore
   |-- PHP.Dockerfile
   |-- README.md
   |-- docker-compose.yml
   |        
   |-- **************************
```

- For more information about nginx configuration see [this article](https://docs.cs-cart.com/4.15.x/install/nginx.html).

## Run application

- Build docker image

``` bash
docker-compose up --build -d
```

- Open website http://10.30.1.99:9090 (for example: ip_localhost 10.30.1.99, port container 9090)
- Click install -> Agree I accept Multi-Vendor license agreement -> click Next step button
- Checking requirements (Get error Incorrect permissions -> Scroll down for get more information)
- Server configuration

``` bash
- Store URL                 http://10.30.1.99:9090
- MySQL server host         10.30.1.99:9306
- MySQL database name       cscart
- MySQL user                cscart
- MySQL password            cscart
```

- Click Install button -> Wait for installation done

- For config database -> using PHPMYADMIN

``` bash
- PHPMYADMIN                http://10.30.1.99:9999
- PHPMYADMIN user           cscart
- PHPMYADMIN password       cscart
```

## Fix error Incorrect permissions are assigned to the files and/or folders listed below

- Run below command for correct permissions:

``` bash
sudo docker exec -it cs-cart_cs-cart-php_1 bash -c "chmod 644 config.local.php ; chmod 755 index.php ; chmod -R 755 design images var ; find design -type f -print0 | xargs -0 chmod 644 ; find images -type f -print0 | xargs -0 chmod 644 ; find var -type f -print0 | xargs -0 chmod 644 ; chown -R www-data:www-data ."
```  

- Reload website in localhost such as http://10.30.1.99:9090 and repeat those steps above.