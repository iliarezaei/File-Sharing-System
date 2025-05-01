<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Secure File Sharing</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
            <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8fafc;
        }
        .hero {
            padding: 80px 0;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }
        .feature-icon {
            font-size: 3rem;
            color: #6366f1;
            margin-bottom: 1rem;
        }
        .cta-section {
            padding: 60px 0;
            background-color: #f1f5f9;
        }
            </style>
    </head>
<body class="antialiased">
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
            </a>
            
            <div class="d-flex">
                @if (Route::has('login'))
                    <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-primary">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Log in</a>
                        @endauth
                    </div>
                        @endif
            </div>
        </div>
                </nav>
    
    <section class="hero text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Secure File Sharing Made Simple</h1>
            <p class="lead mb-5">Upload, manage, and share your files with others in just a few clicks.</p>
            @guest
                <a href="{{ route('login') }}" class="btn btn-light btn-lg px-5 py-3">Get Started</a>
            @else
                <a href="{{ url('/dashboard') }}" class="btn btn-light btn-lg px-5 py-3">Go to Dashboard</a>
            @endguest
        </div>
    </section>
    
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose Our Platform?</h2>
            
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h3>5GB Free Storage</h3>
                    <p class="text-muted">Every user gets 5GB of free storage space to upload their important files.</p>
                </div>
                
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <h3>Easy Sharing</h3>
                    <p class="text-muted">Share your files with other users via email in just a few clicks.</p>
                </div>
                
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Secure Access</h3>
                    <p class="text-muted">Control who has access to your files and revoke access at any time.</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="cta-section text-center">
        <div class="container">
            <h2 class="mb-4">Ready to get started?</h2>
            <p class="lead mb-4">Sign up now and get your 5GB of free storage!</p>
            @guest
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5">Sign Up / Login</a>
            @else
                <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-lg px-5">Go to Dashboard</a>
            @endguest
        </div>
    </section>
    
    <footer class="py-4 bg-dark text-white">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
