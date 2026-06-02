<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});

// Used for general notifications, order status updates, etc.
Broadcast::channel('user.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});

// Used for real-time chat messages.
Broadcast::channel('conversations.{conversationId}', function ($user, $conversationId) {
    // Only participants (buyer or seller) can listen to this conversation
    $conversation = Conversation::find($conversationId);
    
    if (! $conversation) {
        return false;
    }
    
    return $user->id === $conversation->buyer_id || $user->id === $conversation->seller_id;
});
