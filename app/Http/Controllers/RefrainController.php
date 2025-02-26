<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RefrainRequest;

use App\Models\Refrain;
use Carbon\Carbon;

class RefrainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('returnUrl');

        $searchName = trim($request->searchName); // Name search field

        $refrains = Refrain::
            when($searchName, function ($query, $searchName) {
                return $query->where('name', 'LIKE', "%{$searchName}%");
            })
            ->latest()
            ->simplePaginate(50);

        return view('refrains.index', compact('refrains', 'searchName'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->session()->get('returnUrl') ?: $request->session()->put('returnUrl', url()->previous());

        return view('refrains.save');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RefrainRequest $request)
    {
        $data = $request->validated();

        $refrain = Refrain::create($data);

        return redirect($request->session()->get('returnUrl', route('refrains.index')))->with('success', 'The refrain <strong>' . e($refrain->name) . '</strong> was added with success!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Refrain  $refrain
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Refrain $refrain)
    {
        $request->session()->get('returnUrl') ?: $request->session()->put('returnUrl', url()->previous());

        return view('refrains.show', compact('refrain'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Refrain  $refrain
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Refrain $refrain)
    {
        $request->session()->get('returnUrl') ?: $request->session()->put('returnUrl', url()->previous());

        return view('refrains.save', compact('refrain'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Refrain  $refrain
     * @return \Illuminate\Http\Response
     */
    public function update(RefrainRequest $request, Refrain $refrain)
    {
        $data = $request->validated();

        $refrain->update($data);

        return redirect($request->session()->get('returnUrl', route('refrains.index')))->with('status', 'The refrain <strong>' . e($refrain->name) . '</strong> was modified with success!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Refrain  $refrain
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Refrain $refrain)
    {
        $refrain->delete();

        return back()->with('status', 'The refrain <strong>' . e($refrain->name) . '</strong> was deleted with success!');
    }
}
