@php
    $user = auth()->user();
    $canAccess = $user && ($user->isAdmin() || \App\Helpers\MenuHelper::canAccess($item['permission'] ?? ''));
@endphp

@if($canAccess && ($item['visible'] ?? true))
    <li class="nav-item">
        <a href="{{ route($item['route']) }}" class="nav-link {{ request()->routeIs($item['route'] . '*') ? 'active' : '' }}">
            @if(isset($item['icon']))
                <i class="fas fa-{{ $item['icon'] }}"></i>
            @endif
            <span>{{ $item['label'] }}</span>
        </a>
        
        @if(isset($item['submenu']) && !empty($item['submenu']))
            <ul class="nav nav-treeview">
                @foreach($item['submenu'] as $subitem)
                    <li class="nav-item">
                        <a href="{{ route($subitem['route']) }}" class="nav-link {{ request()->routeIs($subitem['route'] . '*') ? 'active' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <span>{{ $subitem['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </li>
@endif
