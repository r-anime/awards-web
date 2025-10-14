<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Illuminate\Http\RedirectResponse;

class PublicDashboard extends Page
{
    protected string $view = 'filament.pages.public-dashboard';
    
    protected static ?string $title = 'Dashboard';
    
    protected static ?string $navigationLabel = 'Dashboard';
    
    protected static ?int $navigationSort = -1;
    
    public static function canAccess(): bool
    {
        return auth()->check(); // Only show in navigation if user is logged in
    }
    
    public function mount(): void
    {
        if (!auth()->check()) {
            $this->redirect('login');
        }
        
        // Check for stored redirect URL and redirect to it
        if (session()->has('redirect_after_login')) {
            $redirectUrl = session('redirect_after_login');
            session()->forget('redirect_after_login');
            $this->redirect($redirectUrl);
        }
    }
}
