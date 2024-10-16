<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Player extends Model
{
    public $timestamps = false;
    use HasFactory;

    /**
     * プレイヤーを1件取得
     * @return １件のプレイヤー情報
     */
    public function playerShow($id) 
    {
        return (Player::query()->where('id', $id)->first());
    }


    /**
     * 新規プレイヤーのレコードを作成し、idを返す
     * 
     * @param int name,hp,mp,money
     * @return 新規プレイヤーのid
     */

    //新しいプレイヤーを作る関数
    public function playerCreate($name, $hp, $mp, $money) 
    {  
        return(DB::table('players')->insertGetId
        ([
            'name' => $name,
            'hp' => $hp,
            'mp' => $mp,
            'money' => $money,
        ]));
    }

    
    /**
     * 指定されたIDのプレイヤーを削除する
     * 
     * @return 削除できたかどうかメッセージを返す
     */
    public function playerDestroy($id)
    {
        // 指定されたIDのプレイヤーを削除
        return Player::query()
        ->where('id', $id)->delete(); // 該当レコードを削除
    }


    /**
     * IDからプレイヤーの情報を取得し、hp,mp,moneyを更新する関数
     * 
     * @param int id,hp,mp,money
     * @return 更新できたかメッセージを表す
     */

    public function playerUpdate($id, $hp, $mp, $money)
    {
        return Player::query()->where('id', $id)->update // 指定されたIDのプレイヤーを検索
        ([
            'hp' => $hp,
            'mp' => $mp,
            'money' => $money
        ]); // 更新するデータを指定
    }

    // プレイヤーのステータスを取得
    public function getPlayer($player_id)
    {
        return DB::table('players')->where('id', $player_id)->first();
    }

    // プレイヤーのHP/MPを回復
    public function healPlayer($player_id, $hp_amount, $mp_amount)
    {
            return DB::table('players')
            ->where('id', $player_id)
            ->update([
                'hp' => DB::raw("LEAST(hp + {$hp_amount}, 200)"),
                'mp' => DB::raw("LEAST(mp + {$mp_amount}, 200)")
            ]);
    }

    //プレイヤーの所持金を更新する関数
    public function updateMoney($playerId,$amount)
    {
        return DB::table('players')
        ->where('id',$playerId)
        ->decrement('money',$amount);
    }

    //プレイヤーの所持金を取得する関数
    public function getPlayerMoney($playerId)
    {
        return DB::table('players')
        ->where('id',$playerId)
        ->value('money');
    }
    


}
