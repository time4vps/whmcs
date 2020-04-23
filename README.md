
# WHMCS Time4VPS Module  
  
This is Time4VPS provisioning module for WHMCS platform.   
  
## Installation  
  
 1. Download latest module [release](https://github.com/time4vps/whmcs/releases).
 2. Upload archive folder contents to your WHMCS installation root directory.
 3. Login to WHMCS admin panel.
 4. Navigate to `Setup -> Products / Services -> Servers`
 5. Click `Add new Server` button
 6. Set following fields:
	- Name: `Time4VPS`
	- Hostname: `billing.time4vps.com`
	- Type: `Time4VPS Reseller Module`
	- Set your Time4VPS username and password accordingly
7. Create DB tables and import all products by navigating to `http://<your whmcs url>/modules/servers/time4vps/import.php` as Admin. If you wish to re-import or delete all Time4VPS product, add parameter `?truncate` to mentioned URL.
  
## License  
[MIT](https://github.com/time4vps/time4vps-lib/blob/master/LICENSE)
