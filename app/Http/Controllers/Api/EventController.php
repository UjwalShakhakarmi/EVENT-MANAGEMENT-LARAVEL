<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    use CanLoadRelationships;
    private array $relations = ['user','attendees','attendees.user'];
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index','show','update']);
        $this->middleware('throttle:api')
                ->only(['store','destory']);
        $this->authorizeResource(Event::class , 'event');

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return Event::all();
        //this shows the uncontrolled collection with all user details and its attendees
        // return EventResource::collection(Event::with('user','attendees')->get());
        
        //this method allows you to control which ones to show i.e event users or attendees
        $query = $this->LoadRelationships(Event::query());

        // foreach ($relations as $relation){
        //     $query->when(
        //         $this->shouldIncludeRelation($relation),
        //         fn($q) => $q->with($relation)
        //     );
        // }

        return EventResource::collection(
            $query->latest()->paginate());
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $event = Event::create([
            
            ...$request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time'
            ]),
            'user_id' => $request->user()->id
        ]);
        // return $event;
        return new EventResource($this->LoadRelationships($event));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        // return $event;
        // $event->load('user','attendees');
        return new EventResource($this->LoadRelationships($event));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
    //     if(Gate::denies('update-event',$event)){
    //         abort(403,'You are not authorized to update this event');
    //     }
        $this->authorize('update-event',$event);
        $event->update(
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time'
        ])
    );
    //   return $event;
      return new EventResource($this->LoadRelationships($event));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response(status: 204);
       
    }
}
