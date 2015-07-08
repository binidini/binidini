To create new client for oauth:
./app/console  bd:oauth-server:client:create --grant-type="client_credentials" --grant-type="password"

To create new user:
./app/console fos:user:create
 
To create new order:
./app/console bd:dff:order:create 

To update coordinates:
./app/console bd:shipment:coordinates:update 

To set default profile pics
cp -r web/bundles/binidiniweb/img web/media/

