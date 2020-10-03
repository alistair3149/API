{{-- Page Title --}}
@section('P__title')
    @parent
    @hasSection('title')
        @yield('title') - Star Citizen Wiki Api
    @else
        Star Citizen Wiki Api
    @endif
@endsection


{{-- Head --}}
@section('head__content')
    @parent
    <link rel="stylesheet" href="{{ URL::asset('/css/app.css') }}">
@endsection


{{-- Body --}}
@section('sidebar--class', 'd-none d-md-flex flex-column')


{{-- Sidebar Content --}}
@section('sidebar__content')
    @include('user.menu.main')
@endsection

@section('sidebar__pre')
    @parent
    <a href="@if(Auth::check() && Auth::user()->isAdmin())
    {{ route('web.user.dashboard') }}
    @else
    {{ route('web.user.account.index') }}
    @endif">
        <img src="{{ asset('media/images/Star_Citizen_Wiki_Logo_White.png') }}"
             class="d-block mx-auto my-5 img-fluid"
             style="max-width: 100px;">
    </a>
@endsection

@section('sidebar__after')
    @parent
    @include('components.sidebar_imprint')
@endsection


{{-- Main Content --}}
@section('topNav--class', 'bg-blue-grey')

@section('topNav__content')
    @include('api.menu.login_logout')
    <div class="d-sm-block d-md-none">
        @include('user.menu.main')
    </div>
@endsection

@section('topNav__title', config('app.name'))

@section('body__after')
    @parent
    <script src="{{ mix('/js/app.js') }}"></script>
@endsection