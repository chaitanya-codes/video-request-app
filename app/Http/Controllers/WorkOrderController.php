<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkorderStatus;
use App\Models\WorkorderFile;
use App\Models\File;
use App\Models\Segment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ProcessWorkOrderUpdate;
use Smalot\PdfParser\Parser;

class WorkOrderController extends Controller
{
    public function dashboard(Request $request) {
        $workOrders = WorkOrder::paginate(10);
        $orderIds = $workOrders->pluck('id');
        $orderStatus = WorkorderStatus::whereIn('video_request_id', $orderIds)->get()->keyBy('video_request_id');

        return view('admin.dashboard', [
            'users' => User::paginate(10),
            'orders' => $workOrders,
            'orderStatus' => $orderStatus
        ]);
    }

    public function viewOrders(Request $request) {
        $workOrders = WorkOrder::paginate(10);
        $orderIds = $workOrders->pluck('id');
        $orderStatus = WorkorderStatus::whereIn('video_request_id', $orderIds)->get()->keyBy('video_request_id');

        return view('admin.view_orders', [
            'orders' => $workOrders,
            'orderStatus' => $orderStatus,
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
        $segments = Segment::where('video_request_id', $id)->where('is_rejected', false)->first() ?? null;

        $chunks = [];
        if ($segments) {
            $files = json_decode($segments->files_path);
            foreach ($files as $index => $file) {
                if ($segments) {
                    $parser = new Parser();
                    $path = storage_path('app/public/' . $file);
                    // dd($path);
                    $pdf = $parser->parseFile($path);
                    $text = $pdf->getText();

                    $lines = preg_split("/\r\n|\n|\r/", $text);
                    $chunks = array_chunk($lines, 4);
                } else {
                    $chunks = [];
                }
            }
        }
        if ($workOrder && $id) {
            return view('admin.view_order', [
                'order' => $workOrder,
                'orderStatus' => $workOrderStatus,
                'orderFiles' => WorkorderFile::where('video_request_id', $id)->where('is_rejected', false)->get(),
                'userFiles' => File::where('video_request_id', $id)->get(),
                'segments' => $segments,
                'chunks' => $chunks
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
        $path = null;
        $uploaded = false;
        $type = null;

        if ($workOrder) {
            if ($request->hasFile('script_file')) {
                $request->validate([
                    'script_file' => 'file|mimes:pdf,doc,docx|max:10240'
                ]);
                $path = $request->file('script_file')->store('scripts', 'public');
                $type = 'script';
                $uploaded = true;
            } else if ($request->hasFile('voiceover_file')) {
                $request->validate([
                    'voiceover_file' => 'file|mimes:mp3,wav|max:10240'
                ]);
                $path = $request->file('voiceover_file')->store('voiceovers', 'public');
                $type = 'voiceover';
                $uploaded = true;
            } else if ($request->hasFile('segment_file')) {
                $request->validate([
                    'segment_file.*' => 'file|mimes:pdf|max:20480'
                ]);
                $path = [];
                foreach ($request->file('segment_file') as $index => $file) {
                    $path[] = $file->store('segments', 'public');
                }
                Segment::create([
                    'video_request_id' => $workOrder->id,
                    'files_path' => json_encode($path)
                ]);
                return redirect()->route('admin.orders.view', ['id' => $workOrder->id])->with('success', 'Segments uploaded successfully!');
            } else if ($request->hasFile('final_video_file')) {
                $request->validate([
                    'final_video_file' => 'file|mimes:mp4,mov,avi,wmv,scorm|max:20480'
                ]);
                $path = $request->file('final_video_file')->store('final_videos', 'public');
                $workOrderStatus->final_video_path = $path;
                $type = 'final_video';
                $uploaded = true;
            }
            if ($uploaded) {
                $workorderFiles = WorkorderFile::create([
                    'video_request_id' => $workOrder->id,
                    'file_path' => $path,
                    'file_type' => $type
                ]);
                // TODO: Upload file in background
                // ProcessWorkOrderUpdate::dispatch($request->file('final_video_file'));
                return redirect()->route('admin.orders.view', ['id' => $workOrder->id])->with('success', substr(ucfirst(explode("/", $path)[0]), 0, -1) . ' uploaded successfully!');
            }
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
        $files = File::where('video_request_id', $id)->get();
        if ($files && $files->isNotEmpty()) {
            return Storage::disk('public')->response($files[$index]->file_path);
        } else {
            return redirect()->route('admin.orders.index')->with('error', 'Files not found for this order!');
        }
    }
}
