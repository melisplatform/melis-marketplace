# melis-marketplace

MelisMarketplace lists all existing Melis Platform modules.  
It also shows if modules are up to date and proposes to make automatic updates.  

## Getting Started

These instructions will get you a copy of the project up and running on your machine.  

### Prerequisites

You will need to install melisplatform/melis-core in order to have this module running.  
This will automatically be done when using composer.  

### Installing

Run the composer command:
```
composer require melisplatform/melis-marketplace
```

## Tools & Elements provided

* MelisMarketPlace tool: downloading and updating of modules  
* Header icon: informs when updates are available  


## Running the code

### List a module on the marketplace    

MelisMarketplace will list all modules from **[Packagist](https://packagist.org/packages/melisplatform/)**  that have a type "melisplatform-module" in their composer.json.  
  
Example from the composer.json of MelisMarketplace:  
```
{
  "name": "melisplatform/melis-marketplace",
  "description": "Melis Platform Market Place",
  "type": "melisplatform-module",
  "license": "OSL-3.0",
  
  ...
  
 } 
```


## Authors

* **Melis Technology** - [www.melistechnology.com](https://www.melistechnology.com/)

See also the list of [contributors](https://github.com/melisplatform/melis-marketplace/contributors) who participated in this project.


## License

This project is licensed under the OSL-3.0 License - see the [LICENSE.md](LICENSE.md) file for details