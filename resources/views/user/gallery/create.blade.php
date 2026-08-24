 <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Gallery') }}
                 </h5>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             <div class="modal-body">
                 <form id="ajaxForm" enctype="multipart/form-data" class="modal-form"
                     action="{{ route('user.gallery.store') }}" method="POST">
                     @csrf
                     <div class="form-group">
                         <label for="image"><strong>{{ __('Image') }}*</strong></label>
                         <div class="showImage mb-3">
                             <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..." class="img-thumbnail"
                                 width="170">
                         </div>
                         <input type="file" name="image" id="image" class="form-control">
                         <p id="errimage" class="mb-0 text-danger em"></p>
                     </div>

                     <div class="form-group">
                         <label for="">{{ __('Language') }} **</label>
                         <select id="language" name="user_language_id" class="form-control">
                             <option value="" selected disabled>
                                 {{ __('Select a language') }}</option>
                             @foreach ($userLanguages as $lang)
                                 <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                             @endforeach
                         </select>
                         <p id="erruser_language_id" class="mb-0 text-danger em"></p>
                     </div>

                     <div class="form-group">
                         <label for="">{{ __('Name') }} **</label>
                         <input type="text" class="form-control" name="name" placeholder="{{ __('Enter name') }}"
                             value="">
                         <p id="errname" class="mb-0 text-danger em"></p>
                     </div>

                     <div class="form-group">
                         <label for="">{{ __('Serial Number') }}
                             **</label>
                         <input type="number" class="form-control ltr" name="serial_number" value=""
                             placeholder="{{ __('Enter Serial Number') }}">
                         <p id="errserial_number" class="mb-0 text-danger em"></p>
                         <p class="text-warning mb-0">
                             <small>{{ __('The higher the serial number is, the later the gallery image will be shown') . '.' }}</small>
                         </p>
                     </div>


                 </form>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                 <button id="" data-form="ajaxForm" type="button"
                     class="submitBtn btn btn-primary">{{ __('Submit') }}</button>
             </div>
         </div>
     </div>
 </div>
