@php
    $lines = \App\Models\Line::orderBy('id')->pluck('text')->filter()->values();
@endphp
@if ($lines->isNotEmpty())
    <section class="sliding-text @@extraClassName">
        <div class="sliding-text__wrap">
            <ul class="sliding-text__list list-unstyled">
                @foreach ($lines as $t)
                    <li>&#8226 {{ $t }}</li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
