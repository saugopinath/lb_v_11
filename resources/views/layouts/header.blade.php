<nav class="main-header navbar navbar-expand navbar-light " style="background-color: #3771ae;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>
    <style>
        .main-header.navbar a,
        .main-header.navbar .nav-link,
        .main-header.navbar .nav-item i,
        .main-header.navbar #navbar-datetime,
        .main-header.navbar .d-md-inline {
            color: white !important;
        }
    </style>



    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item d-flex align-items-center mr-3">
            <i class="nav-icon fas fa-clock mr-1"></i>
            <span id="navbar-datetime" style="font-weight: 500;"></span>
        </li>
        <!-- FAQ Link -->
        <li class="nav-item">
            <a href="#" class="nav-link" style="font-weight:500;">
                FAQ
            </a>
        </li>
        <!-- Fullscreen Button -->
        <li class="nav-item mt-1">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>

        <!-- User Dropdown Menu -->
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ asset('images/download.jpg') }}" class="user-image img-circle elevation-2"
                    alt="User Image">
                <span class="d-none d-md-inline">{{ Auth::user()->username }}</span>
            </a>

            <!-- Bootstrap 5 uses <div> instead of <ul> -->
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">

                <!-- User image -->
                <div class="user-header bg-primary text-center ">
                    <img src="{{ asset('images/download.jpg') }}" class="img-circle elevation-2 w-25 mt-2"
                        alt="User Image">

                    <p>Hello {{ Auth::user()->username }}</p>
                </div>

                <!-- Footer -->
                <div class="user-footer text-center">
                    <a href="#" class="btn btn-info btn-flat btn-sm">Download User Manual</a>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-flat btn-sm">{{ __('Sign Out') }}</button>
                    </form>
                </div>
            </div>
        </li>


    </ul>
</nav>
