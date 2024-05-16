<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\MessageRead;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Http\Resources\MessageResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\MessageAddRequest;
use App\Http\Resources\MessagesResource;
use App\Http\Requests\MessageReadRequest;
use App\Models\Task;

class MessageController extends Controller
{
    public function add(MessageAddRequest $request)
    {
        $data = $request->validated();
        $message = Message::create([
            'message' => $data['message'],
            'user_id' => auth()->user()->id,
            'kind' => $data['kind'],
        ]);
        if ($request->hasFile('images')) {
            $path = 'messages/images/';
            Storage::makeDirectory('public/' . $path);
            foreach ($request->file('images') as $image) {
                $image_name = time() . "-" . Str::random(10) . "." . $image->getClientOriginalExtension();
                $image->move(storage_path('app/public/messages/images'), $image_name);
                $message->images()->create([
                    'name' => $image_name,
                    'path' => $path,
                ]);
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Message added successfully'
        ], 201);
    }

    public function all(Request $request)
    {
        $messages = Message::where('kind',$request->kind)->latest()->paginate($request->get('per_page', 20));
        $collection = [
            'last_page' => $messages->lastPage(),
            'messages' => []
        ];
        foreach ($messages as $message) [
            $collection['messages'][] = new MessagesResource($message)
        ];
        return response()->json([
            'success' => true,
            'data' => $collection
        ]);
    }

    public function message(Message $message)
    {
        if ($message) {
            return response()->json([
                'success' => true,
                'data' => new MessageResource($message)
            ]);
        }
    }

    public function read(MessageReadRequest $request)
    {
        $user = User::findOrFail($request->user_id);
        $existingRead = MessageRead::where('message_id', $request->message_id)
            ->where('user_id', $user->id)
            ->exists();
        if (!$existingRead) {
            MessageRead::create([
                'user_id' => $user->id,
                'message_id' => $request->message_id,
            ]);
            return response()->json([
                'success' => true,
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Message already read'
        ]);
    }

    public function update(Request $request,Message $message)
    {
        if ($message) {
            $message->update([
                'message' => $request->message
            ]);
            return response()->json([
                'success' => true,
                'data' =>  new MessageResource($message)
            ]);
        }
    }

    public function delete(Message $message)
    {
        if($message) {
            foreach($message->images as $image) {
                $imagePath = storage_path('app/public/' . $image->path . $image->name);
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                    $image->delete();
                }
            }
            $message->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Task and associated images deleted successfully'
            ]);
        }
    }

    public function alldata(){
        $task = Task::where('status', 'active')->count();
        $messages = Message::where('kind', 'task')->count();
        return response()->json([
            'success' => true,
            'data' =>[
                'total tasks' =>$task,
                'total messages' =>$messages
            ]
        ]);
    }
}
