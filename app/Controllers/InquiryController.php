<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InquiryModel;
use App\Models\InquiryMessageModel;
use App\Models\SalesOrderModel;
use App\Models\AuditLogModel;

class InquiryController extends BaseController
{
    protected InquiryModel        $inquiryModel;
    protected InquiryMessageModel $messageModel;
    protected AuditLogModel       $audit;

    public function __construct()
    {
        $this->inquiryModel = new InquiryModel();
        $this->messageModel = new InquiryMessageModel();
        $this->audit        = new AuditLogModel();
    }

    // ==========================================
    // CUSTOMER PORTAL ACTIONS
    // ==========================================

    public function customerIndex()
    {
        $customerId = session()->get('customer_id');
        $inquiries = $this->inquiryModel->getInquiriesWithDetails($customerId);

        // Check if there is an active open inquiry
        $hasOpenInquiry = false;
        foreach ($inquiries as $inq) {
            if ($inq['status'] === 'open') {
                $hasOpenInquiry = true;
                break;
            }
        }

        $data = [
            'pageTitle'      => 'Support Inquiries',
            'inquiries'      => $inquiries,
            'hasOpenInquiry' => $hasOpenInquiry
        ];

        return view('customer/inquiries/index', $data);
    }

    public function customerCreate()
    {
        $customerId = session()->get('customer_id');

        // Double check open inquiries
        $openInquiry = $this->inquiryModel->where('customer_id', $customerId)
                                           ->where('status', 'open')
                                           ->first();
        if ($openInquiry) {
            return redirect()->back()->with('error', 'You cannot create a new inquiry if you still have an open inquiry.');
        }

        $rules = [
            'message' => 'required_without[photo]|max_length[1000]',
            'photo'   => 'permit_empty|is_image[photo]|max_size[photo,5120]' // 5MB limit
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $messageText = trim($this->request->getPost('message') ?? '');
        $photoFile   = $this->request->getFile('photo');

        $photoPath = null;
        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            $photoPath = $this->compressImage($photoFile, FCPATH . 'uploads/inquiries');
        }

        if (empty($messageText) && !$photoPath) {
            return redirect()->back()->with('error', 'Please type a message or upload a photo.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $inquiryId = $this->inquiryModel->insert([
            'customer_id'    => $customerId,
            'status'         => 'open',
            'sales_order_id' => null
        ]);

        $this->messageModel->insert([
            'inquiry_id'  => $inquiryId,
            'sender_type' => 'customer',
            'sender_id'   => $customerId,
            'message'     => !empty($messageText) ? $messageText : null,
            'photo_path'  => $photoPath
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to create inquiry. Database error.');
        }

        return redirect()->to("/customer/inquiries/{$inquiryId}")->with('success', 'Inquiry created successfully.');
    }

    public function customerShow(int $id)
    {
        $customerId = session()->get('customer_id');
        $inquiry = $this->inquiryModel->find($id);

        if (!$inquiry || (int)$inquiry['customer_id'] !== (int)$customerId) {
            return redirect()->to('/customer/inquiries')->with('error', 'Inquiry not found.');
        }

        // Get linked Sales Order details
        $so = null;
        if ($inquiry['sales_order_id']) {
            $soModel = new SalesOrderModel();
            $so = $soModel->find($inquiry['sales_order_id']);
        }

        $messages = $this->messageModel->getMessagesForInquiry($id);

        $data = [
            'pageTitle' => 'Inquiry details',
            'inquiry'   => $inquiry,
            'messages'  => $messages,
            'so'        => $so
        ];

        return view('customer/inquiries/show', $data);
    }

    public function customerMessage(int $id)
    {
        $customerId = session()->get('customer_id');
        $inquiry = $this->inquiryModel->find($id);

        if (!$inquiry || (int)$inquiry['customer_id'] !== (int)$customerId) {
            return redirect()->to('/customer/inquiries')->with('error', 'Inquiry not found.');
        }

        if ($inquiry['status'] === 'closed') {
            return redirect()->back()->with('error', 'This inquiry has already been closed. Replies are disabled.');
        }

        $rules = [
            'message' => 'required_without[photo]|max_length[1000]',
            'photo'   => 'permit_empty|is_image[photo]|max_size[photo,5120]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $messageText = trim($this->request->getPost('message') ?? '');
        $photoFile   = $this->request->getFile('photo');

        $photoPath = null;
        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            $photoPath = $this->compressImage($photoFile, FCPATH . 'uploads/inquiries');
        }

        if (empty($messageText) && !$photoPath) {
            return redirect()->back()->with('error', 'Please type a message or upload a photo.');
        }

        $this->messageModel->insert([
            'inquiry_id'  => $id,
            'sender_type' => 'customer',
            'sender_id'   => $customerId,
            'message'     => !empty($messageText) ? $messageText : null,
            'photo_path'  => $photoPath
        ]);

        return redirect()->to("/customer/inquiries/{$id}")->with('success', 'Message sent.');
    }

    // ==========================================
    // ADMIN ACTIONS
    // ==========================================

    public function adminIndex()
    {
        $inquiries = $this->inquiryModel->getInquiriesWithDetails();

        foreach ($inquiries as &$inq) {
            $latestMsg = $this->messageModel->where('inquiry_id', $inq['id'])
                                            ->orderBy('id', 'DESC')
                                            ->first();
            if ($latestMsg) {
                $inq['latest_message_id']  = (int)$latestMsg['id'];
                $inq['latest_sender_type'] = $latestMsg['sender_type'];
                $inq['needs_response']     = ($inq['status'] === 'open' && $latestMsg['sender_type'] === 'customer');
            } else {
                $inq['latest_message_id']  = 0;
                $inq['latest_sender_type'] = null;
                $inq['needs_response']     = false;
            }
        }

        $data = [
            'pageTitle'  => 'Customer Inquiries',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Inquiries', null]],
            'inquiries'  => $inquiries
        ];

        return view('layouts/main', $data + ['content' => view('admin/inquiries/index', $data)]);
    }

    public function adminShow(int $id)
    {
        $inquiry = $this->inquiryModel->find($id);
        if (!$inquiry) {
            return redirect()->to('/admin/inquiries')->with('error', 'Inquiry not found.');
        }

        // Get Customer details
        $customerModel = new \App\Models\CustomerModel();
        $customer = $customerModel->find($inquiry['customer_id']);

        // Get linked Sales Order details
        $so = null;
        if ($inquiry['sales_order_id']) {
            $soModel = new SalesOrderModel();
            $so = $soModel->find($inquiry['sales_order_id']);
        }

        // Get unassigned Sales Orders for this customer
        $db = \Config\Database::connect();
        $unassignedSos = $db->query("
            SELECT so.id, so.so_number, so.amount, so.status, so.created_at
            FROM sales_orders so
            LEFT JOIN inquiries i ON i.sales_order_id = so.id
            WHERE so.customer_id = ? AND i.id IS NULL
        ", [$inquiry['customer_id']])->getResultArray();

        $messages = $this->messageModel->getMessagesForInquiry($id);

        $data = [
            'pageTitle'     => 'Inquiry #' . $inquiry['id'],
            'breadcrumb'    => [['HW Trucks MNL', base_url('dashboard')], ['Inquiries', base_url('admin/inquiries')], ['Detail', null]],
            'inquiry'       => $inquiry,
            'customer'      => $customer,
            'messages'      => $messages,
            'so'            => $so,
            'unassignedSos' => $unassignedSos
        ];

        return view('layouts/main', $data + ['content' => view('admin/inquiries/show', $data)]);
    }

    public function adminMessage(int $id)
    {
        $inquiry = $this->inquiryModel->find($id);
        if (!$inquiry) {
            return redirect()->to('/admin/inquiries')->with('error', 'Inquiry not found.');
        }

        if ($inquiry['status'] === 'closed') {
            return redirect()->back()->with('error', 'This inquiry has already been closed. Replies are disabled.');
        }

        $rules = [
            'message' => 'required_without[photo]|max_length[1000]',
            'photo'   => 'permit_empty|is_image[photo]|max_size[photo,5120]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $messageText = trim($this->request->getPost('message') ?? '');
        $photoFile   = $this->request->getFile('photo');

        $photoPath = null;
        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            $photoPath = $this->compressImage($photoFile, FCPATH . 'uploads/inquiries');
        }

        if (empty($messageText) && !$photoPath) {
            return redirect()->back()->with('error', 'Please type a message or upload a photo.');
        }

        $this->messageModel->insert([
            'inquiry_id'  => $id,
            'sender_type' => 'user',
            'sender_id'   => session()->get('user_id'),
            'message'     => !empty($messageText) ? $messageText : null,
            'photo_path'  => $photoPath
        ]);

        return redirect()->to("/admin/inquiries/{$id}")->with('success', 'Reply sent.');
    }

    public function adminClose(int $id)
    {
        $inquiry = $this->inquiryModel->find($id);
        if (!$inquiry) {
            return redirect()->to('/admin/inquiries')->with('error', 'Inquiry not found.');
        }

        $this->inquiryModel->update($id, ['status' => 'closed']);
        $this->audit->log('inquiries', 'close', $id, "Closed Support Inquiry #{$id}");

        return redirect()->to("/admin/inquiries/{$id}")->with('success', 'Inquiry has been successfully closed.');
    }

    public function adminAssignSo(int $id)
    {
        $inquiry = $this->inquiryModel->find($id);
        if (!$inquiry) {
            return redirect()->to('/admin/inquiries')->with('error', 'Inquiry not found.');
        }

        $soId = $this->request->getPost('sales_order_id');
        if (empty($soId)) {
            return redirect()->back()->with('error', 'Please select a Sales Order.');
        }

        // Validate SO belongs to customer and is not assigned elsewhere
        $soModel = new SalesOrderModel();
        $so = $soModel->find($soId);

        if (!$so || (int)$so['customer_id'] !== (int)$inquiry['customer_id']) {
            return redirect()->back()->with('error', 'Invalid Sales Order selected.');
        }

        $alreadyLinked = $this->inquiryModel->where('sales_order_id', $soId)->first();
        if ($alreadyLinked) {
            return redirect()->back()->with('error', 'The selected Sales Order is already assigned to another inquiry.');
        }

        $this->inquiryModel->update($id, ['sales_order_id' => $soId]);
        $this->audit->log('inquiries', 'assign_so', $id, "Assigned Sales Order #{$soId} ({$so['so_number']}) to Inquiry #{$id}");

        return redirect()->to("/admin/inquiries/{$id}")->with('success', 'Sales Order successfully assigned.');
    }

    // ==========================================
    // ==========================================
    // AJAX CHATBOX ENDPOINTS
    // ==========================================

    private function isSpamBlocked(int $inquiryId): bool
    {
        // Retrieve the last 5 messages in this inquiry
        $lastMessages = $this->messageModel->where('inquiry_id', $inquiryId)
                                           ->orderBy('id', 'DESC')
                                           ->limit(5)
                                           ->findAll();

        if (count($lastMessages) >= 5) {
            $customerMsgCount = 0;
            foreach ($lastMessages as $msg) {
                if ($msg['sender_type'] === 'customer') {
                    $customerMsgCount++;
                } else {
                    break; // Found an admin response, so not 5 in a row
                }
            }
            if ($customerMsgCount >= 5) {
                return true;
            }
        }
        return false;
    }

    public function adminPollList()
    {
        $inquiries = $this->inquiryModel->getInquiriesWithDetails();

        foreach ($inquiries as &$inq) {
            $latestMsg = $this->messageModel->where('inquiry_id', $inq['id'])
                                            ->orderBy('id', 'DESC')
                                            ->first();
            if ($latestMsg) {
                $inq['latest_message_id']   = $latestMsg['id'];
                $inq['latest_sender_type']  = $latestMsg['sender_type'];
                $inq['latest_message']      = !empty($latestMsg['message']) ? $latestMsg['message'] : '[Attachment]';
                $inq['latest_message_time'] = $latestMsg['created_at'];

                if ($latestMsg['sender_type'] === 'customer') {
                    $customerModel = new \App\Models\CustomerModel();
                    $cust = $customerModel->find($latestMsg['sender_id']);
                    $inq['latest_sender_name'] = $cust ? $cust['name'] : 'Customer';
                } else {
                    $userModel = new \App\Models\UserModel();
                    $user = $userModel->find($latestMsg['sender_id']);
                    if ($user) {
                        $parts = explode(' ', trim($user['name']));
                        $firstName = $parts[0] ?? 'Admin';
                        $inq['latest_sender_name'] = 'Parts Admin ' . $firstName;
                    } else {
                        $inq['latest_sender_name'] = 'Parts Admin';
                    }
                }
            } else {
                $inq['latest_message_id']   = 0;
                $inq['latest_sender_type']  = null;
                $inq['latest_message']      = '';
                $inq['latest_message_time'] = null;
                $inq['latest_sender_name']  = '';
            }
        }

        return $this->response->setJSON([
            'status'    => 'success',
            'inquiries' => $inquiries
        ]);
    }

    public function customerUpdates(int $id)
    {
        $customerId = session()->get('customer_id');
        $inquiry = $this->inquiryModel->find($id);

        if (!$inquiry || (int)$inquiry['customer_id'] !== (int)$customerId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized.'])->setStatusCode(403);
        }

        $lastId = (int)$this->request->getGet('last_id');
        $newMessages = $this->messageModel->getNewMessagesForInquiry($id, $lastId);

        $soDetails = null;
        if ($inquiry['sales_order_id']) {
            $soModel = new SalesOrderModel();
            $so = $soModel->find($inquiry['sales_order_id']);
            if ($so) {
                $soDetails = [
                    'id'        => $so['id'],
                    'so_number' => $so['so_number'],
                    'amount'    => number_format($so['amount'], 2),
                    'status'    => $so['status']
                ];
            }
        }

        return $this->response->setJSON([
            'status'         => 'success',
            'inquiry_status' => $inquiry['status'],
            'messages'       => $newMessages,
            'so'             => $soDetails,
            'spam_blocked'   => $this->isSpamBlocked($id)
        ]);
    }

    public function customerMessageAjax(int $id)
    {
        $customerId = session()->get('customer_id');
        $inquiry = $this->inquiryModel->find($id);

        if (!$inquiry || (int)$inquiry['customer_id'] !== (int)$customerId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized.'])->setStatusCode(403);
        }

        if ($inquiry['status'] === 'closed') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'This inquiry is closed. Replies are disabled.'])->setStatusCode(400);
        }

        // Anti-spam check
        if ($this->isSpamBlocked($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Please wait for a staff member to respond before sending more messages.'])->setStatusCode(400);
        }

        $rules = [
            'message' => 'required_without[photo]|max_length[1000]',
            'photo'   => 'permit_empty|is_image[photo]|max_size[photo,5120]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['status' => 'error', 'message' => implode(' ', $this->validator->getErrors())])->setStatusCode(400);
        }

        $messageText = trim($this->request->getPost('message') ?? '');
        $photoFile   = $this->request->getFile('photo');

        $photoPath = null;
        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            $photoPath = $this->compressImage($photoFile, FCPATH . 'uploads/inquiries');
        }

        if (empty($messageText) && !$photoPath) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Please type a message or upload a photo.'])->setStatusCode(400);
        }

        $this->messageModel->insert([
            'inquiry_id'  => $id,
            'sender_type' => 'customer',
            'sender_id'   => $customerId,
            'message'     => !empty($messageText) ? $messageText : null,
            'photo_path'  => $photoPath
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Message sent successfully.'
        ]);
    }

    public function adminUpdates(int $id)
    {
        $inquiry = $this->inquiryModel->find($id);
        if (!$inquiry) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Inquiry not found.'])->setStatusCode(404);
        }

        $lastId = (int)$this->request->getGet('last_id');
        $newMessages = $this->messageModel->getNewMessagesForInquiry($id, $lastId);

        $soDetails = null;
        if ($inquiry['sales_order_id']) {
            $soModel = new SalesOrderModel();
            $so = $soModel->find($inquiry['sales_order_id']);
            if ($so) {
                $soDetails = [
                    'id'        => $so['id'],
                    'so_number' => $so['so_number'],
                    'amount'    => number_format($so['amount'], 2),
                    'status'    => $so['status']
                ];
            }
        }

        return $this->response->setJSON([
            'status'         => 'success',
            'inquiry_status' => $inquiry['status'],
            'messages'       => $newMessages,
            'so'             => $soDetails
        ]);
    }

    public function adminMessageAjax(int $id)
    {
        $inquiry = $this->inquiryModel->find($id);
        if (!$inquiry) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Inquiry not found.'])->setStatusCode(404);
        }

        if ($inquiry['status'] === 'closed') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'This inquiry is closed. Replies are disabled.'])->setStatusCode(400);
        }

        $rules = [
            'message' => 'required_without[photo]|max_length[1000]',
            'photo'   => 'permit_empty|is_image[photo]|max_size[photo,5120]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['status' => 'error', 'message' => implode(' ', $this->validator->getErrors())])->setStatusCode(400);
        }

        $messageText = trim($this->request->getPost('message') ?? '');
        $photoFile   = $this->request->getFile('photo');

        $photoPath = null;
        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            $photoPath = $this->compressImage($photoFile, FCPATH . 'uploads/inquiries');
        }

        if (empty($messageText) && !$photoPath) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Please type a message or upload a photo.'])->setStatusCode(400);
        }

        $this->messageModel->insert([
            'inquiry_id'  => $id,
            'sender_type' => 'user',
            'sender_id'   => session()->get('user_id'),
            'message'     => !empty($messageText) ? $messageText : null,
            'photo_path'  => $photoPath
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Reply sent successfully.'
        ]);
    }

    // ==========================================
    // CORE IMAGE COMPRESSION UTILITY (GD BASED)
    // ==========================================

    private function compressImage($file, $destinationDirectory)
    {
        if (!$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $newName = $file->getRandomName();
        if (!is_dir($destinationDirectory)) {
            mkdir($destinationDirectory, 0777, true);
        }

        $tempPath = $file->getTempName();
        
        // Get dimensions & image type
        list($width, $height, $type) = getimagesize($tempPath);
        if (!$width || !$height) {
            // Not a readable image dimensions, fall back to standard move
            $file->move($destinationDirectory, $newName);
            return 'uploads/inquiries/' . $newName;
        }

        $maxDim = 1200;
        if ($width > $maxDim || $height > $maxDim) {
            $ratio = $width / $height;
            if ($ratio > 1) {
                $newWidth  = $maxDim;
                $newHeight = round($maxDim / $ratio);
            } else {
                $newWidth  = round($maxDim * $ratio);
                $newHeight = $maxDim;
            }
        } else {
            $newWidth  = $width;
            $newHeight = $height;
        }

        // Initialize source resource
        $src = null;
        switch ($type) {
            case IMAGETYPE_GIF:
                $src = imagecreatefromgif($tempPath);
                break;
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($tempPath);
                break;
            case IMAGETYPE_PNG:
                $src = imagecreatefrompng($tempPath);
                break;
        }

        if ($src) {
            $dst = imagecreatetruecolor($newWidth, $newHeight);
            
            // Standard background for JPG conversion (white background)
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);
            
            // Resize image
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // Output as JPEG with quality 70 to destination path
            $pathInfo    = pathinfo($newName);
            $newNameJpg  = $pathInfo['filename'] . '.jpg';
            $destPathJpg = $destinationDirectory . '/' . $newNameJpg;
            
            imagejpeg($dst, $destPathJpg, 70);
            
            imagedestroy($src);
            imagedestroy($dst);
            
            return 'uploads/inquiries/' . $newNameJpg;
        } else {
            // Fallback
            $file->move($destinationDirectory, $newName);
            return 'uploads/inquiries/' . $newName;
        }
    }
}
