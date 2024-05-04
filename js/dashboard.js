
    $(document).ready(function() {
        $('#datepicker').datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            weekStart: 1,
            maxViewMode: 0, 
            minViewMode: 0,
            todayHighlight: true,
            toggleActive: true,
            orientation: 'bottom left',
        });
    
    
        $('#datepicker2 input[name="start"]').datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            weekStart: 1,
            maxViewMode: 1,
            todayHighlight: true,
            toggleActive: true,
            orientation: 'bottom',
        });
    
        $('#datepicker2 input[name="end"]').datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            weekStart: 1,
            maxViewMode: 1,
            todayHighlight: true,
            toggleActive: true,
            orientation: 'bottom',
        });
            
        $('#datepicker3 input[name="start"]').datepicker({
            format: "yyyy-mm",
            minViewMode: 1,
            autoclose: true,
            orientation: 'bottom',
        });
    
        $('#datepicker3 input[name="end"]').datepicker({
            format: "yyyy-mm",
            minViewMode: 1,
            autoclose: true,
            orientation: 'bottom',
        });
    
        $('#datepicker4 input[name="start"]').datepicker({
            format: "yyyy",
            minViewMode: 2,
            autoclose: true,
            orientation: 'bottom',
        });
    
        $('#datepicker4 input[name="end"]').datepicker({
            format: "yyyy",
            minViewMode: 2,
            autoclose: true,
            orientation: 'bottom',
        });
       
        var startDate = $('#datepicker2 input[name="start"]').val();
        var endDate = $('#datepicker2 input[name="end"]').val();
        var startMonth = $('#datepicker3 input[name="start"]').val();
        var endMonth = $('#datepicker3 input[name="end"]').val();
        var startYear = $('#datepicker4 input[name="start"]').val();
        var endYear = $('#datepicker4 input[name="end"]').val();
        var currentDate = new Date().toISOString().slice(0,10);
        $('#datepicker input').val(currentDate);
        var time =  $('#datepicker input').val();
        var timeRange ;
        timeRange = time;
        if (timeInterval === 'weekly') {
        timeRange = startDate + 'to' + endDate;

        } else if (timeInterval === 'monthly') {
            timeRange = startMonth + 'to' + endMonth;
        } else if (timeInterval === 'yearly') {
            timeRange = startYear + 'to' + endYear;
        } else if (timeInterval === 'daily') {
            timeRange = time;
        }
    
    $('#timeInterval').change(function() {
        var selectedValue = $(this).val();
        if (selectedValue === 'daily') {
            $('#datepicker').show();
            $('#datepicker2').hide();
            $('#datepicker3').hide();
            $('#datepicker4').hide();
        } else if (selectedValue === 'weekly') {
            $('#datepicker').hide();
            $('#datepicker2').show();
            $('#datepicker3').hide();
            $('#datepicker4').hide();
        } else if (selectedValue === 'monthly') {
            $('#datepicker').hide();
            $('#datepicker2').hide();
            $('#datepicker3').show();
            $('#datepicker4').hide();
        } else if (selectedValue === 'yearly') {
            $('#datepicker').hide();
            $('#datepicker2').hide();
            $('#datepicker3').hide();
            $('#datepicker4').show();
        }
     
        
    });
    const labels = [
    new Date().setHours(0, 0, 0, 0), // 12:00 AM
    new Date().setHours(6, 0, 0, 0), // 6:00 AM
    new Date().setHours(12, 0, 0, 0), // 12:00 PM
    new Date().setHours(18, 0, 0, 0) // 6:00 PM
];

// Convert the timestamps to readable date strings

legend.sort((a, b) => new Date(a) - new Date(b));

function convertTimeToAMPMFormat(timeArray) {
    return timeArray.map(timeValue => {
        const timeParts = timeValue.split(':');
        let hours = parseInt(timeParts[0]);
        const minutes = timeParts[1];
        let period = 'AM';
        if (hours >= 12) {
            period = 'PM';
            if (hours > 12) {
                hours -= 12;
            }
        }
        if (hours === 0) {
            hours = 12;
        }
        return `${hours}:${minutes} ${period}`;
    });
}

const colors = [
    'rgba(255, 99, 132, 0.2)',
    'rgba(54, 162, 235, 0.2)',
    'rgba(255, 206, 86, 0.2)',
    'rgba(75, 192, 192, 0.2)',
    'rgba(153, 102, 255, 0.2)',
    'rgba(255, 159, 64, 0.2)',
    'rgba(255, 99, 132, 0.2)',
    'rgba(54, 162, 235, 0.2)',
    'rgba(255, 206, 86, 0.2)',
    'rgba(75, 192, 192, 0.2)',
    'rgba(153, 102, 255, 0.2)',
    'rgba(255, 159, 64, 0.2)'
];

const convertedXValues = convertTimeToAMPMFormat(xValues);
const convertedXValues_shopee = convertTimeToAMPMFormat(xValues_shopee);
const convertedXValues_web = convertTimeToAMPMFormat(xValues_web);
const convertedXValues_fb = convertTimeToAMPMFormat(xValues_fb);
const convertedXValues_lzd = convertTimeToAMPMFormat(xValues_lzd);
const convertedXValues_fb_ads = convertTimeToAMPMFormat(xValues_fb_ads);
const convertedXValues_del = convertTimeToAMPMFormat(xValues_del);
const convertedXValues_shp_ads = convertTimeToAMPMFormat(xValues_shp_ads);
const convertedXValues_shp_with = convertTimeToAMPMFormat(xValues_shp_with);


const today = new Date().toISOString().slice(0, 10);

const yesterday = new Date();
yesterday.setDate(yesterday.getDate() - 1);
const yesterdayLabel = yesterday.toISOString().slice(0, 10);

const uniqueCouriers_total_sales = populateUniqueCouriersTotalOrder(yValues, legend, convertedXValues,today,yesterdayLabel);
const datasets_total_sales = createDatasetsTotalOrder(uniqueCouriers_total_sales, today, yesterdayLabel);

datasets_total_sales.forEach(dataset => {
    dataset.data.sort((a, b) => a.x - b.x);
});
let myChart;
const chartElement = document.getElementById('myChart');
if (chartElement) {
  const ctx = chartElement.getContext('2d');
  myChart = new Chart(ctx, {
    type: 'line',
    data: {
      datasets: datasets_total_sales
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: {
          type: 'time',
          time: {
            parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
            unit: 'hour',
            displayFormats: {
              hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
            }
          },
          ticks: {
            autoSkip: true, // Disable auto-skipping of ticks
            source: ['12:00 AM', '6:00 AM', '12:00 PM', '6:00 PM'] // Use these specific times as tick values
          },
          title: {
            display: true,
            text: 'Time',
          }
        },
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Sales',
          }
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            title: function() {
              return ''; // Return an empty string to remove the tooltip title
            }
          }
        },
        title: {
          display: true,
          text: 'Total Sales (MYR)'
        }
      }
    }
  });
}

const uniqueCouriers_total_order = populateUniqueCouriersTotalOrder(order, legend, convertedXValues, today, yesterdayLabel);
const datasets_total_order = createDatasetsTotalOrder(uniqueCouriers_total_order, today, yesterdayLabel);

datasets_total_order.forEach(dataset => {
    dataset.data.sort((a, b) => a.x - b.x);
});
let myChart2;
const chartElement2 = document.getElementById('myChart2');
if (chartElement2) {
const ctx2 = document.getContext('2d');
myChart2 = new Chart(ctx2, {
    type: 'line',
    data: {
        datasets: datasets_total_order
    },
    options: {
        scales: {
            x: {
                type: 'time',
                time: {
                    parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
                    unit: 'hour',
                    displayFormats: {
                        hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
                    }
                },
                ticks: {
                    autoSkip: true, // Disable auto-skipping of ticks
                    source: ['12:00 AM', '6:00 AM', '12:00 PM', '6:00 PM'] // Use these specific times as tick values
                },
                title: {
                    display: true,
                    text: 'Time',
                }
            },

            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Order',
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                  title: function() {
                    return ''; // Return an empty string to remove the tooltip title
                  }
                }
              },
            title: {
                display: true,
                text: 'Total Order'
            },
          
        },
    }
});
}
const uniqueCouriers_shp_sales = populateUniqueCouriersTotalOrder(yValues_shopee, legend_shp, convertedXValues_shopee, today, yesterdayLabel);
const datasets_shp_sales = createDatasetsTotalOrder(uniqueCouriers_shp_sales, today, yesterdayLabel);

datasets_shp_sales.forEach(dataset => {
    dataset.data.sort((a, b) => a.x - b.x);
});
let myChart3;
const chartElement3 = document.getElementById('myChart3');
if (chartElement3) {
const ctx3 = document.getContext('2d');
myChart3 = new Chart(ctx3, {
    type: 'line',
    data: {
        datasets: datasets_shp_sales
    },
    options: {
        scales: {
            x: {
                type: 'time',
                time: {
                    parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
                    unit: 'hour',
                    displayFormats: {
                        hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
                    }
                },
                ticks: {
                    autoSkip: true, // Disable auto-skipping of ticks
                    source: ['12:00 AM', '6:00 AM', '12:00 PM', '6:00 PM'] // Use these specific times as tick values
                },
                title: {
                    display: true,
                    text: 'Time',
                }
            },

            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Sales',
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                  title: function() {
                    return ''; // Return an empty string to remove the tooltip title
                  }
                }
              },
            title: {
                display: true,
                text: 'Total Shopee Sales (MYR)'
            },
          
        },
    }
});
}
const uniqueCouriers_shp_order = populateUniqueCouriersTotalOrder(order_shopee, legend_shp, convertedXValues_shopee, today, yesterdayLabel);
const datasets_shp_order = createDatasetsTotalOrder(uniqueCouriers_shp_order, today, yesterdayLabel);

datasets_shp_order.forEach(dataset => {
    dataset.data.sort((a, b) => a.x - b.x);
});
let myChart4;
const chartElement4 = document.getElementById('myChart4');
if (chartElement4) {
const ctx4 = document.getContext('2d');
myChart4 = new Chart(ctx4, {
    type: 'line',
    data: {
        datasets: datasets_shp_order
    },
    options: {
        scales: {
            x: {
                type: 'time',
                time: {
                    parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
                    unit: 'hour',
                    displayFormats: {
                        hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
                    }
                },
                ticks: {
                    autoSkip: true, // Disable auto-skipping of ticks
                    source: ['12:00 AM', '6:00 AM', '12:00 PM', '6:00 PM'] // Use these specific times as tick values
                },
                title: {
                    display: true,
                    text: 'Time',
                }
            },

            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Order',
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                  title: function() {
                    return ''; // Return an empty string to remove the tooltip title
                  }
                }
              },
            title: {
                display: true,
                text: 'Total Shopee Order'
            },
          
        },
    }
});
}
const uniqueCouriers_web_sales = populateUniqueCouriersTotalOrder(yValues_web, legend_web, convertedXValues_web, today, yesterdayLabel);
const datasets_web_sales = createDatasetsTotalOrder(uniqueCouriers_web_sales, today, yesterdayLabel);

datasets_web_sales.forEach(dataset => {
    dataset.data.sort((a, b) => a.x - b.x);
});
let myChart5;
const chartElement5 = document.getElementById('myChart5');
if (chartElement5) {
const ctx5 = document.getContext('2d');
myChart5 = new Chart(ctx5, {
    type: 'line',
    data: {
        datasets: datasets_web_sales
    },
    options: {
        scales: {
            x: {
                type: 'time',
                time: {
                    parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
                    unit: 'hour',
                    displayFormats: {
                        hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
                    }
                },
                ticks: {
                    autoSkip: true, // Disable auto-skipping of ticks
                    source: ['12:00 AM', '6:00 AM', '12:00 PM', '6:00 PM'] // Use these specific times as tick values
                },
                title: {
                    display: true,
                    text: 'Time',
                }
            },

            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Sales',
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                  title: function() {
                    return ''; // Return an empty string to remove the tooltip title
                  }
                }
              },
            title: {
                display: true,
                text: 'Total Web Sales (MYR)'
            },
          
        },
    }
});
}
const uniqueCouriers_web_order = populateUniqueCouriersTotalOrder(order_web, legend_web, convertedXValues_web, today, yesterdayLabel);
    const datasets_web_order = createDatasetsTotalOrder(uniqueCouriers_web_order, today, yesterdayLabel);

datasets_web_order.forEach(dataset => {
    dataset.data.sort((a, b) => a.x - b.x);
});

let myChart6;
const chartElement6 = document.getElementById('myChart6');
if (chartElement6) {
const ctx6 = document.getContext('2d');
myChart6 = new Chart(ctx6, {
    type: 'line',
    data: {
        datasets: datasets_web_order
    },
    options: {
        scales: {
            x: {
                type: 'time',
                time: {
                    parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
                    unit: 'hour',
                    displayFormats: {
                        hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
                    }
                },
                ticks: {
                    autoSkip: true, // Disable auto-skipping of ticks
                    source: ['12:00 AM', '6:00 AM', '12:00 PM', '6:00 PM'] // Use these specific times as tick values
                },
                title: {
                    display: true,
                    text: 'Time',
                }
            },

            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Order',
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                  title: function() {
                    return ''; // Return an empty string to remove the tooltip title
                  }
                }
              },
            title: {
                display: true,
                text: 'Total Web Order'
            },
          
        },
    }
});
}
const uniqueCouriers_fb_sales = populateUniqueCouriersTotalOrder(yValues_fb, legend_fb, convertedXValues_fb, today, yesterdayLabel);
const datasets_fb_sales = createDatasetsTotalOrder(uniqueCouriers_fb_sales, today, yesterdayLabel);

datasets_fb_sales.forEach(dataset => {
    dataset.data.sort((a, b) => a.x - b.x);
});

let myChart7;
const chartElement7 = document.getElementById('myChart7');
if (chartElement7) {
const ctx7 = document.getContext('2d');
myChart7 = new Chart(ctx7, {
    type: 'line',
    data: {
        datasets: datasets_fb_sales
    },
    options: {
        scales: {
            x: {
                type: 'time',
                time: {
                    parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
                    unit: 'hour',
                    displayFormats: {
                        hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
                    }
                },
                ticks: {
                    autoSkip: true, // Disable auto-skipping of ticks
                    source: ['12:00 AM', '6:00 AM', '12:00 PM', '6:00 PM'] // Use these specific times as tick values
                },
                title: {
                    display: true,
                    text: 'Time',
                }
            },

            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Sales',
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                  title: function() {
                    return ''; // Return an empty string to remove the tooltip title
                  }
                }
              },
            title: {
                display: true,
                text: 'Total Facebook Sales (MYR)'
            },
          
        },
    }
});
}
const uniqueCouriers_fb_order = populateUniqueCouriersTotalOrder(order_fb, legend_fb, convertedXValues_fb, today, yesterdayLabel);
const datasets_fb_order = createDatasetsTotalOrder(uniqueCouriers_fb_order, today, yesterdayLabel);

datasets_fb_order.forEach(dataset => {
    dataset.data.sort((a, b) => a.x - b.x);
});
let myChart8;
const chartElement8 = document.getElementById('myChart8');
if (chartElement8) {
const ctx8 = document.getContext('2d');
myChart8 = new Chart(ctx8, {
    type: 'line',
    data: {
        datasets: datasets_fb_order
    },
    options: {
        scales: {
            x: {
                type: 'time',
                time: {
                    parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
                    unit: 'hour',
                    displayFormats: {
                        hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
                    }
                },
                ticks: {
                    autoSkip: true, // Disable auto-skipping of ticks
                    source: ['12:00 AM', '6:00 AM', '12:00 PM', '6:00 PM'] // Use these specific times as tick values
                },
                title: {
                    display: true,
                    text: 'Time',
                }
            },

            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Order',
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                  title: function() {
                    return ''; // Return an empty string to remove the tooltip title
                  }
                }
              },
            title: {
                display: true,
                text: 'Total Facebook Order'
            },
          
        },
    }
});
}
const uniqueCouriers_lzd_sales = populateUniqueCouriersTotalOrder(yValues_lzd, legend_lzd, convertedXValues_lzd, today, yesterdayLabel);
const datasets_lzd_sales = createDatasetsTotalOrder(uniqueCouriers_lzd_sales, today, yesterdayLabel);


datasets_lzd_sales.forEach(dataset => {
    dataset.data.sort((a, b) => a.x - b.x);
});
let myChart9;
const chartElement9 = document.getElementById('myChart9');
if (chartElement9) {
const ctx9 = document.getContext('2d');
myChart9 = new Chart(ctx9, {
    type: 'line',
    data: {
        datasets: datasets_lzd_sales
    },
    options: {
        scales: {
            x: {
                type: 'time',
                time: {
                    parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
                    unit: 'hour',
                    displayFormats: {
                        hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
                    }
                },
                ticks: {
                    autoSkip: true, // Disable auto-skipping of ticks
                    source: ['12:00 AM', '6:00 AM', '12:00 PM', '6:00 PM'] // Use these specific times as tick values
                },
                title: {
                    display: true,
                    text: 'Time',
                }
            },

            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Sales',
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                  title: function() {
                    return ''; // Return an empty string to remove the tooltip title
                  }
                }
              },
            title: {
                display: true,
                text: 'Total Lazada Sales (MYR)'
            },
          
        },
    }
});
}
const uniqueCouriers_lzd_order = populateUniqueCouriersTotalOrder(order_lzd, legend_lzd, convertedXValues_lzd, today, yesterdayLabel);
const datasets_lzd_order = createDatasetsTotalOrder(uniqueCouriers_lzd_order, today, yesterdayLabel);

datasets_lzd_order.forEach(dataset => {
    dataset.data.sort((a, b) => a.x - b.x);
});
let myChart10;
const chartElement10 = document.getElementById('myChart10');
if (chartElement10) {
const ctx10 = document.getContext('2d');
myChart10 = new Chart(ctx10, {
    type: 'line',
    data: {
        datasets: datasets_lzd_order
    },
    options: {
        scales: {
            x: {
                type: 'time',
                time: {
                    parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
                    unit: 'hour',
                    displayFormats: {
                        hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
                    }
                },
                ticks: {
                    autoSkip: true, // Disable auto-skipping of ticks
                    source: ['12:00 AM', '6:00 AM', '12:00 PM', '6:00 PM'] // Use these specific times as tick values
                },
                title: {
                    display: true,
                    text: 'Time',
                }
            },

            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Order',
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                  title: function() {
                    return ''; // Return an empty string to remove the tooltip title
                  }
                }
              },
            title: {
                display: true,
                text: 'Total Lazada Order'
            },
          
        },
    }
});
}
const uniqueCouriers_fb_ads_sales = populateUniqueCouriersTotalOrder2(yValues_fb_ads, legend_fb_ads,accName_fb_ads,convertedXValues_fb_ads);
const datasets_fb_ads_sales = createDatasetsTotalOrder2(uniqueCouriers_fb_ads_sales, today, yesterdayLabel);

datasets_fb_ads_sales.forEach(dataset => {
    dataset.data.sort((a, b) => a.x - b.x);
});

let myChart11;
const chartElement11 = document.getElementById('myChart11');
if (chartElement11) {
const ctx11 = document.getContext('2d');
myChart11 = new Chart(ctx11, {
    type: 'line',
    data: {
        datasets: datasets_fb_ads_sales
    },
    options: {
        scales: {
            x: {
                type: 'time',
                time: {
                    parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
                    unit: 'hour',
                    displayFormats: {
                        hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
                    }
                },
                ticks: {
                    autoSkip: true, // Disable auto-skipping of ticks
                    source: ['12:00 AM', '6:00 AM', '12:00 PM', '6:00 PM'] // Use these specific times as tick values
                },
                title: {
                    display: true,
                    text: 'Time',
                }
            },

            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Order',
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                  title: function() {
                    return ''; // Return an empty string to remove the tooltip title
                  }
                }
              },
            title: {
                display: true,
                text: 'Facebook Ads Transaction Performance'
            },
          
        },
    }
});
}


const uniqueCouriers_del_sales = populateUniqueCouriersTotalOrder2(yValues_del, legend_del,courier_name_del, convertedXValues_del);
const datasets_del = createDatasetsTotalOrder2(uniqueCouriers_del_sales, today, yesterdayLabel);

let myChart12;
const chartElement12 = document.getElementById('myChart12');
if (chartElement12) {
const ctx12 = document.getContext('2d');
myChart12 = new Chart(ctx12, {
    type: 'line',
    data: {
        datasets: datasets_del
    },
    options: {
        scales: {
            x: {
                type: 'time',
                time: {
                    parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
                    unit: 'hour',
                    displayFormats: {
                        hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
                    }
                },
                ticks: {
                    autoSkip: true, // Disable auto-skipping of ticks
                    source: ['12:00 AM', '6:00 AM', '12:00 PM', '6:00 PM'] // Use these specific times as tick values
                },
                title: {
                    display: true,
                    text: 'Time',
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Fees',
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                  title: function() {
                    return ''; // Return an empty string to remove the tooltip title
                  }
                }
              },
            title: {
                display: true,
                text: 'Courier Total Delivery Fees'
            }
        }
    }
});
}

const uniqueCouriers_shp_ads = populateUniqueCouriersTotalOrder2(yValues_shp_ads, legend_shp_ads,shopeeAccNames,convertedXValues_shp_ads);
const datasets_shp = createDatasetsTotalOrder2(uniqueCouriers_shp_ads, today, yesterdayLabel);
let myChart13;
const chartElement13 = document.getElementById('myChart13');
if (chartElement13) {
const ctx13 = document.getContext('2d');
myChart13 = new Chart(ctx13, {
    type: 'line',
    data: {
        datasets: datasets_shp
    },
    options: {
        scales: {
            x: {
                type: 'time',
                time: {
                    parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
                    unit: 'hour',
                    displayFormats: {
                        hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
                    }
                },
                ticks: {
                    autoSkip: true, // Disable auto-skipping of ticks
                    source: ['12:00 AM', '6:00 AM', '12:00 PM', '6:00 PM'] // Use these specific times as tick values
                },
                title: {
                    display: true,
                    text: 'Time',
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Sales',
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                  title: function() {
                    return ''; // Return an empty string to remove the tooltip title
                  }
                }
              },
            title: {
                display: true,
                text: 'Shopee Ads Performance'
            }
        }
    }
});
}

const uniqueCouriers_shp_with = populateUniqueCouriersTotalOrder(yValues_shp_with, legend_with, convertedXValues_shp_with, today, yesterdayLabel);
const datasets_shp_with = createDatasetsTotalOrder(uniqueCouriers_shp_with, today, yesterdayLabel);
let myChart14;
const chartElement14 = document.getElementById('myChart14');
if (chartElement14) {
const ctx14 = document.getContext('2d');
myChart14 = new Chart(ctx14, {
    type: 'line',
    data: {
        datasets: datasets_shp_with
    },
    options: {
        scales: {
            x: {
                beginAtZero: true,
                type: 'time',
                time: {
                    parser: 'h:mm a', // Parse the time in 12-hour format with AM/PM
                    unit: 'hour',
                    displayFormats: {
                        hour: 'h:mm a' // Display the time in 12-hour format with AM/PM
                    }
                },
                title: {
                    display: true,
                    text: 'Time',
                }

            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Sales',
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                  title: function() {
                    return ''; // Return an empty string to remove the tooltip title
                  }
                }
              },
            title: {
                display: true,
                text: 'Shopee Withdrawl Performance'
            }
        }
    }
});
}

function getDaysCount(startDate, endDate) {
    const oneDay = 24 * 60 * 60 * 1000; // Number of milliseconds in a day
    const start = new Date(startDate);
    const end = new Date(endDate);
    const daysCount = Math.round(Math.abs((end - start) / oneDay));
    return daysCount;
    }// Calculate number of days between startDate and endDate
   
 
    function update(chart, datasets) {
        chart.data.datasets = datasets;
        chart.update(); // Update the chart
    }

function populateUniqueCouriersTotalOrder(order, legend, convertedXValues,startDate,endDate) {
    const unique = new Map();
    const selectedValue = $('#timeInterval').val();
    daysCount = getDaysCount(startDate, endDate);
    let newStartDate = new Date(startDate);
    let newEndDate = new Date(endDate);

  
    newStartDate.setDate(newStartDate.getDate() - daysCount -1);
    newEndDate.setDate(newEndDate.getDate() - daysCount -1);

    newStartDate = newStartDate.toISOString().slice(0, 10);
    newEndDate = newEndDate.toISOString().slice(0, 10);
    for (let i = 0; i < order.length; i++) {
        const legend_order = legend[i];
        const value = {
            x: convertedXValues[i],
            y: order[i]
        };

        if(selectedValue === 'daily'){
            if (!unique.has(legend_order)) {
                unique.set(legend_order, [value]);
            } else {
                unique.get(legend_order).push(value);
            }
        }else{
            dateRange = startDate + ' to ' + endDate;
            newdaterange = newStartDate + ' to ' + newEndDate;
            if (legend_order >= startDate && legend_order <= endDate) {
                if (!unique.has(dateRange)) {
                    unique.set(dateRange, [value]);
                    
                } else {
                    unique.get(dateRange).push(value);
                }

            }else if (legend_order >= newStartDate && legend_order <= newEndDate){
                if (!unique.has(newdaterange)) {
                    unique.set(newdaterange, [value]);
                } else {
                    unique.get(newdaterange).push(value);
                }
            }
        }
    }
  
    unique.forEach((value, key) => {
        value.sort((a, b) => new Date('1970/01/01 ' + a.x) - new Date('1970/01/01 ' + b.x));
    });
    return unique;
}

// Create a map to store unique courier names and their corresponding data

function createDatasetsTotalOrder(unique, startDate, endDate) {
    const colors = [
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)',
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)'
    ];
    
    const datasets = [];
    daysCount = getDaysCount(startDate, endDate);
    let newStartDate = new Date(startDate);
    let newEndDate = new Date(endDate);

  
    newStartDate.setDate(newStartDate.getDate() - daysCount);
    newEndDate.setDate(newEndDate.getDate() - daysCount - 1);

    newStartDate = newStartDate.toISOString().slice(0, 10);
    newEndDate = newEndDate.toISOString().slice(0, 10);

   
    const selectedValue = $('#timeInterval').val();
    for (let i = 0; i < unique.size; i++) {
        const legend_order = Array.from(unique.keys())[i];
        
        const data = unique.get(legend_order);
        if(selectedValue === 'daily'){
            dateRange = endDate;
            if (legend_order === startDate) {
                datasets.push({
                    label: startDate,
                    data: data,
                    backgroundColor: "rgba(255, 99, 132, 0.2)",
                    borderColor: "rgba(255, 99, 132, 1)",
                    tension: 0.4,
                    spanGaps: true
                });
            }
            if (legend_order === endDate) {
                datasets.push({
                    label: endDate,
                    data: data,
                    backgroundColor: "lightgray",
                    borderColor: "gray",
                    borderDash: [5],
                    tension: 0.4,
                    spanGaps: true
                });
            }
        }else if (selectedValue === 'weekly' || selectedValue === 'monthly' || selectedValue === 'yearly'){
        
    
                datasets.push({
                    label: legend_order,
                    data: data,
                    backgroundColor: colors[i % colors.length],
                    borderColor: colors[i % colors.length],
                    tension: 0.4,
                    spanGaps: true
                });

        }
       
    }
 
    return datasets;
}

function populateUniqueCouriersTotalOrder2(order,legend,groups,convertedXValues) {
    const unique = new Map();
    const selectedValue = $('#timeInterval').val();
    for (let i = 0; i < order.length; i++) {
        const legend_order = legend[i];
        const group = groups[i];
        const value = {
            x: convertedXValues[i],
            y: order[i]
        };
        if(selectedValue === 'daily'){
        if (!unique.has(group)) {
            unique.set(group, [{ date: legend_order, value }]);
        } else {    
            unique.get(group).push({ date: legend_order, value });
        }
    }
    }
    unique.forEach((value, key) => {
        value.sort((a, b) => new Date('1970/01/01 ' + a.x) - new Date('1970/01/01 ' + b.x));
    });
    return unique;
}



function createDatasetsTotalOrder2(unique, startDate, endDate) {
    const colors = [
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)',
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)'
    ];
    const datasets = [];
    daysCount = getDaysCount(startDate, endDate);
    let newStartDate = new Date(startDate);
    let newEndDate = new Date(endDate);

  
    newStartDate.setDate(newStartDate.getDate() - daysCount);
    newEndDate.setDate(newEndDate.getDate() - daysCount - 1);

    newStartDate = newStartDate.toISOString().slice(0, 10);
    newEndDate = newEndDate.toISOString().slice(0, 10);

   
    const selectedValue = $('#timeInterval').val();
    
    for (let i = 0; i < unique.size; i++) {
        const legend_order = Array.from(unique.keys())[i];
        const data = unique.get(legend_order);
        data.forEach(item => {
            const legend_shp_ads_sales = item.date;
        if(selectedValue === 'daily'){
            dateRange = endDate;
            if (legend_shp_ads_sales === startDate) {
                datasets.push({
                    label: legend_order,
                    data: [item.value],
                    backgroundColor: "rgba(255, 99, 132, 0.2)",
                    borderColor: "rgba(255, 99, 132, 1)",
                    tension: 0.4,
                    spanGaps: true
                });
            }
            if (legend_shp_ads_sales === endDate) {
                datasets.push({
                    label: legend_order,
                    data: [item.value],
                    backgroundColor: "lightgray",
                    borderColor: "gray",
                    borderDash: [5],
                    tension: 0.4,
                    spanGaps: true
                });
            }
        }else if (selectedValue === 'weekly' || selectedValue === 'monthly' || selectedValue === 'yearly'){
        
            dateRange = startDate + ' to ' + endDate;
            newdaterange = newStartDate + ' to ' + newEndDate;
            if (legend_shp_ads_sales >= startDate && legend_shp_ads_sales <= endDate) {
                datasets.push({
                    label: legend_order,
                    data: [item.value],
                    backgroundColor: colors[i % colors.length],
                    borderColor: colors[i % colors.length],
                    tension: 0.4,
                    spanGaps: true
                });
            }
            if (legend_shp_ads_sales >= newStartDate && legend_shp_ads_sales <= newEndDate) {
                datasets.push({
                    label: legend_order,
                    data: [item.value],
                    backgroundColor: "lightgray",
                    borderColor: "gray",
                    borderDash: [5],
                    tension: 0.4,
                    spanGaps: true
                });
            }
        }
    });
    }

    return datasets;
}
function updateChart() {
    const selectedValue = $('#timeInterval').val();
    let startDate, endDate;
    if (selectedValue === 'daily') {
        startDate = new Date($('#datepicker input').val()).toISOString().slice(0, 10);
        endDate = new Date(startDate);
        endDate.setDate(endDate.getDate() - 1);
        endDate = endDate.toISOString().slice(0, 10);
      
    } else if (selectedValue === 'weekly') {
        startDate = new Date($('#datepicker2 input[name="start"]').val()).toISOString().slice(0, 10);
        endDate = new Date($('#datepicker2 input[name="end"]').val()).toISOString().slice(0, 10);

        endDate = new Date(endDate);

        endDate.setDate(endDate.getDate() + 1);
        endDate = endDate.toISOString().slice(0, 10);
   
    }else if (selectedValue === 'monthly') {
        const startMonth = new Date($('#datepicker3 input[name="start"]').val());
        startDate = new Date(startMonth.getFullYear(), startMonth.getMonth(), 1).toISOString().slice(0, 10);
        
        const endMonth = new Date($('#datepicker3 input[name="end"]').val());
        endDate = new Date(endMonth.getFullYear(), endMonth.getMonth() + 1, 0).toISOString().slice(0, 10);
      
    } else if (selectedValue === 'yearly') {
        startDate = new Date($('#datepicker4 input[name="start"]').val());
        startDate.setMonth(0); 
        startDate.setDate(1); 
        startDate = startDate.toISOString().slice(0, 10);
        endDate = new Date($('#datepicker4 input[name="end"]').val());
        endDate.setMonth(11);
        endDate.setDate(31); 
        endDate = endDate.toISOString().slice(0, 10);
    
    }
    if (chartElement) {
        const uniqueCouriers_total_sales = populateUniqueCouriersTotalOrder(yValues, legend, convertedXValues, startDate, endDate);
        const datasets_total_sales = createDatasetsTotalOrder(uniqueCouriers_total_sales, startDate, endDate);
        update(myChart, datasets_total_sales);
    }
    if (chartElement2) {
    const uniqueCouriers_total_order = populateUniqueCouriersTotalOrder(order, legend, convertedXValues,startDate,endDate);
    const datasets_total_order = createDatasetsTotalOrder(uniqueCouriers_total_order, startDate, endDate);
    update(myChart2,datasets_total_order)
    }
    if (chartElement3) {
    const uniqueCouriers_shp_sales = populateUniqueCouriersTotalOrder(yValues_shopee, legend_shp, convertedXValues_shopee,startDate,endDate);
    const datasets_shp_sales = createDatasetsTotalOrder(uniqueCouriers_shp_sales, startDate, endDate);
    update(myChart3,datasets_shp_sales)
    }
    if (chartElement4) {
    const uniqueCouriers_shp_order = populateUniqueCouriersTotalOrder(order_shopee, legend_shp, convertedXValues_shopee,startDate,endDate);
    const datasets_shp_order = createDatasetsTotalOrder(uniqueCouriers_shp_order, startDate, endDate);
    update(myChart4,datasets_shp_order)
    }
    if (chartElement5) {
    const uniqueCouriers_web_sales = populateUniqueCouriersTotalOrder(yValues_web, legend_web, convertedXValues_web,startDate,endDate);
    const datasets_web_sales = createDatasetsTotalOrder(uniqueCouriers_web_sales, startDate, endDate);
    update(myChart5,datasets_web_sales)
    }
    if (chartElement6) {
    const uniqueCouriers_web_order = populateUniqueCouriersTotalOrder(order_web, legend_web, convertedXValues_web,startDate,endDate);
    const datasets_web_order = createDatasetsTotalOrder(uniqueCouriers_web_order, startDate, endDate);
    update(myChart6,datasets_web_order)
    }
    if (chartElement7) {
    const uniqueCouriers_fb_sales = populateUniqueCouriersTotalOrder(yValues_fb, legend_fb, convertedXValues_fb,startDate,endDate);
    const datasets_fb_sales = createDatasetsTotalOrder(uniqueCouriers_fb_sales, startDate, endDate);
    update(myChart7,datasets_fb_sales)
    }
    if (chartElement8) {
    const uniqueCouriers_fb_order = populateUniqueCouriersTotalOrder(order_fb, legend_fb, convertedXValues_fb,startDate,endDate);
    const datasets_fb_order = createDatasetsTotalOrder(uniqueCouriers_fb_order, startDate, endDate);
    update(myChart8,datasets_fb_order)
    }
    if (chartElement9) {
    const uniqueCouriers_lzd_sales = populateUniqueCouriersTotalOrder(yValues_lzd, legend_lzd, convertedXValues_lzd,startDate,endDate);
    const datasets_lzd_sales = createDatasetsTotalOrder(uniqueCouriers_lzd_sales, startDate, endDate);
    update(myChart9,datasets_lzd_sales)
    }
    if (chartElement10) {
    const uniqueCouriers_lzd_order = populateUniqueCouriersTotalOrder(order_lzd, legend_lzd, convertedXValues_lzd,startDate,endDate);
    const datasets_lzd_order = createDatasetsTotalOrder(uniqueCouriers_lzd_order, startDate, endDate);
    update(myChart10,datasets_lzd_order)
    }
    if (chartElement11) {
    const uniqueCouriers_fb_ads_sales = populateUniqueCouriersTotalOrder2(yValues_fb_ads, legend_fb_ads, accName_fb_ads,convertedXValues_fb_ads);
    const datasets_fb_ads_sales = createDatasetsTotalOrder2(uniqueCouriers_fb_ads_sales, startDate, endDate);
    update(myChart11,datasets_fb_ads_sales)
    }
    if (chartElement12) {
    const uniqueCouriers_del_sales = populateUniqueCouriersTotalOrder2(yValues_del, legend_del,courier_name_del, convertedXValues_del);
    const datasets_del = createDatasetsTotalOrder2(uniqueCouriers_del_sales, startDate, endDate);
    update(myChart12,datasets_del)
    }
    if (chartElement13) {
    const uniqueCouriers_shp_ads = populateUniqueCouriersTotalOrder2(yValues_shp_ads, legend_shp_ads,shopeeAccNames,convertedXValues_shp_ads);
    const datasets_shp = createDatasetsTotalOrder2(uniqueCouriers_shp_ads, startDate, endDate);
    update(myChart13,datasets_shp)
    }
    if (chartElement14) {
    const uniqueCouriers_shp_with = populateUniqueCouriersTotalOrder(yValues_shp_with, legend_with, convertedXValues_shp_with,startDate,endDate);
    const datasets_shp_with = createDatasetsTotalOrder(uniqueCouriers_shp_with, startDate, endDate);
    update(myChart14,datasets_shp_with)
    }
}
 function getParameterByName(name) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }
   
$('#datepicker input, #datepicker2 input[name="end"], #datepicker3 input[name="end"], #datepicker4 input[name="end"]').change(function() {
  updateChart()
         
});

// Call updateChart() to load current date data when the page is loaded

});
