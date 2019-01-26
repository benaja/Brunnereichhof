$(document).ready(function() {
  $('.collapsible').collapsible({
    accordion: true // A setting that changes the collapsible behavior to expandable instead of the default accordion style
  })

  $.get('/evaluation/weeks').done(data => {
    var weeksData = JSON.parse(data)
    var weeks = Array()
    var diagramData = Array()

    for (var i = 1; i <= 52; i++) {
      weeks[i - 1] = i
      diagramData[i - 1] = weeksData[i] == undefined ? 0 : weeksData[i]
    }
    var ctx = document.getElementById('myChart')
    var myChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: weeks,
        datasets: [
          {
            label: 'Anzahl Stunden',
            data: diagramData,
            backgroundColor: '#26a69a',
            borderColor: '#26a69a',
            borderWidth: 1
          }
        ]
      },
      options: {
        scales: {
          yAxes: [
            {
              ticks: {
                beginAtZero: true
              }
            }
          ]
        }
      }
    })
  })
})
