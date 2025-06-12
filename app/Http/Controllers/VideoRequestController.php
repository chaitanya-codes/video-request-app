<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WorkOrder;
use App\Models\WorkorderStatus;
use App\Models\User;
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

        if (isset($data['edit']) && $data["edit"] === 'true') {
            // TODO: Verify user is editing their order only
            $orderId = $data["id"];
            $existingOrder = WorkOrder::find($orderId);
            if ($existingOrder) {
                $existingOrder->update($insertData);
                return redirect()->route("video-requests.create")->with('success', 'Video request updated successfully!');
            } else {
                return redirect()->route("video-requests.create")->with('error', 'Order not found!');
            }
        }

        $newRow = WorkOrder::create($insertData);

        $approved = json_encode([
            'script' => false,
            'voiceover' => false,
            'segment' => false,
            'final_review' => false,
        ]);
        $statusData = [
            'video_request_id' => $newRow->id,
            'stage' => 1,
            'script_path' => null,
            'voiceover_path' => null,
            'segments_path' => null,
            'final_video_path' => null,
            'approved' => $approved,
            'notes' => null,
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
                    'orderStatus' => WorkorderStatus::where('video_request_id', $id)->first()
                ]);
            } else {
                return redirect()->route('video-requests.create')->with('error', 'Order not found!');
            }
        } else {
            return redirect()->route('video-requests.create')->with('error', 'No order ID provided!');
        }
    }

    public function viewOrderFile(Request $request, $id) {
        $workOrderStatus = WorkorderStatus::where('video_request_id', $id)->first();
        $file = urldecode($request->query('path'));
        if (explode('/', $file)[0] === 'segments') {
            return Storage::disk('public')->response($file);
        } else if ($workOrderStatus && $workOrderStatus->$file) {
            return Storage::disk('public')->response($workOrderStatus->$file);
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
        $approved = json_decode($workOrderStatus->approved);
        $stage = $workOrderStatus->stage;

        if ($action === 'approve') {
            $approved->$key = true;
            $workOrderStatus->approved = json_encode($approved);
            if ($stage < 5) $workOrderStatus->stage = ($stage + 1) % 6;
            $workOrderStatus->save();
            return redirect()->route('order.view', ['id' => $workOrder->id])->with('success', 'File approved successfully!');
        } else if ($action === 'edit') {
            $approved->$key = false;
            $reason = $request->input('reason');
            $approved->reason = $reason;
            $workOrderStatus->approved = json_encode($approved);
            $workOrderStatus->$path = null;
            $workOrderStatus->save();
            return redirect()->route('order.view', ['id' => $workOrder->id])->with('error', 'File rejected!');
        } else return redirect()->route('order.view', ['id' => $workOrder->id])->with('error', 'Invalid action!');
    }
}
