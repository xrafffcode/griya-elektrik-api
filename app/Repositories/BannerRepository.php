<?php

namespace App\Repositories;

use App\Interfaces\BannerRepositoryInterface;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerRepository implements BannerRepositoryInterface
{
    public function getAllBanners()
    {
        return Banner::all();
    }

    public function getBannerById($id)
    {
        return Banner::find($id);
    }

    public function createBanner($data)
    {
        $banner = new Banner();
        $banner->desktop_image = $data['desktop_image']->store('assets/banners', 'public');
        $banner->mobile_image = $data['mobile_image']->store('assets/banners', 'public');
        $banner->save();

        return $banner;
    }

    public function updateBanner($data, $id)
    {
        $banner = Banner::find($id);

        if ($data['desktop_image']) {
            $banner->desktop_image = $this->updateImage($banner->desktop_image, $data['desktop_image']);
        }
        if ($data['mobile_image']) {
            $banner->mobile_image = $this->updateImage($banner->mobile_image, $data['mobile_image']);
        }

        $banner->save();

        return $banner;
    }

    public function deleteBanner($id)
    {
        return Banner::destroy($id);
    }

    private function updateImage($oldImage, $newImage): string
    {
        if ($oldImage !== $newImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return $newImage->store('assets/banners', 'public');
    }
}
