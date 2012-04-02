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

Um das Projekt möglichst klein zu halten werden die meisten Dateien für das Framework und 
zugehörige Bundles nicht mit im Repository gespeichert. Sie werden aber automatisch mit

    cd mdkyb 
    bin/vendors install

installiert. Das ist eine ganze Menge (das muss dann allerdings nicht alles auf den Webserver
geladen werden).

Ist die Datenbank richtig konfiguriert (im Moment wird eh noch keine benutzt) und funktioniert der
Webserver sollte die Seite bereits unter

    http://localhost/pfad/zu/mdkyb/web/app_dev.php

erreichbar sein.

Deployment
==========

Für das Deployen der Seite auf den Production Host werde ich ein automatisches Script erstellen. Die 
Schrittfolge ist ungefähr.
