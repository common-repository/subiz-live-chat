# Subiz Wordpress Plugin

# How to test
## First time after you clone this directory
### 1. Run a wordpress site on your local machine.
1. Create a directory name `wordpress`.
2. Inside the directory, create `./html` directory
3. Create an `./docker-compose.yaml` file with the following content

```yaml
version: '3.1'

services:
  wordpress:
    image: wordpress
    restart: always
    ports:
      - 8080:80
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: exampleuser
      WORDPRESS_DB_PASSWORD: examplepass
      WORDPRESS_DB_NAME: exampledb
    volumes:
      - ./html:/var/www/html
  db:
    image: mysql:8.0
    restart: always
    environment:
      MYSQL_DATABASE: exampledb
      MYSQL_USER: exampleuser
      MYSQL_PASSWORD: examplepass
      MYSQL_RANDOM_ROOT_PASSWORD: '1'
    volumes:
      - db:/var/lib/mysql

volumes:
  db:

# admin:7S$JXO5LDno6$1%apm
```
4. Start the cluster
```sh
docker compose up
```

### 2. Run a wordpress site on your local machine.
Move this git directory to `./html/wp-content/plugins/subiz-live-chat`. So there will be `./html/wp-content/plugins/subiz-live-chat/.git` directory.

### 3. Visit localhost:8080 to access your wordpress
Now you
## Next time
1. Visit the `wordpress` directory
2. Run `docker compose up`
3. Visit localhost:8080 to access your wordpress
4. You now can edit file inside `./html/wp-content/plugins/subiz-live-chat` to make changes.

## Here is my current directory
1. `~/src/wordpress/docker-compose.yaml`
2. `~/src/wordpress/html/wp-content/plugins/subiz-live-chat/.git`

### Development
#### Reformat php code
```sh
php-cs-fixer fix .
```

### Pushing change to wordpress
1. Copy file to subiz-live-chat/trunk
2. svn add
3. svn commit # push to wordpress