jQuery(document).ready(() => {
  // Get Event Data When select Event from Selects
  jQuery('#eventSelect').on('change', (evt) => {
    var data = {
      'action': 'select_event',
      'evt_id': evt.target.value
    };

    jQuery.post(bcm_obj.ajaxurl, data, function(response) {
      var json_response = JSON.parse(response);
      jQuery('.bcm-page-wrapper .bcm-page-content').html(json_response['response_html']);
      jQuery('body').append(json_response['modal_html']);
    });
  });

  // Modal Actions
  jQuery('body').on('click', '.track-buttons button.end-btn',  () => {
    jQuery('#bcmConfirmModal .modal-body').text('Are you sure to end track?');
    jQuery('#bcmConfirmModal').modal('show');
  });

  jQuery('body').on('click', '.track-buttons button.start-btn', () => {
    jQuery('#bcmConfirmModal .modal-body').text('Are you sure to start track?');
    jQuery('#bcmConfirmModal').modal('show');
  });

  // Start Track Time When Click Yes button
  var elapsedTime = 0;
  var tInterval = null;
  var is_tracking = false;
  jQuery('body').on('click', '#bcmConfirmModal button.bcm-modal-yes-btn', (evt) => {
    if (is_tracking) {
      tInterval && clearInterval(tInterval);
      is_tracking = false;

      var data = {
        'action': 'end_tracking',
        'evt_id': jQuery('select').children('option:selected').val(),
        'end_time': getDateTime()
      };
      // Save End Time with Event
      jQuery.post(bcm_obj.ajaxurl, data, function(response) {
        console.log(response);
      })
    } else {
      // Post the Start Time of Tracking
      var data = {
        'action': 'start_tracking',
        'evt_id': jQuery('select').children('option:selected').val(),
        'start_time': getDateTime()
      };
      // Save Start Time with Event
      jQuery.post(bcm_obj.ajaxurl, data, function(response) {
        console.log(JSON.parse(response));
        return;
        if (response)
        // If Response is Success Start Tracking
        tInterval = setInterval(() => {
          elapsedTime += 1;
          if (elapsedTime % 60 == 0) {
            jQuery('body #disHour').text(secondsToHM(elapsedTime).hh);
            jQuery('body #disMin').text(secondsToHM(elapsedTime).mm);
          }
        }, 10);

        is_tracking = true;
        // Disable Start Button
        jQuery('body .bcm-page-content button.start-btn').attr('disabled', true);
      });
    }
    jQuery('#bcmConfirmModal').modal('hide');
  });
});


/**
 * secondsToHM
 *
 * @param {seconds} seconds
 * @returns Object
 */
function secondsToHM(seconds) {
  d = Number(seconds);
  var h = Math.floor(d / 3600);
  var m = Math.floor(d % 3600 / 60);
  var s = Math.floor(d % 3600 % 60);

  var hh = h < 10 ? '0' + h : h;
  var mm = m < 10 ? '0' + m : m;

  return {
    mm: mm,
    hh: hh
  }
}

/**
 * getDateTime
 *
 * @returns String dateTime
 */
function getDateTime() {
  var now     = new Date();
  var year    = now.getFullYear();
  var month   = now.getMonth()+1;
  var day     = now.getDate();
  var hour    = now.getHours();
  var minute  = now.getMinutes();
  var second  = now.getSeconds();

  if(month.toString().length == 1) {
       month = '0'+month;
  }
  if(day.toString().length == 1) {
       day = '0'+day;
  }
  if(hour.toString().length == 1) {
       hour = '0'+hour;
  }
  if(minute.toString().length == 1) {
       minute = '0'+minute;
  }
  if(second.toString().length == 1) {
       second = '0'+second;
  }

  var dateTime = year+'-'+month+'-'+day+' '+hour+':'+minute+':'+second;
  return dateTime;
}