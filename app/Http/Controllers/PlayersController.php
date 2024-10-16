<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\Playeritems;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception; 


class PlayersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //プレイヤーの情報を表す関数
    public function index()
    {
        return response()->json(Player::query()->select(['id', 'name','hp','mp','money'])->get());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //指定されたIDのプレイヤーの情報を表す関数
    public function show($id)
    {
        $player = new Player();
        return new Response($player->playerShow($id));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //指定されたIDのプレイヤーの情報を更新する関数
    public function update(Request $request, $id)
    {
        $player = new Player();
    
        // playerUpdate関数を使って指定したIDのプレイヤー情報を更新
        $affectedRows = $player->playerUpdate($id, $request->hp, $request->mp, $request->money);

        return response()->json(['message'=>'Update Success!'],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    //指定されたIDのプレイヤー削除
    public function destroy($id)
    {
        
        $player=new Player();
        $player->playerDestroy($id);
    
        // プレイヤーが見つからなかった場合、404エラーレスポンスを返す
        if (!$player) 
        {
            return response()->json(['message' => 'Player not found']);
        }
            // 削除成功のレスポンスを返す
            return response()->json(['message' => 'Player deleted successfully']);


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    //プレイヤーを作る関数
    public function create(Request $request)
    {
        $player=new Player();
        $newId=$player->playerCreate($request->name,$request->hp,
        $request->mp,$request->money);
        return response()->json(['id'=>$newId]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
     

    }

    //アイテムを追加する関数
    public function addItem(Request $request, $id)
    {
        
        $playeritem = new Playeritems();
        $count=0;

        // 既存のレコードがあるか確認
        $itemExists = $playeritem->playerItemExists($id, $request->item_id);

        if ($itemExists)
        {
            // 既にアイテムが存在する場合はupdate
            $playeritem->player_itemsupdate($id, $request->item_id, $request->count);

            $count = DB::table('player_items')
            ->where('player_id', $id)
            ->where('item_id', $request->item_id)
            ->value('count'); // 更新後の新しいカウントを取得
        } 
        else 
        {
            // 存在しない場合はinsert
            $playeritem->player_itemsinsert($id, $request->item_id, $request->count);

            // 新しく挿入されたカウントを設定
            $count = $request->count;
        }

         // 統一されたレスポンスを返す
         return response()->json([
            'itemId' => $request->item_id,
            'count'  => $count,
        ]); 

    }

    //定数定義
    const MAX_HP=200;
    const MAX_MP=200;
    const DEFAULT_ITEM_USE_COUNT=1;
    const ITEM_TYPE_HP=1;//HP回復アイテムのタイプ
    const ITEM_TYPE_MP=2;//MP回復アイテムのタイプ
    
    //アイテムを使用する関数
    public function useItem(Request $request, $id)
    {

        $playeritem = new Playeritems();
        $player = new Player();

        // アイテムIDとカウントをリクエストから取得
        $item_id = $request->item_id;
        $count = $request->count ?? self::DEFAULT_ITEM_USE_COUNT;  // デフォルトで1個使用

        // プレイヤーのアイテムを取得
        $item = $playeritem->getPlayerItem($id, $item_id);

        // アイテムが存在しないか、所持数がゼロの場合
        if (!$item || $item->count < $count) 
        {   
            return response()->json(['error' => 'アイテムが不足しています'], 400);
        }

        // プレイヤーの現在のHP/MPを取得
        $currentPlayer = $player->getPlayer($id);

        // アイテムのタイプを取得
        $itemDetails = DB::table('items')->where('id', $item_id)->first();
        if (!$itemDetails) 
        {
            return response()->json(['error' => '無効なアイテムIDです'], 400);
        }

        // HP回復の場合の処理 
        //if($itemDetails->type==1)
        if ($itemDetails->type == self::ITEM_TYPE_HP)
        {
            $available_hp_recovery = self::MAX_HP - $currentPlayer->hp;  // 回復可能なHPの量

            // プレイヤーのHPがすでに最大値の場合
            if ($available_hp_recovery <= 0) 
            {
                return response()->json(['error' => 'HPがすでに最大値です'], 400);
            }

            // 使用可能なHP回復薬の数を計算
            $max_usable_count = floor($available_hp_recovery / $itemDetails->value);  // 使用可能なアイテム数を計算
            $used_count = min($count, $max_usable_count + 1);  // 実際に使用する数 (+1は切り捨ての考慮)

            // 実際に回復するHP量を計算
            $hp_amount = $itemDetails->value * $used_count;
            $new_hp = min($currentPlayer->hp + $hp_amount, self::MAX_HP);  // HPは最大値200まで
            // HPを回復
            $player->healPlayer($id, $new_hp - $currentPlayer->hp, 0);  // 実際に回復するHP量を差分で

            // アイテムの所持数を減らす
            $playeritem->decreaseItemCount($id, $item_id, $used_count);

            // 使用後のプレイヤーのステータスを取得
            $updatedPlayer = $player->getPlayer($id);

            //使用されなかったアイテムの数
            $unused_count=$count-$used_count;

        }

        // MP回復の場合の処理 (item_id = 2)
        //else if ($itemDetails->type==2) 
        else if ($itemDetails->type == self::ITEM_TYPE_MP)
        {
            $available_mp_recovery = self::MAX_MP - $currentPlayer->mp;  // 回復可能なMPの量

            // プレイヤーのMPがすでに最大値の場合
            if ($available_mp_recovery <= 0)
            {
                return response()->json(['error' => 'MPがすでに最大値です'], 400);
            }

            // 使用可能なMP回復薬の数を計算
            $max_usable_count = floor($available_mp_recovery / $itemDetails->value);  // 使用可能なアイテム数
            $used_count = min($count, $max_usable_count);  // 実際に使用する数

            // 実際に回復するMP量を計算
            $mp_amount = $itemDetails->value * $used_count;
            $new_mp = min($currentPlayer->mp + $mp_amount, self::MAX_MP);  // MPは最大値200まで

            // MPを回復
            $player->healPlayer($id, 0, $new_mp - $currentPlayer->mp);  // 実際に回復するMP量を差分で

            // アイテムの所持数を減らす
            $playeritem->decreaseItemCount($id, $item_id, $used_count);

            // 使用後のプレイヤーのステータスを取得
            $updatedPlayer = $player->getPlayer($id);

            //使用されなかったアイテムの数
            $unused_count=$count-$used_count;

        }
        else
        {
            throw new Exception('無効なアイテムIDです');
        }

        // 使用後のプレイヤーのステータスを取得
        $updatedPlayer = $player->getPlayer($id);

         // 正常にアイテムを使用した場合のレスポンス
         return response()->json([
            'itemId' => $item_id,
            'usedCount' => $used_count,
            'remainingCount' => $item->count - $used_count,
            'player' => [
                'id' => $updatedPlayer->id,
                'hp' => $updatedPlayer->hp,
                'mp' => $updatedPlayer->mp,
            ]
        ]);

    }



    //ガチャをする関数
    public function useGacha(Request $request,$id)
    {
        $player=new Player();
        $itemModel=new Item();
        $playeritem=new Playeritems();
        $count=$request->input('count',1);

        //トランザクション開始
        DB::beginTransaction();

        try
        {
            //プレイヤーの所持金を確認
            $playerMoney=$player->getPlayerMoney($id);
            $cost=$count*10;

            //count×10の値が所持金より多ければエラーを返す
            if($playerMoney<$cost)
            {
                throw new Exception('お金が足りません'); // エラーを投げる
            }

            //所持金を減らす
            $player->updateMoney($id,$cost);

            //ガチャを引いてアイテムをランダムに取得
            $result=[];
            $missCount=0;
            for($i=0;$i<$count;$i++)
            {
                $randomItem=$itemModel->getRandomItem();
                if($randomItem)
                {
                
                 // ガチャで取得したアイテムのIDを使う
                $existingItem = $playeritem->playerItemExists($id, $randomItem->id);
                
                    if($existingItem)
                    {
                        $playeritem->player_itemsUpdate($id,$randomItem->id,1);
                    }
                    else
                    {
                        // 既存のアイテムがなければ新規に挿入
                        $playeritem->player_itemsinsert($id, $randomItem->id, 1);
                    }

                    //獲得したアイテムを結果に追加
                    $result[]=[
                    'itemId'=>$randomItem->id,
                    'count'=>1
                    ];
                }
                else
                {
                    // ハズレの場合の処理
                    // 何も獲得しなかった場合のロジック
                    $results[] = [
                    'itemId' => null, // ハズレアイテムとしての表示
                    'count' => 0
                    ];
                    $missCount++;//はずれのカウントを増やす
                }
            
            }


            $updateMoney=$player->getPlayerMoney($id);//ガチャ後の所持金を得る。
            $updateItems=$playeritem->getPlayerItembyid($id);//プレイヤーアイテムテーブルからアイテムを取得する。

            //全て成功したのでコミット
            DB::commit();

            return response()->json([
            'results'=>$result,
            'missCount'=>$missCount,
            'player'=>[
                'money'=>$updateMoney,
                'items'=>$updateItems
            ]
            ]);
        }

        catch(Exception $e)
        {
            //エラーが発生したらロールバック
            DB::rollBack();

            //エラーメッセージを返す
            return response()->json(['error'=>$e->getMessage()],400);
        }

    }

}
