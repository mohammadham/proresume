<div class="modal fade" id="addTemplate{{ $cv->id }}" tabindex="-1" role="dialog"
    aria-labelledby="urlsModalLabel{{ $cv->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="urlsModalLabel{{ $cv->id }}">{{ __('Add Template') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addTemplateFormSubmit{{ $cv->id }}" method="post" enctype="multipart/form-data"
                    action="{{ route('admin.register.user.cv_template_add') }}">
                    @csrf
                    <input type="hidden" name="cv_id" value="{{ $cv->id }}">
                    <div class="form-group">
                        <label for="image"><strong> {{ __('Preview Image') }} </strong></label>
                        <div class="showImage mb-3">
                            <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..." class="img-thumbnail"
                                width="170">
                        </div>
                        <input type="file" name="preview_template_image" id="image" class="form-control image">
                        <p id="errpreview_template_image" class="mb-0 text-danger em"></p>
                    </div>

                    <div class="form-group">
                        <label for="">{{ __('Name') }} **</label>
                        <input class="form-control" name="preview_template_name" type="text">
                        <p id="errpreview_template_name" class="mb-0 text-danger em"></p>
                    </div>
                    <div class="form-group">
                        <label for="">{{ __('Serial Number') }} **</label>
                        <input class="form-control" name="preview_template_serial_number" type="number">
                        <p id="errpreview_template_serial_number" class="mb-0 text-danger em"></p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-primary"
                    onclick="document.getElementById('addTemplateFormSubmit{{ $cv->id }}').submit();">
                    {{ __('Submit') }}
                </button>
            </div>
        </div>
    </div>
</div>
