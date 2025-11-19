<style>
    .nav-treeview>.nav-item>.nav-link {
        padding-left: 2rem;
    }

    .nav-treeview .nav-icon {
        margin-left: 0.5rem;
    }
</style>
@php
    // Returns the request path without leading slash
    function currentPath()
    {
        return trim(request()->path(), '/');
    }

    // Check if a link is active (supports query strings too)
    function isActiveLink($url)
    {
        $clean = trim(parse_url($url, PHP_URL_PATH), '/');
        return request()->is($clean) || request()->is($clean . '/*') ? 'active' : '';
    }

    // Check if ANY child is active, to expand parent
    function isMenuOpen($children)
    {
        foreach ($children as $child) {
            $childUrl = $child['url_type'] == 2 ? route($child['link_url']) : url($child['link_url']);
            $clean = trim(parse_url($childUrl, PHP_URL_PATH), '/');

            if (request()->is($clean) || request()->is($clean . '/*')) {
                return 'menu-open';
            }
        }
        return '';
    }
@endphp


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
        <!-- <div class="form-inline mt-3">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                    aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div> -->

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                @php
                    $designation_id = Auth::user()->designation_id;
                @endphp
                <li class="nav-item">
                    <a href="{{ url('/backendlogin') }}" class="nav-link ">
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
                            @php
                                $url = $mymenu['url_type'] == 2 ? route($mymenu['link_url']) : url($mymenu['link_url']);
                            @endphp

                            <li class="nav-item">
                                <a href="{{ $url }}" class="nav-link {{ isActiveLink($url) }}">
                                    <i class="nav-icon {{ $mymenu['icon'] }}"></i>
                                    <p>{{ $mymenu['menu_name'] }}</p>
                                </a>
                            </li>

                        @else
                            @php
                                $menuOpen = isMenuOpen($mymenu['child_menu']);
                                $parentActive = $menuOpen ? 'active' : '';
                            @endphp

                            <li class="nav-item has-treeview {{ $menuOpen }}">
                                <a href="#" class="nav-link {{ $parentActive }}">
                                    <i class="nav-icon {{ $mymenu['icon'] }}"></i>
                                    <p>
                                        {{ $mymenu['menu_name'] }}
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>

                                <ul class="nav nav-treeview" style="{{ $menuOpen ? 'display:block;' : 'display:none;' }}">
                                    @foreach ($mymenu['child_menu'] as $mysubmenu)
                                        @php
                                            $childUrl = $mysubmenu['url_type'] == 2
                                                ? route($mysubmenu['link_url'])
                                                : url($mysubmenu['link_url']);

                                            $activeChild = isActiveLink($childUrl);
                                        @endphp

                                        <li class="nav-item">
                                            <a href="{{ $childUrl }}" class="nav-link {{ $activeChild }}">
                                                <i class="far fa-circle nav-icon {{ $mysubmenu['icon'] }}"></i>
                                                <p>{{ $mysubmenu['menu_name'] }}</p>
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