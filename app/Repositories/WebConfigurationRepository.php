<?php

namespace App\Repositories;

use App\Interfaces\WebConfigurationRepositoryInterface;
use App\Models\WebConfiguration;
use Illuminate\Support\Facades\Storage;

class WebConfigurationRepository implements WebConfigurationRepositoryInterface
{
    public function getWebConfiguration()
    {
        return WebConfiguration::first();
    }

    public function updateWebConfiguration(array $data)
    {
        $webConfiguration = WebConfiguration::first();
        $webConfiguration->title = $data['title'];
        $webConfiguration->description = $data['description'];
        if ($data['logo']) {
            $webConfiguration->logo = $this->updateLogo($webConfiguration->logo, $data['logo']);
        }
        $webConfiguration->save();

        return $webConfiguration;
    }

    private function updateLogo($oldLogo, $newLogo)
    {
        if ($oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        return $newLogo->store('assets/web-configurations', 'public');
    }
}
