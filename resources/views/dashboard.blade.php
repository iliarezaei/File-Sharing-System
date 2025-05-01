@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Storage Usage') }}</div>

                <div class="card-body">
                    <div class="storage-info mb-3">
                        <p>
                            <strong>Used Space:</strong> {{ number_format($usedSpace / (1024 * 1024 * 1024), 2) }} GB of 5 GB
                            <span class="text-muted">({{ number_format($percentUsed, 2) }}%)</span>
                        </p>
                    </div>
                    
                    <div class="storage-bar progress">
                        <div class="progress-bar {{ $percentUsed > 90 ? 'bg-danger' : ($percentUsed > 70 ? 'bg-warning' : 'bg-success') }}" 
                            role="progressbar" 
                            style="width: {{ $percentUsed }}%" 
                            aria-valuenow="{{ $percentUsed }}" 
                            aria-valuemin="0" 
                            aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Upload File') }}</span>
                </div>

                <div class="card-body">
                    <form action="{{ route('files.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">{{ __('Select File') }}</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" required>
                            @error('file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Maximum file size: 1GB</small>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">{{ __('My Files') }}</div>

                <div class="card-body">
                    @if($files->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Size') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Uploaded') }}</th>
                                        <th>{{ __('Shared With') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($files as $file)
                                        <tr>
                                            <td>{{ $file->name }}</td>
                                            <td>{{ $file->formatted_size }}</td>
                                            <td>{{ $file->mime_type }}</td>
                                            <td>{{ $file->created_at->diffForHumans() }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $file->sharedWithUsers->count() }}</span>
                                            </td>
                                            <td class="d-flex">
                                                <a href="{{ route('files.download', $file->id) }}" class="btn btn-sm btn-success me-1" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a href="{{ route('files.share', $file->id) }}" class="btn btn-sm btn-primary me-1" title="Share">
                                                    <i class="fas fa-share-alt"></i>
                                                </a>
                                                <form action="{{ route('files.delete', $file->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this file?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">{{ __('No files uploaded yet.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Files Shared With Me') }}</div>

                <div class="card-body">
                    @if($sharedFiles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Size') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Owner') }}</th>
                                        <th>{{ __('Shared At') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sharedFiles as $file)
                                        <tr>
                                            <td>{{ $file->name }}</td>
                                            <td>{{ $file->formatted_size }}</td>
                                            <td>{{ $file->mime_type }}</td>
                                            <td>{{ $file->user->name }}</td>
                                            <td>{{ $file->pivot->created_at->diffForHumans() }}</td>
                                            <td>
                                                <a href="{{ route('files.download', $file->id) }}" class="btn btn-sm btn-success" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">{{ __('No files have been shared with you.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Add any client-side functionality here
    document.addEventListener('DOMContentLoaded', function() {
        // For example, we could add file upload progress with AJAX here
    });
</script>
@endsection 