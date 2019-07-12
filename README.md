# Recipe-Search-Function
Diese Rezeptesuchfunktion dient als Suchmaschiene für eine persönliche Rezetpte Datenbank. Als solches, braucht sie eine
Verbindung zu einer Datenbank mit den richtigen Spalten, damit sie auch richtig ausgelesen werden kann. 

# Benutzung
**Einfache Suchen:** Als einfache Suche kann man einfache Worter eingeben. Als default werden diese Worter nur im Rezeptetitel
nachgeschlagen, und damit werden nur Rezepte zurückgegeben, die in ihrem Titel dieses Wort besitzen. Wenn mehrere Wörter
eingegeben werden, und die Originalsuche keine Ergebnisse zurückbringt, wird die Suche vereinfacht und es werden nach den
einzelnen Wörtern gesucht. ***Syntax: 'Wort1 Wort2 Wort3 ...'***. *Beispiel: 'Lime Bean Apple'*.

Als vertiefung zu einer normalen Suche kann man auch mit bestimmten Sonderzeichen seine Eingabe genauer gestalten. Darunter
gehören die Zeichen ':', ',' und ';', sowie die Wörter 'and' und 'or'. 

**Zeichen Doppelpunkt(:):** Mit einem Doppelpunkt kann man unter eine bestimmte Spalte nach einem Wort suchen. ***Syntax:
'Spalte: Suchwort'***. *Beispiel: 'Source: german'*. Diese Eingabe wird Rezepte wiedergeben, die unter der Spalte 'Source' einen
Wert von 'german' haben.

**Zeichen Komma(,):** Ein Komma trennt mehrere Wörter die unter eine bestimmte Spalte gesucht werden sollten. Damit kann man
nach der gleichzeitig für mehrere Wörter suchen, ohne die Spalte mehrmals anzugeben. ***Syntax: 'Spalte: Suchwort1, Suchwort2,
Suchwort3 ...'***. *Beispiel: 'Ingedients: carrot, celery, onion'*. Mit dieser Suche bekommt man Rezepte, die als Zutat (Spalte
'Ingedients') 'carrot', 'celery' und 'onion' haben. 

**Zeichen Strichpunkt(;):** Ein Strichpunkt gehört nur nach der gesamten Eingabe, und wird benutzt, um eine spezielle Sortierung
von Rezepten zu erreichen. Dafür muss auch das Kästchen 'carry order data', was später erfasst sein wird, markiert sein.
***Syntax: 'Suche;Sortierspalte:[ASC/DESC]'***. *Beispiel: 'RecipeTitle: orange;NPictures:DESC'*. Hier wird zuerst nach Rezepte
die in ihrem Titel das Wort 'orange' haben gesucht, aber danach wird auch die Sortierreihenfolge bestimmt. In dem Fall wird nach
der Anzahl von Bildern gesucht die das Rezept besitzt (Spalte 'NPictures'), und in absteigender Reihenfolge (DESC). 

**Verbindungswort 'and':** Das Wort 'and' kann benutuzt werden, um mehrere Suchen zu verbinden. ***Syntax: 'Suche1 and Suche2
and Suche3 ...'***. *Beispiel: 'RecipeTitle: fruit and Ingedients: berry'*. Diese Beispielsuche ergibt nur Rezepte, die in ihrem
Titel das Wort 'fruit' haben, und auch als Zutat (Spalte 'Ingedients') 'berry' haben.

**Verbindungswort 'or':** Das Wort 'or' lässt einen eine neue Suchreie in der selben Suche aufstellen. ***Syntax: 'Suche1 or
Suche2 or Suche3 ...'***. *Beispiel: 'Ingedients: flour or Ingedients: corn'*. Unter diese Suche sind Rezepte die als Zutat
'flour' *oder* 'corn' haben.

# Zusätzliche Parameter (Optionen)
Zwischen der Eingabezeile und dem 'Search' Knopf befindet sich ein Knopf der die Options Tray anzeigt. Diese verschiedene
Kästchen können markiert werden, um spezielle Funktionalität zur suche zu bringen.

**Strict Parsing Only:** Normal werden zusätzlich zur Originalsuche auch vereinfachte Suchen gemacht, die nach einzelne Wörter
aus der Originaleingabe suchen. Wenn dieses Kästchen markiert ist, wird nur die Originalsuche ausgeführt, ohne zusätzliche
vereinfache suchen.

**Force Exact Search:** Suchbegriffe werden normal mit Wildcard-charakter umarmt, die dafür sorgen, dass auch Suchergebnisse
gegeben werden, die Worter vor- und nach den Suchbegriff vorkommen. *Beispiel: für den Suchbegriff 'strawberry' gilt auch
'strawberry cake' und 'sweet strawberry jam' und nicht nur 'strawberry'*. Mit markierung von diesem Kästchen werden diese
Charakter entfernt, und damit werden nur genaue Ergebnisse angezeigt.

**Carry Order Data:** Die Funktion von diesem Kästchen, in zusammenhang mit der Funktion vom Strickpunkt, wurde vorhin unter
*'Zeichen Strichpunkt (;)'* erwähnt.

# Erläuterung vom Suchprozess
**Nach dem drücken vom 'search' Knopf**

**JavaScript:** Die Werte von der Sucheingabe und Parameter werden vom HTML Document gelesen. Duch eine JQuery Funktion werden
die Daten durch ein HTTP POST request zu dem RecipeSearchPHP.php Document geschickt. Zusätzlich werden auch manche Nebenaufgaben
erledigt, wie das URL Hash einen neuen Wert zu geben, und einige HTML Elemente zu verändern, wie das Lade-icon anzuzeigen.

**PHP:** Nach der Annahme der Daten wird die Funktion 'searchFunction' aufgerufen. Diese Funktion steuert den Ablauf vom
Suchprozess, wie die Unterfunktionen richtig aufzurufen und die Suchergebnisse zu organisieren. Als erstes, werden, im Fall das
das 'Carry Order Data' Kästchen markiert ist, die Sortierdaten interpretiert und um ihre Gültigkeit geprüft. Wenn das Syntax
ungültig ist wird eine Fehlermeldung angegeben und zurückgeschickt, sonst wird sie angenommen und für die Sortierung angewendet.
Danach, wird die erste große Funktion aufgerufen.

***Parse Query Strict:*** Diese Funktion Teilt die Sucheingabe nach Sonderzeichen und Verbindungsworter auf, und bildet dadurch
eine Array von Schlüsselworter und eine passende complexe SQL Query mit Parameter. Beinhaltet in diese Funktion sind vier
Unterfunktionen, die jeweils ein Element aus der Sucheingabe ausfindig machen, und damit auch eine hierachische
Funktionsstruktur aufbauen. Die Sonderzeichen werden in einer bestimmten Reihenfolge aufgelöst: Zunächst mal wird die Eingabe
nach dem Wort 'or' aufgeteilt, danach mit 'and', folgend mit ':', und zuletzt nach ','. Die erste Unterfunktion 'parseOr' teilt
die Sucheingabe in Untereinheiten basierend auf dem Schlüsselwort 'or', und ruft für jede Untereinheit die zweite Unterfunktion
'parseAnd' auf. Damit werden die Untereinheite in kleinere Abschnitte aufgeteilt mit dem Wort 'and'. Mit der dritten
Unterfunktion 'parseColon', die von der UnterFunktion 'parseAnd' aufgerufen wird, werden die Abschnitte nach dem Inhalt von
einem Doppenpunkt(:) geprüft. Wenn vorhanden, wird auch der Inhalt vor dem Doppelpunkt gegen einer Liste von gültigen Spalten
geprüft, und wenn es als gültig erwiesen wird, wird die Spalte in der endgültigem SQL Query eingesetzt, sonst wird nur
'RecipeTitle' stadessen eingesetzt. 