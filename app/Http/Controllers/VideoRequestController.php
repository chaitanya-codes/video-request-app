<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WorkOrder;
use App\Models\WorkorderStatus;
use App\Models\WorkorderFile;
use App\Models\User;
use App\Models\File;
use App\Models\Segment;
use Illuminate\Support\Facades\Storage;

class VideoRequestController extends Controller {
    
    public function form(Request $request) {
        return view('video_request');
    }

    public function submitForm(Request $request) {
        $data = $request->all();
        if ($request->hasFile('logo_path')) {
            $logoPath = $request->file('logo_path')->store('logos', 'public');
            $data['logo_path'] = $logoPath;
        }
        if ($request->hasFile('files_path')) {
            $request->validate([
                'files_path.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png,gif,svg,mp4|max:20480'
            ]);
            $files = [];
            foreach ($request->file('files_path') as $index => $file) {
                $filePath = $file->store('files', 'public');
                $files[] = $filePath;
            }
            $data['files_path'] = $files;
        }
        return view('video_request_review', [
            'data' => $data,
            'edit' => (isset($data['edit']) && $data['edit'] == true)
        ]);
    }
    
    public function placeOrder(Request $request) {
        $data = $request->all();
        
        $insertData = collect($data)->only([
            'video_name',
            'description',
            'orientation',
            'output_format',
            'avatar_gender',
            'num_modules',
            'logo_path',
            'primary_brand_color',
            'secondary_1_brand_color',
            'secondary_2_brand_color',
            'brand_theme',
            'brand_design_notes',
            'animation_required'
        ])->toArray();
        $insertData['animation_required'] = $data['animation_required'] === 'on' ? 1 : 0;

        $userId = optional(auth()->user())->id ?? rand(0, 5);
        $user = User::find($userId);
        if (!$user) {
            $user = User::create([
                'id' => $userId,
                'name' => 'USER',
                'email' => 'user' . $userId . '@gmail.com',
                'password' => 'pass'
            ]);
            $userId = $user->id;
        }
        $insertData['user_id'] = $userId;
        
        function storeFiles($id, $filesPath) {
            if (isset($filesPath) && $filesPath ?? false) {
                $filesPath = json_decode($filesPath);
                foreach ($filesPath as $filePath) {
                    $files[] = [
                        'video_request_id' => $id,
                        'file_path' => $filePath
                    ];
                }
                File::insert($files);
            }
        }
        if (isset($data['edit']) && $data["edit"] === 'true') {
            // TODO: Verify user is editing their order only
            $orderId = $data["id"];
            $existingOrder = WorkOrder::find($orderId);
            if ($existingOrder) {
                $existingOrder->update($insertData);
                storeFiles($existingOrder->id, $data['files_path'] ?? null);
                return redirect()->route("video-requests.create")->with('success', 'Video request updated successfully!');
            } else {
                return redirect()->route("video-requests.create")->with('error', 'Order not found!');
            }
        }

        $newRow = WorkOrder::create($insertData);

        storeFiles($newRow->id, $data['files_path'] ?? null);

        $statusData = [
            'video_request_id' => $newRow->id,
            'stage' => 1,
            'reason' => null
        ];
        WorkOrderStatus::create($statusData);

        // DB::table('video_requests')->insert($insertData);
        
        return redirect()->route("video-requests.create")->with('success', 'Video request placed successfully!');
    }

    public function viewOrders(Request $request) {
        $userId = optional(auth()->user())->id ?? rand(0, 5);
        $workOrders = WorkOrder::where('user_id', $userId)->paginate(10);
        return view('view_orders', [
            'orders' => $workOrders,
            'userId' => $userId
        ]);
    }

    public function viewOrder(Request $request, $id) {
        $request->validate([
            'id' => 'exists:video_requests,id'
        ]);
        // TODO: Check if user is viewing their order only
        // $userId = auth()->user()->id;

        // sample id
        if ($id) {
            $workOrder = WorkOrder::find($id);
            if ($workOrder) {
                return view('view_order', [
                    'order' => $workOrder,
                    'orderStatus' => WorkorderStatus::where('video_request_id', $id)->first(),
                    'orderFiles' => WorkorderFile::where('video_request_id', $id)->where('is_rejected', false)->get(),
                    'userFiles' => File::where('video_request_id', $id)->get(),
                    'segments' => Segment::where('video_request_id', $id)->where('is_rejected', false)->first() ?? null
                ]);
            } else {
                return redirect()->route('video-requests.create')->with('error', 'Order not found!');
            }
        } else {
            return redirect()->route('video-requests.create')->with('error', 'No order ID provided!');
        }
    }

    public function viewOrderFile(Request $request, $id) {
        $path = $request->query('path');
        $file_type = explode('/', $path)[0];
        $file = null;
        if ($file_type === 'segments') {
            $file = Segment::where('video_request_id', $id)->where('is_rejected', false)->first();
            if ($file) $file = urldecode($path);
        } else {
            $file = WorkorderFile::where('video_request_id', $id)->where('file_type', $file_type)->where('is_rejected', false)->first();
            if ($file) $file = urldecode($file->file_path);
        }
        if ($file) {
            return Storage::disk('public')->response($file);
        } else {
            return redirect()->route('video-requests.create')->with('error', 'File not found!');
        }
    }

    public function reviewOrder(Request $request, $id) {
        $request->validate([
            'id' => 'exists:video_requests,id'
        ]);
        $workOrder = WorkOrder::find($id);
        $workOrderStatus = WorkorderStatus::where('video_request_id', $id)->first();
        $action = $request->input('action');
        $key = $request->input('key');
        $path = $request->input('path');
        $stage = $workOrderStatus->stage;

        if ($action === 'approve') {
            if ($stage < 5) $workOrderStatus->stage = ($stage + 1) % 6;
            $workOrderStatus->reason = null;
            $workOrderStatus->save();
            return redirect()->route('order.view', ['id' => $workOrder->id])->with('success', 'File approved successfully!');
        } else if ($action === 'edit') {
            $reason = $request->input('reason');
            if ($stage == 3) {
                Segment::where('video_request_id', $id)->where('is_rejected', false)->first()->update([
                    'is_rejected' => true
                ]);
            } else {
                WorkorderFile::where('video_request_id', $id)->where('is_rejected', false)->where('file_type', $key)->first()->update([
                    'is_rejected' => true
                ]);
            }
            $workOrderStatus->reason = $reason;
            $workOrderStatus->save();
            return redirect()->route('order.view', ['id' => $workOrder->id])->with('error', 'File rejected!');
        } else return redirect()->route('order.view', ['id' => $workOrder->id])->with('error', 'Invalid action!');
    }
}
