<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\admin\Controller;
use App\Models\Station;
use Illuminate\Http\Request;
use App\Http\Requests\admin\StationRequest;
use Illuminate\Support\Facades\DB;

class StationController extends Controller{
    use \App\Services\Upload;
    /**
     * @param  Request $request
     * Display a listing of the resource.
     */
    public function index(Request $request){
        return view('admin.stations.index')->withStations(Station::orderByDesc('id')->paginate(24));
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param  StationRequest $request
     */
    public function store(StationRequest $request){
        DB::beginTransaction();
        try{
            $station = Station::create($request->except('image'));
            DB::commit();
            return $this->update($request, $station);
        }
        catch(\Exception $e){
            DB::rollBack();
        }
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param  StationRequest $request
     */
    public function update(Request $request, Station $station){
        DB::beginTransaction();
        try{
            $station->update($request->except('image'));
            $station->forceFill([
                'image' => $this->upload('image') ? : $station->image,
            ])->save();
            DB::commit();
            return $request->ajax() ? response()->json([
                'success' => true,
                'message' => __('Station saved successully.'),
                'redirect' => route('admin.stations.index'),
            ]) : redirect()->route('admin.stations.index')->withSuccess(__('Station saved successully.'));
        }
        catch(\Exception $e){
            DB::rollBack();
            return $this->error($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param  Station $station
     */
    public function destroy(Station $station){
        try{
            $station->delete();
            return redirect()->back()->withSuccess(__('Station deleted successully.'));
        }
        catch(\Exception $e){
            return $this->error($e);
        }
    }
}
