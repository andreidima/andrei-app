@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="shadow-lg" style="border-radius: 40px;">
                <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0 0;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-layer-group me-1"></i>Add feature
                    </span>
                </div>

                @include ('errors.errors')

                <div class="card-body py-2 border border-secondary" style="border-radius: 0 0 40px 40px;">
                    <form class="needs-validation" novalidate method="POST" action="/apps/features">
                        @include ('apps.features.form', [
                            'feature' => new App\Models\Apps\Feature(['is_active' => true]),
                            'buttonText' => 'Add feature'
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
