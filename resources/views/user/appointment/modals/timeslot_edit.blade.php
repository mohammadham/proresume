<!-- Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">
                    {{ __('Edit Time Frame') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="ajaxEditForm" action="{{ route('user.timeslot.update') }}" method="POST" >
                    @csrf
                    <input id="intimeslot_id" type="hidden" name="timeslot_id" value="">
                    <div class="form-group">
                        <label for="">{{ __('Start Time') }}</label>
                        <input id="instart" type="text" name="start" class="form-control timepicker">
                        <p id="eerrstart" class="mb-0 text-danger em"></p>
                    </div>
                    <div class="form-group">
                        <label for="">{{ __('End Time') }}</label>
                        <input id="inend" type="text" name="end" class="form-control timepicker">
                        <p id="eerrend" class="mb-0 text-danger em"></p>
                    </div>
                    <div class="form-group">
                        <label for="">{{  __('Maximum Booking') }} *</label>
                        <input id="inmax_booking" type="number" name="max_booking" class="form-control"
                            autocomplete="off" value="0">
                        <p id="eerrmax_booking" class="mb-0 text-danger em"></p>
                        <p class="text-warning mb-0">
                            {{ __('Enter 0 for unlimited booking') }}
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary btn-danger"
                            data-dismiss="modal">{{ __('Close') }}</button>
                        <button id="updateBtn"  type="button"
                            class=" btn btn-primary btn-success">{{ __('Update') }}</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
