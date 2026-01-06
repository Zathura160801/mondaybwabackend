<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Storage;

class AuthService
{
    private $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(array $data)
    {
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $data['photo'] = $this->uploadPhoto($data['photo']);
        }

        return $this->authRepository->register($data);
    }

    public function login(array $data)
    {
        return $this->authRepository->login($data);
    }

    public function tokenLogin(array $data)
    {
        return $this->authRepository->tokenLogin($data);
    }

    public function uploadPhoto(UploadedFile $photo)
    {
        return $photo->store('users', 'public');
    }

    public function deletePhoto(string $photoPath)
    {
        $relativePath = 'users/' . basename($photoPath);
        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}