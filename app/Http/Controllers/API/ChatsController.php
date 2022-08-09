<?php

namespace App\Http\Controllers\API;

use App\Events\NewMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChatMessageRequest;
use App\Http\Requests\CreateGroupRequest;
use App\Http\Resources\ChatMessageResource;
use App\Http\Resources\ChatResource;
use App\Http\Resources\ChatUserResource;
use App\Http\Resources\UserResource;
use App\Jobs\DeleteMessageJob;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatsController extends Controller
{
    /**
     * @param Request $request
     * @return array | \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        //Handle Search/Filtering of Chats List
        if ($request->queryString) {
            // get all Matching Users and Chats
            $chats = Chat::where('name', 'LIKE', '%' . $request->queryString . '%')->get();
            $users = User::where('name', 'LIKE', '%' . $request->queryString . '%')
                ->whereNot('id', $user->id)->get();

            $queryResults = [
                ...$chats,
                ...$users
            ];

            return response()->success(
                $queryResults,
                'Chat List Filtered',
                200);
        }

        $chats = $user->chats;

        $responseData = [
            'chats' => ChatResource::collection($chats)
        ];

        return response()->success(
            $responseData,
            'Chat List Fetched',
            200);
    }

    /**
     * @param Chat $chat
     * @return ChatResource
     */
    public function show(Request $request, $chatId)
    {
        $chat = Chat::find($chatId);

        if ($chat) {
            $chat->load('activeUsers');

            // If The other user is opening the chat then mark messages as read
            if ($chat->type === Chat::TYPE_CHAT) {
                $chat->messages()->where('sender_id', '!=', auth()->id())->update([
                    'read_at' => Carbon::now()
                ]);
            }

            $chat->load('messages');

            return response()->success(
                ChatResource::make($chat),
                'Chat Details Fetched',
                200);
        } else {
            // Check if this is a user id and check if it's not authenticated user's own id
            if (($userId = $request->user()->id) !== $chatId) {
                $newChatUser = User::where('id', $chatId)->first();

                $chat = Chat::whereHas('users', function ($query) use ($userId, $newChatUser) {
                // $query->whereIn('user_id', [$newChatUser->id, $userId]);
                    $query->select(\DB::raw('count(distinct user_id)'))->whereIn('user_id', [$newChatUser->id, $userId]);
                }, '=', 2)->where('type', Chat::TYPE_CHAT)->first();

                if (!$chat) {
                    // Create a Chat
                    $chat = Chat::create([
                        'user_id' => $userId
                    ]);

                    // Assign users to chat
                    $chat->users()->sync([
                        $userId,
                        $newChatUser->id
                    ]);

                    return response()->success(
                        ChatResource::make($chat),
                        'Chat Details Fetched',
                        200);

                } else {
                    return response()->success(
                        ChatResource::make($chat),
                        'Chat Details Fetched',
                        200);
                }
            }

        }

        return response()->error(
            'Incorrect Chat ID',
            'Chat Details not Found!',
            404);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(ChatMessageRequest $request): JsonResponse
    {
        $request->validated();

        // Setting Type of Message to simple message for now bcz files aren't used
        $messageType = 'message';

        $message = $request->get('message');
        $toUser = $request->get('user_id');
        $user = $request->user();
        $chatName = $request->get('chat_title');
        $chatId = $request->get('chat_id');

        // Check If User is trying to send a message to himself
        if ((int)$toUser === auth()->user()->id) {
            return response()->error(
                'Incorrect Sender ID',
                'You cannot send message to yourself!',
                403);
        }

        $chat = '';
        if (!$chatId) {
            // In case Chat If is not passed but there exists a record in DB
            $chat = Chat::whereHas('users', function ($query) use ($user, $toUser) {
                $query->whereIn('user_id', [$toUser, $user->id]);
            })->where('type', Chat::TYPE_CHAT)->first();

            if (!$chat) {
                // Create a Chat
                $chat = Chat::create([
                    'user_id' => $user->id,
                    'name' => $chatName ?: null
                ]);

                // Assign users to chat
                $chat->users()->sync([
                    $user->id,
                    $toUser
                ]);
            }
        }

        if (!$chat) {
            $chat = Chat::where('id', $chatId)->first();
        }

        // Check If User has left the chat or Group
        if ($chat->users()->where('user_id', $user->id)->where('status', ChatUser::STATUS_LEAVE)->exists()) {
            return response()->error(
                'Unable to Send the Message',
                'You have left the group/chat',
                403
            );
        }
        // Store the message
        $chatMessage = $chat->messages()->create([
            'type' => $messageType,
            'message' => $message,
            'sender_id' => $user->id
        ]);

        // Trigger an Event for new message ( Broadcast -> ChatMessage toOthers() )
        broadcast(new NewMessage($user, $chatMessage))->toOthers();

        // Return a Response
        $responseData = [
            'message' => ChatMessageResource::make($chatMessage)
        ];

        return response()->success(
            $responseData,
            'Message Sent',
            201);
    }

    /**
     * @param CreateGroupRequest $request
     * @return mixed
     */
    public function createGroup(CreateGroupRequest $request)
    {
        $request->validated();

        $groupTitle = $request->title;
        $usersList = $request->usersList;
        // Add loggedIn user to the group users as well
        $userId = auth()->user()->id;
        $usersList[] = $userId;

        // Create a new Chat Group
        $chatGroup = Chat::create([
            'name' => $groupTitle ?: 'Chat Group',
            'user_id' => $userId,
            'type' => Chat::TYPE_GROUP
        ]);

        // Attach all users of this group
        $chatGroup->users()->attach($usersList);

        // Create a first message
        $chatGroup->messages()->create([
            'type' => ChatMessage::TYPE_ALERT,
            'message' => 'Group Created',
            'sender_id' => $userId
        ]);

        // Trigger an event
        $responseData = [
            'group' => ChatResource::make($chatGroup),
            'users' => UserResource::collection($chatGroup->users)
        ];

        return response()->success(
            $responseData,
            'Group Created',
            201);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function leaveGroup(Request $request)
    {
//        $request->validated();

        $groupId = $request->get('groupId');
        $user = auth()->user();

        // Check If Group Exist
        if (!$chatGroup = Chat::find($groupId)) {
            return response()->error(
                'Incorrect Group ID',
                'Group not Found!',
                404);
        }

        // Check if user is part of the group
        if (!$chatGroup->users()->where('user_id', $user->id)->exists()) {
            return response()->error(
                'Unable to leave the group',
                'You are not part of this Group!',
                403);
        }

        // Check If user has already left the group
        if ($chatGroup->users()->where('user_id', $user->id)->where('status', ChatUser::STATUS_LEAVE)->exists()) {
            return response()->error(
                'Unable to Leave the Group',
                'You have already left the group',
                403
            );
        }

        // Change User status to left so that he can preview old messages till 'last_active_at' datetime
        $chatGroup->users()->where('user_id', $user->id)->update([
            'status' => ChatUser::STATUS_LEAVE,
            'last_active_at' => Carbon::now()
        ]);

        return response()->success(
            [],
            'Group Left',
            200
        );
    }

    /**
     * @param Request $request
     * @param Chat $chat
     * @return void
     */
    public function deleteHistory(Request $request, Chat $chat)
    {
        /** @var User $user */
        $user = $request->user();

        /** @var ChatMessage $chatMessage */
        $chatMessage = ($query = ChatMessage::query())
            ->where($query->qualifyColumn('chat_id'), $chat->id)
            ->whereDoesntHave(
                'userDeleteMessages',
                function (Builder $query) use ($user) {
                    $query->where('user_id', $user->id);
                }
            );

        if (!$chatMessage->exists()) {
            abort(404);
        }

        DeleteMessageJob::dispatch($chat->id, $user->id);

        $detached = false;

        if ($chat->users()->where('user_id', $user->id)->first()->pivot->status === ChatUser::STATUS_LEAVE) {
            $chat->users()->detach([
                $user->id
            ]);
            $detached = true;
        }

        return response()->success(
            [],
            $detached ? 'Chat Left' : 'Chat Cleared',
            200
        );
    }

    /**
     * @param Request $request
     * @param Chat $chat
     * @return void
     */
    public function addMembers(Request $request, Chat $chat)
    {
        $usersId = $request->users;
        $chat->users()->attach($usersId);

        return;
    }
}
