<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class SidebarItem extends Component
{
    public string $label;

    public string $icon;

    public ?string $route;

    public ?string $permission;

    public ?string $role;

    public array $children;

    public function __construct(
        string $label,
        string $icon,
        ?string $route = null,
        ?string $permission = null,
        ?string $role = null,
        array $children = []
    ) {
        $this->label = $label;
        $this->icon = $icon;
        $this->route = $route;
        $this->permission = $permission;
        $this->role = $role;
        $this->children = $children;
    }

    public function authorized(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($this->role && ! $user->hasRole($this->role)) {
            return false;
        }

        if ($this->permission && ! $user->can($this->permission)) {
            return false;
        }

        return true;
    }

    public function isActive(): bool
    {
        if (! $this->route) {
            return false;
        }

        return request()->routeIs($this->route);
    }

    public function render()
    {
        return view('components.sidebar-item');
    }
}
