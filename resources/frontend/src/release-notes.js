export default [
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
