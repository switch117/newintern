<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Playeritems extends Model
{
    public $timestamps = false;
    use HasFactory;

    public function player_itemsinsert($player_id,$item_id,$count)
    {   

        // データを挿入
        $inserted = DB::table('player_items')->insert([
        'player_id' => $player_id,
        'item_id' => $item_id,
        'count' => $count
        ]);
    }

    // アイテムを更新する関数
    public function player_itemsUpdate($player_id, $item_id, $count)
    {
        return DB::table('player_items')
        ->where('player_id', $player_id)
        ->where('item_id', $item_id)
        ->update(['count' => DB::raw("count + {$count}") ]); // 既存のcountに加算
    }

    // アイテムの存在確認関数
    public function playerItemExists($player_id, $item_id)
    {
        return DB::table('player_items')
        ->where('player_id', $player_id)
        ->where('item_id', $item_id)
        ->exists();  // レコードが存在するかどうかを返す
    }

    //プレイヤーのアイテムを取得
    public function getPlayerItem($player_id,$item_id)
    {
        return DB::table('player_items')
        ->where('player_id',$player_id)
        ->where('item_id',$item_id)
        ->first();
    }

    //プレイヤーのアイテムを取得
    public function getPlayerItembyid($player_id)
    {
        return DB::table('player_items')
        ->where('player_id',$player_id)
        ->get(['item_id','count']);
    }

    // アイテムを使用して数を減らす
    public function decreaseItemCount($player_id, $item_id, $count)
    {
        return DB::table('player_items')
            ->where('player_id', $player_id)
            ->where('item_id', $item_id)
            ->decrement('count', $count);
    }



}
