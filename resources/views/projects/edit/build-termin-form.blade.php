@php
    $isOneTimeTermin = $project->buildTermins->first()?->billing_period === 'one_time';
@endphp

@if($isOneTimeTermin)
    @include('projects.edit.build-termin-form-onetime')
@else
    @include('projects.edit.build-termin-form-subscription')
@endif