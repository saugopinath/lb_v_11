<style>
    /* Container setup for fixed height (excluding user panel + search) */
    /* .sidebar {
    display: flex;
    flex-direction: column;
    height: 110vh;
    overflow: hidden;
  }

  /* Menu wrapper will be scrollable */
    /* .sidebar-menu {
    flex: 1;
    overflow-y: hidden;
    max-height: 100%;
    transition: overflow 0.3s ease;
  } */

    /* Enable scroll only on hover */
    /* .sidebar:hover .sidebar-menu {
    overflow-y: auto;
  } */

    /* Optional: Webkit Scrollbar Styling */
    /* .sidebar-menu::-webkit-scrollbar {
    width: 6px;
  } */

    /* .sidebar-menu::-webkit-scrollbar-thumb {
        background-color: rgba(100, 100, 100, 0.4);
        border-radius: 3px;
    }

    .sidebar-menu::-webkit-scrollbar-track {
        background: transparent;
    } */
    /* Fix menu text breaking issue and hover area width */
    .nav-sidebar .nav-link {
        display: flex;
        align-items: center;
        white-space: nowrap;
        /* Prevent text wrapping */
        overflow: hidden;
        text-overflow: ellipsis;
        /* Add ... for long names */
        width: 100%;
    }

    .nav-sidebar .nav-link span,
    .nav-sidebar .nav-link p {
        flex: 1;
        /* Let text fill available width */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Ensure hover fills full width */
    .nav-sidebar .nav-item>.nav-link:hover {
        width: 100%;
    }

    /* Tooltip for full text on hover */
    .nav-sidebar .nav-link[title] {
        position: relative;
    }


    .sidebar-dark-primary {
        /* background: linear-gradient(180deg, #e71818 0%, #060613 100%) !important; */
        background: linear-gradient(180deg, #010438 0%, #060613 100%) !important;
        /* Top to bottom gradient */
    }

    /* Active menu item */
    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active {
        background: linear-gradient(180deg, #03346E 0%, #065084 100%) !important;
        color: #fff !important;
    }

    /* Brand section */
    .sidebar-dark-primary .brand-link {
        background: linear-gradient(90deg, #234C6A 0%, #065084 100%) !important;
        /* Horizontal gradient */
        color: #fff;
    }

    /* Hover on menu items */
    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link:hover {
        background: linear-gradient(180deg, #03346E 0%, #065084 100%) !important;
        color: #fff;
    }
</style>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <div class="brand-link d-flex align-items-center text-decoration-none">
        <img src="{{ asset('images/download.jpg') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8; max-width: 30px; max-height: 30px;">
        <span class="brand-text font-weight-bold ml-3"> Lakshmir Bhandar </span>
    </div>


    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('images/download.jpg') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->designation_id }}</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline mt-3">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                    aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                @php
                    $designation_id = Auth::user()->designation_id;
                @endphp
                <li class="nav-item">
                    <a href="{{ url('/backendlogin') }}" class="nav-link active">
                        <i class="nav-icon fa fa-link"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                @if (Storage::exists('menu/' . $designation_id . '.json'))
                    @php
                        $menu_contents = json_decode(
                            Storage::disk('local')->get('menu/' . $designation_id . '.json'),
                            JSON_FORCE_OBJECT,
                        );
                    @endphp

                    @foreach ($menu_contents as $mymenu)
                        @if (empty($mymenu['child_menu']))
                            <li class="nav-item">
                                <a href="{{ $mymenu['url_type'] == '2' ? route($mymenu['link_url']) : url($mymenu['link_url']) }}"
                                    class="nav-link" title="{{ $mymenu['menu_name'] }}">
                                    <i class="nav-icon {{ $mymenu['icon'] }}"></i>
                                    <span>{{ $mymenu['menu_name'] }}</span>
                                </a>


                            </li>
                        @else
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon {{ $mymenu['icon'] }}"></i>
                                    <p>
                                        {{ $mymenu['menu_name'] }}
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @foreach ($mymenu['child_menu'] as $mysubmenu)
                                        <li class="nav-item">
                                            <a href="{{ $mysubmenu['url_type'] == 2 ? route($mysubmenu['link_url']) : url($mysubmenu['link_url']) }}"
                                                class="nav-link" title="{{ $mysubmenu['menu_name'] }}">
                                                <i class="far fa-circle nav-icon {{ $mysubmenu['icon'] }}"></i>
                                                <span>{{ $mysubmenu['menu_name'] }}</span>
                                            </a>

                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach

                @endif
            </ul>
        </nav>
    </div>
</aside>
