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

Außerdem sollte das Datenbankschema geupdated werden. Das geschieht über diesen Befehl:

    app/console doctrine:schema:update --force

Wie auch immer die Datenbank zur Zeit aussieht, sie wird auf den Stand des Projekts gebracht. Sollte das 
einmal fehlschlagen, kann man einfach die gesamte Datenbank droppen, neu erstellen und das Schema komplett
neu erstellen lassen.

Für den Testbetrieb sind bereits Fixtures (Testdaten) vorbereitet, die über diesen Befehl in die Datenbank geschrieben werden:

    app/console doctrine:fixtures:load

Für Faule gibt es auch die "update_test.sh", die alle drei Befehle automatisch ausführt. Hat man eine neue Version herunterladen
muss man also theoretisch nur dieses Script ausführen und alles sollte funktionieren und auf dem neusten Stand sein.

Ist die Datenbank richtig konfiguriert und funktioniert der
Webserver sollte die Seite bereits unter

    http://localhost/pfad/zu/mdkyb/web/app_dev.php

erreichbar sein.

Was ist zu beachten
===================

Wenn Änderungen an den Entities/der Datenbank vorgenommen werden, sollte die zugehörige SQL query zunächst mit folgendem Befehl ausgegeben werden (NOCH BEVOR MAN DIE ÄNDERUNGEN IN DIE EIGENE DATENBANK ÜBERNIMMT!):

    app/console doctrine:schema:update --dump-sql

Diese Befehle sollten nun ans Ende der "history.sql" kopiert werden. Sobald man per git einen Commit erstellt ist das Changeset dann
mit der aktuellen Revision verbunden und man kann später auf dem Production Host ohne Probleme nachvollziehen, welche Queries ausgeführt werden müssen, um zur aktuellen Version zu kommen. 

Man kann die Queries auch einfach an die Datei anhängen:

    app/console doctrine:schema:update --dump-sql >> history.sql

Deployment
==========

Hier ist die Schrittfolge zum deployen:

* Auf dem Server index.html erstellen / htaccess ändern, sodass eine Nachricht angezeigt wird, dass an der Seite gebastelt wird (mal sehen wie wir das genau machen).

* Datenbank backuppen (per phpMyAdmin). 

* Dateien backuppen (per FTP).

* Aus dem Wiki auf bitbucket entnehmen, auf welchem Commit der Production Host ist.

* Ich habe das Shellscript "deploy.sh" hinzugefügt. Es erstellt einen Ordner "deploy", in den alle relevanten Dateien hinein kopiert werden (git Verzeichnisse und ähnliches werden ausgelassen). Man muss also lediglich die Dateien auf dem Server mit denen aus /deploy überschreiben. Ausnahme ist die *parameters.ini*, die entweder später aus dem Backup zurückkopiert oder gar nicht erst überschrieben werden sollte. Das Script stellt nach dem Erstellen des deploy-Verzeichnisses automatisch wieder die vollständigen vendors her (daher der lange Update-Prozess. Zu diesem Zeitpunkt ist das Deploy-Verzeichnis aber schon komplett fertig). Es sind lediglich ein paar Besonderheiten beim Forum und beim Wiki zu beachten (siehe unten). Wenn an diesen keine Änderungen vorgenommen wurden, muss man nichts beachten. Ob an Forum/Wiki etwas geändert wurde, kann einfach per git überprüft werden:

    git diff [PRODUCTION COMMIT] external

* Um die Datenbank zu updaten, muss lediglich herausgefunden werden, welche SQL Queries seit dem Commit des Production Hosts in der history.sql hinzugekommen sind und diese auf dem Server per phpmyadmin ausführen:

    git diff [PRODUCTION COMMIT] history.sql

* Nun muss der Hash des aktuellen Commits herausgefunden werden. Dieser wird dann einfach ins bitbucket Wiki unter https://bitbucket.org/blogsh/mdkyb/wiki/Home übernommen.

    git rev-parse HEAD

* Auf dem Server muss noch per FTP das /app/cache Verzeichnis komplett geleert werden.

* Die htaccess/index.html Änderungen aus dem ersten Schritt rückgängig machen.

Administrator
=============

Wer schnell mit phpMyAdmin einen Administratoraccount erstellen will kann diese Testdaten verwenden:

    email:    admin@example.org (beliebig)
    name:     Administrator (beliebig)
    password: CydA6O7GIsXAtLjpLpcIeuM4H/Q=
    salt:     ad23b44dc2240f55f56eccff2d918632
    roles:    a:1:{i:0;s:10:"ROLE_ADMIN";}

Das Passwort, das damit eingestellt wird ist *"adminpw"*.

Ein Account mit den Daten "admin@mdkyb.dev" und "adminpw" wird durch die vorgefertigten Fixtures erstellt.

Forum
=====

* Damit die Schema Updates funktionieren muss das Forum *evtl.* in einer anderen Database als die Hauptseite installiert werden.

* Um das Forum mit der Website zu verbinden, muss es auf der Website einen allgemeinen Administrator-Account geben.

* Damit der Benutzer mit dem Administrator-Benutzer des Forums verbunden wird, muss seine "forumId" unbedingt "2" sein. Dieser Administratoraccount kann dann dazu verwendet werden, anderen Benutzern der Website Administratorrechte zu geben.

* Es soll nicht das ganze phpBB Projekt mit im Repository verwaltet werden. Darum sollte phpBB manuell im Ordner web/forum installiert werden. 
* Die Änderungen am Quelltext des Forums werden durch das Überschreiben der Daten aus external/forum über die Dateien aus web/forum übernommen. 
Um die nötigen Dateien umzukopieren, kann das "external.php" Script verwendet werden.

Einstellungen:

* Unter General > Registration Settings sollte die Registrierung ausgeschaltet sein.
* Unter General > Authentication  "Symfony" als Authentication Method auswählen

MediaWiki
=========

* Damit die Schema Updates funktionieren muss das MediaWiki *evtl.* in einer anderen Database als die Hauptseite installiert werden!
* Der Administratoraccount der Website sollte die Wiki ID 1 bekommen, sodass der Account mit dem Administrator des Wikis verbunden ist.
* Das MediaWiki sollte als "private" installiert werden, sodass nur eingeloggt Besucher Einsicht haben.
* Das MediaWiki sollte separat im Ordner web/wiki installiert werden, es ist nicht im Repository enthalten. 
* Um die Authentifizierung mit der Website zu verbinden muss der Inhalt von external/wiki/LocalSettings.append.php an die vorkonfigurierte Datei LocalSettings.php des Wikis angehangen werden (die Datei existiert erst nach der Installation). 
* Das Script external.php macht dies automatisch!
