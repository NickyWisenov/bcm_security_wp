      <div class="bcm-page-content">
        <div class="row employee-role">
          Supervisor
        </div>
        <div class="row event-details">
          <div class="col-12 event-details__status event-details__status--active">
            Active
          </div>
          <div class="col-6">
            <div class="event-details__item">
              <div class="event-details__item-title">
                Event Name
              </div>
              <div class="event-details__item-value">
                Test Ceremony
              </div>
            </div>
            <div class="event-details__item">
              <div class="event-details__item-title">
                Event Location
              </div>
              <div class="event-details__item-value">
                The Main Street 31
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="event-details__item">
              <div class="event-details__item-title">
                Start Time
              </div>
              <div class="event-details__item-value">
                12/19/2019 10:45
              </div>
            </div>
            <div class="event-details__item">
              <div class="event-details__item-title">
                End Time
              </div>
              <div class="event-details__item-value">
                12/19/2019 20:50
              </div>
            </div>
          </div>
        </div>
        <!-- BEGIN EMPLOYEE -->
        <div class="row track-hours">
          <div class="col-6 track-hours__item">
            <div class="track-hours__item-title">
              Date
            </div>
            <div class="track-hours__item-value">
              <span>12</span>/<span>19</span>/<span>2019</span>
            </div>
          </div>
          <div class="col-6 track-hours__item">
            <div class="track-hours__item-title">
              Time Elapsed
            </div>
            <div class="track-hours__item-value">
              <span>10</span>hrs<span>25</span>mins
            </div>
          </div>
        </div>
        <div class="row track-buttons">
          <button type="button" class="btn btn-success btn-md start-btn">
            START
          </button>
          <button type="button" class="btn btn-danger btn-md end-btn">
            END
          </button>
        </div>
        <!-- END EMPLOYEE -->

        <!-- BEGIN SUPERVISOR -->
        <!-- <div class="row employees-search">
          <div class="input-group md-form form-sm form-2 pl-0">
            <input class="form-control my-0 py-1 amber-border" type="text" placeholder="Search Employee" aria-label="Search Employee">
            <div class="input-group-append">
              <span class="input-group-text amber lighten-3" id="basic-text1">Search</span>
            </div>
          </div>
        </div>
        <div class="row supervisor-actions">
          <div class="col-4 supervisor-actions__number">
            Total: <span>252</span>
          </div>
          <div class="col-8 supervisor-actions__buttons">
            <button class="btn btn-success btn-sm start-shift-btn">
              START SHIFT
            </button>
            <button class="btn btn-sm btn-danger end-shift-btn">
              END SHIFT
            </button>
          </div>
        </div>
        <div class="employees">
          <div class="employees__list">
            <div class="row employee">
              <div class="col-4 p-0 employee__name">
                Neil Heird
              </div>
              <div class="col-4 p-0 employee__hours">
                <span>10</span>hrs <span>25</span>mins
              </div>
              <div class="col-3 employee__actions">
                <button class="stop-track"></button>
              </div>
              <div class="col-1 employee__status employee__status--active">
                <span></span>
              </div>
            </div>
            <div class="row employee">
              <div class="col-4 p-0 employee__name">
                Neil Heird
              </div>
              <div class="col-4 p-0 employee__hours">
                <span>10</span>hrs <span>25</span>mins
              </div>
              <div class="col-3 employee__actions">
                <button class="stop-track"></button>
              </div>
              <div class="col-1 employee__status employee__status--active">
                <span></span>
              </div>
            </div>
            <div class="row employee">
              <div class="col-4 p-0 employee__name">
                Neil Heird
              </div>
              <div class="col-4 p-0 employee__hours">
                <span>10</span>hrs <span>25</span>mins
              </div>
              <div class="col-3 employee__actions">
                <button class="stop-track"></button>
              </div>
              <div class="col-1 employee__status employee__status--active">
                <span></span>
              </div>
            </div>
            <div class="row employee">
              <div class="col-4 p-0 employee__name">
                Neil Heird
              </div>
              <div class="col-4 p-0 employee__hours">
                <span>10</span>hrs <span>25</span>mins
              </div>
              <div class="col-3 employee__actions">
                <button class="stop-track"></button>
              </div>
              <div class="col-1 employee__status employee__status--active">
                <span></span>
              </div>
            </div>
            <div class="row employee">
              <div class="col-4 p-0 employee__name">
                Neil Heird
              </div>
              <div class="col-4 p-0 employee__hours">
                <span>10</span>hrs <span>25</span>mins
              </div>
              <div class="col-3 employee__actions">
                <button class="stop-track"></button>
              </div>
              <div class="col-1 employee__status employee__status--active">
                <span></span>
              </div>
            </div>
          </div>
          <div class="row align-items-center employees__pagination">
            <button class="prev-btn"><</button>
            <div class="page">
              <span class="current-page">1</span>
              /
              <span class="current-page">15</span>
            </div>
            <button class="next-btn">></button>
          </div>
        </div> -->
        <!-- END SUPERVISOR -->
      <!-- </div> -->
      <!-- Modal -->
      <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="confirmModalLabel">Confirm</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              Are you sure to end track?
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">NO</button>
              <button type="button" class="btn btn-primary">YES</button>
            </div>
          </div>
        </div>
      </div>