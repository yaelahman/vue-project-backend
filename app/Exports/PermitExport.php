<?php

namespace App\Exports;

use App\Invoice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PermitExport implements FromView

{
    protected $data;
    protected $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function view(): View
    {

        if ($this->type == 2) {
            $view = 'exports.izin_hari';
        } elseif ($this->type == 3) {
            $view = 'exports.izin_cuti';
        } else {
            $view = 'exports.izin_jam';
        }
        return view($view, $this->data);
    }
}
