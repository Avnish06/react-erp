<?php
$file = 'resources/views/employee/profile.blade.php';
$content = file_get_contents($file);

$new_section = '
    <!-- Personal Details Section -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">Personal Details & Documents</h3>
        </div>
        <div class="panel-body">
            <form action="{{ route(\'employee.profile.details\') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Father\'s Name</label>
                        <input type="text" name="father_name" class="form-input" value="{{ old(\'father_name\', $user->detail->father_name ?? \'\') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mother\'s Name</label>
                        <input type="text" name="mother_name" class="form-input" value="{{ old(\'mother_name\', $user->detail->mother_name ?? \'\') }}">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Father\'s Occupation</label>
                        <input type="text" name="father_occupation" class="form-input" value="{{ old(\'father_occupation\', $user->detail->father_occupation ?? \'\') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-input" value="{{ old(\'bank_name\', $user->detail->bank_name ?? \'\') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Bank Account Number</label>
                        <input type="text" name="bank_account_no" class="form-input" value="{{ old(\'bank_account_no\', $user->detail->bank_account_no ?? \'\') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bank IFSC Code</label>
                        <input type="text" name="bank_ifsc" class="form-input" value="{{ old(\'bank_ifsc\', $user->detail->bank_ifsc ?? \'\') }}">
                    </div>
                </div>

                <h4 style="margin-top:20px; margin-bottom:15px; color:var(--text-main); font-size:16px;">Documents</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">10th Marksheet @if(isset($user->detail->marksheet_10th_path)) <a href="{{ asset(\'storage/\'.$user->detail->marksheet_10th_path) }}" target="_blank" style="font-size:12px; color:var(--primary);">(View Current)</a> @endif</label>
                        <input type="file" name="marksheet_10th" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="form-group">
                        <label class="form-label">12th Marksheet @if(isset($user->detail->marksheet_12th_path)) <a href="{{ asset(\'storage/\'.$user->detail->marksheet_12th_path) }}" target="_blank" style="font-size:12px; color:var(--primary);">(View Current)</a> @endif</label>
                        <input type="file" name="marksheet_12th" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Passport Size Photo @if(isset($user->detail->passport_photo_path)) <a href="{{ asset(\'storage/\'.$user->detail->passport_photo_path) }}" target="_blank" style="font-size:12px; color:var(--primary);">(View Current)</a> @endif</label>
                        <input type="file" name="passport_photo" class="form-input" accept=".jpg,.jpeg,.png">
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary"><i class=\'bx bx-save\'></i> Save Details</button>
                </div>
            </form>
        </div>
    </div>
';

$search = '    <!-- Change Password Section -->';
if (strpos($content, $search) !== false) {
    $content = str_replace($search, $new_section . "\n" . $search, $content);
    file_put_contents($file, $content);
    echo "Updated profile.blade.php\n";
} else {
    echo "Could not find target in profile.blade.php\n";
}
