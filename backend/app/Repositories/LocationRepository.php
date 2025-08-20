<?php
namespace App\Repositories;

use App\Models\Location;

class LocationRepository
{
    public function getAll($perPage = 10)
    {
        return Location::paginate($perPage);
    }

    public function findById($id)
    {
        return Location::findOrFail($id);
    }

    public function create(array $data)
    {
        return Location::create($data);
    }

    public function update($id, array $data)
    {
        $location = Location::findOrFail($id);
        $location->update($data);
        return $location;
    }

    public function delete($id)
    {
        $location = Location::findOrFail($id);
        return $location->delete();
    }
}
