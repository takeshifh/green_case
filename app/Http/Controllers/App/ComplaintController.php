<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Auth;

use App\Models\Complaint;
use App\Models\ContaminationType;
use App\Models\Channel;
use App\Services\ImageUpload;

class ComplaintController extends Controller
{
    public function create()
    {
        $default_latitude = -12.0560257;
        $default_longitude = -77.0844226;
        $contamination_types = ContaminationType::all();

        return view('app.complaints.create', compact(
            'contamination_types', 'default_latitude', 'default_longitude'
        ));
    }

    public function store(ImageUpload $image_uploader)
    {
        $this->validate(request(), [
            'contamination_type' => 'required',
            'latitude'           => 'required',
            'longitude'          => 'required',
            'files'              => 'required',
            'files.*'            => 'image'
        ]);

        $citizen = Auth::guard('web')->user();

        $complaint = Complaint::create([
            'citizen_id'            => $citizen->id,
            'type_contamination_id' => request('contamination_type'),
            'type_communication_id' => Channel::FACEBOOK,
            'complaint_status_id'   => Complaint::COMPLETED,
            'latitude'              => request('latitude'),
            'longitude'             => request('longitude'),
            'district_id'           => 1,
            'commentary'            => request('commentary')
        ]);

        $files = request('files');
        foreach ($files as $key => $image) {
            $filename = "img/reclamos/reclamo_{$complaint->id}_{$key}";
            $path = $image_uploader->save($image, $filename);

            $complaint->images()->create(['img' => $path]);
        }

        return redirect()->route('complaint.index')
            ->with('message', 'Gracias por registrar tu reclamo. Te enviaremos un correo sobre las actualizaciones de tu reclamo');
    }
}
