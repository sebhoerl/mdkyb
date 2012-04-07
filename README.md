Achtung! Es wird noch alles kommentiert... im Moment schreibe ich die Seite aber auf dem Netbook, da versuche ich jedes nicht notwendige Tippen zu vermeiden :)

MDkyb Homepage
==============

Hier eine kleine Erklärung, wie man die Seite nun testen kann. Zuerst braucht man
natürlich einen Webserver, z.B. Apache oder nginx unter Linux oder WAMP unter Windows.
In das Webverzeichnis kann man nun das Repository clonen:

    git clone git@bitbucket.org:blogsh/mdkyb.git

Im Verzeichnis mdkyb/app/config liegt eine Datei "parameters.ini.dist". Diese muss einfach
kopiert werden, sodass es eine Datei "parameters.ini" gibt. In dieser können die Daten für
die Datenbank definiert werden. Die Datei muss nur einmal eingerichtet werden, sie wird von
git automatisch ignoriert, auch wenn man neue Dateien committet.

Wenn man keinen ganzen Datenbankserver wie MySQL erstellen will, kann man auch einfach SQLite verwenden.

    database_driver   = pdo_sqlite
    database_host     = 
    database_name     = my_database.sqlite

sollte dafür reichen, schätz ich mal.

Um das Projekt möglichst klein zu halten werden die meisten Dateien für das Framework und 
zugehörige Bundles nicht mit im Repository gespeichert. Sie werden aber automatisch mit

    cd mdkyb 
    bin/vendors install

installiert werden. Das ist eine ganze Menge (das muss dann allerdings nicht alles auf den Webserver
geladen werden). Dieser Befehl sollte immer ausgeführt werden, wenn man sein Repository updated. Es wird
nicht immer alles runtergeladen, sondern nur updates, es dauert also nur beim ersten mal etwas länger.

Außerdem sollte das Datenbankschema geupdated werden. Das geschieht über das Migrations plugin:

    app/console doctrine:migrations:migrate --no-interaction

Für den Testbetrieb sind bereits Fixtures (testdaten) vorbereitet, die über diesen Befehl in die Datenbank geschrieben werden:

    app/console doctrine:fixtures:load

Für faule gibt es auch die "update_test.sh", die alle drei Befehle automatisch ausführt. Hat man eine neue Version herunterladen
muss man also theoretisch nur dieses Script ausführen und alles sollte funktionieren und auf dem neusten Stand sein.

Ist die Datenbank richtig konfiguriert und funktioniert der
Webserver sollte die Seite bereits unter

    http://localhost/pfad/zu/mdkyb/web/app_dev.php

erreichbar sein.

Was ist zu beachten
===================

Symfony nutzt Doctrine, eine sogenannte ORM (Object Relational Mapper). Das bedeutet, dass man PHP Klassen erstellt und Informationen hinzufügt, wie diese in der Datenbank abgebildet werden sollen. Alles was mit dem Erstellen, Updaten, Löschen usw. von solchen Klassen (Entities) zu tun hat, übernimmt Doctrine. Man sollte also nicht manuell an der Struktur der Datenbank rumspielen (manuell Daten ändern ist kein Problem). Es wird nämlich das Migrations Plugin benutzt. Mit dem Migrations Plugin kann man verschiedene Zustände der Datenbank speichern (z.B. zuerst ist nur eine BlogEntry Klasse da, dann ist eine User Klasse da, usw usf.). Jeder Zustand hat dann eine bestimmte ID (=Version), zu der man immer wieder zurückswitchen kann. Die Migrations wissen also, welche SQL Queries sie ausführen müssen, um zu einer bestimmten Version "vorwärts" zu kommen, aber sie wissen auch welche Befehle benötigt werden um "rückwärts" zu gehen. Ändert man manuell das Schema der Datenbank, dann passen die Befehle nicht mehr und man hat Chaos. (In diesem Fall ist es dann aber trotzdem möglich, die Datenbank komplett zu löschen und die Migrations von 0 bis zur aktuellen Version durchlaufen zu lassen, dann hat man wieder ein valides Schema).

Grundsätzlich sollte es kein Problem sein, in seiner Testinstallation einfach SQLite zu verwenden, sodass man keinen Datenbankserver installieren muss. Ich glaube dabei kann es Probleme geben, wenn man Migrations backuppen will, aber das sollte in der Regel gar nicht nötig sein (außer man will deployen, siehe unten).

Deployment
==========

Für das Deployen der Seite auf den Production Host werde ich ein automatisches Script erstellen (falls
es ssh access auf dem Webspace gibt). Ansonsten hier die Schrittfolge:

* Auf dem Server index.html erstellen / htaccess ändern, sodass eine Nachricht angezeigt wird, dass an der Seite gebastelt wird (mal sehen wie wir das genau machen).

* Datenbank backuppen (per phpMyAdmin).

* Dateien backuppen (per FTP).

* Ich habe das Shellscript "deploy.sh" hinzugefügt. Es erstellt einen Ordner "deploy", in den alle relevanten Dateien hinein kopiert werden (git Verzeichnisse und ähnliches werden ausgelassen). Außerdem muss man die aktuelle Migration Version des Servers eingeben (siehe https://bitbucket.org/blogsh/mdkyb/wiki/Home). Das Script erstellt eine SQL-Datei im "deploy" Verzeichnis, die auf dem Server mit phpmyadmin ausgeführt werden kann um die Datenbank zu updaten. Das Update bringt die Datenbank auf die aktuelle Version aus dem Repository. Man muss also lediglich die SQL Queries ausführen und die Dateien auf dem Server mit denen aus /deploy überschreiben. Das Script stellt danach automatisch wieder die vollständigen vendors her (daher der lange Update-Prozess. Zu diesem Zeitpunkt ist das Deploy-Verzeichnis aber schon komplett fertig).

* Herausfinden, welche Migration die aktuelle ist in der lokalen Kopie (wurde automatisch wieder zurückgesetzt vom Script):

    app/console doctrine:migrations:status

* Den Wert von "Current version" (z.B. 20120402183001) ins Wiki (https://bitbucket.org/blogsh/mdkyb/wiki/Home) übernehmen. DAs ist wichtig, damit das Updaten der Datenbank immer glatt läuft.

* Auf dem Server muss noch per FTP das /app/cache Verzeichnis komplett geleert werden.

Durch das Script ist der ganze Vorgang sehr einfach geworden. Es sollten keine großartigen Schwierigkeiten auftreten.

Forum
=====

Um das Forum mit der Website zu verbinden, muss es auf der Website einen allgemeinen Administrator-Account geben (muss über phpmyadmin erstellt werden, falls es noch keinen gibt). Damit der Benutzer mit dem Administrator-Benutzer des Forums verbunden wird, muss seine "forumId" unbedingt "2" sein. Dieser Administratoraccount kann dann dazu verwendet werden, anderen Benutzern der Website Administratorrechte zu geben.

Es soll nicht das ganze phpBB Projekt mit im Repository verwaltet werden. Darum sollte phpBB manuell im Ordner web/forum installiert werden. Die Änderungen am Quelltext des Forums werden durch das Überschreiben der Daten aus external/forum über die Dateien aus web/forum übernommen. Dieser Ordner wird auch vom Deploy-Script im deploy Ordner erstellt. 
Um die nötigen Dateien umzukopieren, kann das "external.sh" Script verwendet werden.

Einstellungen:

* Unter General > Registration Settings sollte die Registrierung ausgeschaltet sein.
* Unter General > Authentication  "Symfony" als Authentication Method auswählen

