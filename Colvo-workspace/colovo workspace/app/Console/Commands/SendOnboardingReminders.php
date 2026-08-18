<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Notifications\GeneralNotification;

class SendOnboardingReminders extends Command
{
    protected $signature = 'notify:onboarding';
    protected $description = 'Fetch pending onboarding from ERP and notify employees whose document verification is not complete.';

    public function handle()
    {
        $erpUrl = config('services.erp.url', env('ERP_URL', 'http://127.0.0.1:5000'));
        try {
            $response = Http::timeout(10)->get("{$erpUrl}/api/onboarding");
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $emp) {
                        $step1 = is_string($emp['step_1_docs']) ? json_decode($emp['step_1_docs'], true) : $emp['step_1_docs'];
                        $isVerified = true;
                        if (is_array($step1)) {
                            foreach ($step1 as $doc => $status) {
                                if ($status !== true && $status !== 1 && $status !== '1') {
                                    $isVerified = false;
                                    break;
                                }
                            }
                        } else {
                            $isVerified = false;
                        }
                        if (!$isVerified) {
                            $user = User::where('email', $emp['email'])->first();
                            if ($user) {
                                $user->notify(new GeneralNotification([
                                    'title'   => 'Document Verification Pending',
                                    'message' => 'Your document verification has not yet been completed. Please check your onboarding checklist.',
                                    'type'    => 'document',
                                    'url'     => '/employee/joining-document',
                                ]));
                                $this->info("Notified {$user->email}");
                            }
                        }
                    }
                }
                return Command::SUCCESS;
            } else {
                $this->error("Failed to fetch from ERP.");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
