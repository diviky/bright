<div class="card">
    <div class="card-header">
        <h3 class="card-title">Change Password</h3>
    </div>

    <div class="card-body p-6 form-float">
        <form method="POST" action="{{ url('account/password') }}" data-reset="true" easysubmit>
            @csrf
            <input type="hidden" name="username" value="{{ $user->email }}" />
            <input type="hidden" name="email" value="{{ $user->email }}" />

            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" autocomplete="current-password" class="form-control" name="oldpassword"
                    value="" required />
            </div>

            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" autocomplete="new-password" class="form-control" name="password" value=""
                    required />
            </div>

            <div class="form-group">
                <label class="form-label">New Password Confirmation</label>
                <input type="password" autocomplete="new-password" class="form-control" name="password_confirm"
                    value="" required />
            </div>

            <div class="form-group mb-0">
                <button type="submit" class="btn btn-primary btn-block"> Change Password </button>
            </div>
        </form>

    </div>
</div>
