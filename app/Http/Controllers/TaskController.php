<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use App\Http\Requests\TaskAddRequest;
use App\Http\Resources\TasksResource;
// use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Resources\AdminsResource;
use App\Http\Resources\FromTasksresource;
use App\Http\Resources\ToesTasksresource;
use Intervention\Image\Drivers\Imagick\Driver ;

class TaskController extends Controller
{
    public function addTask(TaskAddRequest $request){
        $data = $request->validated();
        $task = Task::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'buyer_id' => $data['buyer_id'],
            'assignee_id' => $data['assignee_id'],
            'deadline' => Carbon::now()->addDays($data['deadline']),
        ]);
        $path = 'tasks/images/';
        Storage::makeDirectory('public/'.$path);
        foreach ($request->file('images') as $image){
            // $manager = new ImageManager(Driver::class);
            $image_name = time()."-".Str::random(10).".".$image->getClientOriginalExtension();
            $image->move(storage_path('app/public/tasks/images'), $image_name);
            // $img = $manager->read($image);
            // $img->scale(width:1080);
            // $img->toJpeg(80)->save(storage_path('app/public/'.$path.$image_name));
            $task->images()->create([
                'name' => $image_name,
                'path' => $path,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task added successfully',
        ],201);
    }

    public function all( Request $request){
        $tasks  =Task::latest()->paginate($request->get('per_page', 20));
        if(count($tasks)>0){
            $collection = [
                'last_Page' => $tasks->lastPage(),
                'tasks' => []
            ];
            foreach ($tasks as $task){
                $collection['tasks'][] = new TasksResource($task);
            }
            return response()->json([
                'success' => true,
                'data' => $collection
            ]);
        }
        return response()->json([
            'message' =>'Tasks not found'
        ],404);
    }

    public function task(Task $task){
        if($task){
            return response()->json([
                'success' => true,
                'data' => new TaskResource($task)
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ]);
        }
        
    }

    public function mytasks(User $user,Request $request){
        if($user){
            return response()->json([
                'success' => true,
                'data' => [
                    'user'=> new AdminsResource($user),
                    'goto' => FromTasksresource::collection($user->buyer),
                    'come' => ToesTasksresource::collection($user->assignee),
                ]
                ]);
        }
    }

    public function update(TaskUpdateRequest $request, Task $task)
    {
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found.'
            ], 404);
        }

        $data = [
            'title' => $request->input('title', $task->title),
            'description' => $request->input('description', $task->description),
            'deadline' => $request->input('deadline', $task->deadline),
            'status' => $request->status ?? $task->status 
        ];
        if ($request->status === 'finished' && $task->assignee_id == $request->user_id) {
            $data['status'] = 'finished';
        } elseif (in_array($request->status, ['accepted', 'failed']) && $task->buyer_id === $request->user_id) {
            $data['status'] = $request->status;
        }
        $task->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully.'
        ]);
    }


    public function delete(Task $task){
        if($task) {
            foreach($task->images as $image) {
                $imagePath = storage_path('app/public/' . $image->path . $image->name);
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                    $image->delete();
                }
            }
            $task->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Task and associated images deleted successfully'
            ]);
        }
        
        
    }
}
