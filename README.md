# e-commerce-Symfony-Pgsql

L’objectif de ce projet est de créer une application web e-commerce simple, permettant de passer commande de produits physiques.


### Installation : 

Pour installer et tester notre projet: 


```
Composer install
```

Pour créer la base de donnée :

```
php bin/console doctrine:database:create
```

```
php bin/console doctrine:schema:update
```


Lancer ensuite le serveur Symfony pour lancer le projet :

```
symfony serve 
```

Creer un compte et lui attribuer le rôle admin pour les accès admins
