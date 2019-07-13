# Recipe-Search-Function
Diese Rezeptesuchfunktion dient als Suchmaschiene für eine persönliche Rezepte Datenbank. Als solches, braucht sie eine
Verbindung zu einer Datenbank mit den richtigen Spalten, damit sie auch richtig ausgelesen werden kann. Diese Datenbank wird
nicht mit dem Code geliefert. 

# Benutzung
**Einfache Suchen:** Als einfache Suche kann man einfach Wörter eingeben. Als default werden diese Wörter nur im Rezeptetitel
nachgeschlagen, und damit werden nur Rezepte zurückgegeben, die in ihrem Titel dieses Wort besitzen. Wenn mehrere Wörter
eingegeben werden, und die Originalsuche keine Ergebnisse zurückbringt, wird die Suche vereinfacht und es werden nach den
einzelnen Wörtern gesucht. ***Syntax: 'Wort1 Wort2 Wort3 ...'***. *Beispiel: 'Lime Bean Apple'*.

Als vertiefung zu einer normalen Suche kann man auch mit bestimmten Sonderzeichen seine Eingabe genauer gestalten. Darunter
gehören die Zeichen ':', ',' und ';', sowie die Wörter 'and' und 'or'. 

**Zeichen Doppelpunkt(:):** Mit einem Doppelpunkt kann man unter eine bestimmte Spalte nach einem Wort suchen. ***Syntax:
'Spalte: Suchwort'***. *Beispiel: 'Source: german'*. Diese Eingabe wird Rezepte wiedergeben, die unter der Spalte 'Source' einen
Wert von 'german' haben.

**Zeichen Komma(,):** Ein Komma trennt mehrere Wörter die unter eine bestimmte Spalte gesucht werden sollten. Damit kann man
unter einer Spalte gleichzeitig nach mehrere Wörter suchen, ohne die Spalte mehrmals anzugeben. ***Syntax: 'Spalte: Suchwort1,
Suchwort2, Suchwort3 ...'***. *Beispiel: 'Ingedients: carrot, celery, onion'*. Mit dieser Suche bekommt man Rezepte, die als
Zutat (Spalte 'Ingedients') 'carrot', 'celery' und 'onion' haben. 

**Zeichen Strichpunkt(;):** Ein Strichpunkt gehört in der Suche nur nach der gesamten Eingabe, und wird benutzt, um eine spezielle Sortierung von Rezepten zu erreichen. Dafür muss auch das Kästchen 'Carry Order Data' markiert sein (die mehreren Kästchen und ihre Funktionen werden unter 'Zusätzliche Parameter' erfasst). ***Syntax: 'Suche;Sortierspalte:[ASC/DESC]'***. *Beispiel: 'RecipeTitle: orange;NPictures:DESC'*. Hier wird zuerst nach Rezepte die in ihrem Titel das Wort 'orange' haben gesucht, aber danach wird auch die Sortierreihenfolge bestimmt. In dem Fall wird nach der Anzahl von Bildern gesucht die das Rezept besitzt (Spalte 'NPictures'), und in absteigender Reihenfolge (DESC). 

**Verbindungswort 'and':** Das Wort 'and' kann benutuzt werden, um mehrere Suchen zu verbinden. ***Syntax: 'Suche1 and Suche2
and Suche3 ...'***. *Beispiel: 'RecipeTitle: fruit and Ingedients: berry'*. Diese Beispielsuche ergibt nur Rezepte, die in ihrem
Titel das Wort 'fruit' haben, *und* auch als Zutat (Spalte 'Ingedients') 'berry' haben.

**Verbindungswort 'or':** Das Wort 'or' lässt einen eine neue Suchreie in der selben Suche aufstellen. ***Syntax: 'Suche1 or
Suche2 or Suche3 ...'***. *Beispiel: 'Ingedients: flour or Ingedients: corn'*. Unter diese Suche sind Rezepte die als Zutat
'flour' *oder* 'corn' haben.

# Zusätzliche Parameter (Optionen)
Zwischen der Eingabezeile und dem 'Search' Knopf befindet sich ein Knopf der die Options Tray anzeigt. Diese verschiedene
Kästchen können markiert werden, um spezielle Funktionalität zur Suche zu bringen.

**Strict Parsing Only:** Normal werden zusätzlich zur Originalsuche auch vereinfachte Suchen gemacht, die nach einzelne Wörter
aus der Originaleingabe suchen. Wenn dieses Kästchen markiert ist, wird nur die Originalsuche ausgeführt, ohne zusätzliche
vereinfache suchen.

**Force Exact Search:** Suchbegriffe werden normal mit Wildcard-charakter umarmt, die dafür sorgen, dass auch Suchergebnisse
gegeben werden, die Wörter vor- und nach den Suchbegriff vorkommen. *Beispiel: für den Suchbegriff 'strawberry' gilt auch
'strawberry cake' und 'sweet strawberry jam' aber nicht nur 'strawberry'*. Mit markierung von diesem Kästchen werden diese
Charakter entfernt, und damit werden nur genaue Ergebnisse angezeigt.

**Carry Order Data:** Die Funktion von diesem Kästchen, in zusammenhang mit der Funktion vom Strickpunkt, wurde vorhin unter
*'Zeichen Strichpunkt (;)'* erwähnt. Kurzgesagt, dieses Kästchen erlaubt den Benutzer eine bestimmte Sortierreihenfolge angeben zu können. Diese Reheinfolge wird nach der Sucheingabe hinter einem Strichpunkt geschrieben. *Beispiel: 'carrot;Source:ASC'*. Diese Suche nach 'carrot' wird nach der 'Source' Spalte in aufsteigender Reihenfolge sortiert.

# Erläuterung vom Suchprozess
**Nach dem drücken vom 'search' Knopf**

**JavaScript:** Die Werte von der Sucheingabe und Parameter werden vom HTML Document gelesen. Durch eine JQuery Funktion werden
die Daten durch ein HTTP POST request zu dem RecipeSearchPHP.php Document geschickt. Zusätzlich werden auch manche Nebenaufgaben
erledigt, wie das URL Hash einen neuen Wert zu geben, und einige HTML Elemente zu verändern, wie das Lade-icon anzuzeigen.

**PHP:** Nach der Annahme der Daten wird die Funktion 'searchFunction' aufgerufen. Diese Funktion steuert den Ablauf vom
Suchprozess, wie die Unterfunktionen richtig aufzurufen und die Suchergebnisse zu organisieren. Als erstes, werden, im Fall das
das 'Carry Order Data' Kästchen markiert ist, die Sortierdaten interpretiert und um ihre Gültigkeit geprüft. Wenn das Syntax
ungültig ist wird eine Fehlermeldung angegeben und zurückgeschickt, sonst wird sie angenommen und für die Sortierung angewendet.
Danach, wird die erste große Funktion aufgerufen, die die Sucheingabe manipuliert.

***Parse Query Strict:*** Diese Funktion Teilt die Sucheingabe nach Sonderzeichen und Verbindungswörter auf, und bildet dadurch
eine Array von Schlüsselwörter und eine passende complexe SQL PDO Statement mit Parameter. Beinhaltet in diese Funktion sind
vier Unterfunktionen, die jeweils ein Element aus der Sucheingabe ausfindig machen, und damit auch eine hierachische
Funktionsstruktur aufbauen. Die Sonderzeichen werden in einer bestimmten Reihenfolge aufgelöst: Zunächst mal wird die Eingabe
nach dem Wort 'or' aufgeteilt, danach mit 'and', folgend mit ':', und zuletzt nach ','. Die erste Unterfunktion 'parseOr' teilt
die Sucheingabe in Untereinheiten basierend auf dem Schlüsselwort 'or', und ruft für jede Untereinheit die zweite Unterfunktion
'parseAnd' auf. Damit werden die Untereinheite in kleinere Abschnitte aufgeteilt mit dem Wort 'and'. Mit der dritten
Unterfunktion 'parseColon', die von der UnterFunktion 'parseAnd' aufgerufen wird, werden die Abschnitte nach dem Inhalt von
einem Doppenpunkt(:) geprüft. Wenn vorhanden, wird der Inhalt vor dem Doppelpunkt gegen einer Liste von gültigen Spalten
geprüft, und wenn es als gültig erwiesen wird, wird die Spalte in der endgültigen SQL PDO Statement eingesetzt, sonst wird nur
'RecipeTitle' stadessen eingesetzt. Als letztes werden die einzelnen Schlüsselwörter die mit Kommas(,) getrennt sind,
auseinander genommen, und in einem Array speichert. Die komplette SQL PDO Statement und Schlüsselwortarray werden an die
Hauptfunktion 'searchFunction' zurückgegeben. 

Eine Besonderheit von dieser Funktion steht darin, dass bis zum Ende die Leerzeichen komplett entfernt werden. Damit werden
einfache Suchen wie 'coffee chili' auf 'coffeechili' verkürzt. Um dieses Problem zu erheben, würde die Funktion
'parseQuerySoft' eingesetzt. Diese Funktion wird nur aufgerufen, wenn die Sucheingabe Leerzeichen beinhaltet, und das 'Strict
Parsing Only' Kästchen *nicht* markiert ist. 

***Parse Query Soft:*** Das Leerzeichen Problem das vorher erwähnt würde ist mit dieser Funktion erhebt. Diese Function 
produziert auch vereinfachte SQL PDO Statements, die dann vereinfachte Suchergebnisse bringen. Zunächst wird die Sucheingabe
nach Leerzeichen aufgeteilt. Diese Wörter werden gegen eine Liste von gültigen Spalten vergleicht, und Wörter die passen werden
ausgefiltert. Restliche Wörter bekommen ihre eigene SQL PDO Statement, die immer unter der Spalte 'RecipeTitle' gesucht werden.
Diese Statements werden an der Hauptfunktion wiedergegeben.

Nach der Verarbeitung von der Sucheingabe, und nachdem alle SQL PDO Statements gebildet sind, werden sie mit der Funktion
'executeQuery' ausgeführt, und die Ergebnisse in einem mehrschichtigem Array eingesetzt.

***Execute Query:*** Diese Funktion ergänzt die SQL PDO Statements mit den Schüsselwörtern, und führt sie aus. Zuerst wird durch
das PDO-Protokoll eine verbindung zu der 'siegel7_Recipes' Datenbank erstellt, mit den Benutzer und Passwort Daten in dem
dbinfo.inc.php File sich befinden (hier nicht vorhanden). Direkt nach der Erstellung von der Verbindung wird die
Buchstabencodierung von ASCII auf UTF-8 umgestellt, um Fehler mit Umlaute zu erheben. Der vorbereitete Statement wird mit hilfe
der Sclüsselwörterarray ergänzt (damit auch gleichzeitig vor SQL Injection attacks abgesichert) und dann ausgeführt. Die
Suchergebnisse werden mit der PDO fetchAll Methode in der Variable '$data' geladen. Reihenweise werden die Ergebnisse ausgelesen
und in der mehrschichtigen Array '$tableData' eingesetzt. Nachdem alle Ergebnisse sortiert geworden sind, wird die Array an der
Hauptfunktion zurückgegeben.
 
Alle SQL PDO Statements werden mit der 'executeQuery' Funktion ausgeführt, und die Daten in einer Array wiedergegeben. Diese
Ergebnissätze werden in der Variable '$resultData' zusammengefasst und diese wird endgültig as JSON Objekt an das JavaScript
wiedergegeben.

**JavaScript:** Die zurückgegebene Daten von dem PHP script erlöst das Verarbeitungsprozess von den Daten. Das JSON object wird
zunächst in einen String umgewandelt, und zuschließend mit der Methode 'JSON.parse()' in eine JavaScript Array umgewandelt. Das
erste Element im Array beinhaltet nur Metainformationen, und wird als solches von dem eigentlichen Ergebnissen getrennt und
under der Variable 'metaInfo' gespeichert. Die restlichen Elemente halten die Ergebnissen von den mehreren Suchen und werden
voneinader gespaltet und in der Array 'recipeDataArray' gespeichert. Anschliesend wird die Funktion 'displayResults' aufgerufen.

***Display Results:*** Aufgabe dieser Funktion ist, die vom PHP angekommenen Daten zu verarbeiten und eine HTML Tabelle zu
erzeugen. Als erstes werden alte Fehlermeldungen und Tabellen von dem Bildshirm entfernt. Neue Fehlermeldungen (wenn vorhanden)
werden aus dem 'metaInfo' gelesen, und auf dem Bildschirm angezeigt. Die Unterfunktion 'generateTable' ist für die Erzeugung von
neuen Tabellen zuständig. Sie, durch Regelung von der Hauptfunktion, erzeugt alle erforderlichen Tabellen von den Ergebnissen,
die dann zusammengesetzt und anschließend angezeigt werden. Kleinere Aufgaben werden zusätzlich auch erledigt, wie das Lade-icon
zu verstecken und die Anzahl der Rezepte einzuschreiben.

**Fertige Anzeige der Suchergebnisse**
