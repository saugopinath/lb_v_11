@extends('layouts.public')

@section('content')
  <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4 p-2  px-4 rounded shadow-sm"
        style="background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%); color: white;">
        <div>
            <h1 class="h4 mb-0 fw-bold">Hyperlink Policy</h1>
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
                        At many places in this application, you will find links to other applications/websites/portals. These links have been placed for your convenience. Respective Departments is not in any way responsible for the contents and reliability of the linked websites and does not necessarily endorse the views expressed in by them. Mere presence of the link or its listing on this website should not be assumed as endorsement of any kind. We cannot guarantee that these links will work all the time and we have no control over availability of linked pages.
                    </p>
                </blockquote>
            </section>
        </div>
@endsection