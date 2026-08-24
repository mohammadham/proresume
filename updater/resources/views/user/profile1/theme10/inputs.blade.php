@if (count($inputs) > 0)
    <div id="sortable">
        @foreach ($inputs as $key => $input)
            {{-- input type text --}}
            @if ($input->type == 1)
                <div class="form-group mb-10">
                    <label for="">{{ $input->label }} @if ($input->required == 1)
                            <span>**</span>
                        @elseif($input->required == 0)
                            ({{ __('Optional') }})
                        @endif
                    </label>
                    <div class="row">
                        <div class="col-md-12">
                            <input class="form-control" type="text" name="{{ $input->name }}"
                                value="{{ old("$input->name") }}" placeholder="{{ $input->placeholder }}">
                        </div>
                    </div>
                    @error($input->name)
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @elseif ($input->type == 2)
                <div class="form-group mb-10">
                    <label for="">{{ $input->label }} @if ($input->required == 1)
                            <span>**</span>
                        @elseif($input->required == 0)
                            ({{ __('Optional') }})
                        @endif
                    </label>
                    <div class="row">
                        <div class="col-md-12">
                            <select class="form-control" name="{{ $input->name }}">
                                <option value="" selected disabled>
                                    {{ $input->placeholder }}
                                </option>
                                @foreach ($input->form_input_options as $key => $option)
                                    <option value="{{ $option->name }}"
                                        {{ old("$input->name") == $option->name ? 'selected' : '' }}>
                                        {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @error($input->name)
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @elseif ($input->type == 3)
                <div class="form-group mb-10">
                    <label for="">{{ $input->label }} @if ($input->required == 1)
                            <span>**</span>
                        @elseif($input->required == 0)
                            ({{ __('Optional') }})
                        @endif
                    </label>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex gap-20 flex-wrap">
                                @foreach ($input->form_input_options as $key => $option)
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="customRadio{{ $option->id }}"
                                            name="{{ $input->name }}[]" value="{{ $option->name }}"
                                            {{ is_array(old("$input->name")) && in_array($option->name, old("$input->name")) ? 'checked' : '' }}
                                            id="option{{ $option->id }}" class="custom-control-input">
                                        <label class="custom-control-label"
                                            for="customRadio{{ $option->id }}">{{ $option->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @error($input->name)
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @elseif ($input->type == 4)
                <div class="form-group mb-10">
                    <label for="">{{ $input->label }} @if ($input->required == 1)
                            <span>**</span>
                        @elseif($input->required == 0)
                            ({{ __('Optional') }})
                        @endif
                    </label>
                    <div class="row">
                        <div class="col-md-12">
                            <textarea class="form-control" name="{{ $input->name }}" rows="5" cols="80"
                                placeholder="{{ $input->placeholder }}">{{ old("$input->name") }}</textarea>
                        </div>
                    </div>
                    @error($input->name)
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @elseif ($input->type == 6)
                <div class="form-group mb-10">
                    <label for="">{{ $input->label }} @if ($input->required == 1)
                            <span>**</span>
                        @elseif($input->required == 0)
                            ({{ __('Optional') }})
                        @endif
                    </label>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" class="form-control datepicker" name="{{ $input->name }}"
                                autocomplete="off" value="{{ old("$input->name") }}"
                                placeholder="{{ $input->placeholder }}">
                        </div>
                    </div>
                    @error($input->name)
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @elseif ($input->type == 7)
                <div class="form-group mb-10">
                    <label for="">{{ $input->label }} @if ($input->required == 1)
                            <span>**</span>
                        @elseif($input->required == 0)
                            ({{ __('Optional') }})
                        @endif
                    </label>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" class="form-control timepicker" name="{{ $input->name }}"
                                autocomplete="off" value="{{ old("$input->name") }}"
                                placeholder="{{ $input->placeholder }}">
                        </div>
                    </div>
                    @error($input->name)
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @elseif ($input->type == 5)
                <div class="form-group mb-10">
                    <label for="">{{ $input->label }} @if ($input->required == 1)
                            <span>**</span>
                        @elseif($input->required == 0)
                            ({{ __('Optional') }})
                        @endif
                        ({{ __('Allowed extensions:') }}
                        {{ $input->file_extensions }})
                    </label>
                    <div class="row">
                        <input type="hidden" name="file_extensions" value="{{ $input->file_extensions }}"
                            name="allowed_extensions">
                        <div class="col-md-12">
                            <input name="{{ $input->name }}" type="file">
                        </div>
                    </div>
                    @error($input->name)
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        @endforeach
    </div>
@endif
