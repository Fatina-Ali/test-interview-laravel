<?php

function apiResponse($data = null, $message = null, $status = 200)
{
    return response()->json([
        'data' => $data,
        'message' => $message,
        'code' => $status
    ], $status);
}


function storager($directory,$file) {
    $filename = \uniqid(\Illuminate\Support\Str::slug($directory)). '.' . $file->getClientOriginalExtension();
    $filesystem = \Illuminate\Support\Facades\Storage::disk('local');
    $link = $directory. '/'. $filename;
    $filesystem->put($link, $file->get());
    $extension = $file->getClientOriginalExtension(); // IMPORTANT TO SUFFIX THE LINK FOR FANCYBOX
    return \base64_encode(\openssl_encrypt($link.'#'.time(),'AES-128-ECB','iA2#4s%')).'.'.$extension;
}


function deStorager($encryptedLink) {
    $link = \substr($encryptedLink,0,\strrpos($encryptedLink,'.'));
    $link = \explode('#',\openssl_decrypt(\base64_decode($link),'AES-128-ECB','iA2#4s%'))[0];
    /** @var Illuminate\Filesystem\FilesystemAdapter */
    $filesystem = \Illuminate\Support\Facades\Storage::disk('local');
    return $filesystem->response($link);
}
