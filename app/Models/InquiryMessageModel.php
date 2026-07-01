<?php

namespace App\Models;

use CodeIgniter\Model;

class InquiryMessageModel extends Model
{
    protected $table         = 'inquiry_messages';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['inquiry_id', 'sender_type', 'sender_id', 'message', 'photo_path', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    // Helper to get messages for an inquiry with sender details
    public function getMessagesForInquiry(int $inquiryId)
    {
        $messages = $this->where('inquiry_id', $inquiryId)
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ($messages as &$msg) {
            if ($msg['sender_type'] === 'customer') {
                $customerModel = new \App\Models\CustomerModel();
                $cust = $customerModel->find($msg['sender_id']);
                $msg['sender_name'] = $cust ? $cust['name'] : 'Customer';
            } else {
                $userModel = new \App\Models\UserModel();
                $user = $userModel->find($msg['sender_id']);
                if ($user) {
                    // Extract first name and prepend "Parts Admin"
                    $parts = explode(' ', trim($user['name']));
                    $firstName = $parts[0] ?? 'Admin';
                    $msg['sender_name'] = 'Parts Admin ' . $firstName;
                } else {
                    $msg['sender_name'] = 'Parts Admin';
                }
            }
        }

        return $messages;
    }

    // Helper to get only new messages since last loaded message ID
    public function getNewMessagesForInquiry(int $inquiryId, int $lastId)
    {
        $messages = $this->where('inquiry_id', $inquiryId)
            ->where('id >', $lastId)
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ($messages as &$msg) {
            if ($msg['sender_type'] === 'customer') {
                $customerModel = new \App\Models\CustomerModel();
                $cust = $customerModel->find($msg['sender_id']);
                $msg['sender_name'] = $cust ? $cust['name'] : 'Customer';
            } else {
                $userModel = new \App\Models\UserModel();
                $user = $userModel->find($msg['sender_id']);
                if ($user) {
                    $parts = explode(' ', trim($user['name']));
                    $firstName = $parts[0] ?? 'Admin';
                    $msg['sender_name'] = 'Parts Admin ' . $firstName;
                } else {
                    $msg['sender_name'] = 'Parts Admin';
                }
            }
        }

        return $messages;
    }
}
