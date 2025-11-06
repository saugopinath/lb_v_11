@extends('layouts.public')

@section('content')

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4 p-2  px-4 rounded shadow-sm"
        style="background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%); color: white;">
        <div>
            <h1 class="h4 mb-0 fw-bold">Copy Right Policy</h1>
        </div>

        <a href="{{ url('/login') }}"
            class="btn btn-light rounded-circle d-flex align-items-center justify-content-center shadow-sm"
            style="width: 36px; height: 36px;"
            title="Back to Home">
            <i class="fas fa-arrow-left text-primary fa-lg"></i>
        </a>
    </div>
    <section id="p_text" class="mt-2">
        <blockquote class="blockquote text-center">
            <p id="p_text_1">
                The contents on this website may not be reproduced partially or fully, without duly & prominently acknowledging the source. The contents of this website cannot be used in any misleading or objectionable context or derogatory manner. However the permission to reproduce the material available on the Jai Bangla website shall not extend to any material which is identified as being copyright of a third party. Authorization to reproduce such material must be obtained from the Departments/copyright holders concerned.
            </p>
        </blockquote>
    </section>
</div>
@endsection