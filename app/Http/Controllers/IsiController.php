<?php

namespace App\Http\Controllers;

use App\Models\Isi;
use Illuminate\Http\Request;

class IsiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $isi = Isi::all();

        return response()->json([
            'success' => true,
            'data' => $isi
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (isset($request->id)) {
            $isi = Isi::find($request->id);
        } else {
            $isi = new Isi();
        }
        $isi->isi = $request->isi;
        $isi->save();

        return response()->json([
            'success' => true,
            'data' => $isi
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $isi = Isi::find($id);
        $isi->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
