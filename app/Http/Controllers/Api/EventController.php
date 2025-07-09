<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\EventRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Event;

class EventController extends Controller
{
    public function storeEvent(EventRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'required|string',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $image = $request->input('image');
       

        // Check for existing package
        $existingEvent = Event::where('title', $title)
        ->first();
        if ($existingEvent) {
            return response()->json(['message' => 'Event already available.'], 409);
        }

        // Create the new package
        Event::create([
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'user_id' => Auth::id(), // Corrected this part
        ]);

        return response()->json(['message' => 'Event has been successfully saved to database.'], 200);
    }

    public function getEvents(EventRequest $request)
    {
        $events = Event::with('user')
        ->latest()
        ->get();

        return response()->json(['events' => $events, ], 200);
    }
    
    public function deleteEvent(EventRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Fill in all empty fields', 'errors' => $validator->errors()], 400);
        }


        $id = $request->input('id');

        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Event has been deleted.'], 200);
    }
}
