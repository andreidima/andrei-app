@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="shadow-lg" style="border-radius: 40px;">
        <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0 0;">
            <span class="badge text-light fs-5">
                <i class="fa-solid fa-diagram-project me-1"></i>Adaugare proiect articol
            </span>
        </div>
        @include ('errors.errors')
        <div class="card-body py-3 border border-secondary" style="border-radius: 0 0 40px 40px;">
            <form method="POST" action="{{ route('validsoftware-blog.projects.store') }}">
                @include ('articole-validsoftware.projects.form', ['buttonText' => 'Adauga proiect'])
            </form>
        </div>
    </div>
</div>
@endsection
