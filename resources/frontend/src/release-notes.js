export default [
  {
    version: '2.8.5',
    changes: [],
    bugfixes: ['Roomdispositioner Fehler bei Statistiken mit totalen und verwendeten Betten behoben.'],
    date: '01.07.2021'
  },
  {
    version: '2.8.4',
    changes: [
      'Im Einsatzplaner können Mitarbeiter, Autos und Werkzeuge per Rechtsklick zum geöffneten Kunden hinzugefügt und auch wieder entfernt werden.',
      'Beim Einsatzplaner werden die Mitarbeiter in den einzelnen Kunden anhand ihrer Funktion sortiert.',
      'Nachdem ein Mitarbeiter zu einem Kunde hinzugefügt wurde, wird die Suche zurückgesetzt.'
    ],
    bugfixes: [],
    date: '16.06.2021'
  },
  {
    version: '2.8.3',
    changes: [],
    bugfixes: [
      'Rapport Kommentar update behoben',
      'Rapport totale Stunden pro Tag nicht richtig mehr ausgerechnet bug behoben',
      'Andere kleine Bugs zum Wochenrapport behoben'
    ],
    date: '07.06.2021'
  },
  {
    version: '2.8.2',
    changes: [
      'Suche nach Mitarbeiter in Familienzulagen',
      'Sortierung der Tabelle anhand der verschiedenen Spalten bei Familienzulagen'
    ],
    bugfixes: [],
    date: '26.05.2021'
  },
  {
    version: '2.8.1',
    changes: [
      '"Stellvertretung" als neue Mitarbeiter Funktion'
    ],
    bugfixes: [
      'Mitarbeiter auf Übersicht aktiv und inaktiv setzen'
    ],
    date: '25.05.2021'
  },
  {
    version: '2.8',
    changes: [
      'Familienzulagen verwalten',
      'Übersicht der Familienzulagen'
    ],
    date: '22.05.2021'
  },
  {
    version: '2.7',
    changes: [
      'Einsatzplan erstellen',
      'Werkzeuge verwalten',
      'Autos verwalten'
    ],
    date: '30.04.2021'
  },
  {
    version: '2.6',
    changes: [
      'Ladegeschwindigkeit der Hofmitarbeiter verbessert.',
      'Nicht verrechnete Vorschüsse werden auf dem Monatsrapport angezeigt.'
    ],
    bugfixes: [
      'Akutalisiert Stunden auf dem Dashboard korrekt'
    ],
    date: '14.12.2020'
  },
  {
    version: '2.5',
    changes: [
      'Auf dem Dashboard können nun auch die Vorjahre angezeigt werden.',
      'Bei den Liniendiagrammen auf dem Dashboard kann zusätzlich das Vorjahr angezeigt werden.',
      'Alle Zahlen beim Dashboard sind immer aktuell und nicht mehr bis zu einer Stunde verzögert.',
      'Bei den Release Notes wird auch das Datum angezeigt.'
    ],
    date: '24.11.2020'
  },
  {
    version: '2.4',
    changes: [
      'Kunden können neu auf die schwarze Liste gesetzt werden und man kann dazu ein Kommentar hinzufügen.',
      'Kunden auf der schwarzen Liste sind auf der Übersicht schwarz gekennzeichnet.',
      'Kundennummer wird auf der Kundenübersicht angezeigt und es kann auch nach Kundennummer gesucht werden.'
    ],
    date: '07.11.2020'
  },
  {
    version: '2.3.2',
    changes: [
      'Statistiken für Vorschuss Manager.',
      'Link zu Mitarbeiter bei Vorschüsse Anezigen.'
    ],
    date: '31.10.2020'
  },
  {
    version: '2.3.1',
    changes: [
      'Vorschüsse können auf Verbucht gesetzt werden.',
      'Suchfunktion bei den Vorschüssen.',
      'Vorschuss-Tabelle kann sortiert werden.',
      'Saldo Pdf kann direkt aus der Mitarbeiter Ansicht generiert werden.'
    ],
    date: '31.10.2020'
  },
  {
    version: '2.3',
    changes: [
      'Beim Wochenrapport kann zwischen Werksvertrag und Personalverleih unterschieden werden.',
      'Stunden für Personalverleich und Werksvertrag werden auf der Auswertung des Wochenrapports angezeigt.'
    ]
  },
  {
    version: '2.2',
    changes: [
      'Vorschüsse erfassen',
      'Vorschüsse können Mithilfe von Stapelverarbeitung erfasst werden.',
      'Vorschüsse können für einzelne Mitarbeiter erfasst und angepasst werden.',
      'Es kann eine Auswertung über das Saldo der Mitarbeiter erstellt werden.',
      'Beim Mitarbeiter kann das Saldo angesehen werden.'
    ]
  },
  {
    version: '2.1',
    changes: [
      'Räume können auf aktiv oder nicht aktiv gesetzt werden.',
      `Neue Seite "Zimmer Anzeigen": Raum und Jahr kann ausgewählt werden und sieht
        dann die history der Reservationen für diesen Raum`,
      'Bessere Performance bei der Generierung einiger Pdfs'
    ],
    bugfixes: [
      'Reservations Statistiken funktionieren wieder.'
    ]
  },
  {
    version: '2.0.2',
    changes: [
      'Pdf generieren für die Übernachtungen pro Mitarbeiter.'
    ],
    bugfixes: [
      'But beim Löschen eines Mitarbeites beim Wochenrapport behoben.',
      'Mann muss sich nun weniger oft erneut anmelden'
    ]
  },
  {
    version: '2.0.1',
    bugfixes: ['Pdf erstellen für Reservation funktioniert wieder.']
  },
  {
    version: '2.0',
    changes: [
      'Das Design und Layout wurde überarbeitet.',
      'Version des Frameworks upgedated.',
      'Änderungen beim Bearbeiten werden nun zuverlässiger gespeichert.',
      'In der Navigation ist zu sehen ob alle Änderunge gespeichert sind.',
      'Einzelne Änderungen können rückgängig gemacht werden.',
      'Einträge beim Roomdispositioner können durchsucht werden.',
      'Die Zeitspanne beim Roomdispositioner kann beliebig angepasst werden (bis zu einem Jahr).',
      'Zusätzliche Auswertungen beim Roomdispositioner.',
      'Ladeanimationen beim generieren der PDFs.',
      'Das Hochladen des Profilbildes eines Mitarbeiters wurde überarbeitet.'
    ],
    bugfixes: ['Kallenderwochen stimmen nun.', 'Diverse Bugs behoben, die du nichtmal bemerkt haben solltest.']
  },
  {
    version: '1.12',
    changes: [
      'Arbeitseintrittsjahr und Führerschein als neues Feld zu Mitarbeiter hinzugefügt.',
      'Jahresauswertung der Übernachtungen aller Mitarbeiter'
    ],
    bugfixes: ['Roomdispositioner Statistik behoben.', 'Miterbeiter Erstellung nur noch mit der entsprechenden Berechtigung möglich.']
  },
  {
    version: '1.11.2',
    changes: ['Navigationsbar überarbeitet'],
    bugfixes: ['Bug von Navigationsbar auf Mobile behoben.', 'Roomdispositioner: Einträge bei erstem Laden nicht angezeigt behoben.']
  },
  {
    version: '1.11.1',
    bugfixes: ['Raum erstellung ohne Bild.', 'Weitere kleine Raum erstellung Fixes.']
  },
  {
    version: '1.11',
    changes: [
      'Stundenangaben können als Admin nun auch pro Kunde erfasst werden und nicht nur pro Woche.',
      'Bei den Stundenangaben kann nun ein PDF für jede Woche erstellt werden.',
      'Stundenangaben für Kunden verbessert.'
    ],
    bugfixes: ['Stundenangaben PDf über das ganze Jahr, fehlerhafte Einträge behoben.', 'Stundenangaben Bug für Kunde behoben']
  },
  {
    version: '1.10.1',
    bugfixes: ['Bilder können auch bei der Erstellung des Raums hochgeladen werden.', 'Stundenangaben Pdf generieren Bugs behoben.']
  },
  {
    version: '1.10',
    changes: [
      `Mitarbeiter können sich auch anmelden. Wenn ein Mitarbeiter ein Email adresse hat, kann er auf
        Passwort vergessen gehen und sich anschliessend anmelden.`,
      'Release Notes sind im Menu verlinkt.'
    ],
    bugfixes: [
      `Neue Rolle für das Bearbeiten von Rollen. Was bedeutet, dass nun die Rolle Admin auch Rollen
        verwalten können.`
    ]
  },
  {
    version: '1.9',
    changes: [
      'Zeitraum beim Roomdispositioner kann beliebig angepasst werden.',
      'Mobile Ansicht verbessert.',
      'Standort des Raums wird auf dem Export angezeigt.'
    ]
  },
  {
    version: '1.8',
    changes: [
      'In den Einstellungen kann für jede Leistungsart die Erfassungsarten angepasst werden.',
      'Überarbeitung des Designs vom Erfassen der Zeit für die Hofmitarbeiter.',
      'Die Zeit-Elemente können per Drag-n-Drp verschoben werden.',
      'Es können meherere Zeit-Elemente am gleichen Tag erstellt werden.'
    ]
  },
  {
    version: '1.7',
    changes: [
      'Abweichende Rechnungsadresse für Kunden erfassen.',
      'Rechnungsadresse wird auf Wochenrapport ausgegeben.',
      'Kalenderwochen werden bei einem Datum-Eingabefeld angezeigt.'
    ]
  },
  {
    version: '1.6',
    changes: ['Kunden können ihre Rapporte einsehen', 'Neues Kundenporal Design.']
  },
  {
    version: '1.5',
    changes: [
      'Pdf Erstellung von den Stundenangaben',
      'Stundenangaben des letzten Jahres können angesehen werden.',
      'Bilder hinzufügen zu Räume.',
      'Gelöschte Räume anzeigen und wiederherstellen.'
    ]
  },
  {
    version: '1.4.3',
    changes: [
      'Auswertung von Verpflegungen der Mitarbeiter pro Monat.',
      'Auswertung von Verpflegungen der Hofmitarbeiter pro Monat.',
      'Genauere Angaben für Frühstück, Mittagessen und Abendessen bei der Auswertung von Verpflegungen der Hofmitarbeiter.'
    ]
  },
  {
    version: '1.4.2',
    changes: ['Bei den Auswertungen kann das Kundenverzeichnis als CSV-Datei exportiert werden.'],
    bugfixes: [
      'PDFs können auch auf Mobile richtig erstellt und direkt im Browser geöffnet werden.',
      'Styling fixes für Zeiterfassung auf Mobile Geräten.'
    ]
  },
  {
    version: '1.4.1',
    changes: [
      `Es ist jetzt möglich, das Passwort zurückzusetzten. Dafür kann man auf der Login-Seite auf
      "Passwort vergesse" klicken un erhält dann eine Email, welche den Link zum Zurücksetzen des Passworts enthält. `
    ]
  },
  {
    version: '1.4.0',
    changes: [
      `Bei jedem Raum sieht man nun direkt von wann bis wann er belegt ist. Dies kann entweder pro Monat oder
       Jahr ausgewählt werden. Die Tabelle kann auch als Pdf exportiert werden.`,
      'Auswertung der Verpflegungen für Mitarbeiter und Hofmitarbeiter über ein ganzes Jahr.',
      'Auswertung der Übernachtungen für jeden Mitarbeiter über ein ganzes Jahr.'
    ]
  },
  {
    version: '1.3.1',
    bugfixes: [
      'Das Datum bei den Reservationen kann wieder bearbeitet werden.',
      'Reservations Statistiken stimmen wieder.',
      'Bug mit gelöschten Reservationen behoben'
    ]
  },
  {
    version: '1.3.0',
    changes: [
      'Es kann nun eine Jahresauswertung für einen Hofmitarbeiter erstellt werden.',
      'Als Admin sieht man nun bei jedem Hofmitarbeiter seine Stundenangaben und kann diese auch direkt ändern.'
    ],
    bugfixes: ['Betten werden wieder freigegeben, wenn eine Reservation gelöscht wird.']
  },
  {
    version: '1.2.1',
    changes: [
      'Neues Design beim Dashboard.',
      'Die Daten des Dashboards werden nun nur noch einmal pro Stunde aktualisiert. Dafür ist die Ladezeit deutlich besser.'
    ],
    bugfixes: ['Mahlzeiten bei Kunde wird nun auch auf dem Mitarbeiter Monatsrapport angezeigt.']
  },
  {
    version: '1.2.0',
    changes: [
      `Mit dieser Version können nun gelöschte Mitarbeiter, Gäste, Hofmitarbeiter und Kunden
          wiederhergestellt werden. Das bedeutet auch, dass ihre Zeitangaben und andere Einträge
          nicht mehr komplett gelöscht werden, sondern immer noch in der Auswertung ersichtlich sind.`,
      `Wenn ein Bett gelöscht wird, welches sich noch in einem Raum befindet, wird es nun nicht mehr aus
          dem Raum gelöscht, sonder ist dort weiterhin ersichtlich.`,
      `Im Raumplaner gibt es nun eine optische Unterscheidung bei Auswählen des Mitarbeiters, zwischen
          einem Mitarbeiter und einem Gast.`,
      'Neue Änderungen werden von nun an immer in einem Popup wie diesem hier angezeigt.'
    ],
    bugfixes: ['Es können wieder Mitarbeiter zu einer Reservation hinzugefügt werden.']
  }
]
