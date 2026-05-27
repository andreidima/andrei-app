@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="shadow-lg" style="border-radius: 40px;">
        <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0 0;">
            <span class="badge text-light fs-5">
                <i class="fa-solid fa-newspaper me-1"></i>Adaugare articol ValidSoftware
            </span>
        </div>
        @include ('errors.errors')
        <div class="card-body py-3 border border-secondary" style="border-radius: 0 0 40px 40px;">
            <form method="POST" action="{{ route('validsoftware-blog.articles.store') }}">
                @include ('articole-validsoftware.articles.form', ['buttonText' => 'Adauga articol'])
            </form>
        </div>
    </div>
</div>
@endsection
