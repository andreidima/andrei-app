@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-bars me-1"></i>Aplicații
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form justify-content-center">
                        <div class="col-lg-6">
                            <input type="text" class="form-control rounded-3" id="searchNume" name="searchNume" placeholder="Nume" value="{{ $searchNume }}">
                        </div>
                    </div>
                    <div class="row custom-search-form justify-content-center">
                        <div class="col-lg-4">
                            <button class="btn btn-sm w-100 btn-primary text-white border border-dark rounded-3" type="submit">
                                <i class="fas fa-search text-white me-1"></i>Caută
                            </button>
                        </div>
                        <div class="col-lg-4">
                            <a class="btn btn-sm w-100 btn-secondary text-white border border-dark rounded-3" href="{{ url()->current() }}" role="button">
                                <i class="far fa-trash-alt text-white me-1"></i>Resetează căutarea
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-3 text-end">
                <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ url()->current() }}/adauga" role="button">
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă aplicație
                </a>
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors.errors')

            <div class="table-responsive rounded">
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded">
                        {{-- <tr class="thead-danger" style="padding:2rem">
                            <th class="text-white culoare2"></th>
                            <th class="text-white culoare2"></th>
                            <th colspan="3" class="text-white culoare2 text-center" style="border-left: 1px white solid; border-right: 1px white solid;">Url</th>
                            <th colspan="3" class="text-white culoare2 text-center" style="border-right: 1px white solid;">Versions</th>
                            <th class="text-white culoare2"></th>
                            <th class="text-white culoare2"></th>
                            <th class="text-white culoare2"></th>
                        </tr> --}}
                        <tr class="thead-danger" style="padding:2rem">
                            <th class="text-white culoare2">#</th>
                            <th class="text-white culoare2">Aplication</th>
                            {{-- <th class="text-white culoare2" style="border-left: 1px white solid;">Local</th>
                            <th class="text-white culoare2">Online</th>
                            <th class="text-white culoare2" style="border-right: 1px white solid;">Github</th>
                            <th class="text-white culoare2">Php</th>
                            <th class="text-white culoare2">Laravel</th>
                            <th class="text-white culoare2" style="border-right: 1px white solid;">Vue</th> --}}
                            <th class="text-white culoare2">Urls</th>
                            <th class="text-white culoare2">Urls info</th>
                            <th class="text-white culoare2">Software tools</th>
                            <th class="text-white culoare2 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($aplicatii as $aplicatie)
                            <tr>
                                <td align="">
                                    {{ ($aplicatii ->currentpage()-1) * $aplicatii ->perpage() + $loop->index + 1 }}
                                </td>
                                <td class="">
                                    {{ $aplicatie->nume }}
                                </td>
                                {{-- <td class="">
                                    @if ( $aplicatie->local_url )
                                        <a href="{{ $aplicatie->local_url }}" target="_blank" style="text-decoration: none">
                                            Local</a>
                                    @endif
                                </td>
                                <td class="">
                                    @if ( $aplicatie->online_url )
                                        <a href="{{ $aplicatie->online_url }}" target="_blank" style="text-decoration: none">
                                            Online</a>
                                    @endif
                                </td>
                                <td class="">
                                    @if ( $aplicatie->github_url )
                                        <a href="{{ $aplicatie->github_url }}" target="_blank" style="text-decoration: none">
                                            Github</a>
                                    @endif
                                </td>
                                <td>
                                    {{ $aplicatie->php_version }}
                                </td>
                                <td>
                                    {{ $aplicatie->laravel_version }}
                                </td>
                                <td>
                                    {{ $aplicatie->vue_version }}
                                </td> --}}
                                <td>
                                    @foreach (explode(',', $aplicatie->urls) as $url)
                                        <a href="{{ $url }}" target="_blank" style="text-decoration: none">
                                            {{ array_key_exists(1, $url = explode('//', $url)) ? $url[1] : null }}
                                        </a>
                                        <br>
                                    @endforeach
                                </td>
                                <td>
                                    {!! nl2br( $aplicatie->urls_info) !!}
                                </td>
                                <td>
                                    @php
                                        $software_tools = array_map('trim', explode(',', $aplicatie->software_tools)); // Trim and explode on the same time
                                        asort($software_tools);
                                    @endphp
                                    @foreach ($software_tools as $tool)
                                    {{ $tool }}
                                    <br>
                                    @endforeach
                                </td>

                                <td>
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ $aplicatie->path() }}" class="flex me-1">
                                            <span class="badge bg-success">Vizualizează</span>
                                        </a>
                                        <a href="{{ $aplicatie->path() }}/modifica" class="flex me-1">
                                            <span class="badge bg-primary">Modifică</span>
                                        </a>
                                        <div style="flex" class="">
                                            <a
                                                href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#stergeAplicatie{{ $aplicatie->id }}"
                                                title="Șterge Aplicație"
                                                >
                                                <span class="badge bg-danger">Șterge</span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- <div>Nu s-au gasit rezervări în baza de date. Încearcă alte date de căutare</div> --}}
                        @endforelse
                        </tbody>
                </table>
            </div>

                <nav>
                    <ul class="pagination justify-content-center">
                        {{$aplicatii->appends(Request::except('page'))->links()}}
                    </ul>
                </nav>
        </div>
    </div>

    {{-- Modalele pentru stergere aplicatie --}}
    @foreach ($aplicatii as $aplicatie)
        <div class="modal fade text-dark" id="stergeAplicatie{{ $aplicatie->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Aplicație: <b>{{ $aplicatie->nume }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur ca vrei să ștergi Aplicația?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="{{ $aplicatie->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge Aplicația
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection
