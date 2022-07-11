# samhsa-store-d9
Store Drupal 9 

docker-compose exec solr sh


docker-compose exec mariadb sh -c 'exec mysql -u root -p"password" samhsa_store_d9 < /docker-entrypoint-initdb.d/store9_db_backup.sql'
