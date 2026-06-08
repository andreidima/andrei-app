@extends ('layouts.app')

@section('content')
@php
    $weekdays = ['Lun', 'Mar', 'Mie', 'Joi', 'Vin', 'Sam', 'Dum'];
@endphp

<style>
    .appointments-page {
        max-width: 1440px;
        margin: 0 auto;
    }

    .appointments-header,
    .appointments-panel,
    .calendar-panel {
        border: 1px solid rgba(20, 24, 80, 0.14);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 0.65rem 1.4rem rgba(20, 24, 80, 0.08);
    }

    .appointments-header {
        background: linear-gradient(135deg, #ffffff 0%, #f7fbff 100%);
    }

    .metric {
        min-height: 6.5rem;
        border-radius: 8px;
        background: #f8fafc;
        border: 1px solid rgba(20, 24, 80, 0.1);
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.5rem;
    }

    .calendar-weekday {
        color: #4b5563;
        font-weight: 700;
        text-align: center;
        font-size: 0.85rem;
    }

    .calendar-day {
        min-height: 10rem;
        border: 1px solid rgba(20, 24, 80, 0.12);
        border-radius: 8px;
        background: #fff;
        padding: 0.55rem;
        overflow: hidden;
    }

    .calendar-day.is-muted {
        background: #f6f7fb;
        color: #7c8493;
    }

    .calendar-day.is-today {
        border-color: #198754;
        box-shadow: inset 0 0 0 1px #198754;
    }

    .calendar-date {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.25rem;
        margin-bottom: 0.45rem;
    }

    .appointment-chip {
        display: block;
        border-left: 4px solid #6a6ba0;
        border-radius: 6px;
        background: #eef2ff;
        color: #141850;
        padding: 0.45rem 0.5rem;
        margin-bottom: 0.45rem;
        text-decoration: none;
        line-height: 1.25;
    }

    .appointment-chip:hover {
        background: #e0e7ff;
        color: #141850;
    }

    .appointment-chip.priority-high {
        border-left-color: #198754;
        background: #eefbf4;
    }

    .appointment-list-item {
        border: 1px solid rgba(20, 24, 80, 0.12);
        border-radius: 8px;
        padding: 0.85rem;
        background: #fff;
    }

    .appointment-list-item.next {
        border-color: #198754;
        background: #f3fbf6;
    }

    @media (max-width: 991.98px) {
        .calendar-grid {
            grid-template-columns: 1fr;
        }

        .calendar-weekday {
            display: none;
        }

        .calendar-day {
            min-height: auto;
        }
    }
</style>

<div class="appointments-page px-3">
    <div class="appointments-header p-3 p-lg-4 mb-3">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <span class="badge culoare2 fs-5 mb-2">
                    <i class="fa-solid fa-calendar-days me-1"></i>Calendar vizionari
                </span>
                <h1 class="h3 mb-1">Urmatoarele apartamente programate</h1>
                <div class="text-muted">Vizualizare rapida pentru intalniri, prioritati si urmatorul pas.</div>
            </div>
            <div class="d-flex flex-wrap align-items-start gap-2">
                <a class="btn btn-outline-secondary rounded-3" href="{{ route('apartamente.index') }}">
                    <i class="fa-solid fa-table-list me-1"></i>Lista
                </a>
                <a class="btn btn-success text-white rounded-3" href="{{ route('apartamente.create') }}">
                    <i class="fa-solid fa-plus me-1"></i>Adauga
                </a>
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <div class="metric p-3">
                    <div class="text-muted small text-uppercase fw-bold">Urmatoarea vizionare</div>
                    @if ($nextAppointment)
                        <div class="h5 mb-1">{{ $nextAppointment->vizionare_at->format('d.m.Y H:i') }}</div>
                        <a href="{{ $nextAppointment->path() }}" class="fw-bold text-decoration-none">{{ $nextAppointment->adresa }}</a>
                        <div class="small text-muted">{{ $nextAppointment->localitate }}</div>
                    @else
                        <div class="h5 mb-1">Nimic programat</div>
                        <div class="small text-muted">Adauga o data de vizionare pe un apartament.</div>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric p-3">
                    <div class="text-muted small text-uppercase fw-bold">Saptamana curenta</div>
                    <div class="display-6 fw-bold">{{ $appointmentsThisWeek }}</div>
                    <div class="small text-muted">vizionari ramase pana duminica</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric p-3">
                    <div class="text-muted small text-uppercase fw-bold">Urmatoarele 12</div>
                    <div class="display-6 fw-bold">{{ $upcomingAppointments->count() }}</div>
                    <div class="small text-muted">programari active in lista rapida</div>
                </div>
            </div>
        </div>
    </div>

    @include ('errors.errors')

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="calendar-panel p-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Luna selectata</div>
                        <h2 class="h4 mb-0">{{ $month->translatedFormat('F Y') }}</h2>
                    </div>
                    <div class="btn-group" role="group" aria-label="Navigare calendar">
                        <a class="btn btn-outline-secondary rounded-start-3" href="{{ route('apartamente.calendar', ['month' => $previousMonth]) }}" title="Luna precedenta">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                        <a class="btn btn-outline-secondary" href="{{ route('apartamente.calendar') }}">Azi</a>
                        <a class="btn btn-outline-secondary rounded-end-3" href="{{ route('apartamente.calendar', ['month' => $nextMonth]) }}" title="Luna urmatoare">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>
                </div>

                <div class="calendar-grid mb-2">
                    @foreach ($weekdays as $weekday)
                        <div class="calendar-weekday">{{ $weekday }}</div>
                    @endforeach
                </div>

                <div class="calendar-grid">
                    @foreach ($calendarDays as $day)
                        <div class="calendar-day {{ $day['in_month'] ? '' : 'is-muted' }} {{ $day['is_today'] ? 'is-today' : '' }}">
                            <div class="calendar-date">
                                <span class="fw-bold">{{ $day['date']->format('d') }}</span>
                                @if ($day['appointments']->count())
                                    <span class="badge bg-success">{{ $day['appointments']->count() }}</span>
                                @endif
                            </div>

                            @forelse ($day['appointments'] as $apartament)
                                <a
                                    class="appointment-chip {{ ($apartament->prioritate ?? 0) >= 4 ? 'priority-high' : '' }}"
                                    href="{{ $apartament->path() }}"
                                    title="{{ $apartament->adresa }}">
                                    <span class="fw-bold">{{ $apartament->vizionare_at->format('H:i') }}</span>
                                    <span>{{ $apartament->adresa }}</span>
                                    <span class="d-block small text-muted">
                                        {{ $apartament->localitate ?: 'Fara localitate' }}
                                        @if ($apartament->scor)
                                            / {{ $apartament->scor }}/10
                                        @endif
                                    </span>
                                </a>
                            @empty
                                <div class="small text-muted">Liber</div>
                            @endforelse
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="appointments-panel p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Agenda rapida</div>
                        <h2 class="h5 mb-0">Programari urmatoare</h2>
                    </div>
                    <i class="fa-solid fa-route text-success fs-4"></i>
                </div>

                <div class="d-flex flex-column gap-2">
                    @forelse ($upcomingAppointments as $apartament)
                        <div class="appointment-list-item {{ $loop->first ? 'next' : '' }}">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <div class="fw-bold">{{ $apartament->vizionare_at->format('D, d.m H:i') }}</div>
                                    <a href="{{ $apartament->path() }}" class="h6 d-block mb-1 text-decoration-none">{{ $apartament->adresa }}</a>
                                    <div class="small text-muted">{{ $apartament->localitate ?: 'Fara localitate' }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $apartament->status_badge }}">{{ $apartament->status_label }}</span>
                                    @if ($apartament->prioritate)
                                        <div class="small text-muted mt-1">P{{ $apartament->prioritate }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="small mt-2">
                                @if ($apartament->pret)
                                    <span class="me-2"><i class="fa-solid fa-euro-sign me-1"></i>{{ number_format($apartament->pret, 0, ',', '.') }}</span>
                                @endif
                                @if ($apartament->suprafata_mp)
                                    <span class="me-2"><i class="fa-solid fa-ruler-combined me-1"></i>{{ $apartament->suprafata_mp }} mp</span>
                                @endif
                                @if ($apartament->agent_contact)
                                    <span><i class="fa-solid fa-address-book me-1"></i>{{ $apartament->agent_contact }}</span>
                                @endif
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <a class="btn btn-sm btn-outline-primary rounded-3" href="{{ $apartament->path() }}">
                                    <i class="fa-solid fa-eye me-1"></i>Detalii
                                </a>
                                <a class="btn btn-sm btn-outline-secondary rounded-3" href="{{ route('apartamente.edit', $apartament) }}">
                                    <i class="fa-solid fa-pen me-1"></i>Modifica
                                </a>
                                @if ($apartament->link_anunt)
                                    <a class="btn btn-sm btn-outline-success rounded-3" href="{{ $apartament->link_anunt }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fa-solid fa-up-right-from-square me-1"></i>Anunt
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fa-regular fa-calendar-xmark fs-1 d-block mb-2"></i>
                            Nu exista vizionari viitoare.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
