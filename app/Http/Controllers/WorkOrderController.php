<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkorderStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ProcessWorkOrderUpdate;

class WorkOrderController extends Controller
{
    public function dashboard(Request $request) {
        return view('admin.dashboard', [
            'users' => User::paginate(10),
            'orders' => WorkOrder::paginate(10),
            'orderStatus' => WorkorderStatus::paginate(10)
        ]);
    }

    public function viewOrders(Request $request) {
        $workOrders = WorkOrder::paginate(10);

        return view('admin.view_orders', [
            'orders' => $workOrders
        ]);
    }

    public function viewUsers(Request $request) {
        $users = User::paginate(10);
        return view('admin.view_users', [
            'users' => $users
        ]);
    }

    public function deleteOrder(Request $request, $id) {
        $request->validate([
            'id' => 'exists:video_requests,id'
        ]);
        $workOrder = WorkOrder::find($id);
        if ($workOrder) {
            $workOrder->delete();
            return redirect()->route('admin.orders.index')->with('success', 'Order (ID: ' . $id . ') deleted successfully!');
        } else {
            return redirect()->route('admin.orders.index')->with('error', 'Order (ID: ' . $id . ') not found!');
        }
    }
    public function viewOrder(Request $request, $id) {
        $request->validate([
            'id' => 'exists:video_requests,id'
        ]);
        $workOrder = WorkOrder::find($id);
        $workOrderStatus = WorkorderStatus::where('video_request_id', $id)->first();
        if ($workOrder && $id) {
            return view('admin.view_order', [
                'order' => $workOrder,
                'orderStatus' => $workOrderStatus
            ]);
        } else {
            return redirect()->route('admin.orders.index')->with('error', 'Order (ID: ' . $id . ') not found!');
        }
    }
    public function updateStatus(Request $request, $id) {
        $request->validate([
            'id' => 'exists:video_requests,id'
        ]);
        $workOrder = WorkOrder::find($id);
        $workOrderStatus = WorkorderStatus::where('video_request_id', $id)->first();
        $videoPath = null;
        $uploaded = false;

        if ($workOrder) {
            if ($request->hasFile('script_file')) {
                $request->validate([
                    'script_file' => 'file|mimes:pdf,doc,docx|max:10240'
                ]);
                $videoPath = $request->file('script_file')->store('scripts', 'public');
                $workOrderStatus->script_path = $videoPath;
                $uploaded = true;
            } else if ($request->hasFile('voiceover_file')) {
                $request->validate([
                    'voiceover_file' => 'file|mimes:mp3,wav|max:10240'
                ]);
                $videoPath = $request->file('voiceover_file')->store('voiceovers', 'public');
                $workOrderStatus->voiceover_path = $videoPath;
                $uploaded = true;
            } else if ($request->hasFile('segment_file')) {
                $request->validate([
                    'segment_file.*' => 'file|mimes:mp4,mov,avi,wmv,scorm|max:20480'
                ]);
                $segments = $workOrderStatus->segments_path ?? [];
                foreach ($request->file('segment_file') as $index => $file) {
                    $videoPath = $file->store('segments', 'public');
                    $segments[] = $videoPath;
                }
                $workOrderStatus->segments_path = $segments;
                $uploaded = true;
            } else if ($request->hasFile('final_video_file')) {
                $request->validate([
                    'final_video_file' => 'file|mimes:mp4,mov,avi,wmv,scorm|max:20480'
                ]);
                $videoPath = $request->file('final_video_file')->store('final_videos', 'public');
                $workOrderStatus->final_video_path = $videoPath;
                $uploaded = true;
            }
            if ($uploaded) {
                $workOrderStatus->save();
                ProcessWorkOrderUpdate::dispatch($workOrderStatus);
                return redirect()->route('admin.orders.view', ['id' => $workOrder->id])->with('success', substr(ucfirst(explode("/", $videoPath)[0]), 0, -1) . ' uploaded successfully!');
            }
            // notes -> $request->input('notes');
            return redirect()->route('admin.orders.view', ['id' => $workOrder->id])->with('error', 'File not uploaded!');
        } else {
            return redirect()->route('admin.orders.view', ['id' => $workOrder->id])->with('error', 'Order not found!');
        }
    }

    public function viewVideo(Request $request, $id) {
        $request->validate([
            'id' => 'exists:workorder_status,id'
        ]);
        $workOrderStatus = WorkorderStatus::where('video_request_id', $id)->first();
        if ($workOrderStatus && $workOrderStatus->final_video_path) {
            return Storage::disk('public')->response($workOrderStatus->video_path);
        } else {
            return redirect()->route('admin.orders.index')->with('error', 'Video not found for this order!');
        }
    }
    public function viewLogo(Request $request, $id) {
        $request->validate([
            'id' => 'exists:workorder_status,id'
        ]);
        $workOrder = WorkOrder::find($id);
        if ($workOrder && $workOrder->logo_path) {
            return Storage::disk('public')->response($workOrder->logo_path);
        } else {
            return redirect()->route('admin.orders.index')->with('error', 'Logo not found for this order!');
        }
    }
    public function viewFile(Request $request, $id) {
        $request->validate([
            'id' => 'exists:workorder_status,id'
        ]);
        $index = $request->input('index');
        $workOrder = WorkOrder::find($id);
        if ($workOrder && $workOrder->files_path) {
            $files = json_decode($workOrder->files_path);
            return Storage::disk('public')->response($files[$index]);
        } else {
            return redirect()->route('admin.orders.index')->with('error', 'Files not found for this order!');
        }
    }
}
