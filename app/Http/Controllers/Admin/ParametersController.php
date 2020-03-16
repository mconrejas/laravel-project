<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Http\Requests;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\Parameter;
use Illuminate\Http\Request;

class ParametersController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perparameter = 25;

        if (!empty($keyword)) {
            $parameters = Parameter::where('name', 'LIKE', "%$keyword%")
                ->orWhere('description', 'LIKE', "%$keyword%")
                ->orWhere('value', 'LIKE', "%$keyword%")
                ->latest()->paginate($perparameter);
        } else {
            $parameters = Parameter::latest()->paginate($perparameter);
        }

        return view('admin.parameters.index', compact('parameters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.parameters.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $this->validate($request, [
			'name' => 'required|string|unique:parameters',
			'value' => 'required'
		]);

        parameter()->set($request->name, $request->value, $request->description?:'' );

        toast('Parameter added!', 'success', 'top-right');

        return redirect('admin/parameters');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $parameter = Parameter::findOrFail($id);

        return view('admin.parameters.show', compact('parameter'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $parameter = Parameter::findOrFail($id);

        return view('admin.parameters.edit', compact('parameter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
			'name' => 'required|string',
			'value' => 'required',
            'description' => 'string|max:500'
		]);
        $requestData = $request->all();
        
        $parameter = Parameter::findOrFail($id);
        cache()->forget($parameter->name);

        $parameter->update($requestData);
        toast('Parameter updated!', 'success', 'top-right');

        return redirect('admin/parameters');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        $parameter = Parameter::find($id);

        cache()->forget($parameter->name);

        $parameter->destroy();

        toast('Parameter deleted!', 'success', 'top-right');

        return redirect('admin/parameters');
    }
}
