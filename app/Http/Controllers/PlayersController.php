<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PlayersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new Response(
            Player::query()->
            select(['id', 'name'])->
            get());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        $player = new Player();
        return new Response
        (
            $player->playerShow($id)
        );
    
        // プレイヤーが見つからなかった場合、404エラーレスポンスを返す
        if (!$player) {
        return new Response('Player not found', 404);}
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
    public function update(Request $request, $id)
    {
        $player = new Player();
    
        // playerUpdate関数を使って指定したIDのプレイヤー情報を更新
        $affectedRows = $player->playerUpdate($id, $request->hp, $request->mp, $request->money);

        if ($affectedRows > 0) {
        return response()->json(['message' => 'Player updated successfully'], 200);
        } else {
        return response()->json(['error' => 'Player not found or no changes made'], 404);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        // 指定したIDのプレイヤーを検索
        $player = Player::find($id);
    
        // プレイヤーが見つからなかった場合、404エラーレスポンスを返す
        if (!$player) {
        return response()->json(['message' => 'Player not found'], 404);
        }

        // プレイヤーを削除
        // $player->delete();

        // 削除成功のレスポンスを返す
        return response()->json(['message' => 'Player deleted successfully'], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        // リクエストのバリデーション
        $request->validate([
        'name' => 'required|string|max:255',
        'hp' => 'required|integer|min:0',
        'mp' => 'required|integer|min:0',
        'money' => 'required|integer|min:0',
        ]);

        try {
            // 新しいPlayerインスタンスを作成
            $player = new Player();
    
            // プレイヤー情報をデータベースに挿入し、新しいIDを取得
            $newId = $player->playerCreate($request->name, $request->hp, $request->mp, $request->money);
    
            // 成功時に新しいプレイヤーのIDを返す
            return response()->json(['id' => $newId], 201);
        } catch (QueryException $e) {
            // エラーが発生した場合にエラーメッセージを返す
            return response()->json(['error' => 'Failed to create player', 'message' => $e->getMessage()], 500);

        }
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
}
