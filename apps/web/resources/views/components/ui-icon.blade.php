@props(['name', 'size' => 16])

<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}>
    @switch($name)
        @case('check')
            <circle cx="12" cy="12" r="9"></circle><path d="m8 12 2.5 2.5L16.5 8.5"></path>
            @break
        @case('alert')
            <path d="M10.3 3.7 2.2 18a2 2 0 0 0 1.7 3h16.2a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path>
            @break
        @case('edit')
            <path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"></path>
            @break
        @case('tag')
            <path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0l-7.2-7.2A2 2 0 0 1 3 12V5a2 2 0 0 1 2-2h7a2 2 0 0 1 1.4.6l7.2 7.2a2 2 0 0 1 0 2.6Z"></path><circle cx="7.5" cy="7.5" r="1"></circle>
            @break
        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.9"></path><path d="M16 3.1a4 4 0 0 1 0 7.8"></path>
            @break
        @case('truck')
            <path d="M3 5h11v11H3z"></path><path d="M14 9h4l3 3v4h-7"></path><circle cx="6.5" cy="18" r="2"></circle><circle cx="17.5" cy="18" r="2"></circle>
            @break
        @case('note')
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path><path d="M14 2v6h6"></path><path d="M8 13h8M8 17h6"></path>
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>
            @break
        @case('bell')
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            @break
        @case('lock')
            <rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
            @break
        @case('clipboard')
            <rect x="5" y="4" width="14" height="17" rx="2"></rect><path d="M9 4V2h6v2M9 10h6M9 14h6"></path>
            @break
        @case('settings')
            <circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.1 2.1-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5v.2h-3v-.2a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.9.3l-.1.1-2.1-2.1.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H5v-3h.2a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1 2.1-2.1.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.5V4h3v.2a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1 2.1 2.1-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.5 1h.2v3h-.2a1.7 1.7 0 0 0-1.4 1.6Z"></path>
            @break
        @case('globe')
            <circle cx="12" cy="12" r="9"></circle><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"></path>
            @break
        @case('home')
            <path d="m3 10 9-7 9 7"></path><path d="M5 9v11h14V9M9 20v-6h6v6"></path>
            @break
        @case('logout')
            <path d="M10 17l5-5-5-5"></path><path d="M15 12H3"></path><path d="M21 19V5a2 2 0 0 0-2-2h-6"></path>
            @break
        @case('login')
            <path d="M14 8l4 4-4 4"></path><path d="M18 12H4"></path><path d="M4 5v14a2 2 0 0 0 2 2h8"></path>
            @break
        @case('flask')
            <path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"></path><path d="M8 15h8"></path>
            @break
        @case('key')
            <circle cx="8" cy="15" r="4"></circle><path d="m11 12 8-8M16 7l2 2M14 9l2 2"></path>
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14"></path>
            @break
        @case('mobile')
            <rect x="7" y="2" width="10" height="20" rx="2"></rect><path d="M11 18h2"></path>
            @break
        @case('package')
            <path d="m4 7 8-4 8 4v10l-8 4-8-4Z"></path><path d="m4 7 8 4 8-4M12 11v10"></path>
            @break
        @case('leaf')
            <path d="M20 4C11 4 5 8 5 15c0 3 2 5 5 5 7 0 10-7 10-16Z"></path><path d="M4 20c4-4 7-6 12-9"></path>
            @break
        @case('wheat')
            <path d="M12 22V10M8 22h8M12 10 8 6M12 10l4-4M8 6l2-3M16 6l-2-3M7 11l-3-2M17 11l3-2M7 15l-3-2M17 15l3-2"></path>
            @break
        @default
            <circle cx="12" cy="12" r="9"></circle>
    @endswitch
</svg>
