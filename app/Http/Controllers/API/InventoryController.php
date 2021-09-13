<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function dataInventory(Request $request)
    {
        $id = $request->input('id');
        $material_name = $request->input('material_name');
        $stock_from = $request->input('stock_from');


        if ($request->id != '' || $request->material_name != '' || $request->stock_from != '') {
            $request->validate([
                'material_name' => 'string|max:255',
                'stock_from' => 'numeric',
            ]);

            if ($id) {
                $inventory = Inventory::find($id);
                if ($inventory) {
                    return ResponseFormatter::success(
                        $inventory,
                        'Data inventory berhasil diambil'
                    );
                } else {
                    return ResponseFormatter::error(
                        null,
                        'Data inventory tidak ada'
                    );
                }
            }

            if ($material_name) {
                $inventory = Inventory::where('material_name', 'like', '%' . $material_name . '%')->get();
                return ResponseFormatter::success(
                    $inventory,
                    'Data inventory berhasil diambil'
                );
            }
            if ($stock_from) {
                $inventory = Inventory::where('stock', '>=', $stock_from)->get();
                return ResponseFormatter::success(
                    $inventory,
                    'Data inventory berhasil diambil'
                );
            }
            return ResponseFormatter::error(
                'Data inventory kosong'
            );
        } else {
            $inventory = Inventory::all();
            if ($inventory != '') {
                return ResponseFormatter::success(
                    $inventory,
                    'Data inventory berhasil diambil'
                );
            } else {
                return ResponseFormatter::error(
                    'Data inventory kosong'
                );
            }
        }
    }
    public function storeInventory(Request $request)
    {
        $request->validate([
            'material_name' => 'string|max:255|required',
            'stock' => 'numeric|required',
        ]);

        $check = $request->material_name;
        $check2 = Inventory::where('material_name', $check)->first();

        try {
            if ($check2) {
                $inventory = Inventory::findOrFail($check2->id);
                $inventory->stock = $inventory->stock + $request->stock;
                $inventory->update();
                return ResponseFormatter::success(
                    $inventory,
                    'Data inventory berhasil ditambahkan',
                    200
                );
            } else {
                $inventory = Inventory::create($request->all());
                return ResponseFormatter::success(
                    $inventory,
                    'Data inventory berhasil ditambahkan',
                    200
                );
            }
        } catch (\Throwable $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something went wrong',
                    'error' => $error
                ],
                'Data inventory gagal ditambahkan',
                400
            );
        }
    }
    public function updateInventory(Request $request, $id)
    {
        $request->validate([
            'material_name' => 'string|max:255',
            'stock' => 'numeric',
        ]);


        try {
            $inventory = Inventory::find($id);
            $inventory->update($request->all());
            return ResponseFormatter::success(
                $inventory,
                'Data inventory berhasil diupdate',
                200
            );
        } catch (\Throwable $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something went wrong',
                    'error' => $error
                ],
                'Data inventory gagal diupdate',
                400
            );
        }
    }
    public function destroyInventory($id){
        if (Inventory::destroy($id)) {
            return ResponseFormatter::success(
                'Data inventory telah dihapus', 200
            );
        }else{
            return ResponseFormatter::error(
                'Data inventory gagal dihapus', 400
            );
        }
    }

    public function listInventory(){
        $inventory = Inventory::all();
        $user = User::all();
        return ResponseFormatter::success(
            $inventory, $user,
            'Data inventory & user berhasil diambil'
        );
    }
}
