<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    public $timestamps = false;
    use HasFactory;

    
    
    /**
     * プレイヤーを1件取得
     * @return １件のプレイヤー情報
     */
    public function playerShow($id) {
        return (Player::query()->where('id', $id)->first());
    }

    /**
     * 新規プレイヤーのレコードを作成し、idを返す
     * 
     * @param int name,hp,mp,money
     * @return 新規プレイヤーのid
     */
    public function playerCreate($name, $hp, $mp, $money) 
    {  
        return(Player::query()->insertGetId([
            'name' => $name,
            'hp' => $hp,
            'mp' => $mp,
            'money' => $money,
        ]));
    }

    public function playerDestroy($id)
    {
        // 指定されたIDのプレイヤーを削除
        return Player::query()
        ->where('id', $id)
        ->delete(); // 該当レコードを削除
    }

    public function playerUpdate($id, $hp, $mp, $money)
    {
    return Player::query()
        ->where('id', $id) // 指定されたIDのプレイヤーを検索
        ->update([
            'hp' => $hp,
            'mp' => $mp,
            'money' => $money
        ]); // 更新するデータを指定
    }
}
