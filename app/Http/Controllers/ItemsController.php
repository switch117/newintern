<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class ItemsController extends Controller
{
    //

   // アイテムを挿入する処理
   public function store(Request $request)
   {
       $data = $request->validate([
           'name' => 'required|string|max:255',
           'value' => 'required|integer|min:1',
           'price' => 'required|integer|min:0',
           'type' => 'required|integer',
           'percent' => 'required|integer|min:0|max:100',
       ]);

       // モデルを直接インスタンス化して利用
       $itemModel = new Item();
       $itemId = $itemModel->insertItem($data['name'], $data['value'], $data['type'], $data['price'], $data['percent']);

       return response()->json(['id' => $itemId], 201);
   }

   // アイテムを更新する処理
   public function update(Request $request, $id)
   {
       $itemModel = new Item(); // モデルをインスタンス化
       $item = $itemModel->getItemById($id);

       if (!$item) {
           return response()->json(['error' => 'Item not found'], 404);
       }

       $data = $request->validate([
           'name' => 'required|string|max:255',
           'value' => 'required|integer|min:1',
           'price' => 'required|integer|min:0',
           'type' => 'required|integer',
           'percent' => 'required|integer|min:0|max:100',
       ]);

       $itemModel->updateItem($id, $data);

       return response()->json(['message' => 'Item updated successfully']);
   }

   // アイテムを削除する処理
   public function destroy($id)
   {
       $itemModel = new Item(); // モデルをインスタンス化
       $item = $itemModel->getItemById($id);

       if (!$item) {
           return response()->json(['error' => 'Item not found'], 404);
       }

       $itemModel->deleteItem($id);

       return response()->json(['message' => 'Item deleted successfully']);
   }



}
