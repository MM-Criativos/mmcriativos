@props(['url'])
@php
    $appName = config('app.name', 'MM Criativos');
    $logoUrl = asset('assets/images/mmsite.png');
@endphp
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            <img src="{{ $logoUrl }}" class="logo" alt="{{ $appName }}" style="height: 48px;">
        </a>
    </td>
</tr>
