@extends('layouts.app')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0 0;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-trophy me-1"></i>
                Achievement Milestones
            </span>
        </div>
    </div>

    {{-- Card Body --}}
    <div class="card-body px-0 py-3">
        <div class="row">
            <div class="col-md-6 mb-3">
            </div>
        </div>




        <div class="row">
            @foreach ($achievements as $key => $achievement)
                <div class="col-md-6 mb-4 text-center">
                    <span class="badge p-1 mb-1 culoare1 fs-5">
                        {{ $key }} - {{ $days = $achievement['since']->diffInDays(Carbon::now()) }} days
                        <br>
                        +1 day in:
                        {{ $achievement['additionalTimeNeededForNextDay'] ?? '' }}
                    </span>
                    @foreach($milestones as $milestone)
                        @php
                            // Calculate progress percentage (max 100%)
                            $progress = min(100, ($days / $milestone['days']) * 100);
                        @endphp

                        <!-- Progress Bar -->
                        <div class="progress mb-1" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                {{ $milestone['name'] }} - {{ $milestone['days'] }} day{{ $milestone['days'] > 1 ? 's' : '' }} - {{ round($progress) }}%
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
