## Install

``` bash
# install the package
composer require pixellweb/pixmycar

# Run import
php artisan import:pixmycar
```

## Notes

- Connexion API SOAP
- WSDL : https://citadelle-ws.pixmycar.com/4DWSDL/
- Doc de merde : http://manuel.ajaris.com/
- Interface https://citadelle.pixmycar.com/Citadelle/
- **Attention il faut communiquer à support@pixmycar.com l'adresse ip pour que cela fonctionne**

### Récupèration de photo preview.
Elle est disponible sous l'intitulé 3/4 Avant Conducteur => 1AvC34 (à l'origine 01_01_Vue Vignette, mais elle n'était pas présente à chaque fois).
Attention, il ne faut pas prendre la plaque d'immatriculation comme réfèrence entre les tables 2 et 3 car en cas de changement elle n'est pas modifié sur 2.

Mail : Le 07/06/2018 à 04:01, Cuoghi Sylvain

Pour retrouver facilement les vues de détails d'un véhicule, vous pouvez chercher les éléments suivants sur la fiche document :

    "Vignette avant" :
        "Type Image" = "Detail"
        "Type de détail" = "01_01_Vue Vignette"
     "Tableau de bord"
        "Type Image" = "Detail"
        "Type de détail" = "02_02_Tableau de bord"
    "Coffre"
        "Type Image" = "Detail"
        "Type de détail" = "03_15_Coffre"
    "Jante"
        "Type Image" = "Detail"
        "Type de détail" = "04_04_Jante"


Pour les vues clés, vous pouvez chercher les éléments suivants sur la fiche document :

    "3/4 avant" :
        "Type Image" = "1AvC34"
     "3/4 arrière"
        "Type Image" = "2ArP34"
