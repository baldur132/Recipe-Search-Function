# Recipe-Search-Function
Diese Rezeptesuchfunktion dient als Suchmaschine für eine persönliche Rezeptedatenbank. Eine Datenbank mit den Spalten wie in
der Datei 'Spalten.txt' dargestellt wird benötigt damit die Suchfunktion benutzt werden kann. Diese Datenbank wird nicht mit dem
Code geliefert. 

# Übersicht Suchsyntax

**'Suchbegriff'**

**'Suchbegriff1 Suchbegriff2 ...'**

**'Spalte: Suchbegriff'**

**'Spalte: Suchbegriff1, Suchbegriff2, ...'**

*Kombination obiger Suchen mit 'and' und 'or' möglich*

# Optionen:
Zwischen der Eingabezeile und dem 'Search' Knopf befindet sich ein Knopf der das Options Tray anzeigt. Die verschiedenen
Parameter können selektiert werden, um spezielle Suchen durchzuführen:

**Strict Parsing Only:** Normal werden zusätzlich zur Originalsuche auch vereinfachte Suchen gemacht, die nach einzelnne Wörtern
aus der Originaleingabe suchen. Wenn dieses Kästchen markiert ist, wird nur die Originalsuche ausgeführt, ohne zusätzliche
vereinfachte Suchen.

**Force Exact Search:** Suchbegriffe werden normal mit Wildcard-zeichen eingeschlossen, die dafür sorgen, dass auch
Suchergebnisse angezeigt werden, die Wörter vor- und nach dem Suchbegriff beinhalten. *Beispiel: für den Suchbegriff
'strawberry' gilt auch 'strawberry cake' und 'sweet strawberry jam' aber nicht nur 'strawberry'*. Mit Markierung von diesem
Kästchen werden diese Zeichen entfernt, und damit werden nur genaue Ergebnisse angezeigt.

# Benutzung
**Einfache Suchen:** Als einfache Suche kann man einfach Wörter eingeben. Als Default werden diese Wörter nur im Rezeptetitel
gesucht, und damit werden nur Rezepte gefunden, die im Titel dieses Wort beinhalten. Wenn mehrere Wörter
eingegeben werden, und die Originalsuche keine Ergebnisse liefert, wird die Suche vereinfacht und es wird nach den
einzelnen Wörtern gesucht. ***Syntax: 'Wort1 Wort2 Wort3 ...'***. *Beispiel: 'Lime Bean Apple'*.

Als Präzisierung einer normalen Suche kann man mit bestimmten Sonderzeichen seine Eingabe genauer formulieren. Dazu gehören die
Zeichen ':', ',' und ';', sowie die Wörter 'and' und 'or'. 

**Zeichen Doppelpunkt(:):** Mit einem Doppelpunkt kann man in einer bestimmten Spalte nach einem Wort suchen. ***Syntax:
'Spalte: Suchwort'***. *Beispiel: 'Source: German'*. Diese Eingabe wird Rezepte finden, die in der Spalte 'Source' den Text
'German' enthalten.

**Zeichen Komma(,):** Ein Komma trennt mehrere Wörter die alle in einer bestimmten Spalte enthalten sein sollen. Damit kann man
in einer Spalte gleichzeitig nach mehreren Wörtern suchen, ohne die Spalte mehrmals anzugeben. ***Syntax: 'Spalte: Suchwort1,
Suchwort2, Suchwort3 ...'***. *Beispiel: 'Ingedients: carrot, celery, onion'*. Mit dieser Suche bekommt man Rezepte, die als
Zutat (Spalte 'Ingedients') 'carrot', 'celery' und 'onion' haben. 

**Verbindungswort 'and':** Das Wort 'and' kann benutzt werden, um mehrere Suchen zu verbinden. ***Syntax: 'Suche1 and Suche2
and Suche3 ...'***. *Beispiel: 'RecipeTitle: fruit and Ingedients: berry'*. Diese Beispielsuche ergibt nur Rezepte, die in ihrem
Titel das Wort 'fruit' haben, *und* auch als Zutat (Spalte 'Ingedients') 'berry' haben.

**Verbindungswort 'or':** Das Wort 'or' lässt einen mehrere Suchen gleichzeitig machen. ***Syntax: 'Suche1 or
Suche2 or Suche3 ...'***. *Beispiel: 'Ingedients: flour or Ingedients: corn'*. Unter diese Suche sind Rezepte die als Zutat
'flour' *oder* 'corn' haben.

# Sortierung von Suchergebnissen
Methoden: Suchfenster oder Tabellenanwählung

Einfache und schnelle Sortierung von den Ergebnissen kann man durch anwählen vom Spaltentitel. Dadurch wird die selbe Suche mit
durchgeführt, und die sortierten Daten werden wiedergegeben. Um nach bosondere Spalten zu sortiern ist die anwendung von einem
Strichpunkt(;) nötig:

**Zeichen Strichpunkt(;):** Ein Strichpunkt gehört in der Suche ans Ende der Eingabe, und wird benutzt, um eine spezielle
Sortierung von Rezepten zu erreichen. ***Syntax: 'Suche; Sortierspalte:[ASC/DESC]'***. *Beispiel: 'RecipeTitle: orange;
NPictures:DESC'*. Die Abkürzungen 'ASC' und 'DESC' stehen für die Wörter 'ascending' und 'descending', die übersetzt
aufsteigend und absteigend bedeuten. Diese Abkürzungen werden benutzt um die Richtung der Reihenfolge zu bestimmen, entweder
aufsteigend oder absteigend. In diesem Beispiel wird zuerst nach Rezepte die in ihrem Titel das Wort 'orange' haben gesucht,
aber danach wird auch die Sortierreihenfolge bestimmt. In dem Fall wird nach der Anzahl von Bildern die das Rezept
besitzt (Spalte 'NPictures'), und in absteigender Reihenfolge (DESC) geordnet. 

# Beschreibung des Suchprozesses
**Nach dem drücken des 'search' Knopfes startet die Suche:**

**JavaScript:** Die Werte der Sucheingabe und Parameter werden vom HTML Dokument gelesen. Durch eine JQuery Funktion werden
die Daten durch mit einem HTTP POST Request zum Server geschickt, das dann das RecipeSearchPHP.php aufruft. Zusätzlich werden
auch manche Nebenaufgaben erledigt, wie das URL Hash einen neuen Wert zu geben um eine richtige vor- rückwärts Navigation zu
ermöglichen, und einige HTML Elemente zu verändern, wie das Lade-icon anzuzeigen.

**PHP:** Nach der ankunft der Sucheingabe wird die Funktion 'searchFunction' aufgerufen. Diese Funktion steuert den Ablauf des
Suchprozesses, wie die Unterfunktionen richtig aufzurufen und die Suchergebnisse zu organisieren. Als erstes werden die
Sortierdaten interpretiert und auf ihre Gültigkeit geprüft. Wenn der Syntax ungültig ist, wird eine Fehlermeldung ausgegeben und
zurückgeschickt, sonst werden die Sortierdaten angenommen und für die Sortierung angewendet. Danach wird die Funktion
'parseQueryStrict' aufgerufen, die die Sucheingabe manipuliert.

***Parse Query Strict:*** Diese Funktion teilt die Sucheingabe nach Sonderzeichen und Verbindungswörter auf, und bildet dadurch
ein Array von Schlüsselwörtern und eine passendes komplexes SQL PDO Statement mit Parameter. Beinhaltet in dieser Funktion sind
vier Unterfunktionen, die jeweils ein Element aus der Sucheingabe ausfindig machen, und damit eine hierarchische
Funktionsstruktur aufbauen. Die Sonderzeichen werden in folgender Reihenfolge aufgelöst: Zunächst wird die Eingabe
nach dem Wort 'or' aufgeteilt, dann mit 'and', folgend nach ':', und zuletzt nach ','. Die erste Unterfunktion 'parseOr' teilt
die Sucheingabe in Untereinheiten basierend auf dem Schlüsselwort 'or', und ruft für jede Untereinheit die zweite Unterfunktion
'parseAnd' auf. Damit werden die Untereinheiten in kleinere Abschnitte aufgeteilt nach dem Wort 'and'. Mit der dritten
Unterfunktion 'parseColon', die von der Unterfunktion 'parseAnd' aufgerufen wird, werden die Abschnitte nach einem
Doppelpunkt(:) gesucht. Falls vorhanden, wird der Inhalt vor dem Doppelpunkt gegen eine Liste von gültigen Spalten
geprüft, und wenn es passt, wird die Spalte in der endgültigen SQL PDO Statement eingesetzt, sonst wird nur 'RecipeTitle'
stattdessen eingesetzt. Als letztes werden die mit Kommas(,) getrennten Schlüsselworter in einem Array gespeichert. Das
komplette SQL PDO Statement und Schlüsselwortarray werden an die Hauptfunktion 'searchFunction' zurückgegeben. 

Eine Besonderheit dieser Funktion besteht darin, dass bis zum Ende die Leerzeichen komplett entfernt werden. Damit werden
einfache Suchen wie 'coffee chili' auf 'coffeechili' verkürzt. Um dieses Problem zu beheben, würde die Funktion
'parseQuerySoft' eingesetzt. Diese Funktion wird nur aufgerufen, wenn die Sucheingabe Leerzeichen beinhaltet, und das 'Strict
Parsing Only' Kästchen *nicht* markiert ist. 

***Parse Query Soft:*** Das Leerzeichenproblem das vorher erwähnt wurde ist mit dieser Funktion behoben. Diese Funktion 
produziert auch vereinfachte SQL PDO Statements, die dann vereinfachte Suchergebnisse liefern. Zunächst wird die Sucheingabe
nach Leerzeichen aufgeteilt. Diese Wörter werden mit eine Liste von gültigen Spalten verglichen, und Wörter die gefunden werden
entfernt. Die verbleibenden Wörter bekommen ihre eigene SQL PDO Statements, die immer unter der Spalte 'RecipeTitle' gesucht
werden. Diese Statements werden an die Hauptfunktion übergeben.

Nach der Verarbeitung der Sucheingabe, und nachdem alle SQL PDO Statements gebildet sind, werden sie mit der Funktion
'executeQuery' ausgeführt, und die Ergebnisse in einem mehrschichtigen Array gespeichert.

***Execute Query:*** Diese Funktion ergänzt die SQL PDO Statements mit den Schüsselwörtern, und führt sie aus. Zuerst wird durch
das PDO-Protokoll eine Verbindung zu der 'siegel7_Recipes' Datenbank erstellt, mit den Benutzer und Passwort Daten die sich in
dem dbinfo.inc.php File sich befinden (hier nicht vorhanden). Direkt nach der Erstellung der Verbindung wird die
Buchstabencodierung von ASCII auf UTF-8 umgestellt, um Fehler mit Umlauten zu beheben. Das vorbereitete Statement wird mit Hilfe
des Sclüsselwörterarrays ergänzt (und damit auch gleichzeitig vor SQL Injektion attacks abgesichert), und dann ausgeführt. Die
Suchergebnisse werden mit der PDO fetchAll Methode in der Variable '$data' geladen. Die Ergebnisse werden reihenweise ausgelesen
und in dem mehrschichtigen Array '$tableData' eingesetzt. Nachdem alle Ergebnisse sortiert gewurden sind, wird die Array an der
Hauptfunktion geliefert.
 
Alle SQL PDO Statements werden mit der 'executeQuery' Funktion ausgeführt, und die Daten in einem Array wiedergegeben. Diese
Ergebnissätze werden in der Variable '$resultData' zusammengefasst und diese wird schließlich als JSON Objekt an das img
Webbrowser laufende JavaScript wiedergegeben.

**JavaScript:** Sobald die Ergebnisse von dem PHP Skript übergeben werden, werden sie vom JavaScript verarbeitet. Das JSON
Objekt wird zunächst in einen String umgewandelt, und schließlich mit der Methode 'JSON.parse()' in ein JavaScript Array
umgewandelt. Das erste Element im Array beinhaltet nur Metainformationen, und wird als solches von den eigentlichen Ergebnissen
getrennt und unter der Variable 'metaInfo' gespeichert. Die restlichen Elemente beinhalten die Ergebnisse von mehreren Suchen
und werden voneinander getrennt und in dem Array 'recipeDataArray' gespeichert. Anschließend wird die Funktion 'displayResults'
aufgerufen.

***Display Results:*** Aufgabe dieser Funktion ist, die Suchergebnisse im 'recipeDataArray' als HTML Tabelle darzustellen. Als
erstes werden alte Fehlermeldungen und Tabellen vom Bildschirm entfernt. Neue Fehlermeldungen (wenn vorhanden) werden aus dem
'metaInfo' gelesen und auf dem Bildschirm angezeigt. Die Unterfunktion 'generateTable' ist für die Erzeugung von neuen Tabellen
zuständig. Die Unterfunction wird mehrmals von 'displayResults' aufgerufen, um alle Ergebnisse anzuzeigen. Anschließend wird vom
Bildschirm entfernt, und die Anzahl der Rezepte angezeigt.

**Fertige Anzeige der Suchergebnisse**
