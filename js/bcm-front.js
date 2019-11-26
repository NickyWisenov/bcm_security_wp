var elapsedTime = 0;
var tInterval = null;
var is_tracking = false;

jQuery(document).ready(() => {
  // Get Event Data When select Event from Selects
  jQuery('#eventSelect').on('change', (evt) => {
    // first stop timer
    bcm_stop_timer();

    var data = {
      'action': 'select_event',
      'evt_id': evt.target.value
    };
    jQuery('.bcm-ajax-loader').show();

    jQuery.ajax({
      type : 'POST',
      url : bcm_obj.ajaxurl,
      data : data,
      dataType: 'json',
      success : function(response) {
        if ( response.success == true ) {
          jQuery('.bcm-page-wrapper .bcm-page-content').html(response.data.response_html);
          jQuery('body').append(response.data.modal_html);
          var status = response.data.time_data.status;
          if (status=='active') {
            bcm_start_timer();
          } else if (status == 'past') {
            // 
          }

        } else {
          // ajax handling error
          alert( response.data.msg );
        }
        jQuery('.bcm-ajax-loader').hide();
      }
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
  jQuery('body').on('click', '#bcmConfirmModal button.bcm-modal-yes-btn', (evt) => {
    jQuery('#bcmConfirmModal').modal('hide');
    jQuery('.bcm-ajax-loader').show();
    if (is_tracking) { // stop tracking button handling
      bcm_stop_timer();

      var data = {
        'action': 'end_tracking',
        'evt_id': jQuery('select').children('option:selected').val(),
      };

      jQuery.ajax({
        type: 'POST',
        url: bcm_obj.ajaxurl,
        data: data,
        dataType: 'json',
        success: function(response) {
          if ( response.success == true ) {
            // 
          } else {
            alert( response.data.msg );
          }
          jQuery('.bcm-ajax-loader').hide();
        }
      });
    } else { // start tracking button handling
      var data = {
        'action': 'start_tracking',
        'evt_id': jQuery('select').children('option:selected').val(),
      };
      // Save Start Time with Event
      jQuery.ajax({
        type: 'POST',
        url: bcm_obj.ajaxurl,
        data: data,
        dataType: 'json',
        success: function(response) {
          if ( response.success == true ) {
            bcm_start_timer();
          } else {
            alert( response.data.msg );
          }
          jQuery('.bcm-ajax-loader').hide();
        }
      });
    }
  });
});

function bcm_start_timer() {
  elapsedTime = Number(jQuery('#disHour').data('second'));
  tInterval = setInterval(() => {
    elapsedTime += 1;
    // if (elapsedTime % 60 == 0) {
      var hms= secondsToHMS(elapsedTime);
      jQuery('body #disHour').text(hms.hh);
      jQuery('body #disMin').text(hms.mm);
      jQuery('body #disSec').text(hms.ss);
    // }
  }, 1000);

  is_tracking = true;
  jQuery('body .bcm-page-content button.end-btn').attr('disabled', false); // Active Stop Button
  jQuery('body .bcm-page-content button.start-btn').attr('disabled', true); // Disable Start Button
}

function bcm_stop_timer() {
  tInterval && clearInterval(tInterval);
  is_tracking = false;
  jQuery('body .bcm-page-content button.end-btn').attr('disabled', true); // Disable Stop Button
}

/**
 * secondsToHMS
 *
 * @param {seconds} seconds
 * @returns Object
 */
function secondsToHMS(seconds) {
  d = Number(seconds);
  var h = Math.floor(d / 3600);
  var m = Math.floor(d % 3600 / 60);
  var s = Math.floor(d % 60);

  var hh = h < 10 ? '0' + h : h;
  var mm = m < 10 ? '0' + m : m;
  var ss = s < 10 ? '0' + s : s;

  return {
    mm: mm,
    hh: hh,
    ss: ss
  }
}
