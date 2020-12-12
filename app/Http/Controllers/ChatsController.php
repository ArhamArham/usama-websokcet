<?php

namespace App\Http\Controllers;

use App\Models\Message;
use DB;
use Illuminate\Http\Request;
use App\Events\MessageSent;

class ChatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('chats');
    }

    public function fetchMessages()
    {
        return Message::whereStatus(1)->with('user')->get();
    }

    public function sendMessage(Request $request)
    {
        $message = auth()->user()->messages()->create([
            'message' => $request->message,
            'status' => 1
        ]);

        broadcast(new MessageSent($message->load('user')))->toOthers();

        return ['status' => 'success'];
    }

    public function status(Request $request)
    {
        if ($request->all() !== []) {
            $messages = Message::where('user_id', '=', auth()->user()->id);
            $messages->update(['status' => 0]);
            return response(['message' => 'Your messages has been cleared']);
        }
        $messages = Message::where('status', 1);
        $messages->update(['status' => 0]);
        return response(['message' => 'All messages has been cleared']);
    }
}
