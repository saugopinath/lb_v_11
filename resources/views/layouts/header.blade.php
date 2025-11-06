<nav class="main-header navbar navbar-expand navbar-light" style="background-color: #3771ae;">
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
        <li class="nav-item d-flex align-items-center">
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
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
       <li class="nav-item">
    <a href="#" class="nav-link" style="font-weight:500;"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Logout
    </a>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</li>


        <!-- User Dropdown Menu -->
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <img src="{{ asset('images/download.jpg') }}" class="user-image img-circle elevation-2"
                    alt="User Image">
                <span class="d-none d-md-inline">{{ Auth::user()->designation_id }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <!-- User image -->
                <li class="user-header bg-primary">
                    <img src="{{ asset('images/download.jpg') }}" class="img-circle elevation-2" alt="User Image">
                    <p>
                        Hello {{ Auth::user()->designation_id }}
                    </p>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                    <div class="float-right">
                        <a class="btn btn-info btn-flat btn-sm" href="#">Download User Manual</a>
                        <a class="btn btn-danger btn-flat btn-sm" href="#"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout
                        </a>
                    </div>
                </li>
            </ul>
        </li>
    </ul>
</nav>
