<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class StockService
{
    public function restock(Menu $menu, int $amount, User $actor): Menu
    {
        if ($amount < 1) {
            throw new InvalidArgumentException('Jumlah penambahan stok harus berupa bilangan bulat positif (minimal 1).');
        }

        return DB::transaction(function () use ($menu, $amount, $actor) {
            $lockedMenu = Menu::with('coffeeShop')->lockForUpdate()->findOrFail($menu->id);

            if ($lockedMenu->coffeeShop->owner_user_id !== $actor->id) {
                throw new AccessDeniedHttpException('Anda hanya dapat mengisi stok menu pada kedai Anda sendiri.');
            }

            $lockedMenu->increment('stock', $amount);

            // Jika sebelumnya tidak tersedia karena stok 0, aktifkan ketersediaan jika menu aktif
            if ($lockedMenu->stock > 0 && $lockedMenu->is_active) {
                $lockedMenu->is_available = true;
                $lockedMenu->save();
            }

            return $lockedMenu;
        });
    }
}
