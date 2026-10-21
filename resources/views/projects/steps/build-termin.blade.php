@php
    $offerItems = $project->rab?->items ?? collect();

    // Semua item sekali bayar -> termin bertahap (DP, pelunasan, dst.); selain itu -> langganan
    $isOneTimeOffer = $offerItems->isNotEmpty()
        && $offerItems->every(fn ($item) => $item->billing_period === 'one_time');
@endphp

@if($isOneTimeOffer)
    @include('projects.steps.build_termin_onetime')
@else
    @include('projects.steps.build-termin-subscription')
@endif