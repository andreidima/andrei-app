@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="shadow-lg" style="border-radius: 40px 40px 40px 40px;">
                <div class="culoare2 border border-secondary p-2" style="border-radius: 40px 40px 0px 0px;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-bars me-1"></i>Aplicații / {{ $aplicatie->nume }}
                    </span>
                </div>

                <div class="card-body py-2 border border-secondary"
                    style="border-radius: 0px 0px 40px 40px;"
                >

            @include ('errors.errors')

                    <div class="table-responsive col-md-12 mx-auto">
                        <table class="table table-striped table-hover"
                        >
                            <tr>
                                <td class="pe-4">
                                    Aplicatie
                                </td>
                                <td>
                                    {{ $aplicatie->nume }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Local url
                                </td>
                                <td>
                                    {{ $aplicatie->local_url }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Online url
                                </td>
                                <td>
                                    {{ $aplicatie->online_url }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Github url
                                </td>
                                <td>
                                    {{ $aplicatie->github_url }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Php version
                                </td>
                                <td>
                                    {{ $aplicatie->php_version }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Laravel version
                                </td>
                                <td>
                                    {{ $aplicatie->laravel_version }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Vue version
                                </td>
                                <td>
                                    {{ $aplicatie->vue_version }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="form-row mb-2 px-2">
                        <div class="col-lg-12 d-flex justify-content-center">
                            <a class="btn btn-secondary text-white rounded-3" href="{{ Session::get('aplicatieReturnUrl') }}">Înapoi</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
