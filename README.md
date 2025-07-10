# Zoomerplanning

**Zoomerplanning** est un logiciel libre de gestion des ressources humaines permettant de gérer un nombre illimité de ressources. Il est développé avec le framework **Symfony 7** et est actuellement installé sur un serveur de production tournant sous **Debian 12** avec **PHP 8.3**.

🔗 Démo : [https://www.revivalsoft.com](https://www.revivalsoft.com)

---

## ⚙️ Installation locale

### 1. Préparation

Ouvrez un terminal et exécutez les commandes suivantes :

```bash
mkdir zoomerplanning
cd zoomerplanning
sudo git clone https://github.com/revivalsoft/zoomerplanning.git .
cd ..
sudo chown -R www-data:www-data zoomerplanning
cd zoomerplanning
code .
```

> ⚠️ Note : le `.` à la fin de la commande `git clone` est important pour cloner dans le dossier courant.

---

### 2. Fichier `.env.local`

Créez un fichier `.env.local` **à la racine du projet**, à côté du fichier `.env`. Exemple de contenu à adapter selon votre configuration :

```
APP_ENV=dev
#APP_DEBUG=true
APP_SECRET=085288xxxxxx08dccdffe09b2

DATABASE_URL="mysql://root:1234@127.0.0.1:3306/zoomerplanning?serverVersion=10.11.6-MariaDB&charset=utf8mb4"

MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
###< symfony/messenger ###

# MAILTRAP : SANDBOX DEVELOPPEMENT ET TESTS
#MAILER_DSN="smtp://<user>:<pass>@sandbox.smtp.mailtrap.io:2525"

# MAILTRAP : PRODUCTION
#MAILER_DSN="smtp://api:<token>@bulk.smtp.mailtrap.io:587"
```

---

### 3. Installation des dépendances

```bash
composer install
composer dump-env dev
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

---

## 🔐 Notifications Push – Clés VAPID

1. Ouvrez le fichier `src/Command/GenerateVapidCommand.php`
2. Remplacez l’adresse mail dans cette ligne :

```php
$envContent .= "VAPID_SUBJECT=mailto:monadressemail@gmail.com\n";
```

3. Générez les clés :

```bash
symfony console app:webpush:generate-vapid
```

4. Elles seront automatiquement ajoutées à `.env.local`.

5. Rechargez l'environnement :

En développement : 
```bash
composer dump-env dev
symfony console cache:clear
```
En production :
```bash
composer dump-env prod
symfony console cache:clear
```

## ⚠️ Attention

> En local, les notifications push **ne fonctionnent pas**.  
> Elles nécessitent **un serveur HTTPS en production**.


---

## ▶️ Lancer le serveur local

```bash
symfony serve -d
```

Par défaut, le site sera accessible à : [http://127.0.0.1:8000](http://127.0.0.1:8000)

### Identifiants de connexion de test

- **Email** : `admin@gmail.com`
- **Mot de passe** : `1234`

---

## 📬 Configuration des emails avec Mailtrap

1. Vous devez **valider un nom de domaine** sur Mailtrap.
2. Pour les webhooks, pointez vers :

```
https://www.mondomaine.com/webhook/mailtrap
```

---

## 🛠️ Supervisor pour la gestion des messages

Installez et configurez `Supervisor` sur Debian :

Fichier : `/etc/supervisor/conf.d/messenger_zoomerplanning.conf`

```ini
[program:messenger_zoomerplanning]
command=/usr/bin/php /var/www/html/zoomerplanning/ptadmin/bin/console messenger:consume async --env=prod
process_name=%(program_name)s_%(process_num)02d
numprocs=3
autostart=true
autorestart=true
user=www-data
stdout_logfile=/var/log/supervisor/messenger_zoomerplanning_%(process_num)02d.log
stderr_logfile=/var/log/supervisor/messenger_zoomerplanning_%(process_num)02d_error.log
```

---

## 🌐 En production

- Le **VirtualHost** doit pointer vers le dossier `public/` du projet.
- Assurez-vous que `Supervisor` tourne correctement pour la file d’attente des messages.

---

## 📜 Licence

Zoomerplanning est un logiciel libre distribué sous **licence GNU GPL v3**.  
Voir le fichier [`LICENSE`](https://www.gnu.org/licenses/gpl-3.0.txt) pour plus de détails.
