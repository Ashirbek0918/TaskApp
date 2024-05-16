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
use App\Http\Resources\AdminsResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Resources\FromTasksresource;
use App\Http\Resources\ToesTasksresource;
use App\Http\Resources\UserTasksResource;
use Intervention\Image\Drivers\Imagick\Driver;
use App\Http\Resources\UserAssigneeTasksResource;
use App\Http\Resources\UserResource;

class TaskController extends Controller
{
    public function addTask(TaskAddRequest $request)
    {
        $data = $request->validated();
        if ($data['assignee_id'] == auth()->user()->id) {
            return response()->json([
                'error' => "The buyer and the assignee must not be the same"
            ], 422);
        }
        $task = Task::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'buyer_id' => auth()->user()->id,
            'assignee_id' => $data['assignee_id'],
            'deadline' => Carbon::now()->addDays($data['deadline']),
        ]);
        if ($request->hasFile('iiamges')) {
            $path = 'tasks/images/';
            Storage::makeDirectory('public/' . $path);
            foreach ($request->file('images') as $image) {
                $image_name = time() . "-" . Str::random(10) . "." . $image->getClientOriginalExtension();
                $image->move(storage_path('app/public/tasks/images'), $image_name);
                $task->images()->create([
                    'name' => $image_name,
                    'path' => $path,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Task added successfully',
        ], 201);
    }

    public function all(Request $request)
    {
        $tasks  = Task::latest()->paginate($request->get('per_page', 20));
        if (count($tasks) > 0) {
            $collection = [
                'last_Page' => $tasks->lastPage(),
                'tasks' => []
            ];
            foreach ($tasks as $task) {
                $collection['tasks'][] = new TasksResource($task);
            }
            return response()->json([
                'success' => true,
                'data' => $collection
            ]);
        }
        return response()->json([
            'message' => 'Tasks not found'
        ], 404);
    }

    public function task(Task $task)
    {
        if ($task) {
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

    public function mytasks($id, Request $request)
    {
        $user = User::findOrFail($id);
        $tasks_assignee_ids = $user->assignee()->pluck('id');
        $users_with_tasks = User::whereIn('id', function ($query) use ($tasks_assignee_ids) {
            $query->select('buyer_id')
                ->from('tasks')
                ->whereIn('id', $tasks_assignee_ids)
                ->distinct();
        })->get();

        $collection = [];

        foreach ($users_with_tasks as $item) {
            $tasks = $item->buyer()->where('assignee_id', $user->id)
                ->whereIn('status', ['active', 'start', 'finished'])
                ->get();

            if ($tasks->isNotEmpty()) {
                $currentUser = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'email' => $item->email,
                    'images' => $this->getImageUrls($item),
                    'tasks' => $tasks->map(function ($task) {
                        return [
                            'id' => $task->id,
                            'title' => $task->title,
                            'created_at' => $task->created_at,
                            'updated_at' => $task->updated_at,
                            'status' => $task->status,
                            'deadline' => $task->deadline,
                            'buyer_id' => $task->buyer_id,
                            'description' => $task->description,
                            'images' => $this->getImageUrls($task)
                        ];
                    })
                ];

                $collection[] = $currentUser;
            }
        }

        return response()->json([
            'success'  => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'images' => $this->getImageUrls($user)
                ],
                'user_tasks' => $collection
            ]
        ]);
    }

    public function my_archive_tasks($id, Request $request)
    {
        $user = User::findOrFail($id);
        $tasks_assignee_ids = $user->assignee()->pluck('id');
        $users_with_tasks = User::whereIn('id', function ($query) use ($tasks_assignee_ids) {
            $query->select('buyer_id')
                ->from('tasks')
                ->whereIn('id', $tasks_assignee_ids)
                ->distinct();
        })->get();

        $collection = [];

        foreach ($users_with_tasks as $item) {
            $tasks = $item->buyer()->where('assignee_id', $user->id)
                ->whereIn('status', ['accepted','failed',])
                ->get();

            if ($tasks->isNotEmpty()) {
                $currentUser = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'email' => $item->email,
                    'images' => $this->getImageUrls($item),
                    'tasks' => $tasks->map(function ($task) {
                        return [
                            'id' => $task->id,
                            'title' => $task->title,
                            'created_at' => $task->created_at,
                            'updated_at' => $task->updated_at,
                            'status' => $task->status,
                            'deadline' => $task->deadline,
                            'buyer_id' => $task->buyer_id,
                            'description' => $task->description,
                            'images' => $this->getImageUrls($task)
                        ];
                    })
                ];

                $collection[] = $currentUser;
            }
        }

        return response()->json([
            'success'  => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'images' => $this->getImageUrls($user)
                ],
                'user_tasks' => $collection
            ]
        ]);
    }




    protected function getImageUrls($item)
    {
        return $item->images->map(function ($image) {
            return url("storage/" . $image->path . $image->name);
        });
    }

    public function my_tasks_users(User $user, Request $request)
    {
        if ($user) {
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => new AdminsResource($user),
                    'goto' => FromTasksresource::collection($user->buyer),
                    'come' => ToesTasksresource::collection($user->assignee),
                ]
            ]);
        }
    }

    public function update(TaskUpdateRequest $request, Task $task)
    {
        $user = auth()->user();
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
        if (in_array($request->status, ['processing', 'finished']) && $task->assignee_id == $user->id) {
            $data['status'] = 'finished';
        } elseif (in_array($request->status, ['accepted', 'failed']) && $task->buyer_id == $user->id) {
            $data['status'] = $request->status;
        }
        $task->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully.'
        ]);
    }


    public function delete(Task $task)
    {
        if ($task) {
            foreach ($task->images as $image) {
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
