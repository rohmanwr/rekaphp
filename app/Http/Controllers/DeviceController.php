<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $devices = Device::when($search, function ($query, $search) {
            return $query->where('kode_device', 'like', "%{$search}%")
                ->orWhere('nama_device', 'like', "%{$search}%");
        })->latest()->paginate(10);

        return view('device.index', compact('devices', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_device' => 'required|string|max:50|unique:devices,kode_device',
            'nama_device' => 'required|string|max:255',
        ]);

        Device::create([
            'kode_device' => $request->kode_device,
            'nama_device' => $request->nama_device,
        ]);

        return redirect()->route('device.index')->with('success', 'Master Device berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        $request->validate([
            'kode_device' => 'required|string|max:50|unique:devices,kode_device,' . $id,
            'nama_device' => 'required|string|max:255',
        ]);

        $device->update([
            'kode_device' => $request->kode_device,
            'nama_device' => $request->nama_device,
        ]);

        return redirect()->route('device.index')->with('success', 'Master Device berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $device->delete();

        return redirect()->route('device.index')->with('success', 'Master Device berhasil dihapus!');
    }
}
