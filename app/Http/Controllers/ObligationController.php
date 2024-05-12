<?php

namespace App\Http\Controllers;

use App\Http\Requests\ObligationAddRequest;
use App\Http\Resources\ObligationsResource;
use App\Models\Obligation;
use Illuminate\Http\Request;

class ObligationController extends Controller
{
    public function create(ObligationAddRequest $request){
        $data = $request->validated();
        $obligation = Obligation::create([
            'obligation' => $data['obligation']
        ]);
        return response()->json([
            'success' => true,
            'data' => new ObligationsResource($obligation)
        ],201);
    }

    public function edit(ObligationAddRequest $request, Obligation $obligation){
        if($obligation){
            $obligation->update([
                'obligation' => $request->obligation
            ]);
        }
        return response()->json([
            'success' => true,
            'data' => new ObligationsResource($obligation)
        ]);
    }

    public function delete (Obligation $obligation){
        if($obligation){
            $obligation->delete();
        }
        return response()->json([
            'success' => true,
        ]);
    }

    public function all(){
        $obligations = Obligation::latest()->get();
        return response()->json([
            'success' => true,
            'data' => ObligationsResource::collection($obligations)
        ]);
    }
}
