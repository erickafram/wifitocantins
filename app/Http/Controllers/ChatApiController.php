<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatApiController extends Controller
{
    public function startConversation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:100',
            'message' => 'required|string|max:2000',
        ]);

        $sessionId = $request->session_id ?? Str::uuid()->toString();

        // Verificar se já existe conversa ativa com esse session_id
        $conversation = ChatConversation::where('session_id', $sessionId)
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if (!$conversation) {
            $conversation = ChatConversation::create([
                'visitor_name' => $request->name,
                'visitor_phone' => $request->phone,
                'visitor_email' => $request->email,
                'visitor_ip' => $request->ip(),
                'visitor_mac' => $request->mac ?? null,
                'session_id' => $sessionId,
                'status' => 'pending',
                'last_message_at' => now(),
                'unread_count' => 1,
            ]);
        }

        // Criar mensagem
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'visitor',
            'message' => $request->message,
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'unread_count' => $conversation->unread_count + 1,
        ]);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'session_id' => $sessionId,
            'message' => $message,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'message' => 'required|string|max:2000',
        ]);

        $conversation = ChatConversation::where('session_id', $request->session_id)
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversa não encontrada. Inicie uma nova conversa.',
            ], 404);
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'visitor',
            'message' => $request->message,
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'unread_count' => $conversation->unread_count + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function getMessages(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $conversation = ChatConversation::where('session_id', $request->session_id)->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversa não encontrada.',
            ], 404);
        }

        $messages = $conversation->messages()
            ->with('admin:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        // Marcar mensagens do admin como lidas
        $conversation->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'status' => $conversation->status,
        ]);
    }

    public function checkNewMessages(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'last_id' => 'nullable|integer',
        ]);

        $conversation = ChatConversation::where('session_id', $request->session_id)->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'has_new' => false,
            ]);
        }

        $query = $conversation->messages()
            ->where('sender_type', 'admin')
            ->with('admin:id,name');

        if ($request->last_id) {
            $query->where('id', '>', $request->last_id);
        }

        $newMessages = $query->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'has_new' => $newMessages->count() > 0,
            'messages' => $newMessages,
            'status' => $conversation->status,
        ]);
    }
}
