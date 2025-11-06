<!-- <div class="tab-pane fade" id="experience_details"> -->
    <div class="card card-default mb-3">
        <div class="card-header">
            <h4><b>Enclosure List (Self Attested)</b></h4>
        </div>
        <div class="card-body">

            <!-- Document Dynamic-->
            @foreach ($doc_list_man as $doc_man)
                <div class="form-group col-md-12 mb-3">
                    <label @if (in_array($doc_man->id, $required_doc)) class="required-field" @endif>
                        {{ $doc_man->doc_name }}
                    </label>

                    <input type="file" name="doc_{{ $doc_man->id }}" id="doc_{{ $doc_man->id }}" class="form-control" tabindex="1" />
                    <div class="imageSize">(Image type must be {{ $doc_man->doc_type }} and image size max {{ $doc_man->doc_size_kb }}KB)</div>
                    <span id="error_doc_{{ $doc_man->id }}" class="text-danger"></span>
                </div>
            @endforeach

            @foreach ($doc_list_opt as $doc_opt)
                <div class="form-group col-md-12 mb-3">
                    <label class="">{{ $doc_opt->doc_name }}</label>
                    <input type="file" name="doc_{{ $doc_opt->id }}" id="doc_{{ $doc_opt->id }}" class="form-control" tabindex="1" />
                    <div class="imageSize">(Image type must be {{ $doc_opt->doc_type }} and image size max {{ $doc_opt->doc_size_kb }}KB)</div>
                    <span id="error_doc_{{ $doc_opt->id }}" class="text-danger"></span>
                </div>
            @endforeach

            @if ($type == $op_type)
                <br><br>
                <h3>Already Uploaded</h3>
                @foreach($docs as $doc)
                    @if($doc->attched_document != "")
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>{{ $doc->doc_type_name }} :</strong>
                            </div>
                            <div class="col-md-8">
                                <?php 
                                    $document_mime_type = $doc->document_mime_type;
                                    $image_extension = match($document_mime_type) {
                                        'image/jpeg' => 'jpg',
                                        'image/png' => 'png',
                                        'application/pdf' => 'pdf',
                                        default => ''
                                    };
                                    $row_image = "data:image/" . $image_extension . ";base64," . $doc->attched_document;
                                ?>
                                @if(in_array(strtolower($image_extension), ['jpg','png']))
                                    <div class="border p-2">
                                        <a class="example-image-link" href="{{ $row_image }}" data-lightbox="example-1">
                                            <img class="example-image" src="{{ $row_image }}" alt="image-1" width="200" height="180" />
                                        </a>
                                    </div>
                                @elseif(strtolower($image_extension) == 'pdf')
                                    <div class="border p-2">
                                        <a id="link"
                                            href="{{ route('jbDownload', ['scheme_id' => $doc->scheme_id, 'created_by_dist_code' => $doc->created_by_dist_code, 'beneficiary_id' => $doc->beneficiary_id, 'document_type' => $doc->document_type]) }}"
                                            target="_blank" class="text-primary">Download PDF Document</a>
                                    </div>
                                @else
                                    <div class="border p-2">
                                        <p>No File Found</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
            <!-- Document Dynamic End-->

            <div class="col-md-12 text-center mt-3">
                <button type="button" name="previous_btn_experience_details" id="previous_btn_experience_details"
                    class="btn btn-info btn-lg">Previous</button>
                <button type="button" name="btn_experience_details" id="btn_experience_details"
                    class="btn btn-success btn-lg">Next</button>
            </div>

        </div>
    </div>
<!-- </div> -->

<script src="{{ asset('js/FormEntry/enclosure.js') }}"></script>
