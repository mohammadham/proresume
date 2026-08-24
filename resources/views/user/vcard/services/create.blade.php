<!-- Create Service Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">
                    {{ __('Add vCard Service') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="ajaxForm" enctype="multipart/form-data" class="modal-form"
                    action="{{ route('user.vcard.serviceStore') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_vcard_id" value="{{ $vcard->id }}">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <div class="col-12 mb-2">
                                    <label
                                        for="image"><strong>{{ __('Image') }}*</strong></label>
                                </div>
                                <div class="col-md-12 showImage mb-3">
                                    <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..."
                                        class="img-thumbnail" width="170">
                                </div>
                                <input type="file" name="image" id="image" class="form-control">
                                <p id="errimage" class="mb-0 text-danger em"></p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="">{{ __('Title') }} **</label>
                        <input type="text" class="form-control {{ $vcard->direction == 2 ? 'rtl' : '' }}"
                            name="title" placeholder="{{ __('Enter title') }}"
                            value="">
                        <p id="errtitle" class="mb-0 text-danger em"></p>
                    </div>

                    <div id="app">
                        <div class="form-group">
                            <label
                                class="form-label">{{ __('External Link Status') }}
                                **</label>
                            <div class="selectgroup w-100">
                                <label class="selectgroup-item">
                                    <input type="radio" name="external_link_status" value="1"
                                        class="selectgroup-input elstatus" data-short_details_id="shortDetails"
                                        data-ext_link_id="extLink">
                                    <span class="selectgroup-button">{{ __('Active') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="external_link_status" value="0"
                                        class="selectgroup-input elstatus" checked data-short_details_id="shortDetails"
                                        data-ext_link_id="extLink">
                                    <span
                                        class="selectgroup-button">{{ __('Deactive') }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group" id="extLink" style="display: none;">
                            <label for="">{{ __('External Link') }}</label>
                            <input type="text" class="form-control" name="external_link">
                            <p class="text-warning mb-0">
                                {{ __('If you dont want any details content, then leave this field blank') }}
                            </p>
                            <p id="errexternal_link" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group" id="shortDetails">
                            <label for="">{{ __('Short Details') }}</label>
                            <textarea class="form-control {{ $vcard->direction == 2 ? 'rtl' : '' }}" name="short_details" rows="4"
                                cols="80" placeholder="{{ __('Enter short details') }}"></textarea>
                            <p class="text-warning mb-0">
                                {{ __('If you dont want any details content, then leave this field blank') }}
                            </p>
                            <p id="errshort_details" class="mb-0 text-danger em"></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="">{{ __('Serial Number') }} **</label>
                        <input type="number" class="form-control ltr" name="serial_number" value=""
                            placeholder="{{ __('Enter Serial Number') }}">
                        <p id="errserial_number" class="mb-0 text-danger em"></p>
                        <p class="text-warning mb-0">
                            <small>{{ __('The higher the serial number is, the later the service will be shown') }}.</small>
                        </p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-dismiss="modal">{{ __('Close') }}</button>
                <button id="" data-form="ajaxForm" type="button"
                    class="submitBtn btn btn-primary">{{ __('Submit') }}</button>
            </div>
        </div>
    </div>
</div>
