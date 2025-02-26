@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="shadow-lg" style="border-radius: 40px;">
                <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0 0;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-{{ isset($refrain) ? 'edit' : 'plus' }} me-1"></i>
                        {{ isset($refrain) ? 'Editează Realizare' : 'Adaugă Realizare' }}
                    </span>
                </div>

                @include ('errors.errors')

                <div class="card-body py-3 px-4 border border-secondary" style="border-radius: 0 0 40px 40px;">
                    <form class="needs-validation" novalidate method="POST" action="{{ isset($refrain) ? route('refrains.update', $refrain->id) : route('refrains.store') }}">
                        @csrf
                        @if(isset($refrain))
                            @method('PUT')
                        @endif

                        @include ('refrains.form', [
                            'refrain' => $refrain ?? null,
                            'buttonText' => isset($refrain) ? 'Salvează modificările' : 'Adaugă Realizare',
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
