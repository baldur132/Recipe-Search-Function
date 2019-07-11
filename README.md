# Recipe-Search-Function
Diese Rezeptesuchfunktion dient als Suchmaschiene für eine persönliche Rezetpte Datenbank. Als solches, braucht sie eine Verbindung zu einer Datenbank mit den richtigen Spalten, damit sie auch richtig ausgelesen werden. 

# Benutzung
**Einfache Suchen:** Als einfache Suche kann man einfache Worter eingeben. Als default werden diese Worter nur im Rezeptetitel nachgeschlagen, und damit werden nur Rezepte zurückgegeben, die in ihrem Titel dieses Wort besitzen. Wenn mehrere Wörter eingegeben werden, und die Originalsuche keine Ergebnisse zurückbringt, wird die Suche vereinfacht und es werden nach den einzelnen Wörtern gesucht. ***Syntax: 'Wort1 Wort2 Wort3 ...'***. *Beispiel: 'Lime Bean Apple'*.

Als vertiefung zu einer normalen Suche kann man auch mit bestimmten Sonderzeichen seine Eingabe genauer gestalten. Darunter gehören die Zeichen ':', ',' und ';', sowie die Wörter 'and' und 'or'. 

**Zeichen Doppelpunkt:** Mit einem Doppelpunkt kann man unter eine bestimmte Spalte nach einem Wort suchen. ***Syntax: 'Spalte: Suchwort'***. *Beispiel: 'Source: german'*. Diese Eingabe wird Rezepte wiedergeben, die unter der Spalte 'Source' einen Wert von 'german' haben.

**Zeichen Komma:** Ein Komma trennt mehrere Wörter die unter eine bestimmte Spalte gesucht werden sollten. Damit kann man nach der gleichzeitig für mehrere Wörter suchen, ohne die Spalte mehrmals anzugeben. ***Syntax: 'Spalte: Suchwort1, Suchwort2, Suchwort3 ...'***. *Beispiel: 'Ingedients: carrot, celery, onion'*. Mit dieser Suche bekommt man Rezepte, die als Zutat (Spalte 'Ingedients') 'carrot', 'celery' und 'onion' haben. 

**Zeichen Strichpunkt:** Ein Strichpunkt gehört nur nach der gesamten Eingabe, und wird benutzt, um eine spezielle Sortierung von Rezepten zu erreichen. Dafür muss auch das Kästchen 'carry order data', was später erfasst sein wird, markiert sein. ***Syntax: 'Suche;Sortierspalte:[ASC|DESC]'***. *Beispiel: 'RecipeTitle: orange;NPictures:DESC'*. Hier wird zuerst nach Rezepte die in ihrem Titel das Wort 'orange' haben gesucht, aber danach wird auch die Sortierreihenfolge bestimmt. In dem Fall wird nach der Anzahl von Bildern gesucht die das Rezept besitzt (Spalte 'NPictures'), und in absteigender Reihenfolge (DESC). 

**Verbindungswort 'and':** Das Wort 'and' kann benutuzt werden, um mehrere Suchen zu verbinden. ***Syntax: 'Suche1 and Suche2 and Suche3 ...'***. *Beispiel: 'RecipeTitle: fruit and Ingedients: berry'*. Diese Beispielsuche ergibt nur Rezepte, die in ihrem Titel das Wort 'fruit' haben, und auch als Zutat (Spalte 'Ingedients') 'berry' haben.

**Verbindungswort 'or':** Das Wort 'or' lässt einen eine neue Suchreie in der selben Suche aufstellen. ***Syntax: 'Suche1 or Suche2 or Suche3 ...'***. *Beispiel: 'Ingedients: flour or Ingedients: corn'*. Unter diese Suche sind Rezepte die als Zutat 'flour' *oder* 'corn' haben.
