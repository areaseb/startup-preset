@php
    $unread = \App\Classes\Notification::countUnread();
    $all = \App\Classes\Notification::count();
@endphp
<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-bell"></i>
        @if($unread)
            <span class="badge badge-warning navbar-badge">{{$unread}}</span>
        @else
            <span class="badge navbar-badge">0</span>
        @endif

    </a>
    @if($all)
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <span class="dropdown-item dropdown-header">{{$all}} Notifiche</span>
            @foreach(\App\Classes\Notification::unread()->latest()->get() as $notification)
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    {{$notification->name}}
                    <span class="float-right text-muted text-sm">{{$notification->created_at->diffForHumans()}}</span>
                </a>
            @endforeach
            <div class="dropdown-divider"></div>
            <a href="{{url('notifications')}}" class="dropdown-item dropdown-footer">Vedi tutte le notifiche</a>
        </div>
    @endif
</li>
