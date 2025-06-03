<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\WorkorderStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ProcessWorkOrderUpdate;

use Illuminate\Http\Request;

class AdminWorkOrderController extends Controller
{
    //
    public function apiViewOrders(Request $request) {
        return response()->json(WorkOrder::paginate(10));
    }
    public function apiViewOrder($id) {
        $order = WorkOrder::with('status')->find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }
        return response()->json($order);
    }
    public function apiUpdateStatus(Request $request, $id) {
        $order = WorkOrder::find($id);
        if (!$order) return response()->json(['error' => 'Order not found'], 404);

        $status = WorkorderStatus::where('video_request_id', $id)->first();
        $uploaded = false;
        $videoPath = null;

        if ($request->hasFile('script_file')) {
            $request->validate(['script_file' => 'file|mimes:pdf,doc,docx|max:10240']);
            $videoPath = $request->file('script_file')->store('scripts', 'public');
            $status->script_path = $videoPath;
            $uploaded = true;
        } else if ($request->hasFile('voiceover_file')) {
            $request->validate(['voiceover_file' => 'file|mimes:mp3,wav|max:10240']);
            $videoPath = $request->file('voiceover_file')->store('voiceovers', 'public');
            $status->voiceover_path = $videoPath;
            $uploaded = true;
        } else if ($request->hasFile('segment_file')) {
            $request->validate(['segment_file.*' => 'file|mimes:mp4,mov,avi,wmv,scorm|max:20480']);
            $segments = $status->segments_path ?? [];
            foreach ($request->file('segment_file') as $file) {
                $segments[] = $file->store('segments', 'public');
            }
            $status->segments_path = $segments;
            $uploaded = true;
        } else if ($request->hasFile('final_video_file')) {
            $request->validate(['final_video_file' => 'file|mimes:mp4,mov,avi,wmv,scorm|max:20480']);
            $videoPath = $request->file('final_video_file')->store('final_videos', 'public');
            $status->final_video_path = $videoPath;
            $uploaded = true;
        }

        if ($uploaded) {
            $status->save();
            ProcessWorkOrderUpdate::dispatch($status);
            return response()->json(['message' => 'File uploaded', 'path' => $videoPath], 200);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }
    public function apiDeleteOrder($id) {
        $order = WorkOrder::find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully']);
    }
    public function apiViewVideo($id) {
        $order = WorkorderStatus::where('video_request_id', $id)->first();
        if ($order && $order->final_video_path) {
            return response()->file(storage_path("app/public/" . $order->final_video_path));
        }
        return response()->json(['error' => 'Video not found'], 404);
    }
    public function apiViewLogo($id) {
        $order = WorkOrder::find($id);
        if ($order && $order->logo_path) {
            return response()->file(storage_path("app/public/" . $order->logo_path));
        }
        return response()->json(['error' => 'Logo not found'], 404);
    }
}
