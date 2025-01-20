<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginClientRequest;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClientController extends Controller
{

    public function deleteAccount()
    {
        return view('backend.client.delete_account');
    }

    public function destroy(Request $request)
    {
        /** @var \App\Models\User */
        $user = Auth::guard('web')->user();
        $client = $user->client;
        $client->user_id = null;
        $client->save();
        $user->delete();

        // Redirect to a confirmation page with a success message
        return redirect()->route('delete_account.deleted.confirmation')->with('success', 'Your account has been deleted successfully.');
    }


}
