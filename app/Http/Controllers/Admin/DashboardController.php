<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Activity, Project, Trainer, ContactMessage, TrainerApplication, ExpertConsultation, Statistics};

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'stats' => [
                'annual_programs' => Activity::count(),
                'projects' => Project::count(),
                'trainers' => Trainer::count(),
                'pending_contacts' => ContactMessage::where('is_read', false)->count(),
                'pending_applications' => TrainerApplication::where('status', 'pending')->count(),
                'pending_consultations' => ExpertConsultation::where('status', 'pending')->count(),
            ],
            'statistics' => Statistics::first(),
            'recent_contacts' => ContactMessage::latest()->take(5)->get(),
            'recent_applications' => TrainerApplication::latest()->take(5)->get(),
        ]);
    }
}
