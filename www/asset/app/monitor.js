
function updateSeries(series, x, y, z) {
  series[0].addPoint([x, y], true, true);
  series[1].addPoint([x, z], true, true);
}

var polledData = {
  cpu:0,mem:0,in:0,out:0,time:(new Date()).getTime()
};

var refreshTime = 10000;

function pollData()
{
  $.get( "monitor.php?data", function( data ) {
    polledData = $.parseJSON(data);
  })
  .always(function() {
    setTimeout(
      function () { 
        pollData();
      }, 
      refreshTime
    );
  });
}
pollData();

function initDataSeries(name)
{
  // generate an array of random data
  var data = [],
      time = (new Date()).getTime(),
      i,
      hist_count = appHistory['time'].length;

  for (i = -200; i < 0; i += 1) {
    if (hist_count + i < 0) {
      data.push({
        x: time + i * refreshTime,
        y: 0
      });
    } else {
      data.push({
        x: appHistory['time'][hist_count + i],
        y: appHistory[name][hist_count + i]
      });
    }
  }
  
  return data;
}

function initChart(serie1Name, serie2Name)
{
  return {
    animation: Highcharts.svg, // don't animate in old IE
    zoomType: 'x',
    events: {
      load: function () {

        // set up the updating of the chart each second
        var series = this.series;

        setInterval(function () {
            var x = polledData.time,
                y = polledData[serie1Name],
                z = polledData[serie2Name];
            updateSeries(series, x, y, z);
        }, refreshTime);
      }
    }
  };
}

$(document).ready(function () {
  refreshTime = $('select[name=refreshTime]').val() * 1000;
  $('select[name=refreshTime]').change(function() {
    refreshTime = $(this).val() * 1000;
  });

  Highcharts.setOptions({
    global: {
      useUTC: false
    },
    tooltip: {
      shared: true
    },
    legend: {
      enabled: true
    },
    exporting: {
      enabled: false
    },
    credits: {
      enabled: false
    },
    xAxis: {
      type: 'datetime',
      tickPixelInterval: 150
    }
  });

  Highcharts.chart('cpu_mem_container', {
    
    chart: initChart('cpu', 'mem'),
    title: {
      text: 'CPU & Memory'
    },
    yAxis: [{ // Primary yAxis
      labels: {
        format: '{value}%',
        style: {
          color: Highcharts.getOptions().colors[0]
        }
      },
      title: {
        text: 'CPU (%)',
        style: {
            color: Highcharts.getOptions().colors[0]
        }
      }
    }, {
      title: {
        text: 'Memory (%)',
        style: {
          color: Highcharts.getOptions().colors[2]
        }
      },
      labels: {
        format: '{value}%',
        style: {
          color: Highcharts.getOptions().colors[2]
        }
      },
      opposite: true
    }],
    series: [{
        name: 'CPU (%)',
        type: 'spline',
        color: Highcharts.getOptions().colors[0],
        data: (function () {
          return initDataSeries('cpu');
        }())
      },
      {
        name: 'Memory (%)',
        type: 'spline',
        color: Highcharts.getOptions().colors[2],
        yAxis: 1,
        data: (function () {
          return initDataSeries('mem');
      }())
    }]
  });

  /**
   * Network
   */
  Highcharts.chart('network_container', {
    
    chart: initChart('in', 'out'),
    title: {
      text: 'Network Traffic'
    },
    yAxis: [{ // Primary yAxis
      labels: {
        format: '{value}kB/s',
        style: {
          color: Highcharts.getOptions().colors[0]
        }
      },
      title: {
        text: 'In (kB/s)',
        style: {
            color: Highcharts.getOptions().colors[0]
        }
      }
    }, {
      title: {
        text: 'Out (kB/s)',
        style: {
          color: Highcharts.getOptions().colors[2]
        }
      },
      labels: {
        format: '{value}kB/s',
        style: {
          color: Highcharts.getOptions().colors[2]
        }
      },
      opposite: true
    }],
    series: [{
        name: 'In (kB/s)',
        type: 'spline',
        color: Highcharts.getOptions().colors[0],
        data: (function () {
          return initDataSeries('in');
        }())
      },
      {
        name: 'Out (kB/s)',
        type: 'spline',
        color: Highcharts.getOptions().colors[2],
        yAxis: 1,
        data: (function () {
          return initDataSeries('out');
      }())
    }]
  });
});
