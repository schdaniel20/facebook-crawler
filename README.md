<<<<<<< HEAD
3
=======
### Installation

Make sure that you have composer installed and globally available.

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

Then clone the repository and run `composer install` command

```bash
git clone http://gitlab.clx/crawlers/facebook.git
cd facebook
composer install
```

### Updating an installation

```bash
git pull origin master
composer update
```

### Running a Facebook crawler


```bash
php crawler.php -f [config-file-name]
```

### Config file example

**Important!** You need to explicitly specify which [fields](https://developers.facebook.com/docs/graph-api/reference/page) to be included in the Facebook's response.


```json
{
    "facebook": {
        "id": "673063276026223",
        "secret": "e68434b3a0c5123c7cc5bd7c985e20bd",
        "token": "262236730632760|t6IA2a2wPbF1nMm0r148YqTtdkQ",
        "graph": "v2.4",
        "fields": ["id", "about", "etc"]
    },
    
    "source": {
        "table": "source_table",
        "user": "root",
        "password": "secret",
        "dsn": "mysql:host=localhost;dbname=mydatabase;charset=utf8"
    },
    
    "target": {
        "table": "target_table",
        "user": "root",
        "password": "secret",
        "dsn": "mysql:host=localhost;dbname=otherdatabase;charset=utf8"
    }
}
```
>>>>>>> b2df68d33a7a18880ccf78a382af5df4298e95c1
