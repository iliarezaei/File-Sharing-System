@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Share File') }}: {{ $file->name }}</span>
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-secondary">{{ __('Back to Dashboard') }}</a>
                </div>

                <div class="card-body">
                    <div class="file-info mb-4">
                        <p><strong>{{ __('File Name') }}:</strong> {{ $file->name }}</p>
                        <p><strong>{{ __('File Size') }}:</strong> {{ $file->formatted_size }}</p>
                        <p><strong>{{ __('File Type') }}:</strong> {{ $file->mime_type }}</p>
                        <p><strong>{{ __('Uploaded') }}:</strong> {{ $file->created_at->format('d M Y, H:i') }}</p>
                    </div>

                    <form action="{{ route('files.share', $file->id) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Share with Email') }}</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" required placeholder="Enter email address">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">{{ __('The user must be registered on the platform.') }}</small>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('Share File') }}</button>
                    </form>

                    <hr>

                    <h5 class="mb-3">{{ __('Currently Shared With') }}</h5>
                    
                    @if($sharedWithUsers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Shared On') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sharedWithUsers as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->pivot->created_at->format('d M Y, H:i') }}</td>
                                            <td>
                                                <form action="{{ route('files.share.remove', ['file' => $file->id, 'user' => $user->id]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Remove Access" onclick="return confirm('Are you sure you want to remove access for this user?')">
                                                        <i class="fas fa-trash"></i> {{ __('Remove') }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">{{ __('This file is not shared with anyone yet.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 