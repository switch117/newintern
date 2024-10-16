<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    public $timestamps = false;
    use HasFactory;

    // アイテムを挿入するメソッド
    public function insertItem($name, $value, $type, $price, $percent)
    {
        return DB::table('items')->insertGetId([
            'name' => $name,
            'value' => $value,
            'type' => $type,
            'price' => $price,
            'percent' => $percent
        ]);
    }

    // 特定のIDのアイテムを取得するメソッド
    public function getItemById($id)
    {
        // テーブル名を直接指定してクエリを実行
        return DB::table('items')->where('id', $id)->first();
    }

    // アイテムを更新するメソッド
    public function updateItem($id, $data)
    {
        return DB::table('items')->where('id', $id)->update($data);
    }

    // アイテムを削除するメソッド
    public function deleteItem($id)
    {
        return DB::table('items')->where('id', $id)->delete();
    }

    //アイテムからパーセントを取得し、乱数生成しアイテムをランダムに取得する関数。
    public function getRandomItem()
    {
        $items = DB::table('items')->get(['id', 'percent']);

        // 総確率を計算
        $totalPercent = $items->sum('percent');
        // ハズレの確率を追加
        $missPercent = 100 - $totalPercent;

        // 乱数を生成
        $random = rand(0, 99); // 0から99までの乱数（100％分）

        // 現在の確率を累積
        $current = 0;

        foreach ($items as $item) 
        {
            $current += $item->percent;
            if ($random < $current)
            {
                return $item; // 当選アイテムを返す
            }
        }

        // ハズレの判定（ハズレの確率が出た場合はnullを返す）
        if ($random >= $current && $random < ($current + $missPercent))
        {
            return null; // ハズレの場合
        }

        return null; // 安全策としてnullを返す
    }


    
}
