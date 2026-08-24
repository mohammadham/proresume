 <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Edit Process') }}
                 </h5>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             <div class="modal-body">
                 <form id="ajaxEditForm" enctype="multipart/form-data" class="modal-form"
                     action="{{ route('user.work.process.update') }}" method="POST">
                     @csrf
                     <input type="hidden" name="id" id="inid">
                     <div class="form-group">
                         <label for="image"><strong>{{ __('Image') }}*</strong></label>
                         <div class="showImage mb-3">
                             <img src="" alt="..." id="inimage" class="img-thumbnail" width="170">
                         </div>
                         <input type="file" name="image" id="image" class="form-control">
                         <p id="eerrimage" class="mb-0 text-danger em"></p>
                     </div>

                     <div class="form-group">
                         <label for="">{{ __('Title') }} **</label>
                         <input type="text" class="form-control" id="intitle" name="title"
                             placeholder="{{ __('Enter title') }}" value="">
                         <p id="eerrtitle" class="mb-0 text-danger em"></p>
                     </div>

                     <div class="form-group">
                         <label for="subtitle">{{ __('Subtitle') }}**</label>
                         <input id="insubtitle" type="text" class="form-control" name="subtitle"
                             placeholder="{{ __('Enter subtitle') }}">
                         <p id="eerrsubtitle" class="mb-0 text-danger em"></p>
                     </div>

                     <div class="form-group">
                         <label for="">{{ __('Serial Number') }}
                             **</label>
                         <input type="number" class="form-control" name="serial_number" id="inserial_number"
                             placeholder="{{ __('Enter Serial Number') }}">
                         <p id="eerrserial_number" class="mb-0 text-danger em"></p>
                         <p class="text-warning mb-0">
                             <small>{{ __('The higher the serial number is, the later the work process will be shown.') }}</small>
                         </p>
                     </div>


                 </form>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                 <button id="updateBtn" type="button" class="btn btn-primary">{{ __('Update') }}</button>
             </div>
         </div>
     </div>
 </div>
