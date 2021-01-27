# Mesh Payment Module for Magento 2

### Requirement & Compatibility
- Requires magento version at least: `2.x`
- Tested and working upto `Magento 2.4.1`

### Installation
- Create the following folder structure inside "app/code" folder and copy all the files
  "Mesh/MeshPayment"
- After you have copied all the files the folder structure should be like this
  "app/code/Mesh/MeshPayment/...."
- Enable Mesh Payment Module
  `php bin/magento module:enable Mesh_MeshPayment`
- Run Setup Upgrade
  `php bin/magento setup:upgrade`
- Run DI Compilation to generate classes
  `php bin/magento setup:di:compile`
- If you are on Production Environment, make sure you run the following command as well
  `php bin/magento setup:static-content:deploy`
- Finally Flush the Cache
  `php bin/magento cache:flush`

### Configuration
- In Your Magento Store Management Console, enable the Mesh Payment Module
