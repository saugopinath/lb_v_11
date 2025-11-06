@extends('layouts.public')

@section('content')
  <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4 p-2  px-4 rounded shadow-sm"
        style="background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%); color: white;">
        <div>
            <h1 class="h4 mb-0 fw-bold">Privacy Policy</h1>
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
                        Though all efforts have been made to ensure the accuracy of the content on this application, the same should not be construed as a statement of law or used for any legal purposes. Respective Departments accepts no responsibility in relation to the accuracy, completeness, usefulness or otherwise, of the contents. Users are advised to verify/check any information, and to obtain any appropriate professional advice before acting on the information provided on this application. Jai Bangla portal does not automatically capture any specific personal information from any user (like name, phone no. or e-mail address) that allows this Directorate to identify any user individually when users visit the site. Users can generally visit the site without revealing Personal Information, unless users choose to provide such information.
                    </p>
                </blockquote>
            </section>
        </div>
@endsection