<nav class="main-header navbar navbar-expand navbar-white navbar-light">

<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
</ul>

<ul class="navbar-nav ml-auto">

    @include('layouts.elements.notifications')

    <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
            {{$user->fullname}}
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="#" class="dropdown-item" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Logout
                <form id="logout-form" action="{{ url('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </a>
            @if($user->can('accounting.read') || $user->can('newsletter.read'))
                <a href="{{url('settings')}}" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a>
                <a href="#" class="dropdown-item emptyCache"><i class="fas fa-redo-alt"></i> Svuta Cache</a>
            @endif


         </div>
    </li>
  </ul>
</nav>
@push('scripts')
    <script>

        $('a.emptyCache').on('click', function(e){
            e.preventDefault();

            $.post(baseURL+'api/clear-cache', {_token: "{{csrf_token()}}"}).done(function(response){
                new Noty({
                    text: "Svuotamento della cache eseguito",
                    type: 'success',
                    theme: 'bootstrap-v4',
                    timeout: 2500,
                    layout: 'topRight'
                }).show();
            });
        });

    </script>
@endpush
