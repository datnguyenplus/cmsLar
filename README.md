# Welcome to Laravel 5.8!
This is CMS has been created Laravel 5.8.

# About CMS

CMS be build follow Module.
All modules will be in the package directory.

## Functions

Command create new a module.
	
    php artisan module:make {Module}
Command create new a controller in module.
	
    php artisan module:make-controller {name_controller} {Module}
   
Command create new a model in module.
	
    php artisan module:make-model {name_model} {Module}
  
 Command create new a migration in module.
	
    php artisan module:migration {create_{name}_table} {Module}
    
  Command  call migrate in a module.
	
    php artisan module:migrate {Module}
  
