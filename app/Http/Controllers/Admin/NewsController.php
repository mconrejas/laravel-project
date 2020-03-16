<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\News;
use Buzzex\Repositories\NewsRepository;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * @var Buzzex\Repositories\NewsRepository
     */
    protected $newsRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $news = $this->newsRepository->getAll();

        return view('admin.news.index', compact('news'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        return view('admin.news.create');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $id)
    {
        $news =  News::withTrashed()->findOrFail($id);

        return view('admin.news.show', compact('news'));
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
        $request->validate([
            'link' => 'required|url',
            'text' => 'required|min:3',
            'class' => 'sometimes',
            'target' => 'sometimes|string',
        ]);
        
        News::create(array_merge($request->all(), ['created_by' => auth()->user()->id ]));

        toast('News created!', 'success', 'top-right');

        return redirect('admin/news');
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
        $request->validate([
            'link' => 'required|url',
            'text' => 'required|min:3',
            'class' => 'sometimes|nullable|string|max:500',
            'target' => 'sometimes|nullable|string|max:500',
        ]);
        
        $news = News::withTrashed()->findOrFail($id);
        $news->update(array_merge($request->all(), ['updated_by' => auth()->user()->id ]));

        toast('News updated!', 'success', 'top-right');

        return redirect('admin/news');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit(Request $request, $id)
    {
        $news = News::withTrashed()->findOrFail($id);

        return view('admin.news.edit', compact('news'));
    }

    /**
     *
     *
     */
    public function remove(Request $request, $id)
    {
        News::find($id)->delete();

        return response()->json(['flash_message' => 'News removed!'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, $id)
    {
        News::withTrashed()->find($id)->restore();

        return response()->json(['flash_message' => 'News restored!' ], 200);
    }

    /**
     * Search from storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $search = $this->newsRepository->search($request);

        $data = array();

        if ($search) {
            foreach ($search['data'] as $key => $new) {
                $data[]  = array(
                    'id' => $new->id,
                    'link' => $new->link,
                    'text' => $new->text,
                    'active' => !$new->trashed()
                );
            }
        }

        return response()->json([
                'last_page' => $search['counts'] ?? 1,
                'data' => $data
            ], 200);
    }
}
