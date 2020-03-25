export default [
  {
    version: '1.9',
    changes: [
      `Zeitraum beim Roomdispositioner kann beliebig angepasst werden.`,
      `Mobile Ansicht verbessert.`,
      `Standort des Raums wird auf dem Export angezeigt.`
    ]
  },
  {
    version: '1.8',
    changes: [
      `In den Einstellungen kann für jede Leistungsart die Erfassungsarten angepasst werden.`,
      `Überarbeitung des Designs vom Erfassen der Zeit für die Hofmitarbeiter.`,
      `Die Zeit-Elemente können per Drag-n-Drp verschoben werden.`,
      `Es können meherere Zeit-Elemente am gleichen Tag erstellt werden.`
    ]
  },
  {
    version: '1.7',
    changes: [
      `Abweichende Rechnungsadresse für Kunden erfassen.`,
      `Rechnungsadresse wird auf Wochenrapport ausgegeben.`,
      `Kalenderwochen werden bei einem Datum-Eingabefeld angezeigt.`
    ]
  },
  {
    version: '1.6',
    changes: [`Kunden können ihre Rapporte einsehen`, `Neues Kundenporal Design.`]
  },
  {
    version: '1.5',
    changes: [
      `Pdf Erstellung von den Stundenangaben`,
      `Stundenangaben des letzten Jahres können angesehen werden.`,
      `Bilder hinzufügen zu Räume.`,
      'Gelöschte Räume anzeigen und wiederherstellen.'
    ]
  },
  {
    version: '1.4.3',
    changes: [
      `Auswertung von Verpflegungen der Mitarbeiter pro Monat.`,
      `Auswertung von Verpflegungen der Hofmitarbeiter pro Monat.`,
      `Genauere Angaben für Frühstück, Mittagessen und Abendessen bei der Auswertung von Verpflegungen der Hofmitarbeiter.`
    ]
  },
  {
    version: '1.4.2',
    changes: [`Bei den Auswertungen kann das Kundenverzeichnis als CSV-Datei exportiert werden.`],
    bugfixes: [
      `PDFs können auch auf Mobile richtig erstellt und direkt im Browser geöffnet werden.`,
      `Styling fixes für Zeiterfassung auf Mobile Geräten.`
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
      `Auswertung der Verpflegungen für Mitarbeiter und Hofmitarbeiter über ein ganzes Jahr.`,
      `Auswertung der Übernachtungen für jeden Mitarbeiter über ein ganzes Jahr.`
    ]
  },
  {
    version: '1.3.1',
    bugfixes: [
      `Das Datum bei den Reservationen kann wieder bearbeitet werden.`,
      `Reservations Statistiken stimmen wieder.`,
      `Bug mit gelöschten Reservationen behoben`
    ]
  },
  {
    version: '1.3.0',
    changes: [
      `Es kann nun eine Jahresauswertung für einen Hofmitarbeiter erstellt werden.`,
      `Als Admin sieht man nun bei jedem Hofmitarbeiter seine Stundenangaben und kann diese auch direkt ändern.`
    ],
    bugfixes: [`Betten werden wieder freigegeben, wenn eine Reservation gelöscht wird.`]
  },
  {
    version: '1.2.1',
    changes: [
      `Neues Design beim Dashboard.`,
      `Die Daten des Dashboards werden nun nur noch einmal pro Stunde aktualisiert. Dafür ist die Ladezeit deutlich besser.`
    ],
    bugfixes: [`Mahlzeiten bei Kunde wird nun auch auf dem Mitarbeiter Monatsrapport angezeigt.`]
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
      `Neue Änderungen werden von nun an immer in einem Popup wie diesem hier angezeigt.`
    ],
    bugfixes: [`Es können wieder Mitarbeiter zu einer Reservation hinzugefügt werden.`]
  }
]
