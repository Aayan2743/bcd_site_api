<?php
namespace App\Http\Controllers;

use App\Mail\ContactEnquiryMail;
use App\Models\ContactEnquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactEnquiryController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:30',
            'email'            => 'required|email|max:255',
            'service_interest' => 'required|string|max:100',
            'message'          => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $enquiry = ContactEnquiry::create([
            'name'             => $request->name,
            'phone'            => $request->phone,
            'email'            => $request->email,
            'service_interest' => $request->service_interest,
            'message'          => $request->message,
        ]);

        Mail::to(env('CONTACT_EMAIL'))->send(
            new ContactEnquiryMail($enquiry)
        );

        return response()->json([
            'success' => true,
            'message' => 'Your enquiry has been submitted successfully.',
            'data'    => $enquiry,
        ], 201);
    }
}
