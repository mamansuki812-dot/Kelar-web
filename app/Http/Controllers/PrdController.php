<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;

class PrdController extends Controller
{
    public function export()
    {
        $pdf = Pdf::loadView('prd.index');
        return $pdf->download('PRD-KELAR-POS-v1.0.pdf');
    }
}
