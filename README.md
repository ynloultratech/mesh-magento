# Mesh Payment Module for Magento 2

### Requirement & Compatibility
- Requires magento version at least: `2.x`
- Tested and working upto `Magento 2.4.1`

### Installation
- Download the Mesh extension as zip file from [here (mesh.zip)](https://github.com/ynloultratech/mesh-magento/releases/latest). Make sure you download the most recent version.
- Create the following folder structure inside `app/code` folder `Mesh/MeshPayment`
- Unzip contents into `app/code/Mesh/MeshPayment` folder
- After you have all the files your folder structure should be like this `app/code/Mesh/MeshPayment/composer.json`
- From the server terminal, navigate to the root Magento directory and run  `php bin/magento module:enable Mesh_MeshPayment`
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
