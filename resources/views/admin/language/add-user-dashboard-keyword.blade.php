    <div class="modal fade" id="addModalTenantDashboardKeyword" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <form id="userAddKeyword" action="{{ route('admin.language_management.user.add_keyword') }}" method="POST">
            @csrf
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add User Dashboard Keyword') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="">{{ __('Keyword') }}*</label>
                            <input type="text" class="form-control" name="user_keyword"
                                placeholder="{{ __('Enter Keyword') }}">
                            <p id="erruser_keyword" class="mb-0 text-danger em"></p>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            {{ __('Close') }}
                        </button>
                        <button data-form="userAddKeyword" type="submit" class="submitBtn btn btn-primary btn-sm">
                            {{ __('Submit') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
