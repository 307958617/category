<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('categories.index');
    }

    public function getCategories()
    {
        $categories = Category::get()->toTree();
        return $categories;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       Category::create(['name' => $request->input('name')]);
    }

    public function addChildCategory(Request $request)
    {
        $request->validate([   //验证数据
            'name' => 'required|unique:categories',
        ]);
        $category = Category::create(['name' =>$request->input('name')]);
        //将上面添加的节点设置一个父节点
        $category->parent_id = $request->input('parentId');
        $category->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        $children = $category->children;
        return response()->json(['children'=>count($children)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([   //验证数据
            'name' => 'required|unique:categories',
        ]);
        //这里可以不用写$category = Category::find($request->input('id'));
        $category->name = $request->input('name');
        $category->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        /**
         * 删掉一个节点：

        $node->delete();

        * **注意！**节点的所有后代将一并删除 注意！ 节点需要向模型一样删除，不能使用下面的语句来删除节点：

        Category::where('id', '=', $id)->delete();
         */
        if(count($category->children) === 0) {
            $category->delete();
        }
    }
}
