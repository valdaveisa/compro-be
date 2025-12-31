<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $google2fa = new Google2FA();
        
        // Setup Mode: if secret is empty
        if (empty($user->google2fa_secret)) {
             // Generate a new secret
             if (!session('google2fa_secret_setup')) {
                 $secret = $google2fa->generateSecretKey();
                 session(['google2fa_secret_setup' => $secret]);
             } else {
                 $secret = session('google2fa_secret_setup');
             }
             
             // Generate QR Code Image
             $QR_Image = $google2fa->getQRCodeInline(
                config('app.name'),
                $user->email,
                $secret
            );
            
            return view('auth.2fa', [
                'mode' => 'setup',
                'QR_Image' => $QR_Image,
                'secret' => $secret
            ]);
        }
        
        // Verify Mode
        return view('auth.2fa', ['mode' => 'verify']);
    }
    
    public function verify(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required',
        ]);
        
        $user = Auth::user();
        $google2fa = new Google2FA();
        
        if (empty($user->google2fa_secret)) {
            // Verifying setup
            $secret = session('google2fa_secret_setup');
            if (!$secret) {
                return redirect()->route('2fa.index')->withErrors(['error' => 'Session expired, please try again.']);
            }
            
            $valid = $google2fa->verifyKey($secret, $request->one_time_password);
            
            if ($valid) {
                 $user->google2fa_secret = $secret;
                 $user->save();
                 session()->forget('google2fa_secret_setup');
                 session(['2fa_verified' => true]);
                 return redirect()->intended('/dashboard');
            }
        } else {
            // Verifying login
            $valid = $google2fa->verifyKey($user->google2fa_secret, $request->one_time_password);
            
            if ($valid) {
                session(['2fa_verified' => true]);
                return redirect()->intended('/dashboard');
            }
        }
        
        return back()->withErrors(['one_time_password' => 'Invalid Authenticator Code']);
    }
}
