@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="shadow-lg" style="border-radius: 40px;">
                <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0 0;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-ban me-1"></i> Refrain details
                    </span>
                </div>

                <div class="card-body border border-secondary p-4" style="border-radius: 0 0 40px 40px;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Nume:</strong> {{ $refrain->name ?? '-' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Refrained Since:</strong>
                            {{ $refrain->since ? \Carbon\Carbon::parse($refrain->since)->format('d.m.Y') : '-' }}
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>Observații:</strong>
                            {!! nl2br(e($refrain->observations)) !!}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Creat la:</strong> {{ $refrain->created_at?->format('d.m.Y H:i') }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Ultima modificare:</strong> {{ $refrain->updated_at?->format('d.m.Y H:i') }}
                        </div>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        <a href="{{ route('refrains.edit', $refrain->id) }}" class="btn btn-primary text-white me-3 rounded-3">
                            <i class="fa-solid fa-edit me-1"></i> Modifică
                        </a>
                        <a class="btn btn-secondary rounded-3" href="{{ Session::get('returnUrl', route('refrains.index')) }}">
                            <i class="fa-solid fa-arrow-left me-1"></i> Înapoi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
