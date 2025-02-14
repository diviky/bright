<div class="card">
    <div class="card-header">
        <h3 class="card-title">Profile</h3>
    </div>

    <div class="card-body p-6 form-float">

        <form method="POST" action="{{ url('account') }}" easysubmit>
            @csrf

            <div class="avatar-upload">
                <div class="avatar-edit">
                    <input type='file' name="avatar" id="imageUpload" accept=".png, .jpg, .jpeg" />
                    <label for="imageUpload"></label>
                </div>
                <div class="avatar-preview">
                    <div id="imagePreview" style="background-image: url({{ asset($user->avatar) }});">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="name" value="{{ $user->name }}" required />
            </div>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" value="{{ $user->email }}" required />
            </div>
            <div class="form-group">
                <label class="form-label">Mobile Number</label>
                <input type="tel" class="form-control" name="mobile" value="{{ $user->mobile }}" required />
            </div>
            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-control" name="password" value="" required />
            </div>
            <div class="form-group mb-0">
                <button type="submit" class="btn btn-primary btn-block"> Save Profile </button>
            </div>
        </form>

    </div>
</div>
